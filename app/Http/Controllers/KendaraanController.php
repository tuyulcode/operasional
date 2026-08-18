<?php

namespace App\Http\Controllers;

use App\Models\JenisKendaraan;
use App\Models\Kendaraan;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    /**
     * Tampilkan daftar kendaraan (index sekaligus handle form edit via query ?edit=id)
     */
    public function index(Request $request)
    {
        $kendaraans = Kendaraan::with('jenisKendaraan')->orderBy('plat_nomor')->get();
        $jenisKendaraans = JenisKendaraan::orderBy('nama_merek')->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = Kendaraan::find($request->query('edit'));
        }

        return view('kendaraan.data-kendaraan.index', compact('kendaraans', 'jenisKendaraans', 'edit'));
    }

    /**
     * Simpan data kendaraan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_kendaraan_id' => 'required|exists:jenis_kendaraan,id',
            'plat_nomor'         => 'required|string|max:255|unique:kendaraan,plat_nomor',
            'nama_jenis'         => 'required|string|max:255',
            'unit'               => 'nullable|in:Unit 1 & 2,Unit 9',
        ], [
            'jenis_kendaraan_id.required' => 'Merek kendaraan wajib dipilih.',
            'jenis_kendaraan_id.exists'   => 'Merek kendaraan tidak valid.',
            'plat_nomor.required'         => 'Plat nomor wajib diisi.',
            'plat_nomor.unique'           => 'Plat nomor sudah terdaftar.',
            'nama_jenis.required'         => 'Jenis kendaraan wajib dipilih.',
            'unit.in'                     => 'Unit yang dipilih tidak valid.',
        ]);

        Kendaraan::create($validated);

        return redirect()->route('kendaraan.index')
            ->with('success', 'Data kendaraan berhasil ditambahkan.');
    }

    /**
     * Update data kendaraan
     */
    public function update(Request $request, $id)
    {
        $kendaraan = Kendaraan::findOrFail($id);

        $validated = $request->validate([
            'jenis_kendaraan_id' => 'required|exists:jenis_kendaraan,id',
            'plat_nomor'         => 'required|string|max:255|unique:kendaraan,plat_nomor,' . $kendaraan->id,
            'nama_jenis'         => 'required|string|max:255',
            'unit'               => 'nullable|in:Unit 1 & 2,Unit 9',
        ], [
            'jenis_kendaraan_id.required' => 'Merek kendaraan wajib dipilih.',
            'jenis_kendaraan_id.exists'   => 'Merek kendaraan tidak valid.',
            'plat_nomor.required'         => 'Plat nomor wajib diisi.',
            'plat_nomor.unique'           => 'Plat nomor sudah terdaftar.',
            'nama_jenis.required'         => 'Jenis kendaraan wajib dipilih.',
            'unit.in'                     => 'Unit yang dipilih tidak valid.',
        ]);

        $kendaraan->update($validated);

        return redirect()->route('kendaraan.index')
            ->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    /**
     * Hapus data kendaraan
     */
    public function destroy($id)
    {
        $kendaraan = Kendaraan::findOrFail($id);

        // Cegah hapus jika masih dipakai di tabel pemakaian_bbm
        if ($kendaraan->pemakaianBbm()->exists()) {
            return redirect()->route('kendaraan.index')
                ->with('error', 'Kendaraan tidak dapat dihapus karena masih memiliki riwayat pemakaian BBM.');
        }

        $kendaraan->delete();

        return redirect()->route('kendaraan.index')
            ->with('success', 'Data kendaraan berhasil dihapus.');
    }
}