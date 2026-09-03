<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Ppn;
use App\Models\TagihanAir;
use App\Models\TagihanAirFoto;
use App\Models\TitikMeter;
use App\Support\NumberFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TagihanAirController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'input');

        if ($tab === 'rekapan') {
            $report = (new RekapanController)->buildReport($request);

            return view('tagihan-air.index', compact('tab', 'report'));
        }

        if ($tab === 'data') {
            $tagihanAirs = TagihanAir::with(['titikMeter.area', 'fotos'])->latest('periode')->paginate(25);

            return view('tagihan-air.index', compact('tab', 'tagihanAirs'));
        }

        $areas = Area::latest()->get();
        $titikMeters = TitikMeter::latest()->get();

        // Map side: 24 bulan terakhir saja (bukan SEMUA record)
        $batasWaktu = now()->subMonths(24)->startOfMonth();
        $meterMap = [];
        foreach (TagihanAir::where('periode', '>=', $batasWaktu)->orderBy('periode')->get(['titik_meter_id', 'periode', 'meter_ini']) as $t) {
            $meterMap[$t->titik_meter_id][$t->periode->format('Y-m')] = (float) $t->meter_ini;
        }

        $edit = null;
        if ($request->has('edit')) {
            $edit = TagihanAir::with('fotos', 'titikMeter.area')->findOrFail($request->query('edit'));
        }

        $ppnAktif = Ppn::where('status', 'aktif')->first();

        return view('tagihan-air.index', compact('tab', 'areas', 'titikMeters', 'meterMap', 'edit', 'ppnAktif'));
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
            'tarif' => 'required|numeric|gt:0',
            'ppn_persentase' => 'nullable|numeric|min:0|max:100',
            'ppn_nominal' => 'nullable|numeric|min:0',
            'meter_lalu' => 'nullable|numeric|min:0',
            'foto_meter' => [
                'nullable',
                'array',
                'max:10',
                function ($attribute, $value, $fail) use ($id) {
                    if ($id === null) {
                        return;
                    }

                    $existing = TagihanAir::withCount('fotos')->find($id);
                    if ($existing && $existing->fotos_count + count($value) > 10) {
                        $fail('Total foto maksimal 10 per transaksi (sudah ada '.$existing->fotos_count.' foto tersimpan).');
                    }
                },
            ],
            'foto_meter.*' => 'image|mimes:jpg,jpeg,png|max:5120',
        ]);
    }

    protected function saveFotos(Request $request, $tagihanId): void
    {
        if (! $request->hasFile('foto_meter')) {
            return;
        }

        foreach ($request->file('foto_meter') as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('foto-meter', 'public');

            TagihanAirFoto::create([
                'tagihan_air_id' => $tagihanId,
                'path_foto' => $path,
            ]);
        }
    }

    protected function deleteFotoFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (str_starts_with($path, 'uploads/')) {
            if (file_exists(public_path($path))) {
                unlink(public_path($path));
            }

            return;
        }

        Storage::disk('public')->delete($path);
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

        $titikMeter = TitikMeter::with('area')->find($validated['titik_meter_id']);
        $area = $titikMeter->area;
        $ppnAktif = Ppn::where('status', 'aktif')->first();

        $pemakaian = ($validated['meter_ini'] - $meterLalu) * $validated['meter_faktor'];
        $jumlahSebelumPpn = $pemakaian * $validated['tarif'];
        $ppnPersentase = $area->kena_ppn ? (float) ($ppnAktif->persentase ?? 0) : 0;
        $ppnNominal = round($jumlahSebelumPpn * $ppnPersentase / 100, 2);
        $jumlah = $jumlahSebelumPpn + $ppnNominal;

        $tagihan = TagihanAir::create([
            'titik_meter_id' => $validated['titik_meter_id'],
            'periode' => $periodeDate,
            'meter_lalu' => $meterLalu,
            'meter_ini' => $validated['meter_ini'],
            'meter_faktor' => $validated['meter_faktor'],
            'tarif' => $validated['tarif'],
            'pemakaian' => $pemakaian,
            'ppn_persentase' => $ppnPersentase,
            'ppn_nominal' => $ppnNominal,
            'jumlah' => $jumlah,
        ]);

        $this->saveFotos($request, $tagihan->id);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tagihan air berhasil ditambahkan.',
                'periode' => $validated['periode'],
            ]);
        }

        return redirect()->route('tagihan-air.index', request()->only(['tab']))
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

        $titikMeter = TitikMeter::with('area')->find($validated['titik_meter_id']);
        $area = $titikMeter->area;
        $ppnAktif = Ppn::where('status', 'aktif')->first();

        $pemakaian = ($validated['meter_ini'] - $meterLalu) * $validated['meter_faktor'];
        $jumlahSebelumPpn = $pemakaian * $validated['tarif'];
        $ppnPersentase = $area->kena_ppn ? (float) ($ppnAktif->persentase ?? 0) : 0;
        $ppnNominal = round($jumlahSebelumPpn * $ppnPersentase / 100, 2);
        $jumlah = $jumlahSebelumPpn + $ppnNominal;

        $data = [
            'titik_meter_id' => $validated['titik_meter_id'],
            'periode' => $periodeDate,
            'meter_lalu' => $meterLalu,
            'meter_ini' => $validated['meter_ini'],
            'meter_faktor' => $validated['meter_faktor'],
            'tarif' => $validated['tarif'],
            'pemakaian' => $pemakaian,
            'ppn_persentase' => $ppnPersentase,
            'ppn_nominal' => $ppnNominal,
            'jumlah' => $jumlah,
        ];

        $tagihan->update($data);

        $this->saveFotos($request, $tagihan->id);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tagihan air berhasil diperbarui.',
                'periode' => $validated['periode'],
            ]);
        }

        return redirect()->route('tagihan-air.index', request()->only(['tab']))
            ->with('success', 'Tagihan air berhasil diperbarui.');
    }

    public function destroyFoto(Request $request, $id)
    {
        $foto = TagihanAirFoto::findOrFail($id);
        $periode = $foto->tagihanAir->periode->format('Y-m');

        $this->deleteFotoFile($foto->path_foto);
        $foto->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Foto meter berhasil dihapus.',
                'periode' => $periode,
            ]);
        }

        return back()->with('success', 'Foto meter berhasil dihapus.');
    }

    public function destroy(Request $request, $id)
    {
        $tagihan = TagihanAir::findOrFail($id);
        $periode = $tagihan->periode->format('Y-m');

        foreach ($tagihan->fotos as $foto) {
            $this->deleteFotoFile($foto->path_foto);
        }
        $tagihan->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tagihan air berhasil dihapus.',
                'periode' => $periode,
            ]);
        }

        return redirect()->route('tagihan-air.index', request()->only(['tab']))
            ->with('success', 'Tagihan air berhasil dihapus.');
    }
}
