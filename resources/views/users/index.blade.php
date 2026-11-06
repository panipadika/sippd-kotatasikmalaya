@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Data Profil</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Manajemen Pengguna</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2.5">
        <div>
            <h4 class="fw-bold text-slate-900 mb-0.5" style="font-size: 1.15rem;">Manajemen Pengguna & Hak Akses</h4>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Kelola akun pengguna dengan 3 peran sistem: <strong>Super Admin</strong>, <strong>Admin</strong>, dan <strong>User</strong>.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm shadow-xs d-flex align-items-center gap-1 py-1 px-2.5" data-bs-toggle="modal" data-bs-target="#modalTambahUser" style="font-size: 0.78rem;">
                <i class="bi bi-person-plus"></i>
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>
    </div>

    <!-- Ringkasan Role Card -->
    <div class="row g-2 mb-2.5">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-xs h-100 border-start border-4 border-danger mb-0">
                <div class="card-body p-2.5">
                    <div class="d-flex align-items-center justify-content-between mb-0.5">
                        <span class="badge bg-danger text-white text-uppercase" style="font-size: 0.65rem;">Super Admin</span>
                        <i class="bi bi-shield-shaded text-danger fs-6"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.85rem;">Akses Penuh Sistem</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.74rem;">Kelola pengguna, master urusan & tahun, serta audit log sistem.</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-xs h-100 border-start border-4 border-primary mb-0">
                <div class="card-body p-2.5">
                    <div class="d-flex align-items-center justify-content-between mb-0.5">
                        <span class="badge bg-primary text-white text-uppercase" style="font-size: 0.65rem;">Admin</span>
                        <i class="bi bi-shield-lock text-primary fs-6"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.85rem;">Pengelola Data Sektoral</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.74rem;">Kelola nilai indikator, atur tabel urusan, serta unduh laporan PDF.</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-xs h-100 border-start border-4 border-secondary mb-0">
                <div class="card-body p-2.5">
                    <div class="d-flex align-items-center justify-content-between mb-0.5">
                        <span class="badge bg-secondary text-white text-uppercase" style="font-size: 0.65rem;">User</span>
                        <i class="bi bi-person text-secondary fs-6"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.85rem;">Peninjau (Read-Only)</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.74rem;">Melihat dashboard, meninjau indikator & nilai, serta unduh laporan PDF.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-xs mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>Email Akun</th>
                            <th class="text-center" style="width: 140px;">Peran</th>
                            <th class="text-center" style="width: 120px;">Status</th>
                            <th class="text-center" style="width: 140px;">Riwayat Data</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block">{{ $u->name }}</span>
                                            @if($u->username)
                                                <small class="badge bg-light text-secondary border font-monospace" style="font-size: 0.65rem;">@{{ $u->username }}</small>
                                            @endif
                                            @if($u->id === auth()->id())
                                                <small class="badge bg-light text-primary border" style="font-size: 0.65rem;">(Akun Anda Saat Ini)</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $u->email }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $u->role_badge_class }} text-uppercase" style="font-size: 0.72rem;">
                                        {{ $u->role_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $u->is_active ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $u->data_indikators_count }} entri
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="openEditUserModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ addslashes($u->username ?? '') }}', '{{ $u->email }}', '{{ $u->role }}', {{ $u->is_active ? 1 : 0 }})" 
                                                title="Edit Pengguna">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="d-inline m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-outline-danger btn-confirm-delete" 
                                                        data-message="Hapus akun {{ addslashes($u->name) }} ({{ $u->role_label }})?" 
                                                        title="Hapus Pengguna">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH PENGGUNA -->
<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-header bg-slate-900 text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-person-plus me-1"></i> Tambah Pengguna Baru</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Nama pegawai...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Username Login <span class="text-muted fw-normal">(Admin)</span></label>
                            <input type="text" name="username" class="form-control font-monospace" placeholder="Otomatis dari nama">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="email@datasektoral.go.id">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Peran Sistem <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="user" selected>User (Peninjau)</option>
                                <option value="admin">Admin (Pengelola Data)</option>
                                <option value="super_admin">Super Admin (Akses Penuh)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT PENGGUNA -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formEditUser" action="">
                @csrf
                @method('PUT')
                <div class="modal-header bg-slate-900 text-white">
                    <h6 class="modal-title fw-bold"><i class="bi bi-person-gear me-1"></i> Edit Pengguna</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editUserName" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold">Username Login</label>
                            <input type="text" name="username" id="editUserUsername" class="form-control font-monospace" placeholder="username_login">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Ubah Sandi <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tetap">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Peran Sistem <span class="text-danger">*</span></label>
                            <select name="role" id="editUserRole" class="form-select" required>
                                <option value="user">User (Peninjau)</option>
                                <option value="admin">Admin (Pengelola Data)</option>
                                <option value="super_admin">Super Admin (Akses Penuh)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editUserActive">
                        <label class="form-check-label small fw-semibold" for="editUserActive">Status Akun Aktif</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditUserModal(id, name, username, email, role, isActive) {
        document.getElementById('formEditUser').action = '/users/' + id;
        document.getElementById('editUserName').value = name;
        document.getElementById('editUserUsername').value = username || '';
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserRole').value = role;
        document.getElementById('editUserActive').checked = (isActive === 1 || isActive === true);

        const modal = new bootstrap.Modal(document.getElementById('modalEditUser'));
        modal.show();
    }
</script>
@endpush
