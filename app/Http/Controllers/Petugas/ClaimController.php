<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Item;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index()
    {
        $claims = Claim::with(['item', 'user'])
            ->where('status', 'menunggu')
            ->latest()
            ->get();

        return view('petugas.claims.index', compact('claims'));
    }

    public function approve(Claim $claim)
    {
        $claim->update([
            'status' => 'disetujui',
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        $claim->item->update(['status' => 'selesai']);

        return back()->with('sukses', 'Klaim disetujui. Serah terima tercatat.');
    }

    public function reject(Request $request, Claim $claim)
    {
        $data = $request->validate([
            'officer_note' => ['required', 'string', 'min:5'],
        ], [
            'officer_note.required' => 'Catatan petugas wajib diisi.',
        ]);

        $claim->update([
            'status' => 'ditolak',
            'officer_note' => $data['officer_note'],
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ]);

        $claim->item->update(['status' => 'aktif']);

        return back()->with('sukses', 'Klaim ditolak.');
    }
}