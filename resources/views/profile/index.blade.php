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
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Masukkan kata sandi saat ini untuk konfirmasi perubahan">
                                <button class="btn btn-outline-secondary btn-toggle-pw" type="button" data-target="current_password" style="border-color: #cbd5e1;" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter">
                                <button class="btn btn-outline-secondary btn-toggle-pw" type="button" data-target="password" style="border-color: #cbd5e1;" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Konfirmasi Kata Sandi Baru</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru">
                                <button class="btn btn-outline-secondary btn-toggle-pw" type="button" data-target="password_confirmation" style="border-color: #cbd5e1;" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Password Visibility
        document.querySelectorAll('.btn-toggle-pw').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                var icon = this.querySelector('i');
                if (!input) return;
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
        });

        // Popup Berhasil Memperbarui Profil
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Pembaruan Berhasil!',
                text: @json(session('success')),
                confirmButtonText: '<i class="bi bi-check2-circle me-1"></i> Selesai',
                confirmButtonColor: '#0d9488',
                timer: 4000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0 p-3',
                    title: 'fw-bold fs-5 text-slate-900',
                    confirmButton: 'btn btn-primary px-4 py-2 rounded-2 fw-semibold shadow-xs'
                }
            });
        @endif

        // Popup Error Validasi
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memperbarui Profil',
                html: '<div class="text-start small"><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>',
                confirmButtonText: 'Periksa Kembali',
                confirmButtonColor: '#dc2626',
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0 p-3',
                    title: 'fw-bold fs-5 text-danger',
                    confirmButton: 'btn btn-danger px-4 py-2 rounded-2 fw-semibold shadow-xs'
                }
            });
        @endif
    });
</script>
@endpush
