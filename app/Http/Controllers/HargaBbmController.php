<?php

namespace App\Http\Controllers;

use App\Models\HargaBbm;
use Illuminate\Http\Request;

class HargaBbmController extends Controller
{
    public function index()
    {
        $bensin = HargaBbm::where('jenis', 'bensin')->first();
        $solar = HargaBbm::where('jenis', 'solar')->first();

        return view('harga-bbm.index', compact('bensin', 'solar'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => 'required|in:bensin,solar',
            'harga_paiton' => 'required|numeric|min:0',
            'harga_luar_paiton' => 'required|numeric|min:0',
        ]);

        HargaBbm::updateOrCreate(
            ['jenis' => $validated['jenis']],
            $validated
        );

        return redirect()->route('harga-bbm.index')
            ->with('success', 'Data harga BBM ' . ucfirst($validated['jenis']) . ' berhasil disimpan.');
    }
}
