<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Total pengguna = semua akun termasuk nonaktif, dipaginasi 15 per halaman.
        $users = User::orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('galat', 'kamu tidak bisa menonaktifkan akun sendiri.');
        }

        if ($user->role === 'admin' && $user->is_active) {
            $activeAdmins = User::where('role', 'admin')->where('is_active', true)->count();
            if ($activeAdmins <= 1) {
                return back()->with('galat', 'Tidak bisa menonaktifkan admin aktif terakhir.');
            }
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('sukses', $user->is_active ? 'Pengguna diaktifkan.' : 'Pengguna dinonaktifkan.');
    }
}