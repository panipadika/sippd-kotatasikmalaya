@extends('layouts.app')

@section('title', 'Manajemen Tahun Anggaran')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Data Profil</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Manajemen Tahun Anggaran</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2.5">
        <div>
            <h4 class="fw-bold text-slate-900 mb-0.5" style="font-size: 1.15rem;">Manajemen Tahun Anggaran Data</h4>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Kelola daftar tahun data. Struktur indikator tetap konsisten antar tahun tanpa menduplikasi baris.</p>
        </div>
        @if(auth()->check() && auth()->user()->isSuperAdmin())
            <div>
                <button type="button" class="btn btn-primary btn-sm shadow-xs d-flex align-items-center gap-1 py-1 px-2.5" data-bs-toggle="modal" data-bs-target="#modalTambahTahun" style="font-size: 0.78rem;">
                    <i class="bi bi-plus-circle"></i>
                    <span>Tambah Tahun Baru</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Alert Edukasi / Konsep Sistem -->
    <div class="alert alert-primary bg-primary bg-opacity-10 border-primary border-opacity-25 d-flex align-items-center gap-2.5 p-2 px-3 mb-2.5 rounded-2">
        <i class="bi bi-info-circle-fill text-primary fs-5 flex-shrink-0"></i>
        <div class="small" style="font-size: 0.76rem;">
            <strong>Konsep Data Tahunan:</strong> Struktur hierarki indikator disimpan permanen. Menambahkan tahun baru otomatis membuka ruang pengisian nilai baru tanpa menduplikasi indikator.
        </div>
    </div>

    <div class="card border-0 shadow-xs mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 120px;" class="text-center">Tahun</th>
                            <th>Keterangan / Label</th>
                            <th class="text-center" style="width: 150px;">Total Data Terisi</th>
                            <th class="text-center" style="width: 140px;">Status Default</th>
                            <th class="text-center" style="width: 120px;">Status Aktif</th>
                            @if(auth()->check() && auth()->user()->isSuperAdmin())
                                <th class="text-center" style="width: 120px;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tahuns as $t)
                            <tr>
                                <td class="text-center">
                                    <span class="fs-5 fw-bold font-monospace text-slate-900">{{ $t->tahun }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $t->keterangan ?: 'Tahun Anggaran ' . $t->tahun }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-primary border px-2 py-1 fs-6">
                                        {{ $t->data_indikators_count }} nilai
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($t->is_default)
                                        <span class="badge bg-primary text-white px-2 py-1">
                                            <i class="bi bi-star-fill me-1"></i> Default Aktif
                                        </span>
                                    @else
                                        @if(auth()->check() && auth()->user()->isSuperAdmin())
                                            <form method="POST" action="{{ route('tahun.set-default', $t->id) }}" class="m-0">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2 small">
                                                    Jadikan Default
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                                        <form method="POST" action="{{ route('tahun.toggle-status', $t->id) }}" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-sm py-0 px-2 {{ $t->status_aktif ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                                {{ $t->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge {{ $t->status_aktif ? 'bg-success' : 'bg-danger' }}">
                                            {{ $t->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    @endif
                                </td>
                                @if(auth()->check() && auth()->user()->isSuperAdmin())
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('tahun.destroy', $t->id) }}" class="d-inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-confirm-delete" 
                                                    data-message="Hapus tahun {{ $t->tahun }}? Hanya dapat dihapus jika belum memiliki catatan data."
                                                    {{ $t->data_indikators_count > 0 ? 'disabled' : '' }}
                                                    title="{{ $t->data_indikators_count > 0 ? 'Tidak dapat dihapus karena memiliki data' : 'Hapus Tahun' }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada data tahun.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH TAHUN -->
@if(auth()->check() && auth()->user()->isSuperAdmin())
<div class="modal fade" id="modalTambahTahun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('tahun.store') }}">
                @csrf
                <div class="modal-header bg-slate-900 text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-calendar-plus me-1"></i> Tambah Tahun Anggaran Baru
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" class="form-control form-control-lg font-monospace fw-bold" placeholder="Contoh: 2027" min="2000" max="2100" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Keterangan / Label</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Tahun Anggaran 2027">
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="switchDefault">
                        <label class="form-check-label small fw-semibold" for="switchDefault">Jadikan tahun aktif default sistem</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Tahun Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
