@php
    $portal = old('portal', request('portal', $portal ?? 'user'));
    $isSuperAdmin = $portal === 'superadmin';
    $isAdmin = $portal === 'admin';
    $isDark = $isSuperAdmin || $isAdmin;

    if ($isSuperAdmin) {
        $portalTitle = 'Super Admin';
        $badgeText = 'PORTAL SUPER ADMIN';
        $badgeIcon = 'bi-shield-lock-fill';
        $subTitle = 'Pemulihan Akun Akses Penuh Sistem';
        $backUrl = url('/' . \App\Models\AppSetting::getSuperAdminSlug() . '/login');
    } elseif ($isAdmin) {
        $portalTitle = 'Admin';
        $badgeText = 'PORTAL ADMINISTRATOR DATA';
        $badgeIcon = 'bi-shield-lock-fill';
        $subTitle = 'Pemulihan Akun Pengelolaan Data Sektoral';
        $backUrl = url('/' . \App\Models\AppSetting::getAdminSlug() . '/login');
    } else {
        $portalTitle = 'Kota Tasikmalaya';
        $badgeText = null;
        $badgeIcon = null;
        $subTitle = 'Masukkan email akun Anda untuk menerima tautan pemulihan kata sandi.';
        $backUrl = url('/' . \App\Models\AppSetting::getUserSlug() . '/login');
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - SIPPD {{ $portalTitle }}</title>
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
        .form-control {
            font-size: 0.83rem;
            padding: 0.42rem 0.75rem;
            @if($isDark)
                background-color: rgba(30, 41, 59, 0.8) !important;
                border-color: rgba(71, 85, 105, 0.6) !important;
                color: #f1f5f9 !important;
            @endif
        }
        @if($isDark)
        .form-control::placeholder {
            color: #64748b !important;
        }
        @endif
        .form-control:focus {
            @if($isSuperAdmin)
                background-color: rgba(30, 41, 59, 0.95) !important;
                border-color: #f43f5e !important;
                box-shadow: 0 0 0 0.2rem rgba(244, 63, 94, 0.2) !important;
                color: #ffffff !important;
            @elseif($isAdmin)
                background-color: rgba(30, 41, 59, 0.95) !important;
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.2) !important;
                color: #ffffff !important;
            @else
                border-color: #0d9488 !important;
                box-shadow: 0 0 0 0.2rem rgba(13, 148, 136, 0.2) !important;
            @endif
        }
        .input-group-text {
            font-size: 0.83rem;
            padding: 0.42rem 0.7rem;
            @if($isDark)
                background-color: rgba(30, 41, 59, 0.8) !important;
                border-color: rgba(71, 85, 105, 0.6) !important;
                color: #94a3b8 !important;
            @endif
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

            <h5 class="fw-bold mb-0.5 {{ $isDark ? 'text-white' : 'text-slate-900' }}" style="{{ $isDark ? '' : 'color: #0f172a;' }} letter-spacing: -0.01em; font-size: 1.12rem;">Lupa Kata Sandi</h5>
            <p class="small mb-0" style="color: {{ $isDark ? '#94a3b8' : '#64748b' }}; font-size: 0.74rem;">{{ $subTitle }}</p>
        </div>

        @if(session('status'))
            @if($isDark)
                <div class="alert alert-success py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2.5 bg-success bg-opacity-20 border-success text-success-emphasis border" style="border-radius: 0.45rem; font-size: 0.76rem;">
                    <i class="bi bi-check-circle-fill"></i>
                    <span style="color: #86efac;">{{ session('status') }}</span>
                </div>
            @else
                <div class="alert alert-success py-2 px-2.5 small d-flex align-items-start gap-2 mb-3" style="font-size: 0.76rem; border-radius: 0.45rem;">
                    <i class="bi bi-check-circle-fill text-success fs-6 mt-0.5"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif
        @endif

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

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <input type="hidden" name="portal" value="{{ $portal }}">

            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold mb-1" style="font-size: 0.75rem; color: {{ $isDark ? '#cbd5e1' : '#64748b' }};">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text {{ $isDark ? '' : 'bg-light' }} border-end-0 {{ $isDark ? '' : 'text-muted' }}">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control {{ $isDark ? '' : 'bg-light' }} border-start-0 ps-0 @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="Masukan Email Anda" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-submit w-100 py-2 rounded-2 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-send-fill" style="font-size: 0.8rem;"></i>
                <span>Kirim Tautan Reset Sandi</span>
            </button>
        </form>

        <div class="mt-3 pt-2.5 border-top text-center" style="{{ $isDark ? 'border-color: rgba(71, 85, 105, 0.4) !important;' : '' }}">
            <a href="{{ $backUrl }}" class="back-link">
                Kembali ke Halaman Masuk
            </a>
        </div>
    </div>
</body>
</html>