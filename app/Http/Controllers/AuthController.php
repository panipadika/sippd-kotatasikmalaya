<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\AppSetting;

class AuthController extends Controller
{
    // ====================================================
    // ROOT REDIRECT — Arahkan ke portal user login
    // ====================================================
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $slug = AppSetting::getUserSlug();
        return redirect("/{$slug}/login");
    }

    // ====================================================
    // SUPER ADMIN LOGIN (username + password)
    // ====================================================
    public function showSuperAdminLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('dashboard');
            }
            // Sudah login tapi bukan super_admin, logout dulu
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
        return view('auth.login-superadmin');
    }

    public function superAdminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $username = trim($request->input('username'));
        $password = $request->input('password');
        $remember = $request->filled('remember');

        $user = User::where('username', $username)
            ->where('role', 'super_admin')
            ->first();

        if ($user && $user->is_active && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            ActivityLog::record('login', 'Super Admin berhasil masuk ke sistem: ' . $user->name);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . ' (Super Admin)!');
        }

        return back()->withErrors([
            'username' => 'Username atau kata sandi salah, atau akun dinonaktifkan.',
        ])->onlyInput('username');
    }

    // ====================================================
    // ADMIN LOGIN (username + password)
    // ====================================================
    public function showAdminLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->route('dashboard');
            }
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
        return view('auth.login-admin');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $username = trim($request->input('username'));
        $password = $request->input('password');
        $remember = $request->filled('remember');

        $user = User::where('username', $username)
            ->whereIn('role', ['admin', 'super_admin'])
            ->first();

        if ($user && $user->is_active && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            ActivityLog::record('login', 'Admin berhasil masuk ke sistem: ' . $user->name . ' (' . $user->role_label . ')');

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . ' (' . $user->role_label . ')!');
        }

        return back()->withErrors([
            'username' => 'Username atau kata sandi salah, atau akun dinonaktifkan.',
        ])->onlyInput('username');
    }

    // ====================================================
    // USER LOGIN (email + password)
    // ====================================================
    public function showUserLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login-user');
    }

    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $email = trim($request->input('email'));
        $password = $request->input('password');
        $remember = $request->filled('remember');

        $user = User::where('email', $email)
            ->where('role', 'user')
            ->first();

        if ($user && $user->is_active && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            ActivityLog::record('login', 'Pengguna berhasil masuk ke sistem: ' . $user->name);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi salah, atau akun belum diaktifkan.',
        ])->onlyInput('email');
    }

    // ====================================================
    // USER REGISTER (email + password)
    // ====================================================
    public function showUserRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register-user');
    }

    public function userRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLog::record('register', 'Pengguna baru mendaftar: ' . $user->name . ' (' . $user->email . ')');

        return redirect()->route('dashboard')
            ->with('success', 'Selamat datang, ' . $user->name . '! Akun Anda berhasil dibuat.');
    }

    // ====================================================
    // LOGOUT
    // ====================================================
    public function logout(Request $request)
    {
        $role = null;
        if (Auth::check()) {
            $role = Auth::user()->role;
            ActivityLog::record('logout', 'Pengguna keluar dari sistem: ' . Auth::user()->name);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to the appropriate login portal based on the user's role
        if ($role === 'super_admin') {
            $slug = AppSetting::getSuperAdminSlug();
            return redirect("/{$slug}/login")->with('success', 'Anda telah berhasil keluar.');
        } elseif ($role === 'admin') {
            $slug = AppSetting::getAdminSlug();
            return redirect("/{$slug}/login")->with('success', 'Anda telah berhasil keluar.');
        }

        $slug = AppSetting::getUserSlug();
        return redirect("/{$slug}/login")->with('success', 'Anda telah berhasil keluar.');
    }

    // ====================================================
    // PROFILE
    // ====================================================
    public function profile()
    {
        return view('profile.index', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6|confirmed',
        ];

        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ];

        $rules['email'] = 'required|email|max:255|unique:users,email,' . $user->id;
        $messages['email.required'] = 'Alamat email wajib diisi.';
        $messages['email.unique'] = 'Email sudah digunakan oleh akun lain.';

        // Admin/Super Admin can update username
        if ($user->isAdmin()) {
            $rules['username'] = 'required|string|max:255|alpha_dash|unique:users,username,' . $user->id;
            $messages['username.required'] = 'Username wajib diisi.';
            $messages['username.unique'] = 'Username sudah digunakan oleh akun lain.';
            $messages['username.alpha_dash'] = 'Username hanya boleh berisi huruf, angka, dash, dan underscore.';
        }

        $request->validate($rules, $messages);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($user->isAdmin() && $request->filled('username')) {
            $user->username = $request->username;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        ActivityLog::record('update_profile', 'Memperbarui profil pengguna');

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
