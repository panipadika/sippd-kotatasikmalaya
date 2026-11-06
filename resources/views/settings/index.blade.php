@extends('layouts.app')

@section('title', 'Pengaturan Portal Login')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Data Profil</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Pengaturan Portal Login</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid p-0" style="max-width: 960px;">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-slate-900 mb-0.5" style="font-size: 1.15rem;">
                <i class="bi bi-link-45deg text-primary me-1"></i> Pengaturan URL Portal Login
            </h4>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                Sesuaikan tautan link URL belakang (slug) untuk masing-masing portal login guna meningkatkan privasi dan keamanan sistem.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2.5 px-3 small d-flex align-items-center gap-2 mb-3 shadow-xs" role="alert">
            <i class="bi bi-check-circle-fill fs-6 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close py-2.5" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2.5 px-3 small d-flex align-items-center gap-2 mb-3 shadow-xs" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close py-2.5" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2.5 px-3 small mb-3 shadow-xs" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Terjadi kesalahan input:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2.5" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Information Card -->
    <div class="card border-0 shadow-xs mb-3 bg-light border-start border-4 border-info">
        <div class="card-body p-3">
            <div class="d-flex align-items-start gap-2.5">
                <i class="bi bi-info-circle-fill text-info fs-5 mt-0.5"></i>
                <div class="small">
                    <span class="fw-bold text-dark d-block mb-1" style="font-size: 0.85rem;">Cara Kerja Pemisahan Portal & Kustomisasi URL:</span>
                    <ul class="mb-0 ps-3 text-muted" style="font-size: 0.78rem; line-height: 1.5;">
                        <li><strong>Super Admin & Admin</strong> menggunakan metode autentikasi <strong>Username + Password</strong> pada URL rahasia masing-masing.</li>
                        <li><strong>Pengguna Umum (User)</strong> menggunakan autentikasi <strong>Email + Password</strong> dan dapat mendaftarkan akun baru secara mandiri.</li>
                        <li>Setiap portal memiliki URL independen. Anda dapat mengubah kata belakang URL (slug) di bawah ini sesuai keinginan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Setting Form -->
    <div class="card border-0 shadow-xs">
        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark small" style="font-size: 0.85rem;">
                <i class="bi bi-sliders me-1 text-secondary"></i> Form Kustomisasi Link Belakang (URL Slug)
            </span>
            <span class="badge bg-primary bg-opacity-10 text-primary small" style="font-size: 0.7rem;">
                Live URL Sync
            </span>
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="POST" action="{{ route('settings.update-slugs') }}" id="formSettings">
                @csrf
                @method('PUT')

                <!-- 1. Super Admin Portal Slug -->
                <div class="p-3 mb-3 rounded-3 border" style="background: #fffbfa; border-color: #fee2e2 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger text-uppercase px-2 py-1" style="font-size: 0.68rem;">Super Admin</span>
                            <span class="fw-bold text-dark" style="font-size: 0.88rem;">Portal Super Administrator</span>
                        </div>
                        <a href="{{ url('/' . $superadminSlug . '/login') }}" target="_blank" class="btn btn-outline-danger btn-sm py-0.5 px-2 text-nowrap" style="font-size: 0.72rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan
                        </a>
                    </div>
                    <p class="text-muted small mb-2.5" style="font-size: 0.76rem;">Portal dengan hak akses penuh, kelola akun, master data, dan audit trail. Login menggunakan <strong>Username + Password</strong>.</p>
                    
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-semibold text-secondary mb-1">Slug URL Belakang <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted">/</span>
                                <input type="text" class="form-control fw-semibold" id="saSlugInput" name="superadmin_login_slug" 
                                       value="{{ old('superadmin_login_slug', $superadminSlug) }}" 
                                       pattern="^[a-z0-9\-]+$" required placeholder="superadmin">
                                <span class="input-group-text bg-white text-muted">/login</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label small fw-semibold text-secondary mb-1">Tautan Lengkap Saat Ini:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-light text-muted small" id="saFullUrl" readonly value="{{ url('/' . $superadminSlug . '/login') }}">
                                <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('saFullUrl', this)" title="Salin Tautan">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Admin Portal Slug -->
                <div class="p-3 mb-3 rounded-3 border" style="background: #f8faff; border-color: #dbeafe !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-uppercase px-2 py-1" style="font-size: 0.68rem;">Admin</span>
                            <span class="fw-bold text-dark" style="font-size: 0.88rem;">Portal Administrator Data</span>
                        </div>
                        <a href="{{ url('/' . $adminSlug . '/login') }}" target="_blank" class="btn btn-outline-primary btn-sm py-0.5 px-2 text-nowrap" style="font-size: 0.72rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan
                        </a>
                    </div>
                    <p class="text-muted small mb-2.5" style="font-size: 0.76rem;">Portal pengelolaan data sektoral, input indikator, dan berkas daerah. Login menggunakan <strong>Username + Password</strong>.</p>
                    
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-semibold text-secondary mb-1">Slug URL Belakang <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted">/</span>
                                <input type="text" class="form-control fw-semibold" id="adminSlugInput" name="admin_login_slug" 
                                       value="{{ old('admin_login_slug', $adminSlug) }}" 
                                       pattern="^[a-z0-9\-]+$" required placeholder="admin">
                                <span class="input-group-text bg-white text-muted">/login</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label small fw-semibold text-secondary mb-1">Tautan Lengkap Saat Ini:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-light text-muted small" id="adminFullUrl" readonly value="{{ url('/' . $adminSlug . '/login') }}">
                                <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('adminFullUrl', this)" title="Salin Tautan">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. User Portal Slug -->
                <div class="p-3 mb-4 rounded-3 border" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success text-uppercase px-2 py-1" style="font-size: 0.68rem;">User Publik</span>
                            <span class="fw-bold text-dark" style="font-size: 0.88rem;">Portal Pengguna Daerah</span>
                        </div>
                        <a href="{{ url('/' . $userSlug . '/login') }}" target="_blank" class="btn btn-outline-success btn-sm py-0.5 px-2 text-nowrap" style="font-size: 0.72rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Tautan
                        </a>
                    </div>
                    <p class="text-muted small mb-2.5" style="font-size: 0.76rem;">Portal umum bagi masyarakat, dinas terkait, dan peninjau. Tersedia fitur login <strong>Email + Password</strong> serta <strong>Pendaftaran Akun Baru</strong>.</p>
                    
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-semibold text-secondary mb-1">Slug URL Belakang <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted">/</span>
                                <input type="text" class="form-control fw-semibold" id="userSlugInput" name="user_login_slug" 
                                       value="{{ old('user_login_slug', $userSlug) }}" 
                                       pattern="^[a-z0-9\-]+$" required placeholder="portal">
                                <span class="input-group-text bg-white text-muted">/login</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-7">
                            <label class="form-label small fw-semibold text-secondary mb-1">Tautan Lengkap Saat Ini:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control bg-light text-muted small" id="userFullUrl" readonly value="{{ url('/' . $userSlug . '/login') }}">
                                <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('userFullUrl', this)" title="Salin Tautan">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Petunjuk Tambahan -->
                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                    <span class="small text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-exclamation-circle text-warning me-1"></i> Format slug: huruf kecil (a-z), angka (0-9), dan tanda hubung (-).
                    </span>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold shadow-xs">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan URL
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const baseUrl = "{{ url('/') }}";

    function updateLivePreview() {
        const saSlug = (document.getElementById('saSlugInput').value.trim() || 'superadmin').toLowerCase();
        const adminSlug = (document.getElementById('adminSlugInput').value.trim() || 'admin').toLowerCase();
        const userSlug = (document.getElementById('userSlugInput').value.trim() || 'portal').toLowerCase();

        document.getElementById('saFullUrl').value = `${baseUrl}/${saSlug}/login`;
        document.getElementById('adminFullUrl').value = `${baseUrl}/${adminSlug}/login`;
        document.getElementById('userFullUrl').value = `${baseUrl}/${userSlug}/login`;
    }

    document.getElementById('saSlugInput').addEventListener('input', updateLivePreview);
    document.getElementById('adminSlugInput').addEventListener('input', updateLivePreview);
    document.getElementById('userSlugInput').addEventListener('input', updateLivePreview);

    function copyToClipboard(elementId, btn) {
        const input = document.getElementById(elementId);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2 text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1500);
        });
    }
</script>
@endpush
