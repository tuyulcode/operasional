<?php

namespace App\Http\Controllers;

use App\Models\Ppn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PpnController extends Controller
{
    public function index()
    {
        $ppns = Ppn::latest()->get();
        return view('ppn.index', compact('ppns'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'persentase' => 'required|numeric|min:0|max:100',
        ]);

        $status = Ppn::count() === 0 ? 'aktif' : 'nonaktif';

        Ppn::create([
            'persentase' => $validated['persentase'],
            'status' => $status,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data PPN berhasil ditambahkan.']);
        }

        return redirect()->route('ppn.index')
            ->with('success', 'Data PPN berhasil ditambahkan.');
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
