<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Admin - SIPPD Kota Tasikmalaya</title>
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
            background: radial-gradient(circle at 20% 20%, #0f172a 0%, #0b1329 45%, #030712 100%);
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
            top: -100px;
            right: -100px;
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.16) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.14) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .login-card {
            border: 1px solid rgba(59, 130, 246, 0.22);
            background: rgba(15, 23, 42, 0.94);
            backdrop-filter: blur(16px);
            border-radius: 1rem;
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.75), 0 0 35px -10px rgba(37, 99, 235, 0.25);
            max-width: 395px;
            width: 100%;
            position: relative;
            z-index: 10;
        }
        .portal-badge {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(30, 64, 175, 0.3) 100%);
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #93c5fd;
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
            background-color: rgba(30, 41, 59, 0.8) !important;
            border-color: rgba(71, 85, 105, 0.6) !important;
            color: #f1f5f9 !important;
            font-size: 0.83rem;
            padding: 0.42rem 0.75rem;
        }
        .form-control:focus {
            background-color: rgba(30, 41, 59, 0.95) !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.2) !important;
            color: #ffffff !important;
        }
        .form-control::placeholder {
            color: #64748b !important;
        }
        .input-group-text {
            background-color: rgba(30, 41, 59, 0.8) !important;
            border-color: rgba(71, 85, 105, 0.6) !important;
            color: #94a3b8 !important;
            font-size: 0.83rem;
            padding: 0.42rem 0.7rem;
        }
        .btn-submit {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.84rem;
            letter-spacing: 0.02em;
            transition: all 0.2s ease;
            box-shadow: 0 8px 18px -4px rgba(37, 99, 235, 0.45);
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 10px 22px -3px rgba(37, 99, 235, 0.55);
            transform: translateY(-1px);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="login-card p-3.5 p-sm-4">
        <div class="text-center mb-3">
            <div class="mb-2 d-flex justify-content-center">
                <img src="{{ asset('logo.png') }}?v=2" alt="Logo Kota Tasikmalaya" style="height: 52px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.35));">
            </div>

            <h5 class="fw-bold text-white mb-0.5" style="letter-spacing: -0.01em; font-size: 1.12rem;">SIPPD KOTA TASIKMALAYA</h5>
            <p class="small mb-0" style="color: #94a3b8; font-size: 0.74rem;">Pengelolaan Data Sektoral & Indikator Pembangunan</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2.5 bg-success bg-opacity-20 border-success text-success-emphasis border" style="border-radius: 0.45rem; font-size: 0.76rem;">
                <i class="bi bi-check-circle-fill"></i>
                <span style="color: #86efac;">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2.5 bg-danger bg-opacity-20 border-danger text-danger-emphasis border" style="border-radius: 0.45rem; font-size: 0.76rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span style="color: #fda4af;">{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="mb-2.5">
                <label for="username" class="form-label small fw-semibold mb-1" style="color: #cbd5e1; font-size: 0.75rem;">Username Admin</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0">
                        <i class="bi bi-person-badge"></i>
                    </span>
                    <input type="text" class="form-control border-start-0 ps-0 @error('username') is-invalid @enderror" 
                           id="username" name="username" value="{{ old('username', 'admin') }}" 
                           placeholder="Masukkan username..." required autofocus>
                </div>
            </div>

            <div class="mb-2.5">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label small fw-semibold mb-0" style="color: #cbd5e1; font-size: 0.75rem;">Kata Sandi</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text border-end-0">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" 
                           id="password" name="password" value="password" 
                           placeholder="••••••••" required>
                    <button class="btn btn-dark border border-start-0 text-slate-400 py-1 px-2.5" type="button" id="togglePasswordBtn" style="border-color: rgba(71, 85, 105, 0.6) !important; background-color: rgba(30, 41, 59, 0.8);">
                        <i class="bi bi-eye text-secondary" style="font-size: 0.82rem;"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="form-check m-0">
                    <input class="form-check-input bg-dark border-secondary" type="checkbox" name="remember" id="remember" checked style="width: 0.9em; height: 0.9em;">
                    <label class="form-check-label small" for="remember" style="color: #94a3b8; font-size: 0.75rem;">
                        Ingat saya di perangkat ini
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-submit w-100 py-2 rounded-2 d-flex align-items-center justify-content-center gap-2">
                <span>Masuk sebagai Admin</span>
                <i class="bi bi-arrow-right" style="font-size: 0.82rem;"></i>
            </button>
        </form>

        <div class="mt-3 pt-2 border-top border-secondary border-opacity-25 text-center">
            <span class="d-block small" style="color: #64748b; font-size: 0.72rem;">
                <i class="bi bi-shield-check me-1"></i> Area Khusus Administrator Pengelola Data Daerah
            </span>
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
