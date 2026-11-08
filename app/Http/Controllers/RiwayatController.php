<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Item;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index()
    {
        $items = Item::with('location', 'category')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $claims = Claim::with('item')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('riwayat.index', compact('items', 'claims'));
    }
}