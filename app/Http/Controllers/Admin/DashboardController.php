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
        // Definisi angka: Total laporan = semua status; Total klaim = semua status;
        // Barang selesai = item berstatus selesai; Total pengguna = semua akun termasuk nonaktif.
        return view('admin.dashboard', [
            'totalLaporan' => Item::count(),
            'totalKlaim' => Claim::count(),
            'barangSelesai' => Item::where('status', 'selesai')->count(),
            'totalPengguna' => User::count(),
        ]);
    }
}