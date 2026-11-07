@extends('layouts.app')

@section('title', 'Katalog ' . $kelompok->nama)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
        @if($kelompok->parent)
            <li class="breadcrumb-item text-muted">{{ $kelompok->parent->nama }}</li>
        @endif
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $kelompok->nama }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- Header Banner Bidang Urusan -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); width: 56px; height: 56px;">
                        <i class="bi bi-folder2-open fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            @if($kelompok->parent)
                                <span class="badge bg-white text-secondary border px-2 py-0.5" style="font-size: 0.72rem;">{{ $kelompok->parent->nama }}</span>
                            @endif
                        </div>
                        <h4 class="fw-bold text-slate-900 mb-1" style="letter-spacing: -0.01em;">{{ $kelompok->nama }}</h4>
                        <p class="text-muted small mb-0">{{ $kelompok->keterangan ?: 'Daftar tabel indikator sektoral di bawah bidang ' . $kelompok->nama }}</p>
                    </div>
                </div>

                <!-- Right controls: Filter Tahun & Tombol Tambah Urusan -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Dropdown Filter Tahun -->
                    <div class="dropdown">
                        <button class="btn btn-sm btn-white border shadow-xs dropdown-toggle fw-semibold text-slate-800 d-inline-flex align-items-center gap-1.5 px-3 py-1.5" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.82rem;">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <span>Tahun: <strong class="text-primary">{{ $selectedTahunNomor }}</strong></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="min-width: 140px; font-size: 0.80rem;">
                            <li class="dropdown-header text-uppercase text-muted" style="font-size: 0.68rem; letter-spacing: 0.05em;">Pilih Tahun Data</li>
                            @foreach($listTahuns as $t)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 {{ $selectedTahunNomor == $t->tahun ? 'active fw-bold' : '' }}" 
                                       href="{{ route('kelompok-urusan.show', ['id' => $kelompok->id, 'tahun' => $t->tahun]) }}">
                                        <span>{{ $t->tahun }}</span>
                                        @if($t->is_default)
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.60rem;">Default</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <button type="button" class="btn btn-sm btn-primary shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5 px-3 py-1.5" data-bs-toggle="modal" data-bs-target="#modalTambahUrusan" style="font-size: 0.82rem;">
                            <i class="bi bi-plus-lg"></i>
                            <span>Tambah Urusan Baru</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Mini Summary Stats -->
            <div class="row g-3 mt-3 pt-3 border-top border-slate-200">
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-2 p-2.5 border shadow-2xs d-flex align-items-center gap-2.5">
                        <div class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-table fs-5"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.70rem; font-weight: 500;">Tabel Urusan</span>
                            <span class="fw-bold text-slate-800" style="font-size: 1.05rem;">{{ $daftarUrusan->count() }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">Tabel</small></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-2 p-2.5 border shadow-2xs d-flex align-items-center gap-2.5">
                        <div class="p-2 rounded-2 bg-info bg-opacity-10 text-info">
                            <i class="bi bi-list-columns-reverse fs-5"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.70rem; font-weight: 500;">Rincian Baris</span>
                            <span class="fw-bold text-slate-800" style="font-size: 1.05rem;">{{ $totalRincianBidang }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">Baris</small></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-2 p-2.5 border shadow-2xs d-flex align-items-center gap-2.5">
                        <div class="p-2 rounded-2 bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle fs-5"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted" style="font-size: 0.70rem; font-weight: 500;">Terisi ({{ $selectedTahunNomor }})</span>
                            <span class="fw-bold text-slate-800" style="font-size: 1.05rem;">{{ $totalTerisiBidang }} <small class="text-muted fw-normal" style="font-size: 0.72rem;">/ {{ $totalRincianBidang }}</small></span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white rounded-2 p-2.5 border shadow-2xs">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-muted" style="font-size: 0.70rem; font-weight: 500;">Kelengkapan Nilai</span>
                            <span class="fw-bold text-slate-800" style="font-size: 0.85rem;">{{ $persentaseBidang }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar {{ $persentaseBidang >= 80 ? 'bg-success' : ($persentaseBidang >= 40 ? 'bg-warning' : 'bg-danger') }}" 
                                 role="progressbar" 
                                 style="width: {{ $persentaseBidang }}%" 
                                 aria-valuenow="{{ $persentaseBidang }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2.5 mb-3">
        <div class="input-group input-group-sm shadow-2xs" style="max-width: 320px;">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search" style="font-size: 0.78rem;"></i></span>
            <input type="text" 
                   id="inputSearchUrusan" 
                   class="form-control bg-white border-start-0 ps-0 text-dark" 
                   placeholder="Cari file atau nama urusan..." 
                   oninput="handleSearchUrusanCards(this.value)"
                   style="font-size: 0.80rem;">
            <button type="button" class="btn btn-white border border-start-0 text-muted d-none" id="btnClearSearchUrusan" onclick="clearSearchUrusanCards()">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <div class="d-flex align-items-center gap-1.5 flex-wrap">
            <span class="small text-muted me-1" id="labelCardCount" style="font-size: 0.75rem;">Menampilkan <strong>{{ $daftarUrusan->count() }}</strong> urusan</span>
            <button type="button" class="btn btn-sm btn-white border shadow-2xs py-1 px-2.5 text-secondary filter-tab-btn active" data-filter="all" onclick="filterCardsByStatus('all', this)" style="font-size: 0.75rem;">
                Semua
            </button>
            <button type="button" class="btn btn-sm btn-white border shadow-2xs py-1 px-2.5 text-secondary filter-tab-btn" data-filter="filled" onclick="filterCardsByStatus('filled', this)" style="font-size: 0.75rem;">
                Terisi
            </button>
            <button type="button" class="btn btn-sm btn-white border shadow-2xs py-1 px-2.5 text-secondary filter-tab-btn" data-filter="empty" onclick="filterCardsByStatus('empty', this)" style="font-size: 0.75rem;">
                Belum Terisi
            </button>
        </div>
    </div>

    <!-- Tampilan File Explorer Grid -->
    <div class="card border-0 shadow-xs rounded-3 p-3 bg-white" style="border: 1px solid #e2e8f0 !important; min-height: 280px;">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-2" id="cardsGridContainer">
            @forelse($daftarUrusan as $u)
                @php
                    $isFullyFilled = $u['total_rincian'] > 0 && $u['total_terisi'] >= $u['total_rincian'];
                    $isPartiallyFilled = $u['total_terisi'] > 0 && $u['total_terisi'] < $u['total_rincian'];
                    $isEmpty = $u['total_terisi'] == 0;
                @endphp
                <div class="col urusan-card-item" 
                     data-nama="{{ strtolower($u['nama']) }}"
                     data-status="{{ $u['total_terisi'] > 0 ? 'filled' : 'empty' }}">
                    <div class="file-explorer-item d-flex align-items-center justify-content-between p-2 rounded-2 position-relative">
                        <!-- Link Utama Pembuka Urusan -->
                        <a href="{{ route('urusan.show', $u['slug']) }}" class="d-flex align-items-center gap-2.5 text-decoration-none text-dark flex-grow-1 text-truncate pe-1">
                            <!-- Icon File Ala Windows Explorer -->
                            <div class="file-icon-wrapper flex-shrink-0 d-flex align-items-center justify-content-center">
                                <i class="bi bi-file-earmark-spreadsheet-fill text-success fs-4"></i>
                            </div>
                            
                            <!-- Label Nama & Detail Ringkas -->
                            <div class="text-truncate">
                                <div class="file-name text-truncate text-slate-800 fw-medium" title="{{ $u['nama'] }}">
                                    {{ $u['nama'] }}
                                </div>
                                <div class="file-meta d-flex align-items-center gap-1.5 text-muted" style="font-size: 0.70rem;">
                                    <span>{{ $u['total_tabel'] }} Halaman</span>
                                    <span>&bull;</span>
                                    <span class="{{ $isFullyFilled ? 'text-success fw-semibold' : ($isPartiallyFilled ? 'text-warning fw-semibold' : 'text-muted') }}">
                                        {{ $u['total_terisi'] }}/{{ $u['total_rincian'] }} terisi
                                    </span>
                                </div>
                            </div>
                        </a>

                        <!-- Menu Aksi Opsi Tambahan (Titik Tiga) -->
                        @if(auth()->check() && auth()->user()->isSuperAdmin())
                            <div class="dropdown flex-shrink-0 ms-1">
                                <button class="btn btn-sm btn-link text-muted p-1 rounded hover-bg-light" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="font-size: 0.78rem;">
                                    <li>
                                        <a class="dropdown-item py-1.5 text-secondary" href="javascript:void(0)" onclick="openModalEditUrusan({{ $u['id'] }}, '{{ addslashes($u['nama']) }}', '{{ $u['icon'] }}')">
                                            <i class="bi bi-pencil me-1.5 text-warning"></i>Ubah Nama
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form method="POST" action="{{ route('urusan.destroy', $u['id']) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus urusan \'{{ addslashes($u['nama']) }}\'? Semua indikator dan data di dalamnya akan terhapus.');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dropdown-item py-1.5 text-danger">
                                                <i class="bi bi-trash3 me-1.5"></i>Hapus
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12 py-5 text-center">
                    <div class="p-3 rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-folder-x text-secondary fs-3 opacity-50"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Folder Kosong</h6>
                    <p class="text-muted small mb-3">Bidang {{ $kelompok->nama }} belum memiliki berkas urusan data.</p>
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahUrusan">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Berkas Urusan
                        </button>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- State Saat Pencarian Tidak Ditemukan -->
        <div id="emptySearchCardsState" class="text-center py-5 d-none">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3 mb-2">
                <i class="bi bi-search text-secondary opacity-50 fs-4"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">Tidak Ditemukan</h6>
            <p class="text-muted small mb-2">Tidak ada berkas urusan yang cocok dengan kata kunci pencarian Anda.</p>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearSearchUrusanCards()">Reset Pencarian</button>
        </div>
    </div>
</div>

<!-- Modal Tambah Urusan Baru (Super Admin) -->
@if(auth()->check() && auth()->user()->isSuperAdmin())
<div class="modal fade" id="modalTambahUrusan" tabindex="-1" aria-labelledby="modalTambahUrusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('urusan.store') }}">
                @csrf
                <input type="hidden" name="kelompok_urusan_id" value="{{ $kelompok->id }}">
                
                <div class="modal-header border-bottom py-3 px-4 bg-white">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="bi bi-file-earmark-plus-fill fs-6"></i>
                        </div>
                        <h6 class="modal-title fw-bold text-slate-900 mb-0" id="modalTambahUrusanLabel" style="font-size: 0.95rem;">
                            Tambah Urusan Baru
                        </h6>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-white">
                    <div class="alert alert-light border small text-secondary mb-3 py-2 px-3 rounded-3 d-flex align-items-center gap-2" style="background-color: #f8fafc !important;">
                        <i class="bi bi-info-circle-fill text-primary fs-6"></i>
                        <span style="font-size: 0.78rem;">Ditambahkan ke bidang <strong>{{ $kelompok->nama }}</strong>.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-slate-700 mb-1.5" style="font-size: 0.80rem;">
                            Nama Urusan / Tabel <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_urusan" class="form-control form-control-sm text-dark px-3 py-2 rounded-2" placeholder="Contoh: Jumlah Sekolah, Jumlah Tenaga Medis" required style="font-size: 0.82rem; border-color: #cbd5e1;">
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-semibold text-slate-700 mb-1.5" style="font-size: 0.80rem;">
                            Deskripsi Singkat <span class="text-muted fw-normal">(Opsional)</span>
                        </label>
                        <textarea name="deskripsi" class="form-control form-control-sm px-3 py-2 rounded-2" rows="2" placeholder="Keterangan singkat cakupan data urusan ini..." style="font-size: 0.82rem; border-color: #cbd5e1;"></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-white border px-3 py-1.5 rounded-2 text-secondary fw-medium" data-bs-dismiss="modal" style="font-size: 0.80rem;">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.80rem;">
                        <i class="bi bi-check2"></i>
                        <span>Simpan Urusan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Urusan (Super Admin) -->
<div class="modal fade" id="modalEditUrusan" tabindex="-1" aria-labelledby="modalEditUrusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" id="formEditUrusan" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="kelompok_urusan_id" value="{{ $kelompok->id }}">
                
                <div class="modal-header border-bottom py-3 px-4 bg-white">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <i class="bi bi-pencil-square fs-6"></i>
                        </div>
                        <h6 class="modal-title fw-bold text-slate-900 mb-0" id="modalEditUrusanLabel" style="font-size: 0.95rem;">
                            Edit Urusan Pemerintahan
                        </h6>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-white">
                    <div class="mb-1">
                        <label class="form-label small fw-semibold text-slate-700 mb-1.5" style="font-size: 0.80rem;">
                            Nama Urusan <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_urusan" id="editNamaUrusanInput" class="form-control form-control-sm text-dark px-3 py-2 rounded-2" required style="font-size: 0.82rem; border-color: #cbd5e1;">
                    </div>
                </div>

                <div class="modal-footer border-top py-2.5 px-4 bg-light d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-white border px-3 py-1.5 rounded-2 text-secondary fw-medium" data-bs-dismiss="modal" style="font-size: 0.80rem;">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary px-3.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size: 0.80rem;">
                        <i class="bi bi-check2"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
.file-explorer-item {
    border: 1px solid transparent;
    transition: all 0.15s ease-in-out;
    background-color: transparent;
    cursor: pointer;
}
.file-explorer-item:hover {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
    border-radius: 6px;
}
.file-icon-wrapper {
    width: 32px;
    height: 32px;
}
.file-name {
    font-size: 0.85rem;
    line-height: 1.25;
}
.file-explorer-item:hover .file-name {
    color: #0284c7 !important;
}
.hover-bg-light:hover {
    background-color: #e2e8f0 !important;
}
.filter-tab-btn.active {
    background-color: #0284c7 !important;
    color: #ffffff !important;
    border-color: #0284c7 !important;
    font-weight: 600;
}
</style>

<script>
let currentFilterStatus = 'all';

function handleSearchUrusanCards(query) {
    const q = (query || '').toLowerCase().trim();
    const clearBtn = document.getElementById('btnClearSearchUrusan');
    if (clearBtn) {
        if (q.length > 0) {
            clearBtn.classList.remove('d-none');
        } else {
            clearBtn.classList.add('d-none');
        }
    }
    applyFilterAndSearch();
}

function clearSearchUrusanCards() {
    const input = document.getElementById('inputSearchUrusan');
    if (input) {
        input.value = '';
        input.focus();
    }
    const clearBtn = document.getElementById('btnClearSearchUrusan');
    if (clearBtn) clearBtn.classList.add('d-none');
    applyFilterAndSearch();
}

function filterCardsByStatus(status, btnElem) {
    currentFilterStatus = status;
    document.querySelectorAll('.filter-tab-btn').forEach(btn => btn.classList.remove('active'));
    if (btnElem) btnElem.classList.add('active');
    applyFilterAndSearch();
}

function applyFilterAndSearch() {
    const q = (document.getElementById('inputSearchUrusan')?.value || '').toLowerCase().trim();
    const items = document.querySelectorAll('.urusan-card-item');
    let visibleCount = 0;

    items.forEach(item => {
        const name = item.getAttribute('data-nama') || '';
        const status = item.getAttribute('data-status') || '';

        const matchesQuery = !q || name.includes(q);
        const matchesStatus = currentFilterStatus === 'all' || status === currentFilterStatus;

        if (matchesQuery && matchesStatus) {
            item.classList.remove('d-none');
            visibleCount++;
        } else {
            item.classList.add('d-none');
        }
    });

    const countLabel = document.getElementById('labelCardCount');
    if (countLabel) {
        countLabel.innerHTML = `Menampilkan <strong>${visibleCount}</strong> urusan`;
    }

    const emptyState = document.getElementById('emptySearchCardsState');
    if (emptyState) {
        if (visibleCount === 0 && items.length > 0) {
            emptyState.classList.remove('d-none');
        } else {
            emptyState.classList.add('d-none');
        }
    }
}

function openModalEditUrusan(id, nama, icon) {
    const form = document.getElementById('formEditUrusan');
    if (form) form.action = `/urusan/${id}`;
    const nameInput = document.getElementById('editNamaUrusanInput');
    if (nameInput) nameInput.value = nama;
    const iconInput = document.getElementById('editIconUrusanInput');
    if (iconInput) iconInput.value = icon || 'bi-table';

    const modal = new bootstrap.Modal(document.getElementById('modalEditUrusan'));
    modal.show();
}
</script>
@endsection
