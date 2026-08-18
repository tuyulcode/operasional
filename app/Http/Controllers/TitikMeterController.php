<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\TitikMeter;
use App\Support\NumberFormatter;
use Illuminate\Http\Request;

class TitikMeterController extends Controller
{
    protected function validatedData(Request $request): array
    {
        $request->merge([
            'meter_faktor' => NumberFormatter::parseId($request->input('meter_faktor')) ?? 0,
            'tarif_harga' => NumberFormatter::parseId($request->input('tarif_harga')) ?? 0,
        ]);

        return $request->validate([
            'area_id' => 'required|exists:area,id',
            'nama' => 'required|string|max:100',
            'meter_faktor' => 'required|numeric|min:0',
            'tarif_harga' => 'required|numeric|min:0',
            'status' => 'required|in:aktif,nonaktif',
        ]);
    }

    public function index(Request $request)
    {
        $titikMeters = TitikMeter::with('area')->latest()->get();
        $areas = Area::latest()->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = TitikMeter::findOrFail($request->query('edit'));
        }

        return view('titik-meter.index', compact('titikMeters', 'areas', 'edit'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        TitikMeter::create($validated);

        return redirect()->route('titik-meter.index')
            ->with('success', 'Data titik meter berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $titikMeter = TitikMeter::findOrFail($id);

        $validated = $this->validatedData($request);

        $titikMeter->update($validated);

        return redirect()->route('titik-meter.index')
            ->with('success', 'Data titik meter berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $titikMeter = TitikMeter::findOrFail($id);

        if ($titikMeter->tagihanAir()->exists()) {
            return redirect()->route('titik-meter.index')
                ->with('error', 'Titik meter tidak dapat dihapus karena sudah digunakan pada tagihan air.');
        }

        $titikMeter->delete();

        return redirect()->route('titik-meter.index')
            ->with('success', 'Data titik meter berhasil dihapus.');
    }
}
