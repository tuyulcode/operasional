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
        ]);

        $validated['kena_ppn'] = $request->boolean('kena_ppn');

        Area::create($validated);

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
        ]);

        $validated['kena_ppn'] = $request->boolean('kena_ppn');

        $area->update($validated);

        return redirect()->route('area.index')
            ->with('success', 'Data area berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $area = Area::findOrFail($id);

        if ($area->titikMeter()->exists()) {
            return redirect()->route('area.index')
                ->with('error', 'Area tidak dapat dihapus karena sudah digunakan pada titik meter.');
        }

        $area->delete();

        return redirect()->route('area.index')
            ->with('success', 'Data area berhasil dihapus.');
    }
}
