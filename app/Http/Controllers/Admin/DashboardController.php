<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Item;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalLaporan' => Item::count(),
            'totalKlaim' => Claim::count(),
            'barangSelesai' => Item::where('status', 'selesai')->count(),
            'totalPengguna' => User::count(),
        ]);
    }
}