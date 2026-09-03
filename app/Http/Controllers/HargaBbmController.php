<?php

namespace App\Http\Controllers;

use App\Models\HargaBbm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HargaBbmController extends Controller
{
    public function index()
    {
        $riwayat = HargaBbm::orderByDesc('tanggal_berlaku')->paginate(15);
        $terakhir = HargaBbm::orderByDesc('tanggal_berlaku')->first();

        return view('harga-bbm.index', compact('riwayat', 'terakhir'));
    }

    public function store(Request $request)
    {
        $terakhir = HargaBbm::orderByDesc('tanggal_berlaku')->first();

        $rules = [
            'tanggal_berlaku' => ['required', 'date', 'unique:harga_bbm,tanggal_berlaku'],
            'harga_pertamax' => ['required', 'numeric', 'min:1'],
            'harga_pertadex' => ['required', 'numeric', 'min:1'],
            'harga_dexlite' => ['required', 'numeric', 'min:1'],
            'harga_pertamax_turbo' => ['required', 'numeric', 'min:1'],
        ];

        $messages = [
            'tanggal_berlaku.unique' => 'Sudah ada data harga BBM untuk tanggal ini.',
            'harga_pertamax.min' => 'Harga Pertamax tidak boleh 0.',
            'harga_pertadex.min' => 'Harga Pertadex tidak boleh 0.',
            'harga_dexlite.min' => 'Harga Dexlite tidak boleh 0.',
            'harga_pertamax_turbo.min' => 'Harga Pertamax Turbo tidak boleh 0.',
        ];

        // Tanggal data baru tidak boleh mundur dari tanggal yang terakhir diinputkan
        if ($terakhir) {
            $rules['tanggal_berlaku'][] = 'after:' . $terakhir->tanggal_berlaku->format('Y-m-d');
            $messages['tanggal_berlaku.after'] = 'Tanggal berlaku tidak boleh sama dengan atau sebelum data terakhir ('
                . $terakhir->tanggal_berlaku->format('d-m-Y') . ').';
        }

        $validated = $request->validate($rules, $messages);

        HargaBbm::create($validated);

        $msg = 'Data harga BBM tanggal ' . \Carbon\Carbon::parse($validated['tanggal_berlaku'])->format('d-m-Y') . ' berhasil ditambahkan.';

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('harga-bbm.index')->with('success', $msg);
    }

    public function update(Request $request, HargaBbm $hargaBbm)
    {
        $validated = $request->validate([
            'tanggal_berlaku' => [
                'required',
                'date',
                Rule::unique('harga_bbm', 'tanggal_berlaku')->ignore($hargaBbm->id),
            ],
            'harga_pertamax' => ['required', 'numeric', 'min:1'],
            'harga_pertadex' => ['required', 'numeric', 'min:1'],
            'harga_dexlite' => ['required', 'numeric', 'min:1'],
            'harga_pertamax_turbo' => ['required', 'numeric', 'min:1'],
        ], [
            'tanggal_berlaku.unique' => 'Sudah ada data harga BBM untuk tanggal ini.',
            'harga_pertamax.min' => 'Harga Pertamax tidak boleh 0.',
            'harga_pertadex.min' => 'Harga Pertadex tidak boleh 0.',
            'harga_dexlite.min' => 'Harga Dexlite tidak boleh 0.',
            'harga_pertamax_turbo.min' => 'Harga Pertamax Turbo tidak boleh 0.',
        ]);

        $hargaBbm->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data harga BBM berhasil diperbarui.']);
        }

        return redirect()->route('harga-bbm.index')
            ->with('success', 'Data harga BBM berhasil diperbarui.');
    }

    public function destroy(Request $request, HargaBbm $hargaBbm)
    {
        if ($hargaBbm->isDipakai()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Data harga BBM tanggal ' . $hargaBbm->tanggal_berlaku->format('d-m-Y') . ' tidak bisa dihapus karena sudah dipakai.'], 422);
            }
            return redirect()->route('harga-bbm.index')
                ->with('error', 'Data harga BBM tanggal ' . $hargaBbm->tanggal_berlaku->format('d-m-Y') . ' tidak bisa dihapus karena sudah dipakai.');
        }

        $hargaBbm->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data harga BBM berhasil dihapus.']);
        }

        return redirect()->route('harga-bbm.index')
            ->with('success', 'Data harga BBM berhasil dihapus.');
    }
}