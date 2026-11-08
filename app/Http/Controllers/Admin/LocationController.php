<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::withCount('items')->orderBy('name')->get();

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:locations,name'],
        ], [
            'name.required' => 'Nama lokasi wajib diisi.',
            'name.unique' => 'Nama lokasi sudah ada.',
        ]);

        Location::create($data);

        return redirect()->route('admin.locations.index')->with('sukses', 'Lokasi disimpan.');
    }

    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:locations,name,'.$location->id],
        ], [
            'name.required' => 'Nama lokasi wajib diisi.',
            'name.unique' => 'Nama lokasi sudah ada.',
        ]);

        $location->update($data);

        return redirect()->route('admin.locations.index')->with('sukses', 'Lokasi diperbarui.');
    }

    public function destroy(Location $location)
    {
        if ($location->items()->count() > 0) {
            return back()->with('galat', 'Lokasi ini masih dipakai '.$location->items()->count().' laporan, jadi belum bisa dihapus. Pindahkan laporannya ke lokasi lain dulu.');
        }

        $location->delete();

        return redirect()->route('admin.locations.index')->with('sukses', 'Lokasi dihapus.');
    }
}