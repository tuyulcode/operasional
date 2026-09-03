<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::latest()->get();

        $edit = null;
        if ($request->has('edit')) {
            $edit = Area::findOrFail($request->query('edit'));
        }

        return view('area.index', compact('areas', 'edit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'alamat' => 'nullable|string|max:255',
            'kena_ppn' => 'sometimes|boolean',
            'format_rekap' => 'required|in:standar,list,multikolom',
        ]);

        $validated['kena_ppn'] = $request->boolean('kena_ppn');

        Area::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data area berhasil ditambahkan.']);
        }

        return redirect()->route('area.index')
            ->with('success', 'Data area berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'alamat' => 'nullable|string|max:255',
            'kena_ppn' => 'sometimes|boolean',
            'format_rekap' => 'required|in:standar,list,multikolom',
        ]);

        $validated['kena_ppn'] = $request->boolean('kena_ppn');

        $area->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data area berhasil diperbarui.']);
        }

        return redirect()->route('area.index')
            ->with('success', 'Data area berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $area = Area::findOrFail($id);

        if ($area->titikMeter()->exists()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Area tidak dapat dihapus karena sudah digunakan pada titik meter.'], 422);
            }
            return redirect()->route('area.index')
                ->with('error', 'Area tidak dapat dihapus karena sudah digunakan pada titik meter.');
        }

        $area->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data area berhasil dihapus.']);
        }

        return redirect()->route('area.index')
            ->with('success', 'Data area berhasil dihapus.');
    }
}
