<?php

namespace App\Http\Controllers;

use App\Models\Penandatangan;
use Illuminate\Http\Request;

class PenandatanganController extends Controller
{
    public function index()
    {
        $penandatangan = Penandatangan::orderBy('id')->get();

        return view('penandatangan.index', compact('penandatangan'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|array|min:2',
            'nama.*' => 'nullable|string|max:150',
            'tempat' => 'nullable|string|max:100',
            'tanggal_cetak' => 'nullable|date',
        ]);

        $rows = Penandatangan::orderBy('id')->get();

        foreach ($rows as $row) {
            $row->update([
                'nama' => $validated['nama'][$row->id] ?? null,
                'tempat' => $validated['tempat'] ?? null,
                'tanggal_cetak' => $validated['tanggal_cetak'] ?? null,
            ]);
        }

        return redirect()->route('penandatangan.index')
            ->with('success', 'Data penandatangan berhasil diperbarui.');
    }
}
