<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email belum diisi.',
            'email.email' => 'Format email tidak valid, contoh: nama@email.com.',
            'password.required' => 'Password minimal 8 karakter.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akunmu dinonaktifkan. Hubungi admin.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah. Periksa lagi, lalu coba masuk.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'Nama belum diisi.',
            'email.required' => 'Email belum diisi.',
            'email.email' => 'Format email tidak valid, contoh: nama@email.com.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password minimal 8 karakter.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $user = \App\Models\User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'pengguna',
        ]);

        Auth::login($user);

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}