<?php

namespace App\Http\Controllers;

use App\Models\PengaturanLaporan;
use Illuminate\Http\Request;

class PengaturanLaporanController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanLaporan::current();

        return view('pengaturan-laporan.index', compact('pengaturan'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'foto_meter_lebar_mm' => 'required|numeric|min:5|max:120',
            'foto_meter_tinggi_mm' => 'required|numeric|min:5|max:120',
        ], [
            'foto_meter_lebar_mm.required' => 'Lebar maksimal foto meter wajib diisi.',
            'foto_meter_lebar_mm.numeric' => 'Lebar maksimal foto meter harus berupa angka.',
            'foto_meter_lebar_mm.min' => 'Lebar maksimal foto meter minimal 5 mm.',
            'foto_meter_lebar_mm.max' => 'Lebar maksimal foto meter maksimal 120 mm.',
            'foto_meter_tinggi_mm.required' => 'Tinggi maksimal foto meter wajib diisi.',
            'foto_meter_tinggi_mm.numeric' => 'Tinggi maksimal foto meter harus berupa angka.',
            'foto_meter_tinggi_mm.min' => 'Tinggi maksimal foto meter minimal 5 mm.',
            'foto_meter_tinggi_mm.max' => 'Tinggi maksimal foto meter maksimal 120 mm.',
        ]);

        $pengaturan = PengaturanLaporan::query()->firstOrCreate([], [
            'foto_meter_lebar_mm' => PengaturanLaporan::DEFAULT_FOTO_LEBAR_MM,
            'foto_meter_tinggi_mm' => PengaturanLaporan::DEFAULT_FOTO_TINGGI_MM,
        ]);

        $pengaturan->update($validated);

        return redirect()->route('pengaturan-laporan.index')
            ->with('success', 'Pengaturan ukuran gambar laporan berhasil diperbarui.');
    }
}
