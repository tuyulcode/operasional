<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\TagihanAir;
use App\Models\TitikMeter;
use App\Support\NumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TagihanAirController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'input');

        if ($tab === 'rekapan') {
            $report = (new RekapanController)->buildReport($request);

            return view('tagihan-air.index', [
                'tab' => 'rekapan',
                'report' => $report,
            ]);
        }

        $query = TagihanAir::with('titikMeter.area')->latest('periode');

        if ($request->filled('area_id')) {
            $query->whereHas('titikMeter', fn ($q) => $q->where('area_id', $request->area_id));
        }

        if ($request->filled('bulan') && preg_match('/^\d{4}-\d{2}$/', $request->bulan)) {
            [$year, $month] = array_map('intval', explode('-', $request->bulan));
            $query->whereYear('periode', $year)->whereMonth('periode', $month);
        }

        $tagihanAirs = $query->get();

        $areas = Area::latest()->get();
        $titikMeters = TitikMeter::latest()->get();

        // Map side: untuk mengisi Meter Lalu otomatis dari periode sebelumnya
        $meterMap = [];
        foreach (TagihanAir::orderBy('periode')->get(['titik_meter_id', 'periode', 'meter_ini']) as $t) {
            $meterMap[$t->titik_meter_id][$t->periode->format('Y-m')] = (float) $t->meter_ini;
        }

        $edit = null;
        if ($request->has('edit')) {
            $edit = TagihanAir::findOrFail($request->query('edit'));
        }

        return view('tagihan-air.index', compact(
            'tagihanAirs', 'areas', 'titikMeters', 'meterMap', 'edit', 'tab'
        ));
    }

    protected function prevMeter($titikMeterId, $periode): ?float
    {
        $meter = TagihanAir::where('titik_meter_id', $titikMeterId)
            ->where('periode', '<', $periode)
            ->orderByDesc('periode')
            ->value('meter_ini');

        return $meter === null ? null : (float) $meter;
    }

    protected function parseInputs(Request $request): void
    {
        $request->merge([
            'meter_ini' => NumberFormatter::parseId($request->input('meter_ini')) ?? 0,
            'meter_faktor' => NumberFormatter::parseId($request->input('meter_faktor')) ?? 0,
            'tarif' => NumberFormatter::parseId($request->input('tarif')) ?? 0,
        ]);
    }

    protected function resolveMeterLalu($titikMeterId, $periode, $manual): ?float
    {
        $prev = $this->prevMeter($titikMeterId, $periode);

        if ($prev !== null) {
            return $prev;
        }

        return NumberFormatter::parseId($manual);
    }

    protected function validateData(Request $request, $id = null)
    {
        $periode = $request->input('periode');
        $periodeDate = $periode ? date('Y-m-01', strtotime($periode.'-01')) : null;

        return $request->validate([
            'titik_meter_id' => [
                'required',
                'exists:titik_meter,id',
                Rule::unique('tagihan_air')
                    ->where(fn ($q) => $q->where('periode', $periodeDate))
                    ->ignore($id),
            ],
            'periode' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'meter_ini' => 'required|numeric|min:0',
            'meter_faktor' => 'required|numeric|min:0',
            'tarif' => 'required|numeric|min:0',
            'meter_lalu' => 'nullable|numeric|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    }

    protected function saveFoto(Request $request)
    {
        if (! $request->hasFile('foto')) {
            return null;
        }

        $name = time().'_'.uniqid().'.'.$request->file('foto')->getClientOriginalExtension();
        $request->file('foto')->move(public_path('uploads/tagihan-air'), $name);

        return 'uploads/tagihan-air/'.$name;
    }

    protected function deleteFoto($path)
    {
        if ($path && file_exists(public_path($path))) {
            unlink(public_path($path));
        }
    }

    public function store(Request $request)
    {
        $this->parseInputs($request);
        $validated = $this->validateData($request);

        $periodeDate = date('Y-m-01', strtotime($validated['periode'].'-01'));
        $meterLalu = $this->resolveMeterLalu($validated['titik_meter_id'], $periodeDate, $request->input('meter_lalu'));

        if ($meterLalu === null) {
            return back()->withInput()->withErrors([
                'meter_lalu' => 'Belum ada histori periode sebelumnya di sistem. Isi Meter Lalu secara manual berdasarkan data awal (wajib).',
            ]);
        }

        $pemakaian = ($validated['meter_ini'] - $meterLalu) * $validated['meter_faktor'];
        $jumlah = $pemakaian * $validated['tarif'];

        TagihanAir::create([
            'titik_meter_id' => $validated['titik_meter_id'],
            'periode' => $periodeDate,
            'meter_lalu' => $meterLalu,
            'meter_ini' => $validated['meter_ini'],
            'meter_faktor' => $validated['meter_faktor'],
            'tarif' => $validated['tarif'],
            'pemakaian' => $pemakaian,
            'jumlah' => $jumlah,
            'foto' => $this->saveFoto($request),
        ]);

        return redirect()->route('tagihan-air.index', request()->only(['area_id', 'bulan']))
            ->with('success', 'Tagihan air berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $tagihan = TagihanAir::findOrFail($id);
        $this->parseInputs($request);
        $validated = $this->validateData($request, $id);

        $periodeDate = date('Y-m-01', strtotime($validated['periode'].'-01'));
        $meterLalu = $this->resolveMeterLalu($validated['titik_meter_id'], $periodeDate, $request->input('meter_lalu'));

        if ($meterLalu === null) {
            $meterLalu = NumberFormatter::parseId($tagihan->meter_lalu) ?? 0;
        }

        $pemakaian = ($validated['meter_ini'] - $meterLalu) * $validated['meter_faktor'];
        $jumlah = $pemakaian * $validated['tarif'];

        $data = [
            'titik_meter_id' => $validated['titik_meter_id'],
            'periode' => $periodeDate,
            'meter_lalu' => $meterLalu,
            'meter_ini' => $validated['meter_ini'],
            'meter_faktor' => $validated['meter_faktor'],
            'tarif' => $validated['tarif'],
            'pemakaian' => $pemakaian,
            'jumlah' => $jumlah,
        ];

        if ($request->hasFile('foto')) {
            $this->deleteFoto($tagihan->foto);
            $data['foto'] = $this->saveFoto($request);
        }

        $tagihan->update($data);

        return redirect()->route('tagihan-air.index', request()->only(['area_id', 'bulan']))
            ->with('success', 'Tagihan air berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tagihan = TagihanAir::findOrFail($id);

        $this->deleteFoto($tagihan->foto);
        $tagihan->delete();

        return redirect()->route('tagihan-air.index', request()->only(['area_id', 'bulan']))
            ->with('success', 'Tagihan air berhasil dihapus.');
    }
}
