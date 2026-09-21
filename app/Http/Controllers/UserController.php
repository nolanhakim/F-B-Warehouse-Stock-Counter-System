<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function authorizeRole(): void
    {
        abort_unless(in_array(session('role'), ['admin', 'super']), 403);
    }

    public function index()
    {
        $this->authorizeRole();

        $users = User::orderBy('id')->paginate(10)->withQueryString();
        $allUsers = User::all();

        return view('super.users', [
            'active' => 'users',
            'pageTitle' => 'Manajemen Pengguna',
            'users' => $users,
            'allUsers' => $allUsers,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeRole();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:super,admin,staff',
            'is_active' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => (bool) $data['is_active'],
        ]);

        ActivityLog::record(session('email'), 'Create', $user->email, 'User baru: ' . $user->name);

        return redirect()->route('users.dynamic')->with('success', 'User baru berhasil ditambahkan.');
    }

    public function destroy(User $user)
    {
        $this->authorizeRole();

        ActivityLog::record(session('email'), 'Delete', $user->email, 'Hapus user: ' . $user->name);

        $user->delete();

        return redirect()->route('users.dynamic')->with('success', 'User berhasil dihapus.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeRole();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:super,admin,staff',
            'is_active' => 'required|boolean',
        ]);

        $old = $user->only(['name', 'email', 'role', 'is_active']);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => (bool) $data['is_active'],
        ];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);

        ActivityLog::record(session('email'), 'Update', $user->email, 'Ubah data user: ' . $user->name . ' (' . json_encode($old) . ' → ' . json_encode($user->only(['name', 'email', 'role', 'is_active'])) . ')');

        return redirect()->route('users.dynamic')->with('success', 'Data user berhasil diperbarui.');
    }
}
