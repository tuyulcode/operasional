<?php

namespace App\Http\Controllers;

use App\Models\Ppn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PpnController extends Controller
{
    public function index()
    {
        $ppns = Ppn::latest()->paginate(15);
        return view('ppn.index', compact('ppns'));
    }

    public function store(Request $request)
    {
        // [DIUBAH] sekarang ada 2 persentase:
        // - persentase           : Persentase Laporan (tampil di rekapan)
        // - persentase_realisasi : Persentase Perhitungan (dipakai menghitung)
        $validated = $request->validate([
            'persentase' => 'required|numeric|min:0|max:100',
            'persentase_realisasi' => 'required|numeric|min:0|max:100',
        ]);

        $status = Ppn::count() === 0 ? 'aktif' : 'nonaktif';

        Ppn::create([
            'persentase' => $validated['persentase'],
            'persentase_realisasi' => $validated['persentase_realisasi'], // [BARU]
            'status' => $status,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data PPN berhasil ditambahkan.']);
        }

        return redirect()->route('ppn.index')
            ->with('success', 'Data PPN berhasil ditambahkan.');
    }

    // [BARU] Edit data PPN (persentase laporan & persentase perhitungan).
    // Status aktif/nonaktif TIDAK diubah di sini (tetap lewat tombol Aktifkan).
    public function update(Request $request, $id)
    {
        // Sama seperti hapus: petugas tidak boleh mengubah data PPN
        if (Auth::user()->role === 'petugas') {
            abort(403, 'Anda tidak memiliki akses untuk mengubah data PPN.');
        }

        $ppn = Ppn::findOrFail($id);

        $validated = $request->validate([
            'persentase' => 'required|numeric|min:0|max:100',
            'persentase_realisasi' => 'required|numeric|min:0|max:100',
        ]);

        $ppn->update([
            'persentase' => $validated['persentase'],
            'persentase_realisasi' => $validated['persentase_realisasi'],
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data PPN berhasil diperbarui.']);
        }

        return redirect()->route('ppn.index')
            ->with('success', 'Data PPN berhasil diperbarui.');
    }

    public function activate(Request $request, $id)
    {
        $ppn = Ppn::findOrFail($id);

        DB::transaction(function () use ($ppn) {
            Ppn::query()->update(['status' => 'nonaktif']);
            $ppn->update(['status' => 'aktif']);
        });

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "PPN {$ppn->persentase}% berhasil diaktifkan."]);
        }

        return redirect()->route('ppn.index')
            ->with('success', "PPN {$ppn->persentase}% berhasil diaktifkan.");
    }

    public function destroy(Request $request, $id)
    {
        $ppn = Ppn::findOrFail($id);

        DB::transaction(function () use ($ppn) {
            $ppn->delete();

            if (!Ppn::where('status', 'aktif')->exists() && Ppn::exists()) {
                Ppn::latest()->first()->update(['status' => 'aktif']);
            }
        });

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data PPN berhasil dihapus.']);
        }

        return redirect()->route('ppn.index')
            ->with('success', 'Data PPN berhasil dihapus.');
    }
}