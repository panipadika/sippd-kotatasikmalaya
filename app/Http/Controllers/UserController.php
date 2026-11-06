<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('dataIndikators')->latest()->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|alpha_dash|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,admin,user',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dash, dan underscore.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'role.in' => 'Peran pengguna tidak valid.',
        ]);

        $username = $validated['username'] ?? null;
        if (empty($username) && in_array($validated['role'], ['super_admin', 'admin'])) {
            $base = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $validated['name'])));
            $username = $base ?: $validated['role'];
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = ($base ?: $validated['role']) . $counter;
                $counter++;
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => $username,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        ActivityLog::record('create_user', 'Menambahkan pengguna baru: ' . $user->name . ' (' . $user->role_label . ')', $user);

        return back()->with('success', 'Pengguna ' . $user->name . ' berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|alpha_dash|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:super_admin,admin,user',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dash, dan underscore.',
            'email.required' => 'Email wajib diisi.',
            'role.in' => 'Peran pengguna tidak valid.',
        ]);

        if ($user->role === 'super_admin' && $validated['role'] !== 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat mengubah peran satu-satunya akun Super Admin pada sistem.');
        }

        if ($user->id === Auth::id() && !$request->has('is_active')) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.');
        }

        $user->name = $validated['name'];
        if (isset($validated['username'])) {
            $user->username = $validated['username'];
        }
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->is_active = $request->has('is_active');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        ActivityLog::record('update_user', 'Memperbarui data pengguna: ' . $user->name . ' (' . $user->role_label . ')', $user);

        return back()->with('success', 'Data pengguna ' . $user->name . ' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus satu-satunya akun Super Admin pada sistem.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record('delete_user', 'Menghapus pengguna: ' . $name);

        return back()->with('success', 'Pengguna ' . $name . ' berhasil dihapus.');
    }
}
