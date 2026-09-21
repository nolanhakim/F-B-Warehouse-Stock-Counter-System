<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private array $roles = ['admin' => 'Admin Gudang', 'staff' => 'Karyawan', 'super' => 'Super Admin'];

    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function doLogin(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return redirect()->back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
        }

        if (!$user->is_active) {
            return redirect()->back()->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi admin.'])->withInput();
        }

        $role = $user->role;

        $user->update(['last_login_at' => now()]);

        session([
            'role' => $role,
            'name' => $user->name,
            'user_id' => $user->id,
            'email' => $user->email,
            'last_login_at' => now()->format('Y-m-d H:i:s'),
            'is_active' => $user->is_active,
        ]);

        return redirect()->route('dashboard')->with('success', 'Berhasil masuk sebagai ' . session('name'));
    }

    public function doRegister(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'staff',
            'is_active' => true,
        ]);

        session()->forget(['role', 'name']);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat. Silakan masuk.');
    }

    public function logout()
    {
        session()->flush();

        return redirect()->route('login')->with('success', 'Anda berhasil keluar dari sistem. Sampai jumpa!');
    }
}