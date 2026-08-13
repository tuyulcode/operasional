<?php

namespace App\Http\Controllers;

use App\Models\PemegangKendaraan;
use App\Models\PemakaianEtoll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtollController extends Controller
{
    public function index(Request $request)
    {
        $pemakaianEtolls = PemakaianEtoll::with('pemegangKendaraan')->latest('tanggal')->latest('id')->get();
        $pemegangKendaraans = PemegangKendaraan::orderBy('nama')->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = PemakaianEtoll::findOrFail($request->query('edit'));
        }

        return view('pemakaian-etoll.index', compact('pemakaianEtolls', 'pemegangKendaraans', 'edit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pemegang_kendaraan_id' => 'required|exists:pemegang_kendaraan,id',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric|min:0',
        ]);

        $validated['dicatat_oleh'] = Auth::id();

        PemakaianEtoll::create($validated);

        return redirect()->route('pemakaian-etoll.index')
            ->with('success', 'Data pemakaian e-toll berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $pemakaianEtoll = PemakaianEtoll::findOrFail($id);

        $validated = $request->validate([
            'pemegang_kendaraan_id' => 'required|exists:pemegang_kendaraan,id',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric|min:0',
        ]);

        $pemakaianEtoll->update($validated);

        return redirect()->route('pemakaian-etoll.index')
            ->with('success', 'Data pemakaian e-toll berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pemakaianEtoll = PemakaianEtoll::findOrFail($id);
        $pemakaianEtoll->delete();

        return redirect()->route('pemakaian-etoll.index')
            ->with('success', 'Data pemakaian e-toll berhasil dihapus.');
    }
}