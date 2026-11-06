@extends('layouts.app')

@section('title', 'Profil Pengguna')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Data Profil</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Profil Pengguna</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-2.5">
        <h4 class="fw-bold text-slate-900 mb-0.5" style="font-size: 1.15rem;">Pengaturan Profil & Keamanan</h4>
        <p class="text-muted small mb-0" style="font-size: 0.78rem;">Kelola informasi pribadi dan perbarui kata sandi akun Anda.</p>
    </div>

    <div class="card border-0 shadow-xs mb-0">
        <div class="card-body p-3">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="d-flex align-items-center gap-2.5 mb-3 pb-2.5 border-bottom">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="fw-bold text-slate-900 mb-0" style="font-size: 0.95rem;">{{ $user->name }}</h6>
                        <span class="text-muted small" style="font-size: 0.75rem;">{{ $user->email }}</span>
                        <div class="mt-0.5">
                            <span class="badge {{ $user->role_badge_class }} text-uppercase" style="font-size: 0.62rem;">
                                Peran: {{ $user->role_label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                @if($user->isAdmin())
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control font-monospace" value="{{ old('username', $user->username) }}" required placeholder="username_login">
                    <div class="form-text small" style="font-size: 0.75rem;">Username digunakan untuk masuk ke portal {{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}.</div>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="pt-3 border-top mt-4 mb-3">
                    <h6 class="fw-bold text-slate-900 mb-1">Ganti Kata Sandi</h6>
                    <small class="text-muted d-block mb-3">Kosongkan jika Anda tidak ingin mengubah kata sandi.</small>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold shadow-xs">
                        <i class="bi bi-check-circle me-1"></i> Simpan Pembaruan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
