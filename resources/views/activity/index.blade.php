@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Data Profil</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Log Aktivitas & Audit Trail</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2.5">
        <div>
            <h4 class="fw-bold text-slate-900 mb-0.5" style="font-size: 1.15rem;">Log Aktivitas & Audit Trail</h4>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Catatan riwayat seluruh aktivitas penting pengguna (penambahan indikator, perubahan nilai, dll).</p>
        </div>
        <form method="GET" action="{{ route('activity.index') }}" class="d-flex align-items-center gap-1.5 m-0">
            <select name="action" class="form-select form-select-sm" onchange="window.submitSpaFilter(this.form)" style="width: 180px; font-size: 0.78rem;">
                <option value="">-- Semua Aksi --</option>
                <option value="create_indikator" {{ $action == 'create_indikator' ? 'selected' : '' }}>Tambah Indikator</option>
                <option value="update_indikator" {{ $action == 'update_indikator' ? 'selected' : '' }}>Ubah Indikator</option>
                <option value="delete_indikator" {{ $action == 'delete_indikator' ? 'selected' : '' }}>Hapus Indikator</option>
                <option value="update_nilai" {{ $action == 'update_nilai' ? 'selected' : '' }}>Pembaruan Nilai</option>
                <option value="batch_update_nilai" {{ $action == 'batch_update_nilai' ? 'selected' : '' }}>Update Nilai Massal</option>
                <option value="import_excel" {{ $action == 'import_excel' ? 'selected' : '' }}>Impor Excel</option>
                <option value="export_excel" {{ $action == 'export_excel' ? 'selected' : '' }}>Ekspor Excel</option>
                <option value="login" {{ $action == 'login' ? 'selected' : '' }}>Masuk (Login)</option>
            </select>
            @if($action)
                <a href="{{ route('activity.index') }}" class="btn btn-outline-secondary btn-sm py-1 px-2"><i class="bi bi-x-lg"></i></a>
            @endif
        </form>
    </div>

    <div class="card border-0 shadow-xs mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 170px;">Waktu Kejadian</th>
                            <th style="width: 160px;">Pengguna</th>
                            <th style="width: 150px;">Aksi</th>
                            <th>Deskripsi Aktivitas</th>
                            <th style="width: 130px;">Alamat IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <span class="small fw-semibold text-dark d-block">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </span>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        {{ $log->created_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar bg-slate-800 text-white rounded-circle d-flex align-items-center justify-content-center fw-bold small" style="width: 26px; height: 26px; font-size: 0.7rem;">
                                            {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <span class="small fw-medium text-dark text-truncate">{{ $log->user->name ?? 'Sistem' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if (str_contains($log->action, 'create')) $badgeClass = 'bg-success';
                                        elseif (str_contains($log->action, 'update')) $badgeClass = 'bg-primary';
                                        elseif (str_contains($log->action, 'delete')) $badgeClass = 'bg-danger';
                                        elseif (str_contains($log->action, 'import')) $badgeClass = 'bg-info text-dark';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} small font-monospace">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td>
                                    <span class="small text-dark">{{ $log->description }}</span>
                                </td>
                                <td>
                                    <span class="small text-muted font-monospace">{{ $log->ip_address ?: '-' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Belum ada catatan log aktivitas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white py-3 px-3 border-top">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    /* Custom Modern Pagination Styling */
    .pagination {
        margin-bottom: 0;
        gap: 3px;
    }
    .pagination .page-item .page-link {
        font-size: 0.8rem;
        padding: 5px 11px;
        border-radius: 6px !important;
        color: #334155;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        font-weight: 500;
        transition: all 0.15s ease;
    }
    .pagination .page-item .page-link:hover {
        background-color: #f1f5f9;
        color: #1e40af;
        border-color: #cbd5e1;
    }
    .pagination .page-item.active .page-link {
        background-color: #1e40af;
        border-color: #1e40af;
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(30, 64, 175, 0.2);
    }
    .pagination .page-item.disabled .page-link {
        color: #94a3b8;
        background-color: #f8fafc;
        border-color: #f1f5f9;
        cursor: not-allowed;
    }
    .pagination svg, 
    .card-footer svg,
    nav[role="navigation"] svg {
        width: 1rem !important;
        height: 1rem !important;
        max-width: 1rem !important;
        max-height: 1rem !important;
        display: inline-block;
    }
</style>
@endsection
