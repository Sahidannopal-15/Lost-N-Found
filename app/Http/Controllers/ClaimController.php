<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Item;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function create(Item $item)
    {
        if ($item->type !== 'temuan' || $item->status !== 'aktif') {
            abort(403);
        }

        if ($item->user_id === auth()->id()) {
            abort(403);
        }

        if ($item->claims()->where('status', 'menunggu')->exists()) {
            return back()->with('galat', 'Barang ini sudah dalam proses klaim.');
        }

        return view('claims.create', compact('item'));
    }

    public function store(Request $request, Item $item)
    {
        if ($item->type !== 'temuan' || $item->status !== 'aktif') {
            abort(403);
        }

        if ($item->user_id === auth()->id()) {
            abort(403);
        }

        if ($item->claims()->where('status', 'menunggu')->exists()) {
            return back()->with('galat', 'Barang ini sudah dalam proses klaim.');
        }

        $data = $request->validate([
            'description' => ['required', 'string', 'min:20'],
            'proof' => ['required', 'string', 'min:10'],
        ], [
            'description.min' => 'Ciri barang minimal 20 karakter. Sebutkan hal yang hanya diketahui pemilik, misalnya isi tas atau goresan.',
            'proof.min' => 'Bukti minimal 10 karakter.',
        ]);

        Claim::create([
            'item_id' => $item->id,
            'user_id' => auth()->id(),
            'description' => $data['description'],
            'proof' => $data['proof'],
            'status' => 'menunggu',
        ]);

        $item->update(['status' => 'dalam_klaim']);

        return redirect()->route('riwayat.index')->with('sukses', 'Klaim dikirim. Petugas akan memeriksa buktimu.');
    }
}