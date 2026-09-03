<?php

namespace App\Http\Controllers;

use App\Models\PemegangKendaraan;
use Illuminate\Http\Request;

class PemegangKendaraanController extends Controller
{
    public function index(Request $request)
    {
        $pemegangKendaraans = PemegangKendaraan::orderBy('nama')->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = PemegangKendaraan::findOrFail($request->query('edit'));
        }

        return view('pemegang-kendaraan.index', compact('pemegangKendaraans', 'edit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        PemegangKendaraan::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data pemegang kendaraan berhasil ditambahkan.']);
        }

        return redirect()->route('pemegang-kendaraan.index')
            ->with('success', 'Data pemegang kendaraan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $pemegangKendaraan = PemegangKendaraan::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
        ]);

        $pemegangKendaraan->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data pemegang kendaraan berhasil diperbarui.']);
        }

        return redirect()->route('pemegang-kendaraan.index')
            ->with('success', 'Data pemegang kendaraan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pemegangKendaraan = PemegangKendaraan::findOrFail($id);

        if ($pemegangKendaraan->pemakaianEtoll()->exists()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data tidak dapat dihapus karena sudah digunakan pada pemakaian e-toll.'], 422);
            }
            return redirect()->route('pemegang-kendaraan.index')
                ->with('error', 'Data tidak dapat dihapus karena sudah digunakan pada pemakaian e-toll.');
        }

        $pemegangKendaraan->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data pemegang kendaraan berhasil dihapus.']);
        }

        return redirect()->route('pemegang-kendaraan.index')
            ->with('success', 'Data pemegang kendaraan berhasil dihapus.');
    }
}