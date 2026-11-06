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
                            <span class="badge bg-slate-800 text-info font-monospace px-2 py-0.5" style="font-size: 0.75rem;">{{ $kelompok->kode }}</span>
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
                   placeholder="Cari nama urusan atau tabel..." 
                   oninput="handleSearchUrusanCards(this.value)"
                   style="font-size: 0.80rem;">
            <button type="button" class="btn btn-white border border-start-0 text-muted d-none" id="btnClearSearchUrusan" onclick="clearSearchUrusanCards()">
                <i class="bi bi-x"></i>
            </button>
        </div>

        <div class="d-flex align-items-center gap-1.5">
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

    <!-- Grid Kartu Urusan -->
    <div class="row g-3" id="cardsGridContainer">
        @forelse($daftarUrusan as $u)
            @php
                $isFullyFilled = $u['total_rincian'] > 0 && $u['total_terisi'] >= $u['total_rincian'];
                $isPartiallyFilled = $u['total_terisi'] > 0 && $u['total_terisi'] < $u['total_rincian'];
                $isEmpty = $u['total_terisi'] == 0;
            @endphp
            <div class="col-md-6 col-lg-4 urusan-card-item" 
                 data-nama="{{ strtolower($u['nama']) }}"
                 data-status="{{ $u['total_terisi'] > 0 ? 'filled' : 'empty' }}">
                <div class="card h-100 border-0 shadow-xs rounded-3 urusan-card position-relative overflow-hidden transition-all" style="border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header Kartu -->
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2.5">
                                <div class="rounded-2 p-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                                    <i class="bi {{ $u['icon'] ?: 'bi-file-earmark-spreadsheet' }} fs-5"></i>
                                </div>
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.65rem;">
                                        <i class="bi bi-file-earmark-text me-0.5"></i>{{ $u['total_tabel'] }} Halaman
                                    </span>
                                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="font-size: 0.78rem;">
                                                <li>
                                                    <a class="dropdown-item py-1.5 text-secondary" href="javascript:void(0)" onclick="openModalEditUrusan({{ $u['id'] }}, '{{ addslashes($u['nama']) }}', '{{ $u['icon'] }}')">
                                                        <i class="bi bi-pencil me-1.5 text-warning"></i>Edit Nama Urusan
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('urusan.destroy', $u['id']) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus urusan \'{{ addslashes($u['nama']) }}\'? Semua indikator dan data di dalamnya akan terhapus.');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dropdown-item py-1.5 text-danger">
                                                            <i class="bi bi-trash3 me-1.5"></i>Hapus Urusan
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Judul Urusan -->
                            <h5 class="fw-bold text-slate-900 mb-1.5 urusan-title" style="font-size: 0.95rem; line-height: 1.35;">
                                <a href="{{ route('urusan.show', $u['slug']) }}" class="text-decoration-none text-slate-900 hover-primary">
                                    {{ $u['nama'] }}
                                </a>
                            </h5>
                            @if(!empty($u['deskripsi']))
                                <p class="text-muted small mb-2 line-clamp-2" style="font-size: 0.75rem;">{{ $u['deskripsi'] }}</p>
                            @else
                                <p class="text-muted small mb-2 fst-italic" style="font-size: 0.72rem;">Tabel indikator sektoral {{ $u['nama'] }}</p>
                            @endif
                        </div>

                        <!-- Bagian Bawah Kartu: Status Data & Tombol Aksi -->
                        <div class="pt-2.5 mt-2 border-top border-slate-100">
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <span class="text-muted" style="font-size: 0.70rem;">
                                    <strong>{{ $u['total_rincian'] }}</strong> rincian baris
                                </span>
                                <span class="badge {{ $isFullyFilled ? 'bg-success text-white' : ($isPartiallyFilled ? 'bg-warning text-dark' : 'bg-light text-secondary border') }} px-2 py-0.5 rounded-pill font-monospace" style="font-size: 0.68rem; font-weight: 600;">
                                    {{ $u['total_terisi'] }}/{{ $u['total_rincian'] }} Terisi
                                </span>
                            </div>

                            <div class="progress mb-3" style="height: 4px;">
                                <div class="progress-bar {{ $u['persentase'] >= 80 ? 'bg-success' : ($u['persentase'] >= 40 ? 'bg-warning' : 'bg-danger') }}" 
                                     role="progressbar" 
                                     style="width: {{ $u['persentase'] }}%" 
                                     aria-valuenow="{{ $u['persentase'] }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>

                            <a href="{{ route('urusan.show', $u['slug']) }}" class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1.5 py-1.5 fw-semibold rounded-2" style="font-size: 0.80rem;">
                                <span>Buka Tabel Data</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-xs rounded-3 text-center py-5">
                    <div class="p-3 rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-inbox text-secondary fs-2 opacity-50"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Belum Ada Urusan Pemerintahan</h5>
                    <p class="text-muted small mb-3">Bidang {{ $kelompok->nama }} belum memiliki tabel urusan data sektoral.</p>
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <div>
                            <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahUrusan">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Urusan Pertama
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- State Saat Pencarian Tidak Ditemukan -->
    <div id="emptySearchCardsState" class="text-center py-5 d-none">
        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-3 mb-2">
            <i class="bi bi-search text-secondary opacity-50 fs-3"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1">Urusan Tidak Ditemukan</h6>
        <p class="text-muted small mb-2">Tidak ada urusan yang cocok dengan kata kunci pencarian Anda.</p>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="clearSearchUrusanCards()">Reset Pencarian</button>
    </div>
</div>

<!-- Modal Tambah Urusan Baru (Super Admin) -->
@if(auth()->check() && auth()->user()->isSuperAdmin())
<div class="modal fade" id="modalTambahUrusan" tabindex="-1" aria-labelledby="modalTambahUrusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form method="POST" action="{{ route('urusan.store') }}">
                @csrf
                <input type="hidden" name="kelompok_urusan_id" value="{{ $kelompok->id }}">
                <div class="modal-header border-bottom py-2.5 px-3">
                    <h6 class="modal-title fw-bold text-slate-900" id="modalTambahUrusanLabel">
                        <i class="bi bi-plus-circle text-primary me-1.5"></i>Tambah Urusan Baru
                    </h6>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3.5">
                    <div class="alert alert-light border small text-muted mb-3 py-2 px-2.5">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Urusan akan ditambahkan di bawah bidang <strong>{{ $kelompok->nama }}</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-slate-800">Nama Urusan / Tabel <span class="text-danger">*</span></label>
                        <input type="text" name="nama_urusan" class="form-control form-control-sm text-dark" placeholder="Contoh: Jumlah Sekolah, Jumlah Tenaga Medis, dll." required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-slate-800">Nomor Urutan</label>
                            <input type="number" name="nomor" class="form-control form-control-sm" placeholder="Otomatis jika kosong">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-slate-800">Ikon Bootstrap</label>
                            <input type="text" name="icon" class="form-control form-control-sm" value="bi-table" placeholder="bi-table">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-slate-800">Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control form-control-sm" rows="2" placeholder="Keterangan singkat cakupan data urusan ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3">Simpan Urusan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Urusan (Super Admin) -->
<div class="modal fade" id="modalEditUrusan" tabindex="-1" aria-labelledby="modalEditUrusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <form method="POST" id="formEditUrusan" action="">
                @csrf
                @method('PUT')
                <input type="hidden" name="kelompok_urusan_id" value="{{ $kelompok->id }}">
                <div class="modal-header border-bottom py-2.5 px-3">
                    <h6 class="modal-title fw-bold text-slate-900" id="modalEditUrusanLabel">
                        <i class="bi bi-pencil-square text-warning me-1.5"></i>Edit Urusan Pemerintahan
                    </h6>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3.5">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-slate-800">Nama Urusan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_urusan" id="editNamaUrusanInput" class="form-control form-control-sm text-dark" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-slate-800">Ikon Bootstrap</label>
                        <input type="text" name="icon" id="editIconUrusanInput" class="form-control form-control-sm" placeholder="bi-table">
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-3">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
.hover-primary:hover {
    color: #0284c7 !important;
}
.urusan-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.urusan-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
    border-color: #cbd5e1 !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
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
