<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = User::findOrFail(session('user_id'));

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6',
        ]);

        $oldName = $user->name;

        $update = ['name' => $data['name']];

        if (!empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);

        session(['name' => $user->name]);

        ActivityLog::record(session('email'), 'Update', $user->email, 'Ubah profil: nama ' . ($oldName !== $user->name ? $oldName . ' → ' . $user->name : '(sama)') . (!empty($data['password']) ? ', ganti password' : ''));

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}