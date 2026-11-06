@extends('layouts.app')

@section('title', 'Pengelolaan Data Tahunan')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Data Profil</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Pengelolaan Data Tahunan</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2.5">
        <div>
            <h4 class="fw-bold text-slate-900 mb-0.5" style="font-size: 1.15rem;">Pengelolaan Data Tahunan</h4>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Input dan perbarui nilai indikator sektoral per tahun secara massal atau per baris.</p>
        </div>
        <div class="d-flex align-items-center gap-1.5">

            @if($currentUrusan)
                <a href="{{ route('urusan.show', $currentUrusan->slug ?: $currentUrusan->id) }}" class="btn btn-outline-primary btn-sm shadow-xs d-flex align-items-center gap-1 py-1 px-2.5" style="font-size: 0.78rem;">
                    <i class="bi bi-diagram-3"></i>
                    <span>{{ (auth()->check() && auth()->user()->canEditData()) ? 'Kelola Struktur' : 'Lihat Tabel' }}</span>
                </a>
            @endif
        </div>
    </div>

    @if(auth()->check() && !auth()->user()->canEditData())
        <div class="alert alert-light border border-secondary border-opacity-25 py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2 rounded-2 shadow-xs" style="font-size: 0.76rem;">
            <i class="bi bi-eye-fill text-primary"></i>
            <div>
                <strong class="text-dark">Mode Peninjau (Role: User)</strong>: Mode baca (read-only). Pengisian dan penyimpanan nilai hanya dapat dilakukan oleh <strong>Admin</strong> dan <strong>Super Admin</strong>.
            </div>
        </div>
    @endif

    <!-- FILTER CARD -->
    <div class="card border-0 shadow-xs mb-2.5">
        <div class="card-body py-2 px-3">
            <form method="GET" action="{{ route('data.index') }}" id="filterDataForm" class="row g-2 align-items-center">
                <!-- Filter Kelompok Urusan -->
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Kelompok Urusan</label>
                    <select name="kelompok_id" class="form-select form-select-sm" onchange="window.submitSpaFilter(this.form)">
                        <option value="">-- Semua Kelompok --</option>
                        @foreach($kelompoks as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelompokId == $k->id ? 'selected' : '' }}>
                                {{ $k->kode }}. {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Urusan -->
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Bidang Urusan</label>
                    <select name="urusan_id" class="form-select form-select-sm fw-semibold" onchange="window.submitSpaFilter(this.form)">
                        @foreach($availableUrusans as $u)
                            <option value="{{ $u->id }}" {{ $selectedUrusanId == $u->id ? 'selected' : '' }}>
                                {{ $u->formatted_nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Tahun -->
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Tahun Anggaran</label>
                    <select name="tahun" class="form-select form-select-sm fw-bold border-primary text-primary" onchange="window.submitSpaFilter(this.form)">
                        @foreach($listTahunGlobal as $th)
                            <option value="{{ $th->tahun }}" {{ $selectedTahunNomor == $th->tahun ? 'selected' : '' }}>
                                {{ $th->tahun }} {{ $th->is_default ? '★' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Indikator -->
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Cari Indikator</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Nama indikator..." value="{{ $search }}">
                        <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="col-12 col-md-1 d-flex align-items-end">
                    <a href="{{ route('data.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-md-4" title="Reset Semua Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @if($currentUrusan)
        <!-- SUMMARY BADGE & BATCH SAVE FORM -->
        <form method="POST" action="{{ route('data.batch') }}" id="batchDataForm">
            @csrf
            <input type="hidden" name="tahun_id" value="{{ $tahun->id ?? 1 }}">

            <div class="card border-0 shadow-xs mb-3 bg-light">
                <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi {{ $currentUrusan->icon ?: 'bi-folder2' }} fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-900 mb-0">{{ $currentUrusan->formatted_nama }}</h6>
                            <small class="text-muted">
                                Tahun <strong>{{ $tahun->tahun ?? 2025 }}</strong> &bull; 
                                Terisi: <strong class="text-primary">{{ $totalTerisi }}</strong> dari {{ $totalIndikator }} indikator 
                                (<strong>{{ $persentase }}%</strong>)
                            </small>
                        </div>
                    </div>

                    @if(auth()->check() && auth()->user()->canEditData())
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-3 shadow-xs d-flex align-items-center gap-1">
                                <i class="bi bi-save2"></i>
                                <span>Simpan Seluruh Nilai</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TABEL INPUT DATA -->
            <div class="card border-0 shadow-xs mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 320px;">Indikator & Hierarki</th>
                                    <th style="width: 90px;">Satuan</th>
                                    <th style="width: 170px;">Nilai Tahun {{ $tahun->tahun ?? 2025 }}</th>
                                    <th style="width: 180px;">Sumber Data</th>
                                    <th style="width: 180px;">Keterangan</th>
                                    <th style="width: 130px;">Info Pembaruan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($indicatorsList as $item)
                                    @php
                                        $level = $item->tree_level ?? 0;
                                        $indent = $level * 24;
                                        $curr = $item->current_data;
                                        $val = $curr ? $curr->nilai : '';
                                        $sumber = $curr ? $curr->sumber_data : '';
                                        $ket = $curr ? $curr->keterangan : '';
                                        $canEditRow = auth()->check() && auth()->user()->canEditData();
                                    @endphp
                                    <tr>
                                        <!-- Indikator with Indentation -->
                                        <td>
                                            <div style="padding-left: {{ $indent }}px;">
                                                <div class="d-flex align-items-center gap-1">
                                                    @if($level > 0)
                                                        <i class="bi bi-arrow-return-right text-muted small me-1"></i>
                                                    @endif
                                                    @if($item->kode_indikator)
                                                        <span class="badge bg-slate-800 text-info font-monospace small" style="font-size: 0.65rem;">
                                                            {{ $item->kode_indikator }}
                                                        </span>
                                                    @endif
                                                    <span class="fw-semibold text-slate-900 {{ $level === 0 ? 'fs-6' : 'small' }}">
                                                        {{ $item->nama_indikator }}
                                                    </span>
                                                </div>
                                                @if($item->deskripsi)
                                                    <small class="text-muted d-block ps-3" style="font-size: 0.7rem;">{{ $item->deskripsi }}</small>
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Satuan -->
                                        <td>
                                            <span class="badge bg-light text-dark border small">{{ $item->satuan ?: '—' }}</span>
                                        </td>

                                        <!-- Nilai Input -->
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" 
                                                       name="data[{{ $item->id }}][nilai]" 
                                                       value="{{ $val }}" 
                                                       {{ !$canEditRow ? 'disabled readonly' : '' }}
                                                       class="form-control form-control-sm font-monospace fw-bold text-end @if($val !== '' && $val !== null) bg-primary bg-opacity-10 text-primary @endif" 
                                                       placeholder="{{ $canEditRow ? 'Belum ada' : '—' }}">
                                            </div>
                                        </td>

                                        <!-- Sumber Data -->
                                        <td>
                                            <input type="text" 
                                                   name="data[{{ $item->id }}][sumber_data]" 
                                                   value="{{ $sumber }}" 
                                                   {{ !$canEditRow ? 'disabled readonly' : '' }}
                                                   class="form-control form-control-sm" 
                                                   placeholder="{{ $canEditRow ? 'Contoh: OPD terkait' : '—' }}">
                                        </td>

                                        <!-- Keterangan -->
                                        <td>
                                            <input type="text" 
                                                   name="data[{{ $item->id }}][keterangan]" 
                                                   value="{{ $ket }}" 
                                                   {{ !$canEditRow ? 'disabled readonly' : '' }}
                                                   class="form-control form-control-sm" 
                                                   placeholder="{{ $canEditRow ? 'Catatan...' : '—' }}">
                                        </td>

                                        <!-- Update Audit Info -->
                                        <td>
                                            @if($curr && $curr->updated_at)
                                                <small class="d-block text-muted" style="font-size: 0.7rem;">
                                                    {{ $curr->updated_at->format('d/m/Y H:i') }}
                                                </small>
                                                <small class="text-secondary fw-semibold" style="font-size: 0.65rem;">
                                                    {{ $curr->updatedByUser->name ?? 'Sistem' }}
                                                </small>
                                            @else
                                                <small class="text-muted fst-italic" style="font-size: 0.7rem;">Belum pernah diisi</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-1 opacity-50"></i>
                                            Belum ada indikator terdaftar pada urusan ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(count($indicatorsList) > 0)
                    <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Pastikan data yang dimasukkan telah sesuai sebelum menekan tombol simpan.</span>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold shadow-xs">
                            <i class="bi bi-save2 me-1"></i> Simpan Seluruh Perubahan Nilai
                        </button>
                    </div>
                @endif
            </div>
        </form>
    @else
        <div class="card border-0 shadow-xs p-5 text-center text-muted">
            <i class="bi bi-hand-index-thumb fs-1 d-block mb-2 opacity-50"></i>
            <h6>Silakan pilih salah satu Urusan Pemerintahan pada filter di atas.</h6>
        </div>
    @endif
</div>
@endsection
