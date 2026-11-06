<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIPPD Kota Tasikmalaya</title>
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
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-card {
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-radius: 1rem;
            box-shadow: 0 20px 50px -12px rgba(0, 0, 0, 0.55);
            max-width: 395px;
            width: 100%;
        }
        .form-control {
            font-size: 0.83rem;
            padding: 0.42rem 0.75rem;
        }
        .input-group-text {
            font-size: 0.83rem;
            padding: 0.42rem 0.7rem;
        }
    </style>
</head>
<body>
    <div class="login-card p-3.5 p-sm-4">
        <div class="text-center mb-3">
            <div class="mb-2 d-flex justify-content-center">
                <img src="{{ asset('logo.png') }}?v=2" alt="Logo Kota Tasikmalaya" style="height: 52px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.12));">
            </div>
            <h5 class="fw-bold text-slate-900 mb-0.5" style="letter-spacing: -0.01em; font-size: 1.12rem;">SIPPD KOTA TASIKMALAYA</h5>
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

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-2.5">
                <label for="email" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 0.75rem;">Email atau Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror @error('login') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email', old('login', 'admin')) }}" 
                           placeholder="admin atau nama@datasektoral.go.id" required autofocus>
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

            <button type="submit" class="btn btn-primary w-100 py-2 rounded-2 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2" style="font-size: 0.84rem;">
                <span>Masuk ke Sistem</span>
                <i class="bi bi-arrow-right" style="font-size: 0.82rem;"></i>
            </button>
        </form>

        <!-- Akun Demo -->
        <div class="mt-3 pt-2.5 border-top">
            <span class="d-block text-center text-muted small fw-semibold mb-2" style="font-size: 0.73rem;">Pilih Akun Cepat (Demo):</span>
            <div class="d-flex gap-1.5">
                <button type="button" class="btn btn-sm btn-outline-danger flex-fill text-nowrap py-1 px-1.5" onclick="fillCreds('superadmin@datasektoral.go.id', 'password')" title="Masuk sebagai Super Admin (Akses Penuh)" style="font-size: 0.72rem;">
                    <i class="bi bi-shield-shaded me-0.5"></i> Super Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary flex-fill text-nowrap py-1 px-1.5" onclick="fillCreds('admin@datasektoral.go.id', 'password')" title="Masuk sebagai Admin (Pengelola Data)" style="font-size: 0.72rem;">
                    <i class="bi bi-shield-lock me-0.5"></i> Admin
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary flex-fill text-nowrap py-1 px-1.5" onclick="fillCreds('user@datasektoral.go.id', 'password')" title="Masuk sebagai User (Lihat & Unduh)" style="font-size: 0.72rem;">
                    <i class="bi bi-person me-0.5"></i> User
                </button>
            </div>
        </div>
    </div>

    <script>
        function fillCreds(login, pass) {
            document.getElementById('email').value = login;
            document.getElementById('password').value = pass;
        }

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
