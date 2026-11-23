<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Mail\ResetPasswordMail;

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
    // SUPER ADMIN LOGIN (email + password)
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
        $identifierField = $request->has('email') ? 'email' : 'username';

        $request->validate([
            $identifierField => 'required|string',
            'password' => 'required|string',
        ], [
            $identifierField . '.required' => 'Alamat email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $identifier = trim($request->input('email') ?: $request->input('username'));
        $password = $request->input('password');
        $remember = $request->filled('remember');

        $user = User::where('role', 'super_admin')
            ->where(function ($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('username', $identifier);
            })
            ->first();

        if ($user && $user->is_active && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            ActivityLog::record('login', 'Super Admin berhasil masuk ke sistem: ' . $user->name);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . ' (Super Admin)!');
        }

        ActivityLog::record('failed_login', 'Percobaan masuk Super Admin gagal untuk identifier: ' . $identifier . ' (IP: ' . $request->ip() . ')');

        return back()->withErrors([
            $identifierField => 'Alamat email atau kata sandi salah, atau akun dinonaktifkan.',
        ])->onlyInput($identifierField);
    }

    // ====================================================
    // ADMIN LOGIN (email + password)
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
        $identifierField = $request->has('email') ? 'email' : 'username';

        $request->validate([
            $identifierField => 'required|string',
            'password' => 'required|string',
        ], [
            $identifierField . '.required' => 'Alamat email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $identifier = trim($request->input('email') ?: $request->input('username'));
        $password = $request->input('password');
        $remember = $request->filled('remember');

        $user = User::whereIn('role', ['admin', 'super_admin'])
            ->where(function ($q) use ($identifier) {
                $q->where('email', $identifier)
                  ->orWhere('username', $identifier);
            })
            ->first();

        if ($user && $user->is_active && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();
            ActivityLog::record('login', 'Admin berhasil masuk ke sistem: ' . $user->name . ' (' . $user->role_label . ')');

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . ' (' . $user->role_label . ')!');
        }

        ActivityLog::record('failed_login', 'Percobaan masuk Admin gagal untuk identifier: ' . $identifier . ' (IP: ' . $request->ip() . ')');

        return back()->withErrors([
            $identifierField => 'Alamat email atau kata sandi salah, atau akun dinonaktifkan.',
        ])->onlyInput($identifierField);
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

        ActivityLog::record('failed_login', 'Percobaan masuk Pengguna gagal untuk email: ' . $email . ' (IP: ' . $request->ip() . ')');

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
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::min(8), 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan gunakan email lain atau masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
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
        ];

        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'current_password.required' => 'Kata sandi saat ini wajib diisi untuk mengubah kata sandi.',
            'current_password.current_password' => 'Kata sandi saat ini salah.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = 'required|current_password';
            $rules['password'] = ['required', 'string', \Illuminate\Validation\Rules\Password::min(8), 'confirmed'];
        }

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

        $passwordChanged = false;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $passwordChanged = true;
        }

        $user->save();

        // Kumpulkan daftar informasi yang berhasil diperbarui
        $updatedParts = [];
        if ($user->wasChanged('name')) {
            $updatedParts[] = 'nama lengkap';
        }
        if ($user->wasChanged('username')) {
            $updatedParts[] = 'username';
        }
        if ($user->wasChanged('email')) {
            $updatedParts[] = 'alamat email';
        }
        if ($passwordChanged) {
            $updatedParts[] = 'kata sandi';
        }

        $message = !empty($updatedParts)
            ? 'Perubahan ' . implode(', ', $updatedParts) . ' berhasil disimpan!'
            : 'Profil berhasil diperbarui!';

        ActivityLog::record('update_profile', 'Memperbarui profil pengguna' . (!empty($updatedParts) ? ' (' . implode(', ', $updatedParts) . ')' : ''));

        return back()->with('success', $message);
    }

    // ====================================================
    // LUPA KATA SANDI (FORGOT & RESET PASSWORD VIA SMTP)
    // ====================================================
    public function showForgotPassword(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.forgot-password', [
            'portal' => $request->query('portal', 'user'),
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Alamat email tidak terdaftar dalam sistem.'])->withInput();
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi Super Admin.'])->withInput();
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $portal = $request->input('portal', $user->role === 'super_admin' ? 'superadmin' : ($user->role === 'admin' ? 'admin' : 'user'));
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
            'portal' => $portal,
        ]);

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
            ActivityLog::record('forgot_password', 'Permintaan reset kata sandi dikirim ke: ' . $user->email);

            return back()->with('status', 'Tautan untuk mereset kata sandi telah dikirim ke email Anda. Silakan periksa kotak masuk atau spam.');
        } catch (\Throwable $e) {
            ActivityLog::record('forgot_password_failed', 'Gagal mengirim email reset kata sandi ke ' . $user->email . ': ' . $e->getMessage());

            if (config('app.env') === 'local' || config('app.debug')) {
                return back()->with('status', 'Permintaan berhasil diproses! (Mode Lokal/Dev: Anda dapat langsung membuka tautan: ' . $resetUrl . ')');
            }

            return back()->withErrors(['email' => 'Gagal mengirim email reset kata sandi. Pastikan konfigurasi SMTP telah aktif.'])->withInput();
        }
    }

    public function showResetPassword(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
            'portal' => $request->query('portal', 'user'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Tautan reset kata sandi tidak valid atau telah kadaluarsa.']);
        }

        // Cek kadaluarsa (60 menit)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Tautan reset kata sandi telah kadaluarsa (lebih dari 60 menit). Silakan ajukan kembali.']);
        }

        // Verifikasi token (support hashed atau direct token)
        if (!Hash::check($request->token, $record->token) && $request->token !== $record->token) {
            return back()->withErrors(['email' => 'Token reset kata sandi tidak valid.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Pengguna tidak ditemukan.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        ActivityLog::record('password_reset_success', 'Kata sandi berhasil direset untuk: ' . $user->email);

        $portal = $request->input('portal', $user->role === 'super_admin' ? 'superadmin' : ($user->role === 'admin' ? 'admin' : 'user'));
        if ($portal === 'superadmin') {
            $slug = AppSetting::getSuperAdminSlug();
        } elseif ($portal === 'admin') {
            $slug = AppSetting::getAdminSlug();
        } else {
            $slug = AppSetting::getUserSlug();
        }

        return redirect("/{$slug}/login")->with('success', 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru.');
    }
}
