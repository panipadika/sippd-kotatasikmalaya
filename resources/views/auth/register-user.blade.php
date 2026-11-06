<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Pengguna - SIPPD Kota Tasikmalaya</title>
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
        .register-card {
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(16px);
            border-radius: 1rem;
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.55), 0 0 30px -10px rgba(20, 184, 166, 0.25);
            max-width: 430px;
            width: 100%;
            position: relative;
            z-index: 10;
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
    </style>
</head>
<body>
    <div class="register-card p-3.5 p-sm-4">
        <div class="text-center mb-3">
            <div class="mb-2 d-flex justify-content-center">
                <img src="{{ asset('logo.png') }}?v=2" alt="Logo Kota Tasikmalaya" style="height: 50px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.12));">
            </div>

            <h5 class="fw-bold text-slate-900 mb-0.5" style="color: #0f172a; letter-spacing: -0.01em; font-size: 1.12rem;">Buat Akun SIPPD</h5>
            <p class="text-muted small mb-0" style="font-size: 0.74rem;">Daftarkan diri Anda untuk mengakses data profil daerah</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2" style="font-size: 0.76rem; border-radius: 0.45rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('user.register.post') }}">
            @csrf
            
            <div class="mb-2.5">
                <label for="name" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 0.74rem;">Nama Lengkap <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control bg-light border-start-0 ps-0 @error('name') is-invalid @enderror" 
                           id="name" name="name" value="{{ old('name') }}" 
                           placeholder="Nama lengkap Anda..." required autofocus>
                </div>
            </div>

            <div class="mb-2.5">
                <label for="email" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 0.74rem;">Alamat Email <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" 
                           placeholder="nama@email.com" required>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-12 col-sm-6">
                    <label for="password" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 0.74rem;">Kata Sandi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted px-2">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control bg-light border-start-0 border-end-0 px-1.5 @error('password') is-invalid @enderror" 
                               id="password" name="password" 
                               placeholder="Min. 6 digit" minlength="6" required>
                        <button class="btn btn-light border border-start-0 text-muted px-2 py-1" type="button" id="togglePasswordBtn" title="Lihat kata sandi">
                            <i class="bi bi-eye" style="font-size: 0.82rem;"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <label for="password_confirmation" class="form-label small fw-semibold text-secondary mb-1" style="font-size: 0.74rem;">Konfirmasi Sandi <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted px-2">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password" class="form-control bg-light border-start-0 border-end-0 px-1.5" 
                               id="password_confirmation" name="password_confirmation" 
                               placeholder="Ulangi sandi" minlength="6" required>
                        <button class="btn btn-light border border-start-0 text-muted px-2 py-1" type="button" id="togglePasswordConfirmBtn" title="Lihat konfirmasi sandi">
                            <i class="bi bi-eye" style="font-size: 0.82rem;"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-submit w-100 py-2.5 rounded-2 d-flex align-items-center justify-content-center gap-2 mt-3.5">
                <span>Daftar Akun Baru</span>
                <i class="bi bi-arrow-right" style="font-size: 0.82rem;"></i>
            </button>
        </form>

        <div class="mt-3 pt-2.5 border-top text-center">
            <span class="small text-muted" style="font-size: 0.74rem;">Sudah memiliki akun?</span>
            <a href="{{ route('user.login') }}" class="small fw-semibold text-decoration-none ms-1" style="color: #0d9488; font-size: 0.74rem;">
                Masuk ke Portal Pengguna &rarr;
            </a>
        </div>
    </div>

    <script>
        try {
            sessionStorage.removeItem('sidebar_open_folders');
            sessionStorage.removeItem('sidebar_scroll_pos');
        } catch (e) {}

        function bindPasswordToggle(btnId, inputId) {
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
        bindPasswordToggle('togglePasswordBtn', 'password');
        bindPasswordToggle('togglePasswordConfirmBtn', 'password_confirmation');
    </script>
</body>
</html>
