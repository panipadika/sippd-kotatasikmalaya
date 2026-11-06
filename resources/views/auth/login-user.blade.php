<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Pengguna - SIPPD Kota Tasikmalaya</title>
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
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #09203f 0%, #1e3a8a 50%, #0d1b2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 340px;
            height: 340px;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 340px;
            height: 340px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .login-card {
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(16px);
            border-radius: 1rem;
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.55), 0 0 30px -10px rgba(20, 184, 166, 0.25);
            max-width: 395px;
            width: 100%;
            position: relative;
            z-index: 10;
        }
        .portal-badge {
            background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(14, 116, 144, 0.15) 100%);
            border: 1px solid rgba(13, 148, 136, 0.25);
            color: #0f766e;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            padding: 0.2rem 0.65rem;
            border-radius: 2rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .form-control {
            font-size: 0.83rem;
            padding: 0.42rem 0.75rem;
        }
        .form-control:focus {
            border-color: #0d9488 !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 148, 136, 0.2) !important;
        }
        .input-group-text {
            font-size: 0.83rem;
            padding: 0.42rem 0.7rem;
        }
        .btn-submit {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.84rem;
            letter-spacing: 0.02em;
            transition: all 0.2s ease;
            box-shadow: 0 8px 18px -4px rgba(13, 148, 136, 0.45);
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            box-shadow: 0 10px 22px -3px rgba(13, 148, 136, 0.55);
            transform: translateY(-1px);
            color: #ffffff;
        }
        .register-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 0.65rem;
        }
    </style>
</head>
<body>
    <div class="login-card p-3.5 p-sm-4">
        <div class="text-center mb-3">
            <div class="mb-2 d-flex justify-content-center">
                <img src="{{ asset('logo.png') }}?v=2" alt="Logo Kota Tasikmalaya" style="height: 52px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.12));">
            </div>

            <h5 class="fw-bold text-slate-900 mb-0.5" style="color: #0f172a; letter-spacing: -0.01em; font-size: 1.12rem;">SIPPD KOTA TASIKMALAYA</h5>
            <p class="text-muted small mb-0" style="font-size: 0.74rem;">Sistem Informasi Profil Pembangunan Daerah</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2.5" style="font-size: 0.76rem; border-radius: 0.45rem;">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2.5" style="font-size: 0.76rem; border-radius: 0.45rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('user.login.post') }}">
            @csrf
            <div class="mb-2.5">
                <label for="email" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 0.75rem;">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', 'user@datasektoral.go.id') }}" 
                           placeholder="nama@email.com" required autofocus>
                </div>
            </div>

            <div class="mb-2.5">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label small fw-semibold text-secondary mb-0" style="font-size: 0.75rem;">Kata Sandi</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control bg-light border-start-0 ps-0 @error('password') is-invalid @enderror" 
                           id="password" name="password" value="password" 
                           placeholder="••••••••" required>
                    <button class="btn btn-light border border-start-0 text-muted py-1 px-2.5" type="button" id="togglePasswordBtn">
                        <i class="bi bi-eye" style="font-size: 0.82rem;"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked style="width: 0.9em; height: 0.9em;">
                    <label class="form-check-label small text-muted" for="remember" style="font-size: 0.75rem;">
                        Ingat saya di perangkat ini
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-submit w-100 py-2 rounded-2 d-flex align-items-center justify-content-center gap-2">
                <span>Masuk ke Akun Anda</span>
                <i class="bi bi-arrow-right" style="font-size: 0.82rem;"></i>
            </button>
        </form>

        <!-- Register Section -->
        <div class="register-box p-2.5 mt-3 text-center">
            <span class="small text-muted d-block mb-1.5" style="font-size: 0.73rem;">Belum memiliki akun pengguna?</span>
            <a href="{{ route('user.register') }}" class="btn btn-outline-primary btn-sm fw-semibold w-100 py-1.5 d-flex align-items-center justify-content-center gap-1.5 rounded-2" style="border-color: #0d9488; color: #0d9488; font-size: 0.77rem;">
                <i class="bi bi-person-plus"></i>
                <span>Daftar Akun Baru Sekarang</span>
            </a>
        </div>
    </div>

    <script>
        try {
            sessionStorage.removeItem('sidebar_open_folders');
            sessionStorage.removeItem('sidebar_scroll_pos');
        } catch (e) {}

        document.getElementById('togglePasswordBtn').addEventListener('click', function () {
            const passInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>
