@php
    $portal = old('portal', request('portal', $portal ?? 'user'));
    $isSuperAdmin = $portal === 'superadmin';
    $isAdmin = $portal === 'admin';
    $isDark = $isSuperAdmin || $isAdmin;

    if ($isSuperAdmin) {
        $portalTitle = 'Super Admin';
        $badgeText = 'PORTAL SUPER ADMIN';
        $badgeIcon = 'bi-shield-lock-fill';
        $subTitle = 'Buat kata sandi baru untuk akun Super Admin';
        $backUrl = url('/' . \App\Models\AppSetting::getSuperAdminSlug() . '/login');
    } elseif ($isAdmin) {
        $portalTitle = 'Admin';
        $badgeText = 'PORTAL ADMINISTRATOR DATA';
        $badgeIcon = 'bi-shield-lock-fill';
        $subTitle = 'Buat kata sandi baru untuk akun Administrator';
        $backUrl = url('/' . \App\Models\AppSetting::getAdminSlug() . '/login');
    } else {
        $portalTitle = 'Kota Tasikmalaya';
        $badgeText = null;
        $badgeIcon = null;
        $subTitle = 'Silakan buat kata sandi baru untuk akun Anda.';
        $backUrl = url('/' . \App\Models\AppSetting::getUserSlug() . '/login');
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi - SIPPD {{ $portalTitle }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <style>
        *, *::before, *::after, html, body {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        *::-webkit-scrollbar, html::-webkit-scrollbar, body::-webkit-scrollbar {
            width: 0px !important;
            height: 0px !important;
            display: none !important;
        }
        html, body {
            height: 100%;
            height: 100vh;
            height: 100dvh;
            margin: 0;
            padding: 0;
            overflow: hidden !important;
        }
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            box-sizing: border-box;
            @if($isSuperAdmin)
                background: radial-gradient(circle at 20% 20%, #1e1b4b 0%, #0f172a 40%, #030712 100%);
            @elseif($isAdmin)
                background: radial-gradient(circle at 20% 20%, #0f172a 0%, #0b1329 45%, #030712 100%);
            @else
                background: radial-gradient(ellipse at center, #065f46 0%, #064e3b 35%, #042f2e 65%, #021f1d 100%);
            @endif
        }
        body::before {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            @if($isSuperAdmin)
                top: -100px;
                right: -100px;
                width: 360px;
                height: 360px;
                background: radial-gradient(circle, rgba(225, 29, 72, 0.15) 0%, transparent 70%);
            @elseif($isAdmin)
                top: -100px;
                right: -100px;
                width: 360px;
                height: 360px;
                background: radial-gradient(circle, rgba(37, 99, 235, 0.16) 0%, transparent 70%);
            @else
                top: -90px;
                right: -90px;
                width: 380px;
                height: 380px;
                background: radial-gradient(circle, rgba(52, 211, 153, 0.28) 0%, rgba(5, 150, 105, 0.12) 45%, transparent 70%);
            @endif
        }
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            @if($isSuperAdmin)
                bottom: -80px;
                left: -80px;
                width: 320px;
                height: 320px;
                background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            @elseif($isAdmin)
                bottom: -80px;
                left: -80px;
                width: 320px;
                height: 320px;
                background: radial-gradient(circle, rgba(14, 165, 233, 0.14) 0%, transparent 70%);
            @else
                bottom: -90px;
                left: -90px;
                width: 380px;
                height: 380px;
                background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(13, 148, 136, 0.1) 45%, transparent 70%);
            @endif
        }
        .login-card {
            border-radius: 1rem;
            max-width: 395px;
            width: 100%;
            position: relative;
            z-index: 10;
            backdrop-filter: blur(16px);
            @if($isSuperAdmin)
                border: 1px solid rgba(244, 63, 94, 0.22);
                background: rgba(15, 23, 42, 0.94);
                box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.75), 0 0 35px -10px rgba(225, 29, 72, 0.25);
            @elseif($isAdmin)
                border: 1px solid rgba(59, 130, 246, 0.22);
                background: rgba(15, 23, 42, 0.94);
                box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.75), 0 0 35px -10px rgba(37, 99, 235, 0.25);
            @else
                border: 1px solid rgba(255, 255, 255, 0.2);
                background: rgba(255, 255, 255, 0.98);
                box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.6), 0 0 40px -5px rgba(16, 185, 129, 0.22);
            @endif
        }
        @if($badgeText)
        .portal-badge {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            padding: 0.2rem 0.65rem;
            border-radius: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            @if($isSuperAdmin)
                background: linear-gradient(135deg, rgba(225, 29, 72, 0.2) 0%, rgba(159, 18, 57, 0.3) 100%);
                border: 1px solid rgba(244, 63, 94, 0.35);
                color: #fda4af;
            @elseif($isAdmin)
                background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(30, 64, 175, 0.3) 100%);
                border: 1px solid rgba(59, 130, 246, 0.35);
                color: #93c5fd;
            @endif
        }
        @endif
        .input-group {
            border-radius: 0.5rem !important;
            overflow: hidden;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            @if($isDark)
                background-color: rgba(30, 41, 59, 0.8) !important;
                border: 1px solid rgba(71, 85, 105, 0.6) !important;
            @else
                background-color: #f8fafc !important;
                border: 1px solid #e2e8f0 !important;
            @endif
        }
        .input-group:hover {
            @if($isDark)
                border-color: rgba(148, 163, 184, 0.5) !important;
            @else
                border-color: #cbd5e1 !important;
                background-color: #ffffff !important;
            @endif
        }
        .input-group:focus-within {
            @if($isSuperAdmin)
                border-color: #f43f5e !important;
                box-shadow: 0 0 0 3.5px rgba(244, 63, 94, 0.22) !important;
                background-color: rgba(30, 41, 59, 0.95) !important;
            @elseif($isAdmin)
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3.5px rgba(59, 130, 246, 0.22) !important;
                background-color: rgba(30, 41, 59, 0.95) !important;
            @else
                border-color: #0d9488 !important;
                box-shadow: 0 0 0 3.5px rgba(13, 148, 136, 0.18) !important;
                background-color: #ffffff !important;
            @endif
        }
        .input-group:focus-within .input-group-text i {
            @if($isSuperAdmin)
                color: #fb7185 !important;
            @elseif($isAdmin)
                color: #60a5fa !important;
            @else
                color: #0d9488 !important;
            @endif
        }
        .input-group:has(.is-invalid) {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3.5px rgba(239, 68, 68, 0.22) !important;
        }
        .input-group-text {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: #94a3b8 !important;
            font-size: 0.88rem;
            padding: 0.45rem 0.75rem;
            display: flex;
            align-items: center;
            transition: color 0.2s ease;
            flex-shrink: 0;
        }
        .form-control {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
            font-size: 0.83rem;
            padding: 0.45rem 0.75rem 0.45rem 0.2rem;
            width: 100%;
            color: {{ $isDark ? '#f1f5f9' : '#0f172a' }} !important;
        }
        .form-control:focus {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
            color: {{ $isDark ? '#ffffff' : '#0f172a' }} !important;
        }
        .form-control::placeholder {
            color: #94a3b8 !important;
        }
        .form-control:disabled, .form-control[readonly] {
            background: transparent !important;
            color: {{ $isDark ? '#94a3b8' : '#64748b' }} !important;
            cursor: not-allowed;
        }
        .btn-toggle-eye {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
            color: #94a3b8 !important;
            padding: 0.45rem 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: color 0.15s ease, transform 0.15s ease;
            flex-shrink: 0;
        }
        .btn-toggle-eye:hover, .btn-toggle-eye:focus {
            color: {{ $isDark ? '#f1f5f9' : '#0f766e' }} !important;
            background: transparent !important;
            box-shadow: none !important;
            outline: none !important;
        }
        .btn-submit {
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.84rem;
            letter-spacing: 0.02em;
            transition: all 0.2s ease;
            @if($isSuperAdmin)
                background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
                box-shadow: 0 8px 18px -4px rgba(225, 29, 72, 0.45);
            @elseif($isAdmin)
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                box-shadow: 0 8px 18px -4px rgba(37, 99, 235, 0.45);
            @else
                background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
                box-shadow: 0 8px 18px -4px rgba(13, 148, 136, 0.45);
            @endif
        }
        .btn-submit:hover {
            color: #ffffff;
            transform: translateY(-1px);
            @if($isSuperAdmin)
                background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
                box-shadow: 0 10px 22px -3px rgba(225, 29, 72, 0.55);
            @elseif($isAdmin)
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                box-shadow: 0 10px 22px -3px rgba(37, 99, 235, 0.55);
            @else
                background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
                box-shadow: 0 10px 22px -3px rgba(13, 148, 136, 0.55);
            @endif
        }
        .back-link {
            text-decoration: none;
            font-weight: 600;
            font-size: 0.75rem;
            transition: all 0.2s ease;
            @if($isSuperAdmin)
                color: #f43f5e;
            @elseif($isAdmin)
                color: #38bdf8;
            @else
                color: #0d9488;
            @endif
        }
        .back-link:hover {
            @if($isSuperAdmin)
                color: #fda4af;
            @elseif($isAdmin)
                color: #93c5fd;
            @else
                color: #0f766e;
            @endif
        }
    </style>
</head>
<body>
    <div class="login-card p-3.5 p-sm-4">
        <div class="text-center mb-3">
            <div class="mb-2 d-flex justify-content-center">
                <img src="{{ asset('logo.png') }}?v=2" alt="Logo Kota Tasikmalaya" style="height: 52px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 10px {{ $isDark ? 'rgba(0, 0, 0, 0.35)' : 'rgba(0, 0, 0, 0.12)' }});">
            </div>

            @if($badgeText)
                <div class="mb-1.5">
                    <span class="portal-badge"><i class="bi {{ $badgeIcon }}"></i> {{ $badgeText }}</span>
                </div>
            @endif

            <h5 class="fw-bold mb-0.5 {{ $isDark ? 'text-white' : 'text-slate-900' }}" style="{{ $isDark ? '' : 'color: #0f172a;' }} letter-spacing: -0.01em; font-size: 1.12rem;">Atur Ulang Kata Sandi</h5>
            <p class="small mb-0" style="color: {{ $isDark ? '#94a3b8' : '#64748b' }}; font-size: 0.74rem;">{{ $subTitle }}</p>
        </div>

        @if($errors->any())
            @if($isDark)
                <div class="alert alert-danger py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2.5 bg-danger bg-opacity-20 border-danger text-danger-emphasis border" style="border-radius: 0.45rem; font-size: 0.76rem;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span style="color: #fda4af;">{{ $errors->first() }}</span>
                </div>
            @else
                <div class="alert alert-danger py-2 px-2.5 small d-flex align-items-start gap-2 mb-3" style="font-size: 0.76rem; border-radius: 0.45rem;">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-6 mt-0.5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="portal" value="{{ $portal }}">

            <div class="mb-2.5">
                <label for="email" class="form-label small fw-semibold mb-1" style="font-size: 0.75rem; color: {{ $isDark ? '#cbd5e1' : '#64748b' }};">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', $email) }}" 
                           required readonly>
                </div>
            </div>

            <div class="mb-2.5">
                <label for="password" class="form-label small fw-semibold mb-1" style="font-size: 0.75rem; color: {{ $isDark ? '#cbd5e1' : '#64748b' }};">Kata Sandi Baru</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" 
                           placeholder="Minimal 8 karakter" required autofocus minlength="8">
                    <button class="btn btn-toggle-eye" type="button" id="togglePasswordBtn" title="Lihat/Sembunyikan Kata Sandi">
                        <i class="bi bi-eye" style="font-size: 0.85rem;"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label small fw-semibold mb-1" style="font-size: 0.75rem; color: {{ $isDark ? '#cbd5e1' : '#64748b' }};">Konfirmasi Kata Sandi Baru</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input type="password" class="form-control" 
                           id="password_confirmation" name="password_confirmation" 
                           placeholder="Ulangi kata sandi baru" required minlength="8">
                    <button class="btn btn-toggle-eye" type="button" id="toggleConfirmBtn" title="Lihat/Sembunyikan Kata Sandi">
                        <i class="bi bi-eye" style="font-size: 0.85rem;"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-submit w-100 py-2 rounded-2 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-check-circle-fill" style="font-size: 0.82rem;"></i>
                <span>Simpan Kata Sandi Baru</span>
            </button>
        </form>

        <div class="mt-3 pt-2.5 border-top text-center" style="{{ $isDark ? 'border-color: rgba(71, 85, 105, 0.4) !important;' : '' }}">
            <a href="{{ $backUrl }}" class="back-link">
                Batalkan & Kembali ke Login
            </a>
        </div>
    </div>

    <script>
        function setupToggle(btnId, inputId) {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        }
        setupToggle('togglePasswordBtn', 'password');
        setupToggle('toggleConfirmBtn', 'password_confirmation');
    </script>
</body>
</html>