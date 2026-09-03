<?php

namespace App\Http\Controllers;

use App\Models\JenisKendaraan;
use Illuminate\Http\Request;

class JenisKendaraanController extends Controller
{
    public function index(Request $request)
    {
        $jenisKendaraans = JenisKendaraan::orderBy('nama_merek')->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = JenisKendaraan::find($request->query('edit'));
        }

        return view('kendaraan.jenis-kendaraan.index', compact('jenisKendaraans', 'edit'));
    }

    /**
     * Simpan data jenis kendaraan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_merek' => 'required|string|max:255|unique:jenis_kendaraan,nama_merek',
        ], [
            'nama_merek.required' => 'Nama merek wajib diisi.',
            'nama_merek.unique'   => 'Nama merek sudah terdaftar.',
        ]);

        JenisKendaraan::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Jenis kendaraan berhasil ditambahkan.']);
        }

        return redirect()->route('jenis-kendaraan.index')
            ->with('success', 'Jenis kendaraan berhasil ditambahkan.');
    }

    /**
     * Update data jenis kendaraan
     */
    public function update(Request $request, $id)
    {
        $jenisKendaraan = JenisKendaraan::findOrFail($id);

        $validated = $request->validate([
            'nama_merek' => 'required|string|max:255|unique:jenis_kendaraan,nama_merek,' . $jenisKendaraan->id,
        ], [
            'nama_merek.required' => 'Nama merek wajib diisi.',
            'nama_merek.unique'   => 'Nama merek sudah terdaftar.',
        ]);

        $jenisKendaraan->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Jenis kendaraan berhasil diperbarui.']);
        }

        return redirect()->route('jenis-kendaraan.index')
            ->with('success', 'Jenis kendaraan berhasil diperbarui.');
    }

    /**
     * Hapus data jenis kendaraan
     */
    public function destroy($id)
    {
        $jenisKendaraan = JenisKendaraan::findOrFail($id);

        if ($jenisKendaraan->kendaraan()->exists()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Jenis kendaraan tidak dapat dihapus karena masih digunakan pada data kendaraan.'], 422);
            }
            return redirect()->route('jenis-kendaraan.index')
                ->with('error', 'Jenis kendaraan tidak dapat dihapus karena masih digunakan pada data kendaraan.');
        }

        $jenisKendaraan->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Jenis kendaraan berhasil dihapus.']);
        }

        return redirect()->route('jenis-kendaraan.index')
            ->with('success', 'Jenis kendaraan berhasil dihapus.');
    }
}