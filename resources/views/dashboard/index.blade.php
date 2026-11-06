@extends('layouts.app')

@section('title', 'Dashboard Ringkasan Data Sektoral')

@section('content')
<div class="container-fluid p-0">
    <!-- Header Page Info -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2.5 mb-3">
        <div>
            <h4 class="fw-bold text-slate-900 mb-1" style="font-size: 1.25rem; letter-spacing: -0.01em;">
                Dashboard Ringkasan Data Sektoral
            </h4>
            <p class="text-muted small mb-0" style="font-size: 0.80rem;">
                Pemantauan hierarki Kelompok Urusan, Urusan Pemerintahan, dan Indikator Sektoral Kota Tasikmalaya Tahun <strong id="dashboardSelectedTahunSpan" class="text-primary">{{ $selectedTahunNomor }}</strong>.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Year Selector Input Group (Clean & Neatly Spaced) -->
            <div class="input-group input-group-sm shadow-xs" style="width: auto;">
                <span class="input-group-text bg-white border-end-0 text-muted fw-semibold" style="font-size: 0.78rem;">
                    <i class="bi bi-calendar3 me-1.5 text-primary"></i>Tahun:
                </span>
                <select id="dashboardTahunSelect" name="tahun" class="form-select form-select-sm fw-bold bg-white border-start-0 text-slate-800" onchange="changeDashboardYear(this.value)" style="min-width: 95px; font-size: 0.82rem; cursor: pointer; border-color: #cbd5e1;">
                    @foreach($listTahunGlobal as $th)
                        <option value="{{ $th->tahun }}" {{ $selectedTahunNomor == $th->tahun ? 'selected' : '' }}>
                            {{ $th->tahun }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- STATISTIC CARDS ROW (4 KPI Cards) -->
    <div class="row g-2.5 mb-3">
        <!-- Card 1: Kelompok Urusan (Folder Utama: A s.d. H) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden position-relative" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="bi bi-folder-fill fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <span class="text-muted small fw-semibold text-uppercase d-block text-truncate" style="font-size: 0.68rem; letter-spacing: 0.06em;">Kelompok Urusan</span>
                        <div class="d-flex align-items-baseline gap-1.5">
                            <h3 class="fw-bold text-slate-900 mb-0" id="kpiTotalKelompok" style="font-size: 1.45rem;">{{ $totalKelompok }}</h3>
                            <span class="text-muted small" style="font-size: 0.72rem;">Kategori</span>
                        </div>
                        <small class="text-secondary text-truncate d-block mt-0.5" style="font-size: 0.70rem;">Kategori A s.d. H</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Urusan Pemerintahan (Folder Bidang di dalam Kelompok) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden position-relative" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(13, 148, 136, 0.1); color: #0d9488;">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <span class="text-muted small fw-semibold text-uppercase d-block text-truncate" style="font-size: 0.68rem; letter-spacing: 0.06em;">Urusan Pemerintahan</span>
                        <div class="d-flex align-items-baseline gap-1.5">
                            <h3 class="fw-bold text-slate-900 mb-0" id="kpiTotalUrusan" style="font-size: 1.45rem;">{{ $totalUrusan }}</h3>
                            <span class="text-muted small" style="font-size: 0.72rem;">Bidang</span>
                        </div>
                        <small class="text-secondary text-truncate d-block mt-0.5" style="font-size: 0.70rem;">Pendidikan, Kesehatan, PU, dll.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Indikator Sektoral (Tabel di dalam Urusan) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden position-relative" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(217, 119, 6, 0.1); color: #d97706;">
                        <i class="bi bi-table fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <span class="text-muted small fw-semibold text-uppercase d-block text-truncate" style="font-size: 0.68rem; letter-spacing: 0.06em;">Indikator Sektoral</span>
                        <div class="d-flex align-items-baseline gap-1.5">
                            <h3 class="fw-bold text-slate-900 mb-0" id="kpiTotalIndikator" style="font-size: 1.45rem;">{{ $totalIndikator }}</h3>
                            <span class="text-muted small" style="font-size: 0.72rem;">Tabel Aktif</span>
                        </div>
                        <small class="text-secondary text-truncate d-block mt-0.5" id="kpiSubIndikator" style="font-size: 0.70rem;">{{ $totalRincianData }} Rincian Baris Data</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Kelengkapan Data Nilai Terpilih -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden position-relative" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="bi bi-check2-circle fs-4"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <span class="text-muted small fw-semibold text-uppercase d-block text-truncate" id="kpiTahunTitle" style="font-size: 0.68rem; letter-spacing: 0.06em;">Kelengkapan ({{ $selectedTahunNomor }})</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="fw-bold text-slate-900 mb-0" id="kpiPersentase" style="font-size: 1.45rem;">{{ $persentaseKelengkapan }}%</h3>
                            <small class="text-muted fw-semibold" id="kpiTerisiCount" style="font-size: 0.75rem;">({{ $totalDataTerisi }}/{{ $totalRincianData }} Terisi)</small>
                        </div>
                        <div class="progress mt-1.5 rounded-pill" style="height: 6px; background-color: #e2e8f0;">
                            <div class="progress-bar rounded-pill {{ $persentaseKelengkapan >= 80 ? 'bg-success' : ($persentaseKelengkapan >= 50 ? 'bg-warning' : 'bg-primary') }}" id="kpiProgressBar" role="progressbar" style="width: {{ $persentaseKelengkapan }}%" aria-valuenow="{{ $persentaseKelengkapan }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row g-2.5 mb-3">
        <!-- Chart 1: Perbandingan Kelengkapan per Kelompok Urusan (Bar Chart) -->
        <div class="col-12 col-lg-8">
            <div class="card h-100 border-0 shadow-xs rounded-3">
                <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-1 rounded bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <h6 class="fw-bold text-slate-900 mb-0" id="chartKelompokTitle" style="font-size: 0.88rem;">
                            Persentase Kelengkapan per Kelompok Urusan ({{ $selectedTahunNomor }})
                        </h6>
                    </div>
                    <span class="badge bg-light text-secondary border rounded-pill px-2" id="chartKelompokBadge" style="font-size: 0.68rem;">
                        Tahun {{ $selectedTahunNomor }}
                    </span>
                </div>
                <div class="card-body p-3">
                    <div style="height: 230px; position: relative;">
                        <canvas id="kelompokChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Tren Pengisian Data Antar Tahun (Line/Area Chart) -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 border-0 shadow-xs rounded-3">
                <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-1 rounded bg-success bg-opacity-10 text-success">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h6 class="fw-bold text-slate-900 mb-0" style="font-size: 0.88rem;">Tren Data Antar Tahun</h6>
                    </div>
                    <small class="text-muted" style="font-size: 0.70rem;">Jumlah Nilai Terisi</small>
                </div>
                <div class="card-body p-3">
                    <div style="height: 230px; position: relative;">
                        <canvas id="trenTahunChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SUMMARY TABLE & RECENT UPDATES ROW -->
    <div class="row g-2.5 mb-3">
        <!-- Left Column: Status & Rangkuman Hirarkis Lengkap -->
        <div class="col-12 {{ (auth()->check() && auth()->user()->isAdmin()) ? 'col-xl-8' : 'col-xl-12' }}">
            <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden">
                <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-1 rounded bg-info bg-opacity-10 text-info">
                            <i class="bi bi-layers-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-900 mb-0" style="font-size: 0.88rem;">Rangkuman Hierarki & Status Pengisian</h6>
                            <small class="text-muted" style="font-size: 0.70rem;">Kelompok Urusan &rarr; Urusan Pemerintahan &rarr; Indikator Sektoral</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text bg-light border-end-0 py-0 text-muted"><i class="bi bi-search" style="font-size: 0.75rem;"></i></span>
                            <input type="text" id="tableFilterInput" class="form-control form-control-sm bg-light border-start-0 ps-0" placeholder="Cari urusan/indikator..." onkeyup="filterSummaryTable(this.value)" style="font-size: 0.75rem;">
                        </div>
                        <button type="button" class="btn btn-sm btn-light border py-1 px-2 text-secondary" onclick="toggleFilterOnlyActive()" id="btnFilterActive" title="Tampilkan hanya urusan yang memiliki indikator aktif" style="font-size: 0.72rem;">
                            <i class="bi bi-funnel me-1"></i>Hanya Aktif
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 520px;">
                        <table class="table table-hover align-middle mb-0" id="summaryTable">
                            <thead class="table-light sticky-top" style="z-index: 5;">
                                <tr style="font-size: 0.72rem; letter-spacing: 0.03em;" class="text-uppercase text-secondary">
                                    <th style="width: 55px;" class="ps-3">Kode</th>
                                    <th>Kelompok & Urusan Pemerintahan</th>
                                    <th class="text-center" style="width: 90px;">Indikator</th>
                                    <th class="text-center" style="width: 85px;">Rincian</th>
                                    <th class="text-center" style="width: 80px;">Terisi</th>
                                    <th style="width: 140px;">Kelengkapan</th>
                                    <th class="text-center" style="width: 85px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="kelompokStatsTbody">
                                @forelse($kelompokStats as $ks)
                                    <!-- Kelompok Urusan Header Row -->
                                    <tr class="table-light fw-bold kelompok-header-row" data-kelompok-id="{{ $ks['id'] }}" style="border-top: 2px solid #e2e8f0; background-color: #f8fafc;">
                                        <td class="ps-3">
                                            <span class="badge bg-slate-800 text-info font-monospace" style="font-size: 0.72rem;">{{ $ks['kode'] }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <span class="text-slate-900 fw-bold" style="font-size: 0.85rem;">{{ $ks['nama'] }}</span>
                                                <span class="badge bg-light text-secondary border font-normal" style="font-size: 0.65rem;">{{ $ks['total_urusan'] }} Urusan</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $ks['total_indikator'] > 0 ? 'bg-primary text-white' : 'bg-light text-muted border' }}" style="font-size: 0.72rem;">{{ $ks['total_indikator'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-white text-dark border" style="font-size: 0.72rem;">{{ $ks['total_rincian'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-white text-primary border fw-bold" style="font-size: 0.72rem;">{{ $ks['total_terisi'] }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar {{ $ks['persentase'] >= 80 ? 'bg-success' : ($ks['persentase'] >= 40 ? 'bg-warning' : 'bg-danger') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $ks['persentase'] }}%" 
                                                         aria-valuenow="{{ $ks['persentase'] }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="small fw-bold text-muted" style="width: 42px; text-align: right; font-size: 0.75rem;">{{ $ks['persentase'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted small" style="font-size: 0.70rem;">Kelompok</span>
                                        </td>
                                    </tr>

                                    <!-- Urusan Pemerintahan Rows di bawah Kelompok ini -->
                                    @if(!empty($ks['daftar_urusan']))
                                        @foreach($ks['daftar_urusan'] as $u)
                                            <tr class="urusan-data-row {{ $u['total_indikator'] > 0 ? 'has-active-indicators' : 'has-no-indicators' }}" data-kelompok-id="{{ $ks['id'] }}" style="{{ $u['total_indikator'] > 0 ? 'background-color: #ffffff;' : '' }}">
                                                <td class="ps-4">
                                                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.68rem;">↳ {{ $u['kode'] }}</span>
                                                </td>
                                                <td class="ps-3">
                                                    <a href="{{ route('kelompok-urusan.show', $u['id']) }}" class="small text-slate-900 fw-bold d-inline-flex align-items-center gap-1.5 text-decoration-none hover-primary mb-1" style="font-size: 0.82rem;" title="Buka Katalog Bidang {{ $u['nama'] }}">
                                                        <span>{{ $u['nama'] }}</span>
                                                        <i class="bi bi-box-arrow-up-right text-primary opacity-50" style="font-size: 0.70rem;"></i>
                                                    </a>
                                                    
                                                    <!-- List Indikator Sektoral (Tabel) -->
                                                    @if(!empty($u['daftar_indikator']) && count($u['daftar_indikator']) > 0)
                                                        <div class="d-flex flex-wrap gap-1.5 mt-1">
                                                            @foreach($u['daftar_indikator'] as $ind)
                                                                <a href="{{ route('urusan.show', ['id' => $ind['slug']]) }}" 
                                                                   class="indicator-chip d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-2 border text-decoration-none shadow-xs text-nowrap" 
                                                                   title="Buka tabel {{ $ind['nama'] }} ({{ $ind['total_terisi'] }}/{{ $ind['total_rincian'] }} Terisi)">
                                                                    <i class="bi bi-table text-primary" style="font-size: 0.68rem;"></i>
                                                                    <span class="text-slate-800 fw-semibold text-nowrap" style="font-size: 0.72rem;">{{ $ind['nama'] }}</span>
                                                                    <span class="badge {{ $ind['persentase'] >= 100 ? 'bg-success text-white' : ($ind['persentase'] >= 50 ? 'bg-warning text-dark' : 'bg-secondary text-white') }} px-1.5 py-0.5 rounded-pill" style="font-size: 0.62rem; font-weight: 600;">
                                                                        {{ $ind['total_terisi'] }}/{{ $ind['total_rincian'] }}
                                                                    </span>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted" style="font-size: 0.68rem; font-style: italic;">Belum ada tabel indikator</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $u['total_indikator'] > 0 ? 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25' : 'bg-light text-muted' }}" style="font-size: 0.72rem;">
                                                        {{ $u['total_indikator'] }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-white text-secondary border" style="font-size: 0.72rem;">{{ $u['total_rincian'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-white text-primary border" style="font-size: 0.72rem;">{{ $u['total_terisi'] }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="progress flex-grow-1" style="height: 5px;">
                                                            <div class="progress-bar {{ $u['persentase'] >= 80 ? 'bg-success' : ($u['persentase'] >= 40 ? 'bg-warning' : 'bg-danger') }}" 
                                                                 role="progressbar" 
                                                                 style="width: {{ $u['persentase'] }}%" 
                                                                 aria-valuenow="{{ $u['persentase'] }}" 
                                                                 aria-valuemin="0" 
                                                                 aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <span class="small text-muted" style="width: 42px; text-align: right; font-size: 0.72rem;">{{ $u['persentase'] }}%</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('kelompok-urusan.show', $u['id']) }}" 
                                                       class="btn btn-xs btn-outline-primary py-0.5 px-2 rounded-2 shadow-none" 
                                                       title="Buka katalog bidang {{ $u['nama'] }}" 
                                                       style="font-size: 0.70rem;">
                                                        <i class="bi bi-grid-fill me-0.5"></i> Katalog
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                            Belum ada data kelompok urusan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->check() && auth()->user()->isAdmin())
        <!-- Right Column: Feed Pembaruan Data Nilai Terakhir (Hanya Admin / Super Admin) -->
        <div class="col-12 col-xl-4">
            <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden">
                <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-1 rounded bg-secondary bg-opacity-10 text-secondary">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-900 mb-0" style="font-size: 0.88rem;">Pembaruan Terakhir</h6>
                            <small class="text-muted" style="font-size: 0.70rem;">Feed data nilai yang baru diperbarui</small>
                        </div>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('activity.index') }}" class="small text-decoration-none" style="font-size: 0.75rem;">Semua Log &rarr;</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div id="recentUpdatesList" class="list-group list-group-flush" style="max-height: 520px; overflow-y: auto;">
                        @forelse($recentUpdates as $upd)
                            <div class="list-group-item p-2.5 px-3 border-bottom-subtle">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.68rem; font-weight: 600;">
                                        {{ $upd->indikator->urusan->nama_urusan ?? 'Indikator Sektoral' }}
                                    </span>
                                    <small class="text-muted font-monospace" style="font-size: 0.68rem;">
                                        {{ $upd->updated_at->diffForHumans() }}
                                    </small>
                                </div>
                                <h6 class="fw-semibold text-slate-900 mb-1" style="font-size: 0.78rem;">
                                    {{ $upd->indikator->nama_indikator ?? '-' }}
                                </h6>
                                <div class="d-flex align-items-center justify-content-between text-muted small mt-1">
                                    <div>
                                        Tahun <strong>{{ $upd->tahun->tahun ?? '-' }}</strong>: 
                                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-1.5 py-0.5 border border-success border-opacity-25">
                                            {{ $upd->formatted_nilai }} {{ $upd->indikator->satuan ?? '' }}
                                        </span>
                                    </div>
                                    <small class="text-secondary text-truncate ms-2" style="font-size: 0.68rem;" title="{{ $upd->updatedByUser->name ?? 'Administrator Data Sektoral' }}">
                                        {{ $upd->updatedByUser->name ?? 'Admin' }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-card-checklist fs-2 d-block mb-1 opacity-50"></i>
                                <span class="small">Belum ada riwayat pembaruan data nilai.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    .indicator-chip {
        background-color: #ffffff;
        border-color: #cbd5e1 !important;
        white-space: nowrap !important;
        flex-shrink: 0;
        transition: all 0.15s ease-in-out;
    }
    .indicator-chip:hover {
        background-color: #eff6ff;
        border-color: #93c5fd !important;
        transform: translateY(-1px);
    }
</style>
@endsection

@push('scripts')
<script>
    window.kelompokChartInstance = null;
    window.trenTahunChartInstance = null;
    window.kelompokFullNames = @json($kelompokStats->map(fn($ks) => $ks['kode'] . ' - ' . $ks['nama'])->values());

    function initDashboardCharts() {
        // Bar Chart Kelompok
        const ctxKelompok = document.getElementById('kelompokChart');
        if (ctxKelompok) {
            if (typeof Chart !== 'undefined') {
                const existing = Chart.getChart(ctxKelompok);
                if (existing) existing.destroy();
            }
            const labels = @json($kelompokStats->map(function($ks) { 
                return $ks['kode'] . ' (' . \Illuminate\Support\Str::limit($ks['nama'], 16) . ')'; 
            })->values());
            const percentages = @json($kelompokStats->pluck('persentase')->values());

            window.kelompokChartInstance = new Chart(ctxKelompok, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Persentase Kelengkapan (%)',
                        data: percentages,
                        backgroundColor: 'rgba(37, 99, 235, 0.85)',
                        borderColor: 'rgb(29, 78, 216)',
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: function(items) {
                                    return window.kelompokFullNames[items[0].dataIndex] || '';
                                },
                                label: function(context) {
                                    return ' Kelengkapan: ' + context.parsed.y + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Line Chart Tren Antar Tahun
        const ctxTren = document.getElementById('trenTahunChart');
        if (ctxTren) {
            if (typeof Chart !== 'undefined') {
                const existing = Chart.getChart(ctxTren);
                if (existing) existing.destroy();
            }
            window.trenTahunChartInstance = new Chart(ctxTren, {
                type: 'line',
                data: {
                    labels: @json($chartTahuns),
                    datasets: [{
                        label: 'Total Nilai Terisi',
                        data: @json($chartDataCounts),
                        fill: true,
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderColor: 'rgb(16, 185, 129)',
                        tension: 0.35,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    }

    // ==========================================
    // SILENT REALTIME DASHBOARD ENGINE
    // ==========================================
    let isDashboardSyncing = false;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function syncDashboardRealtime(customYear = null, isUserAction = false) {
        if (isDashboardSyncing) return;
        isDashboardSyncing = true;

        const yearSelect = document.getElementById('dashboardTahunSelect');
        const targetYear = customYear || (yearSelect ? yearSelect.value : '{{ $selectedTahunNomor }}');

        const summaryTable = document.getElementById('summaryTable');
        if (isUserAction && summaryTable) {
            summaryTable.style.transition = 'opacity 0.15s ease';
            summaryTable.style.opacity = '0.5';
        }

        try {
            const url = `{{ route('dashboard') }}?realtime=1&tahun=${encodeURIComponent(targetYear)}`;
            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) throw new Error('Sync response not ok: ' + res.status);
            const data = await res.json();

            if (data && data.success) {
                // 1. Update Header Year text
                const headerYear = document.getElementById('dashboardSelectedTahunSpan');
                if (headerYear) headerYear.textContent = data.selectedTahunNomor;

                // Sync year dropdown options dynamically from system listTahuns
                if (yearSelect && Array.isArray(data.listTahuns) && data.listTahuns.length > 0) {
                    const currentOpts = Array.from(yearSelect.options).map(o => String(o.value));
                    const newOpts = data.listTahuns.map(t => String(t.tahun));
                    const isDifferent = currentOpts.length !== newOpts.length || !currentOpts.every((v, i) => v === newOpts[i]);
                    if (isDifferent) {
                        yearSelect.innerHTML = data.listTahuns.map(t => 
                            `<option value="${t.tahun}" ${String(t.tahun) === String(data.selectedTahunNomor) ? 'selected' : ''}>${t.tahun}</option>`
                        ).join('');
                    } else {
                        yearSelect.value = data.selectedTahunNomor;
                    }
                } else if (yearSelect) {
                    yearSelect.value = data.selectedTahunNomor;
                }

                // 2. Update KPI Cards
                const kpiKelompok = document.getElementById('kpiTotalKelompok');
                if (kpiKelompok) kpiKelompok.textContent = data.totalKelompok;

                const kpiUrusan = document.getElementById('kpiTotalUrusan');
                if (kpiUrusan) kpiUrusan.textContent = data.totalUrusan;

                const kpiIndikator = document.getElementById('kpiTotalIndikator');
                if (kpiIndikator) kpiIndikator.textContent = data.totalIndikator;

                const kpiSubInd = document.getElementById('kpiSubIndikator');
                if (kpiSubInd) kpiSubInd.textContent = `${data.totalRincianData} Rincian Baris Data`;

                const kpiTitle = document.getElementById('kpiTahunTitle');
                if (kpiTitle) kpiTitle.textContent = `Kelengkapan (${data.selectedTahunNomor})`;

                const kpiPct = document.getElementById('kpiPersentase');
                if (kpiPct) kpiPct.textContent = `${data.persentaseKelengkapan}%`;

                const kpiCount = document.getElementById('kpiTerisiCount');
                if (kpiCount) kpiCount.textContent = `(${data.totalDataTerisi}/${data.totalRincianData} Terisi)`;

                const kpiProgress = document.getElementById('kpiProgressBar');
                if (kpiProgress) {
                    kpiProgress.style.width = `${data.persentaseKelengkapan}%`;
                    kpiProgress.setAttribute('aria-valuenow', data.persentaseKelengkapan);
                    kpiProgress.className = `progress-bar rounded-pill ${data.persentaseKelengkapan >= 80 ? 'bg-success' : (data.persentaseKelengkapan >= 50 ? 'bg-warning' : 'bg-primary')}`;
                }

                // 3. Update Chart 1 (Bar Kelompok)
                const chartKelTitle = document.getElementById('chartKelompokTitle');
                if (chartKelTitle) chartKelTitle.textContent = `Persentase Kelengkapan per Kelompok Urusan (${data.selectedTahunNomor})`;

                const chartKelBadge = document.getElementById('chartKelompokBadge');
                if (chartKelBadge) chartKelBadge.textContent = `Tahun ${data.selectedTahunNomor}`;

                const statsArray = Array.isArray(data.kelompokStats) ? data.kelompokStats : Object.values(data.kelompokStats || {});

                if (window.kelompokChartInstance && statsArray.length > 0) {
                    const labels = statsArray.map(ks => {
                        const shortName = ks.nama.length > 16 ? ks.nama.substring(0, 16) + '...' : ks.nama;
                        return `${ks.kode} (${shortName})`;
                    });
                    const pcts = statsArray.map(ks => ks.persentase);
                    window.kelompokFullNames = statsArray.map(ks => `${ks.kode} - ${ks.nama}`);

                    window.kelompokChartInstance.data.labels = labels;
                    window.kelompokChartInstance.data.datasets[0].data = pcts;
                    window.kelompokChartInstance.update();
                }

                // 4. Update Chart 2 (Tren Tahun)
                if (window.trenTahunChartInstance && Array.isArray(data.chartTahuns)) {
                    window.trenTahunChartInstance.data.labels = data.chartTahuns;
                    window.trenTahunChartInstance.data.datasets[0].data = data.chartDataCounts;
                    window.trenTahunChartInstance.update();
                }

                // 5. Update Table Rows
                renderRealtimeTable(statsArray);

                // 6. Update Recent Updates List
                renderRealtimeRecent(data.recentUpdates);

                // 7. Update URL without page reload
                if (isUserAction && window.history && window.history.replaceState) {
                    const currentUrl = new URL(window.location);
                    currentUrl.searchParams.set('tahun', data.selectedTahunNomor);
                    window.history.replaceState({}, '', currentUrl);
                }
            }
        } catch (err) {
            console.warn('Realtime dashboard sync skipped:', err);
        } finally {
            if (summaryTable) {
                summaryTable.style.opacity = '1';
            }
            isDashboardSyncing = false;
        }
    }

    function renderRealtimeTable(stats) {
        const tbody = document.getElementById('kelompokStatsTbody');
        const statsArray = Array.isArray(stats) ? stats : Object.values(stats || {});
        if (!tbody) return;

        if (statsArray.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-2 d-block mb-1"></i>Belum ada data kelompok urusan.</td></tr>`;
            return;
        }

        let html = '';
        statsArray.forEach(ks => {
            const pClass = ks.persentase >= 80 ? 'bg-success' : (ks.persentase >= 40 ? 'bg-warning' : 'bg-danger');
            const indBadgeClass = ks.total_indikator > 0 ? 'bg-primary text-white' : 'bg-light text-muted border';

            html += `
                <tr class="table-light fw-bold kelompok-header-row" data-kelompok-id="${ks.id}" style="border-top: 2px solid #e2e8f0; background-color: #f8fafc;">
                    <td class="ps-3"><span class="badge bg-slate-800 text-info font-monospace" style="font-size: 0.72rem;">${escapeHtml(ks.kode)}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="text-slate-900 fw-bold" style="font-size: 0.85rem;">${escapeHtml(ks.nama)}</span>
                            <span class="badge bg-light text-secondary border font-normal" style="font-size: 0.65rem;">${ks.total_urusan} Urusan</span>
                        </div>
                    </td>
                    <td class="text-center"><span class="badge ${indBadgeClass}" style="font-size: 0.72rem;">${ks.total_indikator}</span></td>
                    <td class="text-center"><span class="badge bg-white text-dark border" style="font-size: 0.72rem;">${ks.total_rincian}</span></td>
                    <td class="text-center"><span class="badge bg-white text-primary border fw-bold" style="font-size: 0.72rem;">${ks.total_terisi}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar ${pClass}" role="progressbar" style="width: ${ks.persentase}%" aria-valuenow="${ks.persentase}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="small fw-bold text-muted" style="width: 42px; text-align: right; font-size: 0.75rem;">${ks.persentase}%</span>
                        </div>
                    </td>
                    <td class="text-center"><span class="text-muted small" style="font-size: 0.70rem;">Kelompok</span></td>
                </tr>
            `;

            const daftarUrusan = Array.isArray(ks.daftar_urusan) ? ks.daftar_urusan : Object.values(ks.daftar_urusan || {});
            if (daftarUrusan.length > 0) {
                daftarUrusan.forEach(u => {
                    const spClass = u.persentase >= 80 ? 'bg-success' : (u.persentase >= 40 ? 'bg-warning' : 'bg-danger');
                    let indChips = '';
                    let actionBtn = '<span class="text-muted" style="font-size: 0.68rem;">—</span>';

                    const daftarIndikator = Array.isArray(u.daftar_indikator) ? u.daftar_indikator : Object.values(u.daftar_indikator || {});
                    if (daftarIndikator.length > 0) {
                        indChips = '<div class="d-flex flex-wrap gap-1.5 mt-1">';
                        daftarIndikator.forEach(ind => {
                            const badgeColor = ind.persentase >= 100 ? 'bg-success text-white' : (ind.persentase >= 50 ? 'bg-warning text-dark' : 'bg-secondary text-white');
                            const targetUrl = `{{ url('/urusan') }}/${encodeURIComponent(ind.slug)}`;
                            indChips += `
                                <a href="${targetUrl}" 
                                   class="indicator-chip d-inline-flex align-items-center gap-1.5 px-2.5 py-1 rounded-2 border text-decoration-none shadow-xs text-nowrap" 
                                   title="Buka tabel ${escapeHtml(ind.nama)} (${ind.total_terisi}/${ind.total_rincian} Terisi)">
                                    <i class="bi bi-table text-primary" style="font-size: 0.68rem;"></i>
                                    <span class="text-slate-800 fw-semibold text-nowrap" style="font-size: 0.72rem;">${escapeHtml(ind.nama)}</span>
                                    <span class="badge ${badgeColor} px-1.5 py-0.5 rounded-pill" style="font-size: 0.62rem; font-weight: 600;">${ind.total_terisi}/${ind.total_rincian}</span>
                                </a>
                            `;
                        });
                        indChips += '</div>';

                        const firstSlug = daftarIndikator[0].slug;
                        actionBtn = `
                            <a href="{{ url('/urusan') }}/${encodeURIComponent(firstSlug)}" 
                               class="btn btn-xs btn-outline-primary py-0.5 px-1.5 shadow-none" 
                               title="Buka tabel indikator" 
                               style="font-size: 0.68rem;">
                                Buka <i class="bi bi-arrow-right-short"></i>
                            </a>
                        `;
                    } else {
                        indChips = '<span class="text-muted" style="font-size: 0.68rem; font-style: italic;">Belum ada tabel indikator</span>';
                    }

                    const indCountBadge = u.total_indikator > 0 
                        ? `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.72rem;">${u.total_indikator}</span>`
                        : `<span class="badge bg-light text-muted" style="font-size: 0.72rem;">0</span>`;

                    html += `
                        <tr class="urusan-data-row ${u.total_indikator > 0 ? 'has-active-indicators' : 'has-no-indicators'}" data-kelompok-id="${ks.id}" style="${u.total_indikator > 0 ? 'background-color: #ffffff;' : ''}">
                            <td class="ps-4"><span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.68rem;">↳ ${escapeHtml(u.kode)}</span></td>
                            <td class="ps-3">
                                <span class="small text-slate-800 fw-semibold d-block" style="font-size: 0.80rem;">${escapeHtml(u.nama)}</span>
                                ${indChips}
                            </td>
                            <td class="text-center">${indCountBadge}</td>
                            <td class="text-center"><span class="badge bg-white text-secondary border" style="font-size: 0.72rem;">${u.total_rincian}</span></td>
                            <td class="text-center"><span class="badge bg-white text-primary border fw-bold" style="font-size: 0.72rem;">${u.total_terisi}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 5px;">
                                        <div class="progress-bar ${spClass}" role="progressbar" style="width: ${u.persentase}%" aria-valuenow="${u.persentase}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="small text-muted" style="width: 42px; text-align: right; font-size: 0.72rem;">${u.persentase}%</span>
                                </div>
                            </td>
                            <td class="text-center">${actionBtn}</td>
                        </tr>
                    `;
                });
            }
        });

        tbody.innerHTML = html;

        // Re-apply filter if user has entered search keyword or toggled active
        const filterInput = document.getElementById('tableFilterInput');
        if ((filterInput && filterInput.value.trim() !== '') || onlyActiveFilterState) {
            filterSummaryTable(filterInput ? filterInput.value : '');
        }
    }

    function renderRealtimeRecent(recent) {
        const list = document.getElementById('recentUpdatesList');
        if (!list) return;
        const recentArray = Array.isArray(recent) ? recent : Object.values(recent || {});

        if (recentArray.length === 0) {
            list.innerHTML = `<div class="text-center py-5 text-muted"><i class="bi bi-card-checklist fs-2 d-block mb-1 opacity-50"></i><span class="small">Belum ada riwayat pembaruan data nilai.</span></div>`;
            return;
        }

        let html = '';
        recentArray.forEach(upd => {
            html += `
                <div class="list-group-item p-2.5 px-3 border-bottom-subtle">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.68rem; font-weight: 600;">
                            ${escapeHtml(upd.urusan)}
                        </span>
                        <small class="text-muted font-monospace" style="font-size: 0.68rem;">
                            ${escapeHtml(upd.time_ago)}
                        </small>
                    </div>
                    <h6 class="fw-semibold text-slate-900 mb-1" style="font-size: 0.78rem;">
                        ${escapeHtml(upd.indikator)}
                    </h6>
                    <div class="d-flex align-items-center justify-content-between text-muted small mt-1">
                        <div>
                            Tahun <strong>${escapeHtml(upd.tahun)}</strong>: 
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-1.5 py-0.5 border border-success border-opacity-25">
                                ${escapeHtml(upd.nilai)} ${escapeHtml(upd.satuan)}
                            </span>
                        </div>
                        <small class="text-secondary text-truncate ms-2" style="font-size: 0.68rem;" title="${escapeHtml(upd.user)}">
                            ${escapeHtml(upd.user)}
                        </small>
                    </div>
                </div>
            `;
        });

        list.innerHTML = html;
    }

    function changeDashboardYear(newYear) {
        syncDashboardRealtime(newYear, true);
    }

    let onlyActiveFilterState = false;
    function toggleFilterOnlyActive() {
        onlyActiveFilterState = !onlyActiveFilterState;
        const btn = document.getElementById('btnFilterActive');
        if (btn) {
            if (onlyActiveFilterState) {
                btn.classList.remove('btn-light', 'text-secondary');
                btn.classList.add('btn-primary', 'text-white');
            } else {
                btn.classList.remove('btn-primary', 'text-white');
                btn.classList.add('btn-light', 'text-secondary');
            }
        }
        filterSummaryTable(document.getElementById('tableFilterInput')?.value || '');
    }

    function filterSummaryTable(query) {
        const q = (query || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#summaryTable tbody tr');

        rows.forEach(row => {
            const isKelompok = row.classList.contains('kelompok-header-row');
            const hasActive = row.classList.contains('has-active-indicators');
            const rowText = row.textContent.toLowerCase();
            const matchesQuery = q === '' || rowText.includes(q);

            if (isKelompok) {
                row.style.display = '';
            } else {
                if (onlyActiveFilterState && !hasActive) {
                    row.style.display = 'none';
                } else if (!matchesQuery) {
                    row.style.display = 'none';
                } else {
                    row.style.display = '';
                }
            }
        });
    }

    window.initDashboardCharts = initDashboardCharts;
    window.changeDashboardYear = changeDashboardYear;
    window.syncDashboardRealtime = syncDashboardRealtime;
    window.toggleFilterOnlyActive = toggleFilterOnlyActive;
    window.filterSummaryTable = filterSummaryTable;

    function startRealtimeSync() {
        initDashboardCharts();

        // Silent background polling sync every 10 seconds
        if (!window.dashboardSyncInterval) {
            window.dashboardSyncInterval = setInterval(() => {
                if (!document.hidden) {
                    syncDashboardRealtime();
                }
            }, 10000);
        }

        // Instantly sync when returning to tab
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                syncDashboardRealtime();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startRealtimeSync);
    } else {
        startRealtimeSync();
    }
</script>
@endpush
