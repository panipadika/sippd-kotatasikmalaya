@extends('layouts.app')

@section('title', $urusan->formatted_nama)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Data Profil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kelompok-urusan.show', $urusan->kelompok_urusan_id) }}" class="text-decoration-none text-muted">{{ $urusan->kelompokUrusan->nama }}</a></li>
        <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $urusan->formatted_nama }}</li>
    </ol>
</nav>
@endsection

@section('content')
<style>
    .btn-tambah-kolom {
        background-color: #ffffff !important;
        color: #0284c7 !important;
        border: 1.5px solid #0284c7 !important;
        transition: all 0.2s ease-in-out;
    }
    .btn-tambah-kolom:hover,
    .btn-tambah-kolom:focus,
    .btn-tambah-kolom:active {
        background-color: #0284c7 !important;
        color: #ffffff !important;
        border-color: #0284c7 !important;
    }
    .btn-tambah-kolom:hover i,
    .btn-tambah-kolom:hover span,
    .btn-tambah-kolom:focus i,
    .btn-tambah-kolom:focus span {
        color: #ffffff !important;
    }

    /* Drag & Reorder Styling for Sub-Kolom & Sub-Baris Items */
    .sub-col-item,
    .sub-baris-item {
        transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        border: 1px solid #e2e8f0;
    }
    .sub-col-item:hover,
    .sub-baris-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .sub-col-item.is-dragging,
    .sub-baris-item.is-dragging {
        opacity: 0.45;
        border: 2px dashed #0284c7 !important;
        background-color: #f0f9ff !important;
    }
    .sub-col-item.item-highlight-pulse,
    .sub-baris-item.item-highlight-pulse {
        animation: subItemPulse 0.45s ease-out;
    }
    @keyframes subItemPulse {
        0% { background-color: #e0f2fe; border-color: #0284c7; }
        100% { background-color: #ffffff; border-color: #e2e8f0; }
    }
    .drag-handle {
        cursor: grab;
        user-select: none;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .btn-sub-up:hover,
    .btn-sub-down:hover {
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
    }

    /* Styling Baris Induk (PAUD, SD, SMP, SMA, SMK, dll) - Warna Jelas, Kontras & Mudah Dibaca */
    .table-induk-row {
        background-color: #e2eefb !important;
    }
    .table-induk-row > td,
    .table-induk-cell {
        background-color: #e2eefb !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        border-top: 1.5px solid #93c5fd !important;
        border-bottom: 1.5px solid #93c5fd !important;
    }
    .table-induk-row:hover > td {
        background-color: #d4e7f9 !important;
    }
    .table-induk-row .cell-val-display {
        color: #0f172a !important;
        font-weight: 700 !important;
    }

    /* Kolom Total / Jumlah pada Baris Induk - Harmonis, Lebih Dalam & Angka Sangat Menonjol */
    .table-induk-row > td.cell-total-col {
        background-color: #c9e2fa !important;
        border-left: 1.5px solid #93c5fd !important;
        border-right: 1.5px solid #93c5fd !important;
        border-top: 1.5px solid #93c5fd !important;
        border-bottom: 1.5px solid #93c5fd !important;
    }
    .table-induk-row:hover > td.cell-total-col {
        background-color: #badbf9 !important;
    }
    .table-induk-row > td.cell-total-col .cell-val-display {
        color: #0369a1 !important;
        font-weight: 800 !important;
    }

    /* Kolom Aksi Sticky pada Baris Induk */
    .custom-data-table tr.table-induk-row td.sticky-action-col {
        background-color: #e2eefb !important;
        border-top: 1.5px solid #93c5fd !important;
        border-bottom: 1.5px solid #93c5fd !important;
    }
    .custom-data-table tr.table-induk-row:hover td.sticky-action-col {
        background-color: #d4e7f9 !important;
    }

    /* Styling Baris Sub-Induk (Induk Bertingkat Level 1+) */
    .table-sub-induk-row {
        background-color: #f0f7fe !important;
    }
    .table-sub-induk-row > td,
    .table-sub-induk-cell {
        background-color: #f0f7fe !important;
        color: #0f172a !important;
        font-weight: 650 !important;
        border-top: 1px solid #bae6fd !important;
        border-bottom: 1px solid #bae6fd !important;
    }
    .table-sub-induk-row:hover > td {
        background-color: #e0f0fd !important;
    }
    .table-sub-induk-row .cell-val-display {
        color: #0f172a !important;
        font-weight: 650 !important;
    }
    .table-sub-induk-row > td.cell-total-col {
        background-color: #d8eeff !important;
        border-left: 1px solid #bae6fd !important;
        border-right: 1px solid #bae6fd !important;
        border-top: 1px solid #bae6fd !important;
        border-bottom: 1px solid #bae6fd !important;
    }
    .table-sub-induk-row:hover > td.cell-total-col {
        background-color: #cae6fc !important;
    }
    .table-sub-induk-row > td.cell-total-col .cell-val-display {
        color: #0284c7 !important;
        font-weight: 750 !important;
    }
    .custom-data-table tr.table-sub-induk-row td.sticky-action-col {
        background-color: #f0f7fe !important;
        border-top: 1px solid #bae6fd !important;
        border-bottom: 1px solid #bae6fd !important;
    }
    .custom-data-table tr.table-sub-induk-row:hover td.sticky-action-col {
        background-color: #e0f0fd !important;
    }

    /* Kolom Total / Jumlah pada Baris Biasa & Sub-Baris */
    td.cell-total-col {
        background-color: #f0f9ff !important;
    }
    td.cell-total-col .cell-val-display {
        color: #0284c7 !important;
        font-weight: 700 !important;
    }

    /* ========================================================
       RESIZABLE TABLE COLUMNS & ROWS (GARIS BISA DIGESER)
       ======================================================== */
    .table-resizable-wrap {
        position: relative !important;
        overflow-x: auto !important;
    }
    .custom-data-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 100%;
    }
    .custom-data-table th,
    .custom-data-table td {
        position: relative;
    }

    /* Column Resizer Line (Garis Kolom Vertikal) */
    .col-resizer-line {
        position: absolute;
        top: 0;
        right: 0;
        width: 8px;
        height: 100%;
        cursor: col-resize;
        user-select: none;
        z-index: 25;
    }
    .col-resizer-line::after {
        content: '';
        position: absolute;
        top: 0;
        right: 2px;
        width: 2px;
        height: 100%;
        background-color: transparent;
        transition: background-color 0.15s ease;
    }
    .col-resizer-line:hover::after,
    .col-resizer-line.is-active::after {
        background-color: #0284c7 !important;
    }

    /* Row Resizer Line (Garis Baris Horizontal) */
    .row-resizer-line {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 8px;
        cursor: row-resize;
        user-select: none;
        z-index: 15;
    }
    .row-resizer-line::after {
        content: '';
        position: absolute;
        bottom: 1px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: transparent;
        transition: background-color 0.15s ease;
    }
    .row-resizer-line:hover::after,
    .row-resizer-line.is-active::after {
        background-color: #0284c7 !important;
    }

    /* Guide lines while dragging */
    .table-resize-guide-col {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #0284c7;
        z-index: 9999;
        pointer-events: none;
        display: none;
        box-shadow: 0 0 6px rgba(2, 132, 199, 0.6);
    }
    .table-resize-guide-row {
        position: absolute;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #0284c7;
        z-index: 9999;
        pointer-events: none;
        display: none;
        box-shadow: 0 0 6px rgba(2, 132, 199, 0.6);
    }
    body.is-resizing-col {
        cursor: col-resize !important;
        user-select: none !important;
    }
    body.is-resizing-row {
        cursor: row-resize !important;
        user-select: none !important;
    }

    /* BORDER TEGAS DAN BERGARIS JELAS SEPERTI TABEL EXCEL DENGAN STICKY AKSI */
    .table-responsive {
        overflow-x: auto !important;
        position: relative;
        max-width: 100%;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: auto !important;
        scrollbar-color: #0284c7 #e2e8f0 !important;
    }

    .table-responsive::-webkit-scrollbar {
        height: 14px !important;
        background-color: #f8fafc !important;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #e2e8f0 !important;
        border-radius: 8px !important;
        margin: 0 4px !important;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #0284c7 !important;
        border-radius: 8px !important;
        border: 3px solid #e2e8f0 !important;
        cursor: grab !important;
        transition: background-color 0.15s ease;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #0369a1 !important;
    }

    .table-responsive::-webkit-scrollbar-thumb:active {
        background: #0c4a6e !important;
        cursor: grabbing !important;
    }

    .table-responsive.table-resizable-wrap.is-mouse-dragging {
        cursor: grabbing !important;
        user-select: none !important;
    }
    .table-responsive.table-resizable-wrap.is-mouse-dragging * {
        cursor: grabbing !important;
        user-select: none !important;
    }

    .custom-data-table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        min-width: 100% !important;
        border-top: 1px solid #cbd5e1 !important;
        border-left: 1px solid #cbd5e1 !important;
    }
    .custom-data-table th,
    .custom-data-table td {
        border-right: 1px solid #cbd5e1 !important;
        border-bottom: 1px solid #cbd5e1 !important;
        vertical-align: middle;
        padding: 0.35rem 0.55rem !important;
        font-size: 0.8rem !important;
        line-height: 1.35 !important;
    }
    .custom-data-table thead th {
        background-color: #f1f5f9 !important;
        border-bottom: 2px solid #94a3b8 !important;
        color: #0f172a;
    }
    .custom-data-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    /* STICKY AKSI COLUMN ON THE RIGHT - ALWAYS VISIBLE */
    .custom-data-table th.sticky-action-col {
        position: sticky !important;
        right: 0 !important;
        z-index: 10 !important;
        background-color: #f1f5f9 !important;
        border-left: 2px solid #94a3b8 !important;
        box-shadow: -3px 0 6px -2px rgba(15, 23, 42, 0.1) !important;
        width: 78px !important;
        min-width: 78px !important;
        max-width: 78px !important;
    }
    .custom-data-table td.sticky-action-col {
        position: sticky !important;
        right: 0 !important;
        z-index: 8 !important;
        background-color: #ffffff !important;
        border-left: 2px solid #cbd5e1 !important;
        box-shadow: -3px 0 6px -2px rgba(15, 23, 42, 0.06) !important;
        width: 78px !important;
        min-width: 78px !important;
        max-width: 78px !important;
    }
    .custom-data-table tr:hover td.sticky-action-col {
        background-color: #f8fafc !important;
    }
    .custom-data-table tr.table-primary td.sticky-action-col {
        background-color: #eff6ff !important;
    }
    .custom-data-table tr.opacity-75 td.sticky-action-col {
        background-color: #f8fafc !important;
    }

    /* Sticky Tombol Tambah Baris/Kolom di Footer */
    .sticky-bottom-actions {
        position: sticky !important;
        left: 1rem !important;
        display: inline-flex !important;
    }
    .editable-cell {
        transition: all 0.15s ease-in-out;
        position: relative;
        cursor: pointer;
    }
    .editable-cell:hover {
        background-color: #f1f5f9 !important;
    }
    .editable-cell:hover .cell-edit-icon {
        opacity: 0.7 !important;
    }
    .cell-live-input {
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.35);
        border-color: #0d6efd;
        background-color: #ffffff;
        font-size: 0.875rem;
    }
    .cell-success-pulse {
        animation: pulseGreen 1s ease-in-out;
    }
    @keyframes pulseGreen {
        0% { background-color: rgba(25, 135, 84, 0.35); }
        100% { background-color: transparent; }
    }
    .column-header-th:hover .col-header-opt-btn {
        opacity: 0.85 !important;
    }

    /* 2-Column Urusan Layout with Collapsible Side-Menu (Reference Design) */
    .urusan-side-menu-wrap {
        width: 250px;
        min-width: 250px;
        max-width: 250px;
        position: sticky;
        top: 0.75rem;
        max-height: calc(100vh - 1.5rem);
        background: #ffffff;
        border-radius: 8px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        z-index: 10;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .urusan-side-menu-wrap.is-collapsed {
        width: 0 !important;
        min-width: 0 !important;
        max-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        opacity: 0;
        visibility: hidden;
        border: 0 !important;
        pointer-events: none;
    }
    .sibling-table-item {
        color: #334155;
        border-radius: 6px;
        padding: 0.42rem 0.65rem;
        font-size: 0.78rem;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        border: 1px solid transparent;
    }
    .sibling-table-item:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .sibling-table-item.active {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
        font-weight: 700;
        border-color: #bae6fd;
        border-left: 3px solid #0284c7 !important;
    }
    .sibling-table-item.active i {
        color: #0284c7 !important;
    }
    @media (max-width: 991.98px) {
        .urusan-side-menu-wrap {
            display: none !important;
        }
        #btnToggleUrusanSideMenu {
            display: none !important;
        }
    }
</style>

<div class="container-fluid p-0">
    <!-- COMPACT URUSAN BANNER -->
    <div class="card border-0 shadow-xs mb-2 bg-white">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-2">
                <!-- Left: Icon, Title & Stats Chips -->
                <div class="d-flex align-items-center gap-2.5 min-w-0">
                    <div class="bg-primary text-white rounded-2 d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 34px; height: 34px;">
                        <i class="bi {{ $urusan->icon ?: 'bi-folder2' }} fs-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('kelompok-urusan.show', $urusan->kelompok_urusan_id) }}" class="badge bg-light text-secondary border text-decoration-none px-1.5 py-0.5 d-inline-flex align-items-center gap-1" style="font-size: 0.65rem;" title="Buka Katalog {{ $urusan->kelompokUrusan->nama }}">
                                <i class="bi bi-folder2-open text-primary"></i>
                                <span>{{ $urusan->kelompokUrusan->nama }}</span>
                            </a>
                            <h5 class="fw-bold text-slate-900 mb-0 text-truncate" style="font-size: 1rem;">{{ $urusan->formatted_nama }}</h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-1.5 py-0.5" style="font-size: 0.65rem;">
                                <i class="bi bi-diagram-3 me-0.5"></i>{{ $totalIndikator }} Indikator
                            </span>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-1.5 py-0.5" style="font-size: 0.65rem;">
                                <i class="bi bi-calendar-check me-0.5"></i>Periode: {{ $tahunAwal }} - {{ $tahunAkhir }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Action: Toggle Menu, Atur Tabel, Import Excel, Unduh Excel & Unduh PDF -->
                <div class="d-flex flex-wrap align-items-center flex-shrink-0" style="gap: 8px;">
                    <!-- Tombol Buka/Tutup Menu Tabel Bidang -->
                    <button type="button" 
                            id="btnToggleUrusanSideMenu" 
                            class="btn btn-sm btn-white border shadow-xs d-inline-flex align-items-center py-1.5 px-2.5 rounded-2 fw-semibold text-secondary" 
                            onclick="window.toggleUrusanSideMenu()" 
                            title="Buka / Tutup Daftar Tabel Bidang" 
                            style="font-size: 0.78rem; gap: 5px;">
                        <i class="bi bi-layout-sidebar-inset text-primary" id="iconToggleUrusanSideMenu"></i>
                        <span class="d-none d-sm-inline" id="labelToggleUrusanSideMenu">Menu Tabel</span>
                    </button>

                    @if(auth()->check() && auth()->user()->canEditData())
                        <!-- Tombol Atur Tabel (Warna Biru Muda) -->
                        <button type="button" class="btn btn-sm text-white fw-semibold shadow-xs d-inline-flex align-items-center py-1.5 px-3 rounded-2" data-bs-toggle="modal" data-bs-target="#modalAturTabel" title="Atur jumlah kolom dan baris tabel aktif" style="font-size: 0.78rem; background: #0ea5e9; border: 1px solid #0284c7; gap: 6px;">
                            <i class="bi bi-sliders2"></i>
                            <span>Atur Tabel</span>
                        </button>

                        <!-- Tombol Import Excel (Khusus Admin/Super Admin) -->
                        <button type="button" class="btn btn-sm btn-outline-success shadow-xs d-inline-flex align-items-center py-1.5 px-3 rounded-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalImportExcel" title="Import data indikator dari file Excel (.xlsx / .xls / .csv)" style="font-size: 0.78rem; gap: 6px; border-color: #10b981; color: #059669; background-color: #ecfdf5;">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                            <span>Import Excel</span>
                        </button>
                    @endif

                    <!-- Tombol Unduh Excel (Bisa Diakses Semua Role) -->
                    <a href="{{ route('urusan.excel.export', ['id' => $urusan->slug ?: $urusan->id, 'tahun' => $tahun->tahun ?? 2025, 'tahun_awal' => $tahunAwal, 'tahun_akhir' => $tahunAkhir, 'tabel' => $activeTableId, 'show_footer_total' => $showFooterTotal ? 1 : 0]) }}" id="btnExportExcel" data-no-spa="true" download class="btn btn-sm btn-outline-success shadow-xs d-inline-flex align-items-center py-1.5 px-3 rounded-2 fw-semibold" title="Unduh data urusan ini dalam format Excel (.xlsx)" style="font-size: 0.78rem; gap: 6px;">
                        <i class="bi bi-file-earmark-excel"></i>
                        <span>Unduh Excel</span>
                    </a>

                    <!-- Tombol Unduh PDF Urusan Ini -->
                    <a href="{{ route('urusan.pdf', ['id' => $urusan->slug ?: $urusan->id, 'tahun' => $tahun->tahun ?? 2025, 'tahun_awal' => $tahunAwal, 'tahun_akhir' => $tahunAkhir, 'tabel' => $activeTableId, 'show_footer_total' => $showFooterTotal ? 1 : 0]) }}" id="btnExportPdf" data-no-spa="true" download class="btn btn-sm btn-outline-danger shadow-xs d-inline-flex align-items-center py-1.5 px-3 rounded-2 fw-semibold" title="Unduh data urusan ini dalam format PDF" style="font-size: 0.78rem; gap: 6px;">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Unduh PDF</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2-COLUMN LAYOUT: SIDE-MENU TABEL & MAIN TABLE CONTENT -->
    <div class="d-flex align-items-start gap-2.5 w-100 position-relative" id="urusanLayoutContainer">
        <!-- SIDE PANEL MENU: DAFTAR TABEL BIDANG (Matching Reference Image) -->
        <aside id="urusanSideMenu" class="urusan-side-menu-wrap border shadow-xs" aria-label="Menu Tabel Bidang">
            <div class="p-2.5 border-bottom bg-light bg-opacity-75 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <div class="rounded-2 p-1 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.75rem;">
                        <i class="bi bi-folder2-open"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.62rem; letter-spacing: 0.05em;">Tabel Bidang</span>
                        <span class="d-block fw-bold text-dark text-truncate" style="font-size: 0.78rem;" title="{{ $urusan->kelompokUrusan->nama ?? 'Bidang' }}">
                            {{ $urusan->kelompokUrusan->nama ?? 'Bidang' }}
                        </span>
                    </div>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.68rem;" title="{{ count($siblingUrusans ?? []) }} Tabel">
                    {{ count($siblingUrusans ?? []) }}
                </span>
            </div>

            @if(isset($siblingUrusans) && count($siblingUrusans) > 4)
                <div class="px-2 pt-2 pb-1 border-bottom bg-white">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 py-0.5 px-2 text-muted" style="font-size: 0.72rem;"><i class="bi bi-search"></i></span>
                        <input type="text" id="filterSiblingInput" class="form-control form-control-sm border-start-0 py-0.5 px-1.5" style="font-size: 0.72rem;" placeholder="Cari nama tabel..." oninput="window.filterSiblingTables(this.value)">
                    </div>
                </div>
            @endif

            <div class="overflow-y-auto flex-grow-1 p-2" style="max-height: calc(100vh - 190px);" id="siblingTablesList">
                <div class="nav flex-column gap-1">
                    @if(isset($siblingUrusans))
                        @foreach($siblingUrusans as $subU)
                            @php
                                $isActiveSub = ((string) $subU->id === (string) $urusan->id) || ((string) $subU->slug === (string) $urusan->slug);
                            @endphp
                            <a href="{{ route('urusan.show', $subU->slug ?: $subU->id) }}"
                               class="sibling-table-item nav-link px-2.5 py-1.5 rounded-2 d-flex align-items-center gap-2 text-decoration-none {{ $isActiveSub ? 'active' : '' }}"
                               style="font-size: 0.78rem;"
                               data-table-title="{{ strtolower($subU->formatted_nama) }}"
                               title="{{ $subU->nama_urusan }}">
                                <i class="bi bi-file-earmark-text flex-shrink-0" style="font-size: 0.85rem;"></i>
                                <span class="text-truncate flex-grow-1">{{ $subU->formatted_nama }}</span>
                                @if($isActiveSub)
                                    <i class="bi bi-chevron-right ms-auto" style="font-size: 0.65rem;"></i>
                                @endif
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="p-2 border-top bg-light bg-opacity-50 mt-auto">
                <a href="{{ route('kelompok-urusan.show', $urusan->kelompok_urusan_id) }}" 
                   class="btn btn-outline-primary btn-sm w-100 py-1 d-flex align-items-center justify-content-center gap-1.5 shadow-xs" 
                   style="font-size: 0.74rem;">
                    <i class="bi bi-grid-fill"></i>
                    <span>Katalog Bidang</span>
                </a>
            </div>
        </aside>

        <!-- RIGHT MAIN CONTENT: TOOLBAR & TABLE -->
        <main id="urusanMainContent" class="flex-grow-1 min-w-0" style="transition: all 0.25s ease;">

    @if(auth()->check() && !auth()->user()->canEditData())
        <div class="alert alert-light border border-secondary border-opacity-25 py-1.5 px-2.5 small d-flex align-items-center gap-2 mb-2 rounded-2 shadow-xs" style="font-size: 0.76rem;">
            <i class="bi bi-eye-fill text-primary"></i>
            <div>
                <strong class="text-dark">Mode Peninjau (Role: User)</strong>: Anda memiliki hak akses untuk meninjau data, unduh Excel, & unduh PDF. Pengeditan dikhususkan untuk <strong>Admin</strong> dan <strong>Super Admin</strong>.
            </div>
        </div>
    @endif

    <!-- UNIFIED COMPACT TOOLBAR (PENCARIAN DI KIRI, AKSI BARIS/KOLOM & FILTER TAHUN DI KANAN) -->
    <div class="card border-0 shadow-xs mb-2 bg-white">
        <div class="card-body py-1.5 px-3 d-flex flex-wrap align-items-center justify-content-between" style="gap: 12px;">
            <!-- Left: Table Tabs & Search Form -->
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px; flex: 1 1 auto; max-width: 650px;">
                @if(count($tables) > 1 || (auth()->check() && auth()->user()->canEditData()))
                    <span class="small fw-bold text-muted ps-1 text-nowrap" style="font-size: 0.75rem;"><i class="bi bi-folder2-open text-primary me-1"></i>Halaman:</span>
                    <ul class="nav nav-pills align-items-center" id="tableTabs" style="gap: 4px;">
                        @php 
                            // Untuk tabel tanpa kolom tahun (snapshot per-tahun), tampilkan HANYA halaman tabel aktif ini saja
                            // agar tidak berjejer ke samping, karena pergantian halaman dilakukan via filter tahun.
                            $tabsToRender = (empty($hasYearColumns) && empty($hasYearRows))
                                ? array_filter($tables, fn($t) => (int)($t['id'] ?? 1) === (int)$activeTableId)
                                : $tables;
                            if (empty($tabsToRender)) {
                                $tabsToRender = [$tables[0] ?? ['id' => 1, 'name' => 'Tabel 1', 'row_count' => 0]];
                            }
                        @endphp
                        @foreach($tabsToRender as $tbl)
                            @php 
                                $isCurrent = ($activeTableId == ($tbl['id'] ?? 1));
                                $tName = $tbl['name'];
                                $hasYrInName = preg_match('/(19|20)\d{2}/', $tName);
                                $tblYr = $tbl['tahun_range'] ?? $tbl['tahun'] ?? null;
                            @endphp
                            <li class="nav-item d-flex align-items-center">
                                @if($isCurrent)
                                    <span class="nav-link py-1 px-2.5 rounded-2 d-flex align-items-center active bg-primary text-white shadow-xs fw-bold" 
                                          style="font-size: 0.78rem; gap: 6px; cursor: default;"
                                          title="Halaman aktif: {{ $tName }}{{ $tblYr ? ' (Tahun ' . $tblYr . ')' : '' }}">
                                        <i class="bi bi-table text-white"></i>
                                        <span>{{ $tName }}</span>
                                        @if(!$hasYrInName && $tblYr)
                                            <span class="badge bg-white text-primary fw-bold" style="font-size: 0.65rem; padding: 0.15em 0.4em;">
                                                {{ $tblYr }}
                                            </span>
                                        @endif
                                        <span class="badge bg-white text-primary" style="font-size: 0.65rem; padding: 0.15em 0.4em;">
                                            {{ $tbl['row_count'] ?? 0 }} baris
                                        </span>
                                    </span>
                                @else
                                    <a href="{{ route('urusan.show', ['id' => $urusan->slug ?: $urusan->id, 'tabel' => $tbl['id']]) }}" 
                                       class="nav-link py-1 px-2.5 rounded-2 d-flex align-items-center bg-light text-secondary border shadow-xs fw-semibold text-decoration-none" 
                                       style="font-size: 0.78rem; gap: 6px;"
                                       title="Pindah ke halaman: {{ $tName }}{{ $tblYr ? ' (Tahun ' . $tblYr . ')' : '' }}">
                                        <i class="bi bi-table text-secondary"></i>
                                        <span>{{ $tName }}</span>
                                        @if(!$hasYrInName && $tblYr)
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border fw-bold" style="font-size: 0.65rem; padding: 0.15em 0.4em;">
                                                {{ $tblYr }}
                                            </span>
                                        @endif
                                        <span class="badge bg-white text-secondary border" style="font-size: 0.65rem; padding: 0.15em 0.4em;">
                                            {{ $tbl['row_count'] ?? 0 }} baris
                                        </span>
                                    </a>
                                @endif
                                @if(auth()->check() && auth()->user()->canEditData())
                                    <button type="button" class="btn btn-xs btn-link text-muted p-0 ms-1 border-0" onclick="openRenameTableModal('{{ $tbl['id'] }}', '{{ addslashes($tbl['name']) }}', '{{ $tblYr }}')" title="Ubah Nama & Tahun Halaman Ini" style="font-size: 0.75rem;">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </button>
                                @endif
                            </li>
                        @endforeach

                        @if(auth()->check() && auth()->user()->canEditData())
                            <li class="nav-item">
                                <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2 rounded-2 d-flex align-items-center gap-1 shadow-none" data-bs-toggle="modal" data-bs-target="#modalTambahHalaman" title="Tambah Halaman Baru (Misal: Tahun 2026)" style="font-size: 0.75rem;">
                                    <i class="bi bi-plus-circle-fill"></i>
                                    <span>Tambah Halaman</span>
                                </button>
                            </li>
                        @endif
                    </ul>

                    @if(count($tables) > 1 && auth()->check() && auth()->user()->canEditData())
                        <form method="POST" action="{{ route('urusan.table.destroy', ['id' => $urusan->slug ?: $urusan->id, 'tableId' => $activeTableId]) }}" onsubmit="return confirmDeleteTable(event, this, '{{ addslashes($activeTable['name']) }}');" class="m-0 ms-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0.5 px-1.5 rounded-2" title="Hapus halaman tabel ini" style="font-size: 0.75rem;">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </form>
                    @endif
                    <div class="vr mx-1 my-auto text-secondary opacity-25" style="height: 20px;"></div>
                @endif

                <!-- Search Form (Dipindahkan ke Kiri, Live Search Cepat & Tanpa Reload) -->
                <form method="GET" action="{{ route('urusan.show', $urusan->slug ?: $urusan->id) }}" id="tableSearchForm" data-no-spa="true" class="d-flex align-items-center m-0 w-100" style="max-width: 320px;" onsubmit="event.preventDefault(); window.handleSearchSubmit(event); return false;">
                    <input type="hidden" name="tahun_awal" value="{{ $tahunAwal }}">
                    <input type="hidden" name="tahun_akhir" value="{{ $tahunAkhir }}">
                    <input type="hidden" name="tabel" value="{{ $activeTableId }}">
                    <div class="input-group input-group-sm w-100 shadow-xs">
                        <span class="input-group-text bg-white border-end-0 text-muted py-1 px-2.5" style="font-size: 0.78rem;"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearchInput" class="form-control border-start-0 py-1 px-2" placeholder="Cari nama, kata, atau kode..." value="{{ $search ?? '' }}" style="font-size: 0.78rem;" autocomplete="off" oninput="window.handleLiveSearchInput(this.value)" onkeydown="if(event.key==='Enter'){event.preventDefault(); window.handleSearchSubmit(event); return false;}">
                        <button type="button" class="btn btn-outline-secondary py-1 px-2 border-start-0 border-end-0" id="btnResetSearch" onclick="window.clearSearchFilter()" title="Hapus Pencarian" style="display: {{ !empty($search) ? 'inline-flex' : 'none' }}; align-items: center;">
                            <i class="bi bi-x-lg text-danger" style="font-size: 0.72rem;"></i>
                        </button>
                        <button class="btn btn-outline-primary py-1 px-2.5 fw-semibold" type="button" onclick="window.handleSearchSubmit(event)" style="font-size: 0.78rem;">Cari</button>
                    </div>
                </form>
            </div>

            <!-- Right: Tambah Baris, Tambah Kolom, dan Filter Rentang Tahun di Paling Kanan -->
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                @if(auth()->check() && auth()->user()->canEditData())
                    <!-- Tombol Tambah Baris (Dipindahkan ke Kanan) -->
                    <button type="button" class="btn btn-sm btn-primary shadow-xs d-inline-flex align-items-center py-1.5 px-3 rounded-2 fw-semibold" onclick="quickAddRow()" title="Tambah 1 baris baru ke tabel" style="font-size: 0.78rem; gap: 6px;">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Tambah Baris</span>
                    </button>

                    <!-- Tombol Tambah Kolom (Dipindahkan ke Kanan) -->
                    <button type="button" class="btn btn-sm shadow-xs d-inline-flex align-items-center py-1.5 px-3 rounded-2 fw-semibold btn-tambah-kolom" data-bs-toggle="modal" data-bs-target="#modalTambahKolom" title="Tambah 1 kolom baru ke tabel" style="font-size: 0.78rem; gap: 6px;">
                        <i class="bi bi-layout-three-columns"></i>
                        <span>Tambah Kolom</span>
                    </button>
                @endif

                @if(!empty($hasYearColumns) || !empty($hasYearRows))
                    <!-- Filter Rentang Tahun: Untuk Tabel dengan Kolom Multi-Tahun atau Baris Multi-Tahun -->
                    <form method="GET" action="{{ route('urusan.show', $urusan->slug ?: $urusan->id) }}" class="d-flex align-items-center m-0 bg-white px-2.5 py-1 rounded-2 border shadow-xs" style="gap: 6px;" id="formFilterRentangTahun" onsubmit="event.preventDefault(); applyTahunRangeFilter(); return false;">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('tabel'))
                            <input type="hidden" name="tabel" value="{{ request('tabel') }}">
                        @endif
                        <span class="small text-muted fw-bold text-nowrap d-flex align-items-center" style="font-size: 0.78rem;">
                            <i class="bi bi-calendar3 text-primary me-1.5"></i>Tahun:
                        </span>
                        <select name="tahun_awal" id="filterTahunAwal" class="form-select form-select-sm fw-bold border bg-light py-1 px-2 rounded-2 shadow-none text-dark" onchange="applyTahunRangeFilter()" style="width: 82px; font-size: 0.78rem; cursor: pointer;">
                            @foreach($allAvailableYears as $yr)
                                <option value="{{ $yr }}" {{ (int)$tahunAwal === (int)$yr ? 'selected' : '' }}>
                                    {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-muted small fw-semibold mx-0.5" style="font-size: 0.78rem;">-</span>
                        <select name="tahun_akhir" id="filterTahunAkhir" class="form-select form-select-sm fw-bold border bg-light py-1 px-2 rounded-2 shadow-none text-dark" onchange="applyTahunRangeFilter()" style="width: 82px; font-size: 0.78rem; cursor: pointer;">
                            @foreach($allAvailableYears as $yr)
                                <option value="{{ $yr }}" {{ (int)$tahunAkhir === (int)$yr ? 'selected' : '' }}>
                                    {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @else
                    <!-- Filter Tahun Tunggal: Sesuai Halaman Tabel Aktif -->
                    <form method="GET" action="{{ route('urusan.show', $urusan->slug ?: $urusan->id) }}" class="d-flex align-items-center m-0 bg-white px-2.5 py-1 rounded-2 border shadow-xs" style="gap: 6px;" id="formFilterTahunTunggal" onsubmit="event.preventDefault(); switchTahunTunggal(document.getElementById('filterTahunTunggalSelect')); return false;">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        <input type="hidden" name="tabel" id="filterTahunTabelId" value="{{ $activeTableId }}">
                        <span class="small text-muted fw-bold text-nowrap d-flex align-items-center" style="font-size: 0.78rem;">
                            <i class="bi bi-calendar-event text-primary me-1.5"></i>Tahun Data:
                        </span>
                        <select name="tahun" id="filterTahunTunggalSelect" class="form-select form-select-sm fw-bold border bg-light py-1 px-2 rounded-2 shadow-none text-dark" onchange="switchTahunTunggal(this)" style="width: auto; min-width: 84px; font-size: 0.78rem; cursor: pointer;" title="Tahun data untuk halaman tabel ini">
                            @foreach($allAvailableYears as $yr)
                                <option value="{{ $yr }}" data-table-id="{{ $tableYearMap[$yr] ?? $activeTableId }}" {{ (int)($selectedTahunNomor ?? $tahun->tahun) === (int)$yr ? 'selected' : '' }}>
                                    {{ $yr }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                @php
                    $showFooterTotal = (bool) ($activeTable['show_footer_total'] ?? true);
                    $showFooterJumlah = (bool) ($activeTable['show_footer_jumlah'] ?? true);
                    $showFooterSubtotal = (bool) ($activeTable['show_footer_subtotal'] ?? true);
                    $currentFooterMode = $activeTable['footer_mode'] ?? null;

                    if (!$showFooterTotal) {
                        $currentFooterMode = 'none';
                        $footerModeLabel = 'Nonaktif';
                    } elseif ($showFooterJumlah && $showFooterSubtotal) {
                        $currentFooterMode = 'both';
                        $footerModeLabel = 'Jumlah & Total';
                    } elseif ($showFooterJumlah && !$showFooterSubtotal) {
                        $currentFooterMode = 'jumlah';
                        $footerModeLabel = 'Jumlah Saja';
                    } elseif (!$showFooterJumlah && $showFooterSubtotal) {
                        $currentFooterMode = 'total';
                        $footerModeLabel = 'Total Saja';
                    } else {
                        $currentFooterMode = 'none';
                        $footerModeLabel = 'Nonaktif';
                    }

                    $footerLabel = $activeTable['footer_label'] ?? 'Jumlah';
                    $footerOverrides = $activeTable['footer_overrides'] ?? [];
                @endphp
                @if(auth()->check() && auth()->user()->canEditData())
                    <!-- Dropdown Opsi Baris Jumlah / Total di Bawah -->
                    <div class="dropdown d-inline-block position-relative" id="dropdownFooterModeWrap">
                        <button type="button" 
                                id="btnToggleFooterTotal"
                                class="btn btn-sm {{ $showFooterTotal ? 'btn-light border border-primary border-opacity-30 text-primary fw-bold' : 'btn-white border text-muted' }} shadow-xs d-inline-flex align-items-center py-1.5 px-2.5 rounded-2 dropdown-toggle" 
                                data-bs-toggle="dropdown" 
                                data-bs-auto-close="true"
                                aria-expanded="false"
                                title="Pilih opsi tampilan baris Jumlah / Total di bagian bawah tabel" 
                                style="font-size: 0.78rem; gap: 5px;">
                            <i class="bi bi-calculator-fill {{ $showFooterTotal ? 'text-primary' : 'text-secondary opacity-50' }}"></i>
                            <span>Baris Bawah: <span id="labelFooterStatus">{{ $footerModeLabel }}</span></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" id="menuFooterTotalDropdown" style="font-size: 0.8rem; min-width: 215px; z-index: 1050;">
                            <li class="px-3 py-1 text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Opsi Tampilan Baris</li>
                            <li>
                                <a class="dropdown-item footer-mode-item d-flex align-items-center justify-content-between py-1.5 px-3 {{ $currentFooterMode === 'both' ? 'active fw-bold' : '' }}" data-mode="both" href="javascript:void(0)" onclick="setFooterMode('both')">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-layers-fill text-primary"></i>
                                        <span>Jumlah & Total</span>
                                    </span>
                                    <i class="bi bi-check-lg text-primary ms-2 footer-mode-check {{ $currentFooterMode === 'both' ? '' : 'd-none' }}"></i>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item footer-mode-item d-flex align-items-center justify-content-between py-1.5 px-3 {{ $currentFooterMode === 'jumlah' ? 'active fw-bold' : '' }}" data-mode="jumlah" href="javascript:void(0)" onclick="setFooterMode('jumlah')">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-calculator text-success"></i>
                                        <span>Jumlah Saja</span>
                                    </span>
                                    <i class="bi bi-check-lg text-primary ms-2 footer-mode-check {{ $currentFooterMode === 'jumlah' ? '' : 'd-none' }}"></i>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item footer-mode-item d-flex align-items-center justify-content-between py-1.5 px-3 {{ $currentFooterMode === 'total' ? 'active fw-bold' : '' }}" data-mode="total" href="javascript:void(0)" onclick="setFooterMode('total')">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-sigma text-info"></i>
                                        <span>Total Saja</span>
                                    </span>
                                    <i class="bi bi-check-lg text-primary ms-2 footer-mode-check {{ $currentFooterMode === 'total' ? '' : 'd-none' }}"></i>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item footer-mode-item d-flex align-items-center justify-content-between py-1.5 px-3 text-danger {{ $currentFooterMode === 'none' ? 'active fw-bold' : '' }}" data-mode="none" href="javascript:void(0)" onclick="setFooterMode('none')">
                                    <span class="d-flex align-items-center gap-2">
                                        <i class="bi bi-eye-slash"></i>
                                        <span>Nonaktifkan (Sembunyikan)</span>
                                    </span>
                                    <i class="bi bi-check-lg text-danger ms-2 footer-mode-check {{ $currentFooterMode === 'none' ? '' : 'd-none' }}"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif

                <!-- Tombol Reset Ukuran Tabel (Pribadi per Akun) -->
                <button type="button" class="btn btn-sm bg-white border shadow-xs d-inline-flex align-items-center py-1.5 px-2.5 rounded-2 fw-semibold text-secondary" onclick="resetAllTableSizes()" title="Kembalikan ukuran kolom & baris ke ukuran normal/standar" style="font-size: 0.78rem; gap: 5px;">
                    <i class="bi bi-arrows-angle-contract text-muted"></i>
                    <span>Reset Ukuran</span>
                </button>
            </div>
        </div>
    </div>

    <!-- HIERARCHICAL INDICATORS TABLE -->
    <div class="card border-0 shadow-xs mb-4">
        <div class="card-body p-0">
            <div class="table-responsive table-resizable-wrap" id="mainTableResponsiveWrapper">
                <table class="table table-bordered table-hover align-middle mb-0 custom-data-table">
                    <thead class="bg-light">
                        @php
                            $canEdit = auth()->check() && auth()->user()->canEditData();

                            // Helper: leaf count of a column node
                            $getLeafCount = function($col) use (&$getLeafCount) {
                                if (empty($col['sub_columns']) || !is_array($col['sub_columns'])) {
                                    return 1;
                                }
                                $count = 0;
                                foreach ($col['sub_columns'] as $sub) {
                                    $count += $getLeafCount($sub);
                                }
                                return max(1, $count);
                            };

                            // Helper: max tree depth of a column node
                            $getNodeDepth = function($col) use (&$getNodeDepth) {
                                if (empty($col['sub_columns']) || !is_array($col['sub_columns'])) {
                                    return 1;
                                }
                                $maxSub = 1;
                                foreach ($col['sub_columns'] as $sub) {
                                    $maxSub = max($maxSub, $getNodeDepth($sub));
                                }
                                return 1 + $maxSub;
                            };

                            // Calculate maximum header depth across all columns
                            $maxHeaderDepth = 1;
                            foreach ($activeColumns as $c) {
                                $maxHeaderDepth = max($maxHeaderDepth, $getNodeDepth($c));
                            }
                            $hasSubColumns = $maxHeaderDepth > 1;

                            // Helper: gather all leaf columns with ancestor chain
                            $leafColumns = [];
                            $collectLeaves = function($cols, $ancestors = []) use (&$collectLeaves, &$leafColumns) {
                                foreach ($cols as $col) {
                                    $currAncestors = array_merge($ancestors, [$col]);
                                    if (!empty($col['sub_columns']) && is_array($col['sub_columns'])) {
                                        $collectLeaves($col['sub_columns'], $currAncestors);
                                    } else {
                                        $leaf = $col;
                                        $leaf['ancestors'] = $ancestors;
                                        $leaf['parent'] = !empty($ancestors) ? end($ancestors) : null;
                                        $leaf['parent_id'] = !empty($ancestors) ? (end($ancestors)['id'] ?? null) : null;
                                        $leaf['root'] = !empty($ancestors) ? $ancestors[0] : $col;
                                        $leaf['is_sub'] = !empty($ancestors);
                                        $leafColumns[] = $leaf;
                                    }
                                }
                            };
                            $collectLeaves($activeColumns);

                            $totalColspan = max(1, count($leafColumns) + ($canEdit ? 1 : 0));

                            // Build the grid rows for thead
                            $headerRows = array_fill(0, $maxHeaderDepth, []);
                            $buildHeaderRows = function($cols, $currentDepth, $maxDepth) use (&$buildHeaderRows, &$headerRows, $getLeafCount) {
                                foreach ($cols as $c) {
                                    $hasSubs = !empty($c['sub_columns']) && is_array($c['sub_columns']);
                                    $leafCount = $getLeafCount($c);
                                    if ($hasSubs) {
                                        $headerRows[$currentDepth][] = [
                                            'col' => $c,
                                            'colspan' => $leafCount,
                                            'rowspan' => 1,
                                            'has_subs' => true,
                                            'is_leaf' => false,
                                            'depth' => $currentDepth,
                                        ];
                                        $buildHeaderRows($c['sub_columns'], $currentDepth + 1, $maxDepth);
                                    } else {
                                        $headerRows[$currentDepth][] = [
                                            'col' => $c,
                                            'colspan' => 1,
                                            'rowspan' => $maxDepth - $currentDepth,
                                            'has_subs' => false,
                                            'is_leaf' => true,
                                            'depth' => $currentDepth,
                                        ];
                                    }
                                }
                            };
                            $buildHeaderRows($activeColumns, 0, $maxHeaderDepth);
                        @endphp

                        @foreach($headerRows as $depth => $cells)
                            <tr class="{{ $depth === 0 ? 'bg-light' : ($depth === 1 ? 'bg-slate-50 border-bottom border-slate-300' : 'bg-white border-bottom border-slate-200') }}">
                                @if(count($cells) === 0 && count($activeColumns) === 0)
                                    <th class="text-muted small py-2 px-3 text-start">Kolom Tabel (Belum Ditentukan)</th>
                                @endif
                                @foreach($cells as $item)
                                    @php
                                        $col = $item['col'];
                                        $cName = $col['name'] ?? '';
                                        $hasSubs = $item['has_subs'];
                                        $colspan = $item['colspan'];
                                        $rowspan = $item['rowspan'];
                                        $isSubTotal = in_array(strtolower(trim($cName)), ['jumlah', 'total', 'jml', 'jmlh']) || ($col['is_total'] ?? false);
                                        $isN = in_array(strtolower(trim($cName)), ['no', 'no.']);
                                        $cMinW = $isN ? '42px' : 'auto';
                                    @endphp
                                    @if($hasSubs)
                                        <!-- Kolom Induk dengan Sub-Kolom (Colspan {{ $colspan }}) -->
                                        <th colspan="{{ $colspan }}" rowspan="{{ $rowspan }}" class="py-1.5 px-2 text-center column-header-th position-relative {{ $depth === 0 ? 'bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25' : 'bg-slate-100 border-bottom border-slate-300' }}" style="{{ $cMinW !== 'auto' ? 'min-width: ' . $cMinW . ';' : '' }}">
                                            <div class="d-flex align-items-center justify-content-center gap-1.5">
                                                <span class="fw-bold {{ $depth === 0 ? 'text-primary text-uppercase' : 'text-slate-800' }}" style="font-size: {{ $depth === 0 ? '0.76rem' : '0.73rem' }}; letter-spacing: 0.3px;">
                                                    @if(preg_match('/^(19|20)\d{2}$/', trim($cName)))
                                                        <i class="bi bi-calendar3 me-1 opacity-75"></i>
                                                    @endif
                                                    {{ $col['name'] }}
                                                </span>
                                                @if($canEdit)
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-xs btn-link text-muted p-0 border-0 opacity-40 col-header-opt-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi '{{ $col['name'] }}'">
                                                            <i class="bi bi-three-dots-vertical" style="font-size: 0.68rem;"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 0.78rem;">
                                                            <li>
                                                                <button type="button" class="dropdown-item py-1 px-2.5 d-flex align-items-center gap-2 text-success fw-semibold" onclick="openSubColumnModal('{{ $col['id'] }}', '{{ addslashes($col['name']) }}')">
                                                                    <i class="bi bi-diagram-3-fill"></i> Atur Sub-Kolom
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item py-1 px-2.5 d-flex align-items-center gap-2" onclick="openEditColumnModal('{{ $col['id'] }}', '{{ addslashes($col['name']) }}', '{{ $col['type'] ?? 'text' }}')">
                                                                    <i class="bi bi-pencil text-primary"></i> Edit Nama Kolom
                                                                </button>
                                                            </li>
                                                            <li><hr class="dropdown-divider my-1"></li>
                                                            <li>
                                                                @if($depth === 0)
                                                                    <button type="button" class="dropdown-item py-1 px-2.5 d-flex align-items-center gap-2 text-danger" onclick="deleteColumn('{{ $col['id'] }}', '{{ addslashes($col['name']) }}', '{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}', '{{ $activeTableId }}')">
                                                                        <i class="bi bi-trash3 text-danger"></i> Hapus Kolom
                                                                    </button>
                                                                @else
                                                                    <button type="button" class="dropdown-item py-1 px-2.5 d-flex align-items-center gap-2 text-danger" onclick="deleteSubColDirect('{{ $col['id'] }}', '{{ addslashes($col['name']) }}')">
                                                                        <i class="bi bi-trash3 text-danger"></i> Hapus Sub-Kolom
                                                                    </button>
                                                                @endif
                                                            </li>
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </th>
                                    @else
                                        <!-- Kolom Daun (Leaf Column) -->
                                        <th colspan="{{ $colspan }}" rowspan="{{ $rowspan }}" data-col-id="{{ $col['id'] }}" class="py-1.5 px-2 {{ $isN ? 'text-center' : ($depth > 0 ? 'text-center' : 'text-start') }} column-header-th position-relative {{ $rowspan > 1 ? 'align-middle' : '' }} {{ $isSubTotal ? 'bg-primary bg-opacity-10' : ($depth > 0 ? 'bg-slate-50' : '') }}" style="{{ $cMinW !== 'auto' ? 'min-width: ' . $cMinW . ';' : '' }}; font-size: {{ $depth > 0 ? '0.72rem' : '0.74rem' }};">
                                            <div class="d-flex align-items-center {{ $isN || $depth > 0 ? 'justify-content-center' : 'justify-content-between' }} gap-1">
                                                <span class="fw-bold {{ $isSubTotal ? 'text-primary' : 'text-slate-800' }} {{ $depth === 0 ? 'text-uppercase' : '' }}" style="letter-spacing: 0.3px;">
                                                    {{ $col['name'] }}
                                                    @if($depth === 0 && ($col['id'] === 'col_nilai' || strtolower($col['name']) === 'nilai'))
                                                        <small class="text-muted fw-normal text-capitalize">({{ $tahun->tahun ?? 2025 }})</small>
                                                    @endif
                                                </span>
                                                @if($canEdit)
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-xs btn-link text-muted p-0 border-0 opacity-40 col-header-opt-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Opsi '{{ $col['name'] }}'">
                                                            <i class="bi bi-three-dots-vertical" style="font-size: 0.68rem;"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 0.78rem;">
                                                            <li>
                                                                <button type="button" class="dropdown-item py-1 px-2.5 d-flex align-items-center gap-2 text-success fw-semibold" onclick="openSubColumnModal('{{ $col['id'] }}', '{{ addslashes($col['name']) }}')">
                                                                    <i class="bi bi-diagram-3-fill"></i> Tambah Sub-Kolom
                                                                </button>
                                                            </li>
                                                            <li>
                                                                <button type="button" class="dropdown-item py-1 px-2.5 d-flex align-items-center gap-2" onclick="openEditColumnModal('{{ $col['id'] }}', '{{ addslashes($col['name']) }}', '{{ $col['type'] ?? 'text' }}')">
                                                                    <i class="bi bi-pencil text-primary"></i> Edit Kolom
                                                                </button>
                                                            </li>
                                                            <li><hr class="dropdown-divider my-1"></li>
                                                            <li>
                                                                @if($depth === 0)
                                                                    <button type="button" class="dropdown-item py-1 px-2.5 d-flex align-items-center gap-2 text-danger" onclick="deleteColumn('{{ $col['id'] }}', '{{ addslashes($col['name']) }}', '{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}', '{{ $activeTableId }}')">
                                                                        <i class="bi bi-trash3 text-danger"></i> Hapus Kolom
                                                                    </button>
                                                                @else
                                                                    <button type="button" class="dropdown-item py-1 px-2.5 d-flex align-items-center gap-2 text-danger" onclick="deleteSubColDirect('{{ $col['id'] }}', '{{ addslashes($col['name']) }}')">
                                                                        <i class="bi bi-trash3 text-danger"></i> Hapus Sub-Kolom
                                                                    </button>
                                                                @endif
                                                            </li>
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </th>
                                    @endif
                                @endforeach

                                @if($depth === 0 && $canEdit)
                                    <!-- Kolom Header Aksi -->
                                    <th rowspan="{{ $maxHeaderDepth }}" data-col-id="col_aksi" class="text-center py-1.5 px-1 sticky-action-col align-middle" style="width: 78px; min-width: 78px; max-width: 78px; z-index: 10;">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <span class="fw-bold text-slate-800 text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.3px;">Aksi</span>
                                            <button type="button" class="btn btn-xs btn-primary text-white py-0 px-1 border-0 rounded-1" data-bs-toggle="modal" data-bs-target="#modalTambahKolom" title="Tambah Kolom Baru">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs text-white py-0 px-1 border-0 rounded-1" data-bs-toggle="modal" data-bs-target="#modalAturTabel" title="Atur Tabel (Jumlah Kolom & Baris)" style="background: #0ea5e9;">
                                                <i class="bi bi-table"></i>
                                            </button>
                                        </div>
                                    </th>
                                @endif
                            </tr>
                        @endforeach
                    </thead>
                    <tbody>
                        @if(auth()->check() && auth()->user()->canEditData())
                            <tr id="inlineAddRow" class="table-primary bg-primary bg-opacity-10 border-top border-bottom border-primary" style="display: none;">
                                <!-- Kolom-kolom Dinamis -->
                                @foreach($leafColumns as $leaf)
                                    @if($leaf['is_sub'])
                                        <td class="text-center">
                                            <input type="text" id="inlineCustom_{{ $leaf['id'] }}" data-col-id="{{ $leaf['id'] }}" class="form-control form-control-sm inline-custom-field text-center font-monospace" placeholder="{{ $leaf['name'] }}...">
                                        </td>
                                    @else
                                        @php
                                            $cName = $leaf['name'] ?? '';
                                            $isN = in_array(strtolower(trim($cName)), ['no', 'no.', 'nomor']);
                                        @endphp
                                        @if($leaf['id'] === 'col_satuan')
                                            <td class="text-start">
                                                <input type="text" id="inlineSatuan" class="form-control form-control-sm text-start" placeholder="%, Org, dll">
                                            </td>
                                        @elseif($leaf['id'] === 'col_nilai')
                                            <td class="text-center">
                                                <input type="text" id="inlineNilai" class="form-control form-control-sm text-center font-monospace fw-bold" placeholder="0">
                                            </td>
                                        @elseif($leaf['id'] === 'col_sumber')
                                            <td class="text-start">
                                                <input type="text" id="inlineSumber" class="form-control form-control-sm text-start" placeholder="Sumber Data...">
                                            </td>
                                        @else
                                            <td class="{{ $isN ? 'text-center' : 'text-start' }}">
                                                <input type="text" id="inlineCustom_{{ $leaf['id'] }}" data-col-id="{{ $leaf['id'] }}" class="form-control form-control-sm inline-custom-field {{ $isN ? 'text-center font-monospace' : 'text-start' }}" placeholder="{{ $leaf['name'] }}...">
                                            </td>
                                        @endif
                                    @endif
                                @endforeach
                                <!-- Kolom Aksi -->
                                <td class="text-center sticky-action-col" style="width: 78px; min-width: 78px; max-width: 78px;">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <button type="button" class="btn btn-primary btn-sm py-1 px-2 fw-semibold" id="btnSaveInline" onclick="saveInlineRow()" title="Simpan Baris Ini ke Tabel">
                                            <i class="bi bi-check-lg"></i> Simpan
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" onclick="cancelInlineRow()" title="Batal">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        @php
                            $firstDescriptorCol = collect($activeColumns)->first(function ($c) {
                                $ln = strtolower(trim($c['name'] ?? ''));
                                return !in_array($ln, ['no', 'no.', 'nomor']) && empty($c['sub_columns']) && ($c['type'] ?? 'text') !== 'number';
                            });
                            if (!$firstDescriptorCol) {
                                $firstDescriptorCol = collect($activeColumns)->first(function ($c) {
                                    $ln = strtolower(trim($c['name'] ?? ''));
                                    return !in_array($ln, ['no', 'no.', 'nomor']) && empty($c['sub_columns']);
                                });
                            }
                            $firstDescriptorColId = $firstDescriptorCol['id'] ?? null;

                            // Recursive helpers for multi-level child aggregation (works for Induk, Sub-Induk, and deep hierarchies)
                            $getLeafValueForSubCol = function ($indicator, $subId, $colYearId, $yrNum) use (&$getLeafValueForSubCol) {
                                if ($indicator->children && $indicator->children->count() > 0) {
                                    $sum = 0;
                                    $hasVal = false;
                                    foreach ($indicator->children as $c) {
                                        $cv = $getLeafValueForSubCol($c, $subId, $colYearId, $yrNum);
                                        if ($cv !== null) {
                                            $sum += $cv;
                                            $hasVal = true;
                                        }
                                    }
                                    return $hasVal ? $sum : null;
                                }
                                foreach ($indicator->dataIndikators as $di) {
                                    $matchesYr = false;
                                    if ($colYearId && (int)$di->tahun_id === (int)$colYearId) {
                                        $matchesYr = true;
                                    } elseif ($yrNum && $di->tahun && (int)$di->tahun->tahun === (int)$yrNum) {
                                        $matchesYr = true;
                                    } elseif (!$colYearId && !$yrNum) {
                                        $matchesYr = true;
                                    }
                                    if ($matchesYr) {
                                        $v = $di->getCustomValue($subId);
                                        if ($v !== null && $v !== '') {
                                            $norm = str_replace(',', '.', trim((string) $v));
                                            if (is_numeric($norm)) return (float) $norm;
                                        }
                                    }
                                }
                                return null;
                            };

                            $getLeafValueForCol = function ($indicator, $colId, $colYearId, $yrNum, $isYrCol) use (&$getLeafValueForCol) {
                                if ($indicator->children && $indicator->children->count() > 0) {
                                    $sum = 0;
                                    $hasVal = false;
                                    foreach ($indicator->children as $c) {
                                        $cv = $getLeafValueForCol($c, $colId, $colYearId, $yrNum, $isYrCol);
                                        if ($cv !== null) {
                                            $sum += $cv;
                                            $hasVal = true;
                                        }
                                    }
                                    return $hasVal ? $sum : null;
                                }
                                $cDi = null;
                                if ($isYrCol) {
                                    $cDi = $indicator->dataIndikators->first(function ($di) use ($yrNum, $colYearId) {
                                        return ($yrNum && $di->tahun && (int)$di->tahun->tahun === $yrNum) || ($colYearId && $di->tahun_id === $colYearId);
                                    });
                                } else {
                                    if ($colYearId) {
                                        $cDi = $indicator->dataIndikators->firstWhere('tahun_id', $colYearId);
                                    }
                                    if (!$cDi) {
                                        $cDi = $indicator->current_data ?? $indicator->dataIndikators->first();
                                    }
                                }
                                if ($cDi) {
                                    $cv = $cDi->getCustomValue($colId);
                                    if ($cv !== null && $cv !== '') {
                                        $norm = str_replace(',', '.', trim((string) $cv));
                                        if (is_numeric($norm)) return (float) $norm;
                                    }
                                    if ($cDi->nilai !== null && $cDi->nilai !== '') {
                                        $norm = str_replace(',', '.', trim((string) $cDi->nilai));
                                        if (is_numeric($norm)) return (float) $norm;
                                    }
                                }
                                return null;
                            };
                        @endphp

                        @forelse($flattenedIndicators as $ind)
                            @php
                                $level = $ind->tree_level ?? 0;
                                $indentPx = $level * 22;
                                $valObj = $ind->current_data;
                                $hasVal = $valObj && $valObj->nilai !== null && $valObj->nilai !== '';
                                $hasIndukChildren = $ind->children && $ind->children->count() > 0;
                                $rowIndukClass = $hasIndukChildren ? ($level === 0 ? 'table-induk-row' : 'table-sub-induk-row') : '';
                                $cellIndukClass = $hasIndukChildren ? ($level === 0 ? 'table-induk-cell' : 'table-sub-induk-cell') : '';
                            @endphp
                            <tr class="indikator-data-row {{ !$ind->status_aktif ? 'opacity-75 bg-light' : '' }} {{ $rowIndukClass }}" 
                                id="rowIndikator_{{ $ind->id }}"
                                data-id="{{ $ind->id }}"
                                data-parent-id="{{ $ind->parent_id ?? '' }}"
                                data-level="{{ $level }}"
                                data-is-induk="{{ $hasIndukChildren ? 'true' : 'false' }}">

                                <!-- Kolom-kolom Dinamis (Murni Ditentukan Pengguna) -->
                                @if(count($activeColumns) === 0)
                                    <td class="text-muted small fst-italic py-3">
                                        Kolom belum ditentukan. Klik <strong>Atur Tabel</strong> untuk menentukan kolom data.
                                    </td>
                                @endif
                                @foreach($leafColumns as $col)
                                    @if(!empty($col['is_sub']))
                                        <!-- Render Sel Sub-Kolom (Dapat Bertingkat/Nested) -->
                                        @php
                                            $subId = $col['id'];
                                            $subName = $col['name'] ?? '';
                                            $isTotal = in_array(strtolower(trim($subName)), ['jumlah', 'total', 'jml', 'jmlh']) || ($col['is_total'] ?? false);
                                            $val = null;
                                            $rawVal = '';

                                            // Detect Year context from leaf or any ancestor in hierarchy
                                            $yrNum = null;
                                            $colYearId = $selectedTahunId ?? ($tahun->id ?? null);
                                            $yrRecord = null;
                                            if (preg_match('/^(19|20)\d{2}$/', trim($subName), $mYr)) {
                                                $yrNum = (int) $mYr[0];
                                            } else {
                                                foreach (array_reverse($col['ancestors'] ?? []) as $anc) {
                                                    if (preg_match('/^(19|20)\d{2}$/', trim($anc['name'] ?? ''), $mYr)) {
                                                        $yrNum = (int) $mYr[0];
                                                        break;
                                                    }
                                                }
                                            }
                                            if ($yrNum) {
                                                $thColModel = \App\Models\Tahun::where('tahun', $yrNum)->first();
                                                if ($thColModel) {
                                                    $colYearId = $thColModel->id;
                                                }
                                                $yrRecord = $ind->dataIndikators->first(function ($di) use ($yrNum, $colYearId) {
                                                    return ($di->tahun && (int)$di->tahun->tahun === $yrNum) || ($di->tahun_id === $colYearId);
                                                });
                                            }

                                            if ($hasIndukChildren) {
                                                // BARIS INDUK / SUB-INDUK: Nilai otomatis dihitung rekursif dari seluruh sub-baris turunan
                                                if ($isTotal) {
                                                    $parentTotalSum = 0;
                                                    $hasAnySibling = false;
                                                    $siblings = !empty($col['parent']['sub_columns']) ? $col['parent']['sub_columns'] : [];
                                                    foreach ($siblings as $sibling) {
                                                        if (($sibling['id'] ?? '') === $subId) {
                                                            break;
                                                        }
                                                        if (!($sibling['is_total'] ?? false) && !in_array(strtolower(trim($sibling['name'] ?? '')), ['jumlah', 'total', 'jml', 'jmlh'])) {
                                                            $sChildSum = $getLeafValueForSubCol($ind, $sibling['id'], $colYearId, $yrNum);
                                                            if ($sChildSum !== null) {
                                                                $parentTotalSum += $sChildSum;
                                                                $hasAnySibling = true;
                                                            }
                                                        }
                                                    }
                                                    $val = $hasAnySibling ? ((floor($parentTotalSum) == $parentTotalSum) ? (string)(int)$parentTotalSum : (string)round($parentTotalSum, 2)) : '0';
                                                    $rawVal = $val;
                                                } else {
                                                    $cSubSum = $getLeafValueForSubCol($ind, $subId, $colYearId, $yrNum);
                                                    $val = ($cSubSum !== null) ? ((floor($cSubSum) == $cSubSum) ? (string)(int)$cSubSum : (string)round($cSubSum, 2)) : '0';
                                                    $rawVal = $val;
                                                }
                                            } else {
                                                // SUB-BARIS / BARIS BIASA: Ambil dari data inputan
                                                if ($yrRecord) {
                                                    $val = $yrRecord->getCustomValue($subId);
                                                    $rawVal = $val;
                                                } elseif ($valObj) {
                                                    $val = $valObj->getCustomValue($subId);
                                                    $rawVal = $val;
                                                }

                                                // Hitung otomatis jika Kolom Total (is_total: true)
                                                if ($isTotal) {
                                                    $sum = 0;
                                                    $hasAny = false;
                                                    $siblings = !empty($col['parent']['sub_columns']) ? $col['parent']['sub_columns'] : [];
                                                    foreach ($siblings as $sibling) {
                                                        if (($sibling['id'] ?? '') === $subId) {
                                                            break;
                                                        }
                                                        if (!($sibling['is_total'] ?? false) && !in_array(strtolower(trim($sibling['name'] ?? '')), ['jumlah', 'total', 'jml', 'jmlh'])) {
                                                            $sVal = $yrRecord ? $yrRecord->getCustomValue($sibling['id']) : ($valObj ? $valObj->getCustomValue($sibling['id']) : null);
                                                            if ($sVal !== null && $sVal !== '' && is_numeric($sVal)) {
                                                                $sum += (float) $sVal;
                                                                $hasAny = true;
                                                            }
                                                        }
                                                    }
                                                    if ($hasAny) {
                                                        $val = (floor($sum) == $sum) ? (string)(int)$sum : (string)round($sum, 2);
                                                        $rawVal = $val;
                                                    } else {
                                                        $val = '0';
                                                        $rawVal = '0';
                                                    }
                                                }
                                            }
                                            $canEditSubCell = $canEdit && !$hasIndukChildren && !$isTotal;
                                        @endphp
                                        <td class="{{ $canEditSubCell ? 'editable-cell' : '' }} py-1.5 px-2 text-center {{ $cellIndukClass }} {{ $isTotal ? 'cell-total-col fw-bold' : '' }} cell-metric"
                                            data-id="{{ $ind->id }}"
                                            data-field="{{ $subId }}"
                                            data-parent-field="{{ $col['parent_id'] ?? ($col['parent']['id'] ?? '') }}"
                                            data-tahun-id="{{ $colYearId }}"
                                            data-is-total="{{ $isTotal ? 'true' : 'false' }}"
                                            data-raw="{{ $rawVal }}"
                                            data-align="center"
                                            data-type="{{ $col['type'] ?? 'number' }}"
                                            data-is-descriptor="false"
                                            data-is-sub="true"
                                            data-col-role="sub_column"
                                            data-col-name="{{ strtolower(trim($subName)) }}"
                                            title="{{ $isTotal ? 'Jumlah otomatis dari sub-kolom ini (' . ($val ?: '0') . ')' : ($hasIndukChildren ? 'Total otomatis dari seluruh sub-baris (' . ($val ?: '0') . ')' : ($canEditSubCell ? 'Klik untuk mengisi sub-kolom ' . $subName . ' (' . ($col['parent_name'] ?? '') . ')' : $subName)) }}">
                                            <div class="cell-wrapper rounded d-flex align-items-center justify-content-center gap-1">
                                                <span class="cell-val-display text-center {{ $isTotal ? 'text-total-val text-primary fw-bold font-monospace' : ($hasIndukChildren ? 'text-slate-900 fw-bold' : 'text-slate-800') }}" style="font-size: 0.82rem;">
                                                    @if($val !== null && $val !== '')
                                                        {{ $val }}
                                                    @else
                                                        @if($isTotal)
                                                            <span class="text-primary fw-bold font-monospace">0</span>
                                                        @elseif($hasIndukChildren)
                                                            <span class="text-muted font-monospace opacity-50">—</span>
                                                        @else
                                                            <span class="text-muted fst-italic opacity-40" style="font-size: 0.72rem;">{{ $canEditSubCell ? '+ Isi' : '—' }}</span>
                                                        @endif
                                                    @endif
                                                </span>
                                                @if($canEditSubCell)
                                                    <i class="bi bi-pencil small text-primary opacity-0 cell-edit-icon" style="font-size: 0.60rem;"></i>
                                                @endif
                                            </div>
                                        </td>
                                    @else
                                        <!-- Render Sel Kolom Biasa (Non-Sub) -->
                                        @php
                                            $colId = $col['id'];
                                            $lowerName = strtolower(trim($col['name']));
                                            $isNo = in_array($lowerName, ['no', 'no.', 'nomor']);
                                            $isNama = ($colId === 'col_nama' || $colId === 'nama_indikator' || in_array($lowerName, ['nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian', 'tahun', 'thn', 'periode']) || ($firstDescriptorColId && $colId === $firstDescriptorColId));
                                            $isNilai = ($colId === 'col_nilai' || $lowerName === 'nilai' || str_starts_with($lowerName, 'nilai '));
                                            $isSatuan = ($colId === 'col_satuan' || $lowerName === 'satuan');
                                            $isSumber = ($colId === 'col_sumber' || $lowerName === 'sumber data' || $lowerName === 'sumber_data');
                                            // Kolom row total hanya berlaku jika ada kolom metrik/data yang mendahului kolom ini dalam baris
                                            $hasPrecedingMetricCol = false;
                                            for ($cIdx = 0; $cIdx < $loop->index; $cIdx++) {
                                                $pCol = $activeColumns[$cIdx] ?? null;
                                                if (!$pCol) continue;
                                                $pLower = strtolower(trim($pCol['name'] ?? ''));
                                                if (!empty($pCol['sub_columns']) && is_array($pCol['sub_columns'])) {
                                                    $hasPrecedingMetricCol = true;
                                                    break;
                                                }
                                                if (!in_array($pLower, ['no', 'no.', 'nomor', 'nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian', 'satuan', 'sumber data', 'sumber_data', 'aksi', 'jumlah', 'total', 'jml', 'jmlh'])) {
                                                    $hasPrecedingMetricCol = true;
                                                    break;
                                                }
                                            }
                                            $isRowTotal = $hasPrecedingMetricCol && (in_array($lowerName, ['jumlah', 'total', 'jml', 'jmlh']) || ($col['is_total'] ?? false));

                                            $val = null;
                                            $rawVal = '';
                                            if ($isNama) {
                                                if ($valObj && $valObj->getCustomValue($colId) !== null && $valObj->getCustomValue($colId) !== '') {
                                                    $val = $valObj->getCustomValue($colId);
                                                } else {
                                                    $val = $ind->nama_indikator;
                                                }
                                                // Jika nilai nama_indikator kosong, jadikan null agar otomatis menampilkan '+ Isi'
                                                if ($val !== null && is_string($val)) {
                                                    $trimmedVal = trim($val);
                                                    if ($trimmedVal === '') {
                                                        $val = null;
                                                    }
                                                }
                                                $rawVal = $val ?: '';
                                            } elseif ($isSatuan) {
                                                if (!empty($ind->satuan)) {
                                                    $val = $ind->satuan;
                                                } elseif ($valObj && !empty($valObj->satuan)) {
                                                    $val = $valObj->satuan;
                                                } elseif ($hasIndukChildren) {
                                                    $firstChildWithSatuan = $ind->children->first(fn($c) => !empty($c->satuan));
                                                    if ($firstChildWithSatuan) {
                                                        $val = $firstChildWithSatuan->satuan;
                                                    }
                                                }
                                                $rawVal = $val;
                                            } elseif ($isNilai && $valObj && $valObj->nilai !== null && $valObj->nilai !== '') {
                                                $val = $valObj->formatted_nilai;
                                                $rawVal = $valObj->nilai;
                                            } elseif ($isSumber && $valObj && !empty($valObj->sumber_data)) {
                                                $val = $valObj->sumber_data;
                                                $rawVal = $valObj->sumber_data;
                                            } elseif ($valObj && $valObj->getCustomValue($colId) !== null) {
                                                $val = $valObj->getCustomValue($colId);
                                                $rawVal = $val;
                                            }

                                            // Fallback jika kolom bertipe tahun: cek apakah indikator memiliki data pada tahun tersebut
                                            $isYrCol = preg_match('/^(19|20)\d{2}$/', trim($col['name'] ?? ''), $mYrCol);
                                            $colYearId = $selectedTahunId ?? ($tahun->id ?? null);
                                            $yrNum = null;
                                            $yrRecord = null;
                                            if ($isYrCol) {
                                                $yrNum = (int) $mYrCol[0];
                                                $thColModel = \App\Models\Tahun::where('tahun', $yrNum)->first();
                                                if ($thColModel) {
                                                    $colYearId = $thColModel->id;
                                                }
                                                $yrRecord = $ind->dataIndikators->first(function ($di) use ($yrNum, $colYearId) {
                                                    return ($di->tahun && (int)$di->tahun->tahun === $yrNum) || ($di->tahun_id === $colYearId);
                                                });
                                                if ($yrRecord && ($val === null || $val === '')) {
                                                    if ($yrRecord->getCustomValue($colId) !== null && $yrRecord->getCustomValue($colId) !== '') {
                                                        $val = $yrRecord->getCustomValue($colId);
                                                        $rawVal = $val;
                                                    } elseif ($yrRecord->nilai !== null && $yrRecord->nilai !== '') {
                                                        $val = $yrRecord->formatted_nilai;
                                                        $rawVal = $yrRecord->nilai;
                                                    }
                                                }
                                            } else {
                                                $yrRecord = $valObj ?? ($colYearId ? $ind->dataIndikators->firstWhere('tahun_id', $colYearId) : null);
                                            }

                                            // Auto-sum dari sub-kolom atau metrik sebelumnya dalam baris ini (untuk kolom standalone JUMLAH / TOTAL)
                                            if ($isRowTotal && !$hasIndukChildren) {
                                                $autoRowSum = 0;
                                                $hasAutoSum = false;
                                                for ($cIdx = 0; $cIdx < $loop->index; $cIdx++) {
                                                    $pCol = $activeColumns[$cIdx] ?? null;
                                                    if (!$pCol) continue;
                                                    $pLower = strtolower(trim($pCol['name'] ?? ''));
                                                    if (in_array($pLower, ['no', 'no.', 'nomor', 'nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian', 'satuan', 'sumber data', 'sumber_data', 'aksi', 'jumlah', 'total', 'jml', 'jmlh'])) {
                                                        continue;
                                                    }
                                                    if (!empty($pCol['sub_columns']) && is_array($pCol['sub_columns'])) {
                                                        foreach ($pCol['sub_columns'] as $pSub) {
                                                            $subLower = strtolower(trim($pSub['name'] ?? ''));
                                                            if (in_array($subLower, ['jumlah', 'total', 'jml', 'jmlh']) || ($pSub['is_total'] ?? false)) {
                                                                continue;
                                                            }
                                                            $sRaw = $yrRecord ? $yrRecord->getCustomValue($pSub['id']) : ($valObj ? $valObj->getCustomValue($pSub['id']) : null);
                                                            if ($sRaw !== null && $sRaw !== '') {
                                                                $norm = str_replace(',', '.', trim((string) $sRaw));
                                                                if (is_numeric($norm)) {
                                                                    $autoRowSum += (float) $norm;
                                                                    $hasAutoSum = true;
                                                                }
                                                            }
                                                        }
                                                    } else {
                                                        $cRaw = null;
                                                        if ($yrRecord) {
                                                            $cRaw = $yrRecord->getCustomValue($pCol['id']) ?? $yrRecord->nilai;
                                                        } elseif ($valObj) {
                                                            $cRaw = $valObj->getCustomValue($pCol['id']) ?? $valObj->nilai;
                                                        }
                                                        if ($cRaw !== null && $cRaw !== '') {
                                                            $norm = str_replace(',', '.', trim((string) $cRaw));
                                                            if (is_numeric($norm)) {
                                                                $autoRowSum += (float) $norm;
                                                                $hasAutoSum = true;
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($hasAutoSum) {
                                                    $val = (floor($autoRowSum) == $autoRowSum) ? (string)(int)$autoRowSum : (string)round($autoRowSum, 2);
                                                    $rawVal = $val;
                                                } elseif ($val !== null && $val !== '') {
                                                    $rawVal = $val;
                                                } else {
                                                    $val = '0';
                                                    $rawVal = '0';
                                                }
                                            }

                                            // Auto-sum dari sub-indikator jika ini adalah baris Induk untuk semua kolom metrik/data (nilai, tahun, maupun custom column seperti Jumlah)
                                            if ($hasIndukChildren && !$isNo && !$isNama && !$isSatuan && !$isSumber) {
                                                $getChildMetricVal = function($item) use (&$getChildMetricVal, $colId, $isYrCol, $yrNum, $colYearId, $isRowTotal, $activeColumns) {
                                                    if ($item->children && $item->children->count() > 0) {
                                                        $subSum = 0;
                                                        $subHasVal = false;
                                                        foreach ($item->children as $subChild) {
                                                            $res = $getChildMetricVal($subChild);
                                                            if ($res !== null) {
                                                                $subSum += $res;
                                                                $subHasVal = true;
                                                            }
                                                        }
                                                        return $subHasVal ? $subSum : null;
                                                    }

                                                    $cDi = null;
                                                    if ($isYrCol) {
                                                        $cDi = $item->dataIndikators->first(function ($di) use ($yrNum, $colYearId) {
                                                            return ($yrNum && $di->tahun && (int)$di->tahun->tahun === $yrNum) || ($colYearId && $di->tahun_id === $colYearId);
                                                        });
                                                    } else {
                                                        if ($colYearId) {
                                                            $cDi = $item->dataIndikators->firstWhere('tahun_id', $colYearId);
                                                        }
                                                        if (!$cDi) {
                                                            $cDi = $item->current_data ?? $item->dataIndikators->first();
                                                        }
                                                    }

                                                    if ($cDi) {
                                                        $cv = $cDi->getCustomValue($colId);
                                                        if ($cv !== null && $cv !== '') {
                                                            $norm = str_replace(',', '.', trim((string) $cv));
                                                            if (is_numeric($norm)) return (float) $norm;
                                                        }
                                                        // Jika kolom Jumlah pada child belum tersimpan, hitung dari sub-kolomnya
                                                        if ($isRowTotal) {
                                                            $cRowSum = 0;
                                                            $cHasVal = false;
                                                            foreach ($activeColumns as $pCol) {
                                                                if ($pCol['id'] === $colId) break;
                                                                $pLower = strtolower(trim($pCol['name'] ?? ''));
                                                                if (in_array($pLower, ['no', 'no.', 'nomor', 'nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian', 'satuan', 'sumber data', 'sumber_data', 'aksi', 'jumlah', 'total', 'jml', 'jmlh'])) continue;
                                                                if (!empty($pCol['sub_columns']) && is_array($pCol['sub_columns'])) {
                                                                    foreach ($pCol['sub_columns'] as $pSub) {
                                                                        $subL = strtolower(trim($pSub['name'] ?? ''));
                                                                        if (in_array($subL, ['jumlah', 'total', 'jml', 'jmlh']) || ($pSub['is_total'] ?? false)) continue;
                                                                        $sRaw = $cDi->getCustomValue($pSub['id']);
                                                                        if ($sRaw !== null && $sRaw !== '') {
                                                                            $norm = str_replace(',', '.', trim((string) $sRaw));
                                                                            if (is_numeric($norm)) { $cRowSum += (float) $norm; $cHasVal = true; }
                                                                        }
                                                                    }
                                                                } else {
                                                                    $cRaw = $cDi->getCustomValue($pCol['id']) ?? $cDi->nilai;
                                                                    if ($cRaw !== null && $cRaw !== '') {
                                                                        $norm = str_replace(',', '.', trim((string) $cRaw));
                                                                        if (is_numeric($norm)) { $cRowSum += (float) $norm; $cHasVal = true; }
                                                                    }
                                                                }
                                                            }
                                                            if ($cHasVal) return $cRowSum;
                                                        }
                                                        if ($cDi->nilai !== null && $cDi->nilai !== '') {
                                                            $norm = str_replace(',', '.', trim((string) $cDi->nilai));
                                                            if (is_numeric($norm)) return (float) $norm;
                                                        }
                                                    }
                                                    return null;
                                                };

                                                $childSum = 0;
                                                $hasChildVal = false;
                                                foreach ($ind->children as $child) {
                                                    $cVal = $getChildMetricVal($child);
                                                    if ($cVal !== null) {
                                                        $childSum += $cVal;
                                                        $hasChildVal = true;
                                                    }
                                                }
                                                if ($hasChildVal) {
                                                    if (floor($childSum) == $childSum) {
                                                        $val = number_format($childSum, 0, ',', '.');
                                                    } else {
                                                        $val = rtrim(rtrim(number_format($childSum, 4, ',', '.'), '0'), ',');
                                                    }
                                                    $rawVal = (string) $childSum;
                                                }
                                            }

                                            // Kolom Nomor otomatis
                                            if ($isNo && ($val === null || $val === '')) {
                                                $val = (string) ($ind->hierarchical_no ?? ($level > 0 && $ind->parent ? ($ind->parent->urutan . '.' . ($ind->urutan ?: $loop->parent->iteration)) : ($ind->urutan ?: $loop->parent->iteration)));
                                                $rawVal = $val;
                                            }

                                            // Jika ini kolom pertama dan belum ada data terisi, tampilkan nama baris indikator agar bisa langsung diklik & diedit
                                            if ($loop->first && ($val === null || $val === '')) {
                                                $val = $ind->nama_indikator;
                                                if ($val !== null && is_string($val)) {
                                                    $trimmedVal = trim($val);
                                                    if ($trimmedVal === '') {
                                                        $val = null;
                                                    }
                                                }
                                                $rawVal = $val ?: '';
                                            }

                                            // Jika kolom nama_indikator kosong, pastikan tetap null agar menampilkan '+ Isi'
                                            if ($isNama && $val !== null && is_string($val)) {
                                                $trimmedVal = trim($val);
                                                if ($trimmedVal === '') {
                                                    $val = null;
                                                    $rawVal = '';
                                                }
                                            }

                                            $canEditNormalCell = $canEdit && (!$hasIndukChildren || $isNama) && !$isRowTotal;
                                            $isCenterCol = $isNo || $isRowTotal;
                                            $cellAlign = $isCenterCol ? 'text-center' : 'text-start';
                                            $cellJustify = $isCenterCol ? 'justify-content-center' : 'justify-content-start';
                                            $isDescriptorCol = ($isNo || $isNama || $isSatuan || $isSumber);
                                            $colRole = $isNo ? 'no' : ($isNama ? 'nama' : ($isSatuan ? 'satuan' : ($isSumber ? 'sumber' : ($isRowTotal ? 'total' : 'metric'))));
                                        @endphp
                                        <td class="{{ $canEditNormalCell ? 'editable-cell' : '' }} py-1.5 px-2 {{ $cellAlign }} {{ $level > 0 && !$hasIndukChildren ? 'bg-light bg-opacity-40' : '' }} {{ $cellIndukClass }} {{ $isDescriptorCol ? 'cell-descriptor' : 'cell-metric' }} {{ $isRowTotal ? 'cell-total-col fw-bold bg-primary bg-opacity-10 text-primary' : '' }}"
                                            data-id="{{ $ind->id }}"
                                            data-field="{{ $colId }}"
                                            data-tahun-id="{{ $colYearId }}"
                                            data-raw="{{ $rawVal }}"
                                            data-align="{{ $isCenterCol ? 'center' : 'start' }}"
                                            data-type="{{ $col['type'] ?? 'text' }}"
                                            data-is-descriptor="{{ $isDescriptorCol ? 'true' : 'false' }}"
                                            data-is-total="{{ $isRowTotal ? 'true' : 'false' }}"
                                            data-is-row-total="{{ $isRowTotal ? 'true' : 'false' }}"
                                            data-col-role="{{ $colRole }}"
                                            data-col-name="{{ strtolower(trim($col['name'] ?? '')) }}"
                                            title="{{ $isRowTotal ? 'Jumlah otomatis baris ini (' . ($val ?: '0') . ')' : (($hasIndukChildren && !$isNama) ? 'Total otomatis dari seluruh sub-baris' : ($canEditNormalCell ? 'Klik untuk langsung mengisi / mengedit sel ini' : 'Nilai Indikator')) }}">
                                            <div class="cell-wrapper rounded d-flex align-items-center {{ $cellJustify }} gap-1">
                                                @if($level > 0 && ($isNama || ($loop->first && !$isNo)))
                                                    <span class="d-inline-flex align-items-center me-1 flex-shrink-0" style="padding-left: {{ $indentPx }}px;">
                                                        <span class="text-secondary opacity-50 font-monospace me-1" style="font-size: 0.82rem;">└──</span>
                                                        @if($hasIndukChildren)
                                                            <span class="badge text-white border-0 px-1.5 py-0.5 me-1" style="font-size: 0.60rem; font-weight: 700; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border-radius: 4px; box-shadow: 0 1px 2px rgba(2, 132, 199, 0.25);">
                                                                <i class="bi bi-folder me-0.5"></i> SUB-INDUK
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-1 py-0 me-1" style="font-size: 0.60rem; font-weight: 600;">Sub</span>
                                                        @endif
                                                    </span>
                                                @elseif($hasIndukChildren && ($isNama || ($loop->first && !$isNo)))
                                                    <span class="badge bg-primary text-white border-0 px-1.5 py-0.5 me-1.5 flex-shrink-0" style="font-size: 0.62rem; font-weight: 700; background-color: #2563eb !important; border-radius: 4px; box-shadow: 0 1px 2px rgba(37, 99, 235, 0.25);">
                                                        <i class="bi bi-folder2-open me-0.5"></i> INDUK
                                                    </span>
                                                @endif
                                                <span class="cell-val-display {{ $cellAlign }} {{ $isRowTotal ? 'text-primary fw-bold font-monospace' : ($hasIndukChildren ? 'fw-bold text-dark' : 'text-slate-800') }}" style="font-size: 0.81rem;">
                                                    @if($val !== null && $val !== '')
                                                        {{ $val }}
                                                    @else
                                                        @if($hasIndukChildren)
                                                            <span class="text-muted font-monospace opacity-50">—</span>
                                                        @elseif($isRowTotal)
                                                            <span class="text-primary fw-bold font-monospace">0</span>
                                                        @else
                                                            <span class="text-muted fst-italic opacity-40" style="font-size: 0.75rem;">{{ $canEditNormalCell ? '+ Isi' : '—' }}</span>
                                                        @endif
                                                    @endif
                                                </span>
                                                @if($canEditNormalCell && !$isRowTotal)
                                                    <i class="bi bi-pencil small text-primary opacity-0 cell-edit-icon" style="font-size: 0.62rem;"></i>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                @endforeach

                                @if($canEdit)
                                    <!-- Action Button (Tambah Sub-Baris & Hapus) -->
                                    <td class="text-center py-1 px-1 sticky-action-col" style="width: 78px; min-width: 78px;">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            @if($level < 3)
                                                <button type="button" class="btn btn-outline-primary btn-sm py-0.5 px-1.5 border-0" 
                                                        onclick="openSubRowModal({{ $ind->id }}, '{{ addslashes($ind->nama_indikator) }}')" 
                                                        title="Tambah Sub-Baris / Rincian di bawah '{{ $ind->nama_indikator }}'">
                                                    <i class="bi bi-node-plus-fill" style="font-size: 0.88rem;"></i>
                                                </button>
                                            @endif
                                            @php
                                                $rowLabel = trim($ind->nama_indikator);
                                                if (!$rowLabel) {
                                                    $rowLabel = 'No. ' . ($ind->hierarchical_no ?? ($ind->urutan ?: $loop->iteration));
                                                }
                                            @endphp
                                            <form method="POST" action="{{ route('indikator.destroy', $ind->id) }}" class="d-inline m-0" onsubmit="return confirmDeleteIndikator(event, this, '{{ addslashes($rowLabel) }}', {{ $hasIndukChildren ? 'true' : 'false' }});">
                                                @csrf
                                                @method('DELETE')
                                                @if($ind->children && $ind->children->count() > 0)
                                                    <input type="hidden" name="force" value="1">
                                                @endif
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-0.5 px-1.5 border-0" title="Hapus Baris">
                                                    <i class="bi bi-trash3" style="font-size: 0.85rem;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr id="emptyStateRow">
                                <td colspan="{{ $totalColspan }}" class="text-center py-5 text-muted">
                                    <div class="py-3">
                                        <i class="bi bi-table fs-1 text-primary opacity-50 d-block mb-2"></i>
                                        <h6 class="fw-bold text-dark">Belum ada data pada tabel urusan ini</h6>
                                        @if(auth()->check() && auth()->user()->canEditData())
                                            <p class="small text-muted mb-3">Tentukan jumlah kolom dan baris yang Anda inginkan dengan tombol <strong>Atur Tabel</strong>.</p>
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <button type="button" class="btn btn-sm text-white fw-bold shadow-xs d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAturTabel" style="background: #0ea5e9;">
                                                    <i class="bi bi-table"></i> Atur Tabel
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-sm shadow-xs d-flex align-items-center gap-1" onclick="quickAddRow()">
                                                    <i class="bi bi-plus-circle"></i> Tambah Baris Langsung
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm shadow-xs d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalTambahKolom">
                                                    <i class="bi bi-plus-circle"></i> Tambah Kolom
                                                </button>
                                            </div>
                                        @else
                                            <p class="small text-muted mb-0">Data untuk urusan ini belum diinput oleh Administrator.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @php
                        $footerSubtotalLabel = $activeTable['footer_subtotal_label'] ?? 'TOTAL';
                        $showFooterSubtotal = $activeTable['show_footer_subtotal'] ?? true;
                        $leafSums = [];

                        $footerParentGroups = [];
                        $currentGroup = null;
                        $hasGroupWithSubs = false;

                        foreach ($leafColumns as $leaf) {
                            $parentId = $leaf['parent']['id'] ?? null;
                            $parentName = $leaf['parent']['name'] ?? null;

                            if ($parentId !== null) {
                                if ($currentGroup && $currentGroup['type'] === 'parent' && $currentGroup['id'] === $parentId) {
                                    $currentGroup['colspan']++;
                                    $currentGroup['leaf_ids'][] = $leaf['id'];
                                    $currentGroup['leaf_names'][] = $leaf['name'];
                                } else {
                                    if ($currentGroup) {
                                        $footerParentGroups[] = $currentGroup;
                                    }
                                    $currentGroup = [
                                        'type' => 'parent',
                                        'id' => $parentId,
                                        'name' => $parentName,
                                        'colspan' => 1,
                                        'leaf_ids' => [$leaf['id']],
                                        'leaf_names' => [$leaf['name']],
                                        'parent_obj' => $leaf['parent'],
                                    ];
                                }
                            } else {
                                if ($currentGroup) {
                                    $footerParentGroups[] = $currentGroup;
                                    $currentGroup = null;
                                }
                                $footerParentGroups[] = [
                                    'type' => 'leaf',
                                    'id' => $leaf['id'],
                                    'name' => $leaf['name'],
                                    'colspan' => 1,
                                    'leaf_ids' => [$leaf['id']],
                                    'leaf_names' => [$leaf['name']],
                                    'parent_obj' => null,
                                ];
                            }
                        }
                        if ($currentGroup) {
                            $footerParentGroups[] = $currentGroup;
                        }

                        foreach ($footerParentGroups as $g) {
                            if ($g['type'] === 'parent' && $g['colspan'] > 1) {
                                $hasGroupWithSubs = true;
                                break;
                            }
                        }
                    @endphp
                    @php
                        $actualFooterLabel = $footerLabel;
                        if (!$hasGroupWithSubs && $showFooterSubtotal && !$showFooterJumlah) {
                            $actualFooterLabel = $footerSubtotalLabel ?: 'TOTAL';
                        }
                    @endphp
                    <tfoot id="tableFooterTotal" class="table-footer-total" style="{{ $showFooterTotal && ($showFooterJumlah || ($hasGroupWithSubs && $showFooterSubtotal)) && count($flattenedIndicators) > 0 ? '' : 'display: none;' }}">
                        <!-- Baris 1: Total Per Leaf Column -->
                        <tr id="tableFooterRowJumlah" class="fw-bold bg-slate-100 border-top border-bottom border-slate-300" style="{{ $showFooterJumlah ? '' : 'display: none;' }} border-top: 2px solid #94a3b8 !important; border-bottom: 2px solid #94a3b8 !important;">
                            @foreach($leafColumns as $col)
                                @if(!empty($col['is_sub']))
                                    @php
                                        $subId = $col['id'];
                                        $subName = $col['name'] ?? '';
                                        $isSubTotal = in_array(strtolower(trim($subName)), ['jumlah', 'total', 'jml', 'jmlh']) || ($col['is_total'] ?? false);
                                        $hasOverride = isset($footerOverrides[$subId]) && $footerOverrides[$subId] !== '';
                                        $overrideVal = $hasOverride ? $footerOverrides[$subId] : null;

                                        $yrNum = null;
                                        $colYearId = $selectedTahunId ?? ($tahun->id ?? null);
                                        if (preg_match('/^(19|20)\d{2}$/', trim($subName), $mYr)) {
                                            $yrNum = (int) $mYr[0];
                                        } else {
                                            foreach (array_reverse($col['ancestors'] ?? []) as $anc) {
                                                if (preg_match('/^(19|20)\d{2}$/', trim($anc['name'] ?? ''), $mYr)) {
                                                    $yrNum = (int) $mYr[0];
                                                    break;
                                                }
                                            }
                                        }
                                        if ($yrNum) {
                                            $thColModel = \App\Models\Tahun::where('tahun', $yrNum)->first();
                                            if ($thColModel) {
                                                $colYearId = $thColModel->id;
                                            }
                                        }

                                        $subSum = 0;
                                        $hasSubSum = false;
                                        foreach ($flattenedIndicators as $indRow) {
                                            if ($indRow->parent_id !== null) continue;
                                            $cVal = $getLeafValueForSubCol($indRow, $subId, $colYearId, $yrNum);
                                            if ($cVal !== null) {
                                                $subSum += $cVal;
                                                $hasSubSum = true;
                                            }
                                        }
                                        $subTotalVal = $hasOverride ? $overrideVal : ($hasSubSum ? (floor($subSum) == $subSum ? (string)(int)$subSum : (string)round($subSum, 2)) : '0');
                                        $leafSums[$subId] = $hasOverride && is_numeric($overrideVal) ? (float)$overrideVal : ($hasSubSum ? $subSum : 0);
                                    @endphp
                                    <td class="text-center py-2 px-2 footer-col-total {{ $isSubTotal ? 'bg-primary bg-opacity-10 text-primary fw-bold' : 'text-slate-800' }}"
                                        data-col-id="{{ $subId }}"
                                        data-parent-id="{{ $col['parent_id'] ?? '' }}"
                                        data-has-override="{{ $hasOverride ? 'true' : 'false' }}"
                                        data-align="center"
                                        ondblclick="editFooterCell(this)"
                                        title="Jumlah total kolom {{ $subName }}. Klik ganda (double click) untuk ubah manual.">
                                        <span class="footer-total-val font-monospace fw-bold" style="font-size: 0.84rem;">
                                            {{ $subTotalVal }}
                                        </span>
                                    </td>
                                @else
                                    @php
                                        $colId = $col['id'];
                                        $lowerName = strtolower(trim($col['name']));
                                        $isNo = in_array($lowerName, ['no', 'no.', 'nomor']);
                                        $isDesc = ($colId === 'col_nama' || $colId === 'nama_indikator' || in_array($lowerName, ['nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian']) || ($firstDescriptorColId && $colId === $firstDescriptorColId));
                                        $isNumeric = ($colId === 'col_nilai' || $lowerName === 'nilai' || ($col['type'] ?? 'text') === 'number' || in_array($lowerName, ['jumlah', 'total', 'jml', 'jmlh']));
                                        $hasOverride = isset($footerOverrides[$colId]) && $footerOverrides[$colId] !== '';
                                        $overrideVal = $hasOverride ? $footerOverrides[$colId] : null;
                                    @endphp
                                    @if($isNo)
                                        <td class="text-center py-2 px-2 bg-slate-100 text-muted">—</td>
                                    @elseif($isDesc)
                                        <td class="text-start ps-3 py-2 px-2 bg-slate-100 text-slate-900 footer-label-cell"
                                            data-row="row1"
                                            ondblclick="editFooterLabel(this, 'row1')"
                                            title="Klik ganda (double click) untuk ubah nama label (misal: Kota / Jumlah)"
                                            style="font-size: 0.84rem; cursor: pointer;">
                                            <strong class="text-uppercase footer-label-text" style="letter-spacing: 0.4px;">{{ $actualFooterLabel }}</strong>
                                        </td>
                                    @elseif($isNumeric)
                                        @php
                                            $colSum = 0;
                                            $hasColSum = false;
                                            foreach ($flattenedIndicators as $indRow) {
                                                if ($indRow->parent_id !== null) continue;
                                                $cVal = $getLeafValueForCol($indRow, $colId, $selectedTahunId ?? ($tahun->id ?? null), null, false);
                                                if ($cVal !== null) {
                                                    $colSum += $cVal;
                                                    $hasColSum = true;
                                                }
                                            }
                                            $colTotalVal = $hasOverride ? $overrideVal : ($hasColSum ? (floor($colSum) == $colSum ? (string)(int)$colSum : (string)round($colSum, 2)) : '0');
                                            $leafSums[$colId] = $hasOverride && is_numeric($overrideVal) ? (float)$overrideVal : ($hasColSum ? $colSum : 0);
                                        @endphp
                                        <td class="text-center py-2 px-2 footer-col-total text-slate-800 font-monospace fw-bold"
                                            data-col-id="{{ $colId }}"
                                            data-has-override="{{ $hasOverride ? 'true' : 'false' }}"
                                            data-align="center"
                                            ondblclick="editFooterCell(this)"
                                            title="Jumlah total kolom {{ $col['name'] }}. Klik ganda (double click) untuk ubah manual."
                                            style="font-size: 0.84rem;">
                                            <span class="footer-total-val font-monospace fw-bold">
                                                {{ $colTotalVal }}
                                            </span>
                                        </td>
                                    @else
                                        <td class="text-center py-2 px-2 bg-slate-100 text-muted opacity-40">—</td>
                                    @endif
                                @endif
                            @endforeach
                            @if($canEdit)
                                <td class="text-center py-2 px-1 sticky-action-col bg-slate-100 text-muted opacity-40" style="width: 78px; min-width: 78px; max-width: 78px;">—</td>
                            @endif
                        </tr>

                        @if($hasGroupWithSubs)
                            <!-- Baris 2: Total Gabungan Sub-Kolom (Grup Parent) -->
                            <tr id="tableFooterRowSubtotal" class="fw-bold footer-subtotal-row" style="{{ $showFooterSubtotal ? '' : 'display: none;' }} background-color: #e2e8f0; border-bottom: 2px solid #64748b !important;">
                                @foreach($footerParentGroups as $g)
                                    @if($g['type'] === 'leaf')
                                        @php
                                            $colId = $g['id'];
                                            $lowerName = strtolower(trim($g['name'] ?? ''));
                                            $isNo = in_array($lowerName, ['no', 'no.', 'nomor']);
                                            $isDesc = ($colId === 'col_nama' || $colId === 'nama_indikator' || in_array($lowerName, ['nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian']) || ($firstDescriptorColId && $colId === $firstDescriptorColId));
                                        @endphp
                                        @if($isNo)
                                            <td class="text-center py-2 px-2 bg-slate-200 text-muted opacity-50">—</td>
                                        @elseif($isDesc)
                                            <td class="text-start ps-3 py-2 px-2 bg-slate-200 text-slate-900 footer-label-cell"
                                                data-row="row2"
                                                ondblclick="editFooterLabel(this, 'row2')"
                                                title="Klik ganda (double click) untuk ubah nama label (misal: Tasikmalaya / Total)"
                                                style="font-size: 0.84rem; cursor: pointer;">
                                                <strong class="text-uppercase footer-label-text" style="letter-spacing: 0.4px;">{{ $footerSubtotalLabel }}</strong>
                                            </td>
                                        @else
                                            <td class="text-center py-2 px-2 bg-slate-200 text-muted opacity-40">—</td>
                                        @endif
                                    @else
                                        @php
                                            $pId = $g['id'];
                                            $pColspan = $g['colspan'];
                                            $pName = $g['name'];
                                            $pKey = 'subtotal_' . $pId;
                                            $hasPOverride = isset($footerOverrides[$pKey]) && $footerOverrides[$pKey] !== '';
                                            $pOverrideVal = $hasPOverride ? $footerOverrides[$pKey] : null;

                                            $groupSum = 0;
                                            $hasGroupVal = false;
                                            foreach ($g['leaf_ids'] as $lId) {
                                                if (isset($leafSums[$lId])) {
                                                    $groupSum += $leafSums[$lId];
                                                    $hasGroupVal = true;
                                                }
                                            }
                                            $groupTotalVal = $hasPOverride ? $pOverrideVal : ($hasGroupVal ? (floor($groupSum) == $groupSum ? (string)(int)$groupSum : (string)round($groupSum, 2)) : '0');
                                        @endphp
                                        <td colspan="{{ $pColspan }}"
                                            class="text-center py-2 px-2 footer-subtotal-cell bg-primary bg-opacity-10 text-primary fw-bold"
                                            data-parent-id="{{ $pId }}"
                                            data-leaf-ids="{{ json_encode($g['leaf_ids']) }}"
                                            data-has-override="{{ $hasPOverride ? 'true' : 'false' }}"
                                            ondblclick="editFooterSubtotalCell(this)"
                                            title="Total gabungan sub-kolom ({{ implode(' + ', $g['leaf_names']) }}) untuk {{ $pName }}. Klik ganda untuk ubah manual."
                                            style="border-left: 1px solid #cbd5e1 !important; border-right: 1px solid #cbd5e1 !important; cursor: pointer;">
                                            <span class="footer-subtotal-val font-monospace fw-bold" style="font-size: 0.86rem;">
                                                {{ $groupTotalVal }}
                                            </span>
                                        </td>
                                    @endif
                                @endforeach
                                @if($canEdit)
                                    <td class="text-center py-2 px-1 sticky-action-col bg-slate-200 text-muted opacity-40" style="width: 78px; min-width: 78px; max-width: 78px;">—</td>
                                @endif
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    </main>
</div>
</div>

<!-- ==================== MODAL: IMPORT EXCEL ==================== -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 620px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('urusan.excel.import', $urusan->slug ?: $urusan->id) }}" enctype="multipart/form-data" id="formImportExcel" onsubmit="handleImportSubmit(event)">
                @csrf
                <input type="hidden" name="table_id" value="{{ $activeTableId }}">

                <!-- Header Hijau Elegan (Excel Branding) -->
                <div class="modal-header text-white py-3 px-4 border-0" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-3 p-2 bg-white bg-opacity-20 d-flex align-items-center justify-content-center shadow-xs">
                            <i class="bi bi-file-earmark-spreadsheet-fill fs-5 text-white"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalImportExcelLabel">
                                Import Data Excel
                            </h6>
                            <small class="text-white-50" style="font-size: 0.75rem;">
                                Unggah file Excel (.xlsx / .xls / .csv) untuk mengisi data secara otomatis
                            </small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Template Download Callout -->
                    <div class="p-3 mb-3.5 rounded-3 border d-flex align-items-center justify-content-between gap-3" style="background-color: #f0fdf4; border-color: #bbf7d0 !important;">
                        <div class="d-flex align-items-center gap-2.5">
                            <i class="bi bi-info-circle-fill text-success fs-5 flex-shrink-0"></i>
                            <div>
                                <div class="fw-semibold text-dark small" style="font-size: 0.8rem;">Gunakan Template Sesuai Tabel Ini</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Unduh template Excel dengan struktur kolom tabel aktif agar data terbaca dengan sempurna.</div>
                            </div>
                        </div>
                        <a href="{{ route('urusan.excel.template', ['id' => $urusan->slug ?: $urusan->id, 'tabel' => $activeTableId, 'tahun_awal' => $tahunAwal, 'tahun_akhir' => $tahunAkhir]) }}" data-no-spa="true" download class="btn btn-sm btn-success text-nowrap fw-semibold px-3 py-1.5 rounded-2 d-inline-flex align-items-center gap-1.5 shadow-xs flex-shrink-0" style="font-size: 0.76rem;">
                            <i class="bi bi-download"></i>
                            <span>Unduh Template</span>
                        </a>
                    </div>

                    <!-- File Dropzone Area -->
                    <div class="mb-3.5">
                        <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-1.5">
                            <span><i class="bi bi-cloud-arrow-up text-success me-1"></i> Pilih File Excel / CSV <span class="text-danger">*</span></span>
                            <span class="badge bg-light text-muted border fw-normal" style="font-size: 0.7rem;">Maks 10 MB</span>
                        </label>
                        <div class="border border-2 border-dashed rounded-3 p-4 text-center cursor-pointer position-relative transition-all" id="dropzoneExcel" style="background-color: #fafafa; border-color: #cbd5e1 !important;" onclick="document.getElementById('fileInputExcel').click()">
                            <input type="file" name="file" id="fileInputExcel" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept=".xlsx, .xls, .csv" required onchange="handleExcelFileSelect(this)">
                            <div id="dropzoneDefaultState">
                                <i class="bi bi-file-earmark-excel-fill text-success d-block mb-2" style="font-size: 2.2rem;"></i>
                                <div class="fw-semibold text-dark small mb-1">Klik untuk memilih file atau seret & lepas ke sini</div>
                                <div class="text-muted" style="font-size: 0.73rem;">Mendukung format .xlsx, .xls, dan .csv</div>
                            </div>
                            <div id="dropzoneSelectedState" style="display: none;">
                                <div class="d-inline-flex align-items-center gap-2 p-2 px-3 rounded-2 bg-success bg-opacity-10 border border-success border-opacity-25">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <div class="text-start">
                                        <div class="fw-bold text-dark small" id="excelFileName">nama_file.xlsx</div>
                                        <div class="text-muted" id="excelFileSize" style="font-size: 0.7rem;">0 KB</div>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-link text-danger p-0 ms-2" onclick="event.stopPropagation(); resetExcelFile();" title="Hapus file">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="card bg-light border-0 rounded-3 p-3">
                        <div class="fw-bold text-dark small mb-2" style="font-size: 0.76rem;">Opsi Import Data:</div>
                        <div class="form-check mb-1.5">
                            <input class="form-check-input" type="checkbox" name="update_existing" id="optUpdateExisting" value="1" checked>
                            <label class="form-check-label small text-dark" for="optUpdateExisting" style="font-size: 0.75rem;">
                                Perbarui nilai jika baris indikator sudah ada di tabel ini
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="create_missing" id="optCreateMissing" value="1" checked>
                            <label class="form-check-label small text-dark" for="optCreateMissing" style="font-size: 0.75rem;">
                                Tambahkan baris indikator baru secara otomatis jika belum terdaftar
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-2 fw-medium" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold px-4 rounded-2 shadow-xs d-flex align-items-center gap-1.5" id="btnSubmitImport">
                        <i class="bi bi-upload"></i>
                        <span>Mulai Import Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: ATUR TABEL ==================== -->
<div class="modal fade" id="modalAturTabel" tabindex="-1" aria-labelledby="modalAturTabelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}" id="formAturTabel">
                @csrf
                <input type="hidden" name="action" value="setup_table" id="actionFieldAturTabel">
                <input type="hidden" name="table_id" value="{{ $activeTableId }}">

                <!-- Header Warna Biru Muda -->
                <div class="modal-header text-white py-3 px-4 border-0" style="background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 p-1.5 bg-white bg-opacity-20 d-flex align-items-center justify-content-center">
                            <i class="bi bi-table fs-5 text-white"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalAturTabelLabel">
                                @php
                                    $tName = trim($activeTable['name'] ?? '');
                                    $isGenericTable = empty($tName) || preg_match('/^Tabel\s*\d*$/i', $tName);
                                @endphp
                                @if($isGenericTable)
                                    Atur Tabel ({{ $urusan->formatted_nama }})
                                @else
                                    Atur Tabel {{ $tName }} ({{ $urusan->formatted_nama }})
                                @endif
                            </h6>
                            <small class="text-white-50" style="font-size: 0.75rem;">Tentukan jumlah kolom dan jumlah baris untuk halaman tabel ini</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Form Kontrol: Kolom & Baris -->
                    <div class="card bg-light border p-3.5 mb-3 rounded-3 shadow-none">
                        <div class="row g-3">
                            <!-- 1. Mau Berapa Kolom? -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-1.5">
                                    <span><i class="bi bi-layout-three-columns text-secondary me-1"></i> Mau Berapa Kolom?</span>
                                    <span class="badge bg-white text-dark border fw-semibold" id="badgeNumCols">0 Kolom</span>
                                </label>
                                <input type="number" name="num_cols" id="inputNumCols" class="form-control form-control-md fw-bold text-dark font-monospace shadow-xs border" min="0" max="20" value="{{ count($allColumns) > 0 ? count($allColumns) : 3 }}" required oninput="renderColInputs()">
                                <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Jumlah kolom data tabel.</small>
                            </div>

                            <!-- 2. Mau Berapa Baris? -->
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-1.5">
                                    <span><i class="bi bi-list-ol text-secondary me-1"></i> Mau Berapa Baris?</span>
                                    <span class="badge bg-white text-dark border fw-semibold" id="badgeNumRows">0 Baris</span>
                                </label>
                                <input type="number" name="num_rows" id="inputNumRows" class="form-control form-control-md fw-bold text-dark font-monospace shadow-xs border" min="0" max="100" value="{{ count($flattenedIndicators) > 0 ? count($flattenedIndicators) : 5 }}" required oninput="updateRowBadge()">
                                <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Jumlah baris awal data.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Label Nama Header Kolom -->
                    <div class="mb-2">
                        <span class="small fw-bold text-dark">
                            <i class="bi bi-pencil-square text-secondary me-1"></i> Nama Header Setiap Kolom:
                        </span>
                    </div>

                    <!-- Container Input Nama Kolom -->
                    <div id="colInputsContainer" class="row g-2 mb-3 px-1 py-1" style="max-height: 240px; overflow-y: auto;">
                        <!-- Generated dynamically by renderColInputs() -->
                    </div>

                    <!-- Opsi Baris Jumlah / Total di Bawah -->
                    <div class="card bg-white border p-3 mb-3 rounded-3 shadow-none">
                        <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                            <div>
                                <label class="form-check-label fw-bold text-dark mb-0 d-block" for="checkShowFooterTotal" style="font-size: 0.82rem; cursor: pointer;">
                                    <i class="bi bi-calculator text-primary me-1"></i> Aktifkan Baris Bawah Tabel (Kalkulasi)
                                </label>
                                <small class="text-muted" style="font-size: 0.72rem;">Menampilkan baris Jumlah atau Total nilai data di baris paling bawah tabel.</small>
                            </div>
                            <div class="form-check form-switch m-0 p-0">
                                <input class="form-check-input ms-3 fs-5" type="checkbox" name="show_footer_total" id="checkShowFooterTotal" value="1" {{ $showFooterTotal ? 'checked' : '' }} onchange="onModalFooterTotalToggle(this.checked)" style="cursor: pointer;">
                            </div>
                        </div>

                        <!-- Sub-opsi: Pilihan Munculkan Jumlah Saja, Total Saja, atau Keduanya -->
                        <div id="modalFooterSubOptions" style="{{ $showFooterTotal ? '' : 'display: none;' }}">
                            <div class="mb-2">
                                <div class="form-check form-switch d-flex align-items-center justify-content-between p-0 m-0">
                                    <div>
                                        <label class="form-check-label fw-semibold text-dark mb-0 d-block" for="checkShowFooterJumlah" style="font-size: 0.80rem; cursor: pointer;">
                                            <i class="bi bi-calculator text-success me-1"></i> Tampilkan Baris JUMLAH (Per Kolom / Sub-Kolom)
                                        </label>
                                        <small class="text-muted" style="font-size: 0.70rem;">Menghitung rincian jumlah pada masing-masing kolom/sub-kolom.</small>
                                    </div>
                                    <input class="form-check-input ms-3 fs-6" type="checkbox" name="show_footer_jumlah" id="checkShowFooterJumlah" value="1" {{ $showFooterJumlah ? 'checked' : '' }} onchange="onModalFooterSubOptionsChange()" style="cursor: pointer;">
                                </div>
                            </div>

                            <div class="pt-2 border-top">
                                <div class="form-check form-switch d-flex align-items-center justify-content-between p-0 m-0">
                                    <div>
                                        <label class="form-check-label fw-semibold text-dark mb-0 d-block" for="checkShowFooterSubtotal" style="font-size: 0.80rem; cursor: pointer;">
                                            <i class="bi bi-sigma text-info me-1"></i> Tampilkan Baris TOTAL (Gabungan Sub-Kolom)
                                        </label>
                                        <small class="text-muted" style="font-size: 0.70rem;">Menghitung total gabungan seluruh sub-kolom (misal Total Kolam + Sawah).</small>
                                    </div>
                                    <input class="form-check-input ms-3 fs-6" type="checkbox" name="show_footer_subtotal" id="checkShowFooterSubtotal" value="1" {{ $showFooterSubtotal ? 'checked' : '' }} onchange="onModalFooterSubOptionsChange()" style="cursor: pointer;">
                                </div>
                            </div>

                            <!-- Pilihan Preset Cepat -->
                            <div class="mt-2.5 pt-2 border-top d-flex align-items-center justify-content-between">
                                <span class="text-muted small" style="font-size: 0.72rem; font-weight: 600;">Pilihan Cepat:</span>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary py-0.5 px-2" style="font-size: 0.70rem;" onclick="setModalFooterPreset('both')">Jumlah & Total</button>
                                    <button type="button" class="btn btn-outline-secondary py-0.5 px-2" style="font-size: 0.70rem;" onclick="setModalFooterPreset('jumlah')">Jumlah Saja</button>
                                    <button type="button" class="btn btn-outline-secondary py-0.5 px-2" style="font-size: 0.70rem;" onclick="setModalFooterPreset('total')">Total Saja</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan Info Sederhana & Bersih -->
                    <div class="d-flex align-items-center gap-2 p-2.5 rounded-3 bg-light border text-muted small mb-0" style="font-size: 0.78rem;">
                        <i class="bi bi-info-circle text-secondary flex-shrink-0 fs-6"></i>
                        <div>Setelah klik <strong>Terapkan & Tampilkan Tabel</strong>, susunan kolom dan baris akan langsung diperbarui. Anda dapat klik langsung di sel tabel untuk mengisi nilai data.</div>
                    </div>
                </div>

                <div class="modal-footer bg-light d-flex justify-content-between py-2 px-4 border-top">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearAllTable()" title="Kosongkan seluruh kolom">
                        <i class="bi bi-trash3 me-1"></i> Kosongkan Semua Kolom
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm fw-bold px-4 text-white shadow-xs border-0" style="background: #0284c7;">
                            <i class="bi bi-check2-circle me-1"></i> Terapkan & Tampilkan Tabel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: TAMBAH HALAMAN (TABEL BARU) ==================== -->
<div class="modal fade" id="modalTambahHalaman" tabindex="-1" aria-labelledby="modalTambahHalamanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('urusan.table.store', $urusan->slug ?: $urusan->id) }}" id="formTambahHalaman">
                @csrf
                <div class="modal-header bg-primary text-white py-3 px-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-folder-plus fs-5"></i>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalTambahHalamanLabel">Tambah Halaman Tabel Baru</h6>
                            <small class="text-white-50" style="font-size: 0.75rem;">Buat halaman / tab tabel baru untuk urusan ini (misal: Tahun 2026)</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            Nama Halaman / Tab <span class="text-danger">*</span>
                        </label>
                        @php
                            $suggestedNewTabName = 'Tahun ' . (date('Y') + 1);
                            if (preg_match('/(19|20)\d{2}/', $activeTable['name'] ?? '', $mTabYr)) {
                                $suggestedNewTabName = 'Tahun ' . (((int)$mTabYr[0]) + 1);
                            }
                            $defaultNewTabYear = date('Y') + 1;
                            if (preg_match('/(19|20)\d{2}/', $suggestedNewTabName, $mSuggYr)) {
                                $defaultNewTabYear = (int)$mSuggYr[0];
                            }
                        @endphp
                        <input type="text" name="nama_tabel" id="inputNamaHalamanBaru" class="form-control form-control-md fw-bold text-dark shadow-xs" placeholder="Contoh: Tahun 2026, Profil 2026..." value="{{ $suggestedNewTabName }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            Tahun Data untuk Halaman Ini
                        </label>
                        <input type="number" name="tahun" id="inputTambahHalamanTahun" class="form-control form-control-sm text-dark shadow-xs" placeholder="Contoh: 2026" value="{{ $defaultNewTabYear }}" min="2000" max="2100">
                        <small class="text-muted" style="font-size: 0.72rem;">Setiap baris data yang disalin akan dikaitkan dengan tahun ini.</small>
                    </div>

                    <!-- Pilihan Cerdas: Salin Struktur & Isi Data dari Halaman yang Ada -->
                    <div class="card bg-light border p-3 rounded-3 mb-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="duplicate_structure" id="checkDuplicateStructure" value="1" checked onchange="toggleDuplicateOptions(this.checked)">
                            <label class="form-check-label fw-bold text-dark small" for="checkDuplicateStructure">
                                <i class="bi bi-copy text-primary me-1"></i> Salin Kolom, Daftar Indikator & Isi Data
                            </label>
                        </div>
                        <div id="duplicateSourceSelectContainer">
                            <label class="form-label text-muted small mb-1" style="font-size: 0.74rem;">Salin dari halaman:</label>
                            <select name="source_table_id" id="selectSourceTable" class="form-select form-select-sm shadow-xs fw-semibold">
                                @foreach($tables as $tbl)
                                    <option value="{{ $tbl['id'] }}" {{ $activeTableId == ($tbl['id'] ?? 1) ? 'selected' : '' }}>
                                        {{ $tbl['name'] }} ({{ $tbl['row_count'] ?? 0 }} baris indikator)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                                <i class="bi bi-info-circle text-primary me-1"></i>
                                Semua kolom (termasuk sub-kolom), susunan baris, serta seluruh isi data dari tahun sebelumnya akan disalin otomatis sehingga Anda tinggal mengedit/mengubah yang diperlukan tanpa mulai dari kosong.
                            </small>
                        </div>
                    </div>

                    <div id="customTableBlankContainer" style="display: none;">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark mb-1">Jumlah Kolom</label>
                                <input type="number" name="num_cols" class="form-control form-control-sm" value="3" min="1" max="20">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-dark mb-1">Jumlah Baris Awal</label>
                                <input type="number" name="num_rows" class="form-control form-control-sm" value="5" min="1" max="50">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-xs d-flex align-items-center gap-1.5">
                        <i class="bi bi-check-lg"></i>
                        <span>Buat Halaman Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: UBAH NAMA HALAMAN ==================== -->
<div class="modal fade" id="modalRenameTable" tabindex="-1" aria-labelledby="modalRenameTableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}" id="formRenameTable">
                @csrf
                <input type="hidden" name="action" value="rename_table">
                <input type="hidden" name="table_id" id="renameTableId" value="{{ $activeTableId }}">

                <div class="modal-header bg-primary text-white py-3 px-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square fs-5"></i>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalRenameTableLabel">Ubah Nama Halaman</h6>
                            <small class="text-white-50" style="font-size: 0.75rem;">Ganti nama label tab halaman ini</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Nama Halaman</label>
                        <input type="text" name="nama_tabel" id="inputRenameTableName" class="form-control form-control-md fw-bold text-dark shadow-xs" required autofocus>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark mb-1">Tahun Terkait Halaman</label>
                        <input type="text" name="tahun" id="inputRenameTableTahun" class="form-control form-control-sm text-dark shadow-xs" placeholder="Contoh: 2025 atau 2023 - 2025">
                        <small class="text-muted" style="font-size: 0.72rem;">Tahun atau rentang tahun terkait data halaman ini (misal: 2025 atau 2023 - 2025).</small>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-xs">
                        <i class="bi bi-check-lg me-1"></i> Simpan Nama & Tahun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: TAMBAH KOLOM ==================== -->
@php
    $lastCol = count($allColumns) > 0 ? end($allColumns) : null;
    $suggestedNextYear = null;
    $suggestedColName = 'Kolom ' . (count($allColumns) + 1);
    if ($lastCol && is_numeric(trim($lastCol['name'] ?? ''))) {
        $suggestedNextYear = (int) trim($lastCol['name']) + 1;
        $suggestedColName = (string) $suggestedNextYear;
    }
@endphp
<div class="modal fade" id="modalTambahKolom" tabindex="-1" aria-labelledby="modalTambahKolomLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}" id="formTambahKolom">
                @csrf
                <input type="hidden" name="action" value="add_column">
                <input type="hidden" name="table_id" value="{{ $activeTableId }}">

                <div class="modal-header bg-primary text-white py-3 px-4 border-0">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-layout-three-columns fs-5"></i>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalTambahKolomLabel">Tambah Kolom Baru</h6>
                            <small class="text-white-50" style="font-size: 0.75rem;">Tambahkan kolom pada {{ !empty($activeTable['name']) && !preg_match('/^Tabel\s*\d*$/i', trim($activeTable['name'])) ? $activeTable['name'] : 'tabel ini' }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-1.5">
                            <span>Nama Kolom <span class="text-danger">*</span></span>
                            <span class="text-muted small fw-normal" style="font-size: 0.72rem;">Header kolom tabel</span>
                        </label>
                        <input type="text" name="name" id="inputNamaKolomBaru" class="form-control form-control-md fw-bold text-dark shadow-xs" placeholder="Contoh: 2027, Target, Realisasi..." value="{{ $suggestedColName }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted fw-semibold mb-1.5" style="font-size: 0.72rem;">
                            <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Saran Cepat (Klik untuk memilih):
                        </div>
                        <div class="d-flex flex-wrap gap-1.5" id="colSuggestions">
                            @if($suggestedNextYear)
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" onclick="applyColSuggestion('{{ $suggestedNextYear }}', 'number')">
                                    <i class="bi bi-calendar-plus me-1"></i> {{ $suggestedNextYear }}
                                </button>
                            @endif
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="applyColSuggestion('Target', 'number')">Target</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="applyColSuggestion('Realisasi', 'number')">Realisasi</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="applyColSuggestion('Capaian (%)', 'percentage')">Capaian (%)</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="applyColSuggestion('Keterangan', 'text')">Keterangan</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1" onclick="applyColSuggestion('Sumber Data', 'text')">Sumber Data</button>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold text-dark mb-1">Tipe Data Kolom</label>
                        <select name="type" id="inputTipeKolomBaru" class="form-select form-select-md shadow-xs">
                            <option value="text" {{ $suggestedNextYear ? '' : 'selected' }}>Teks / Tulisan</option>
                            <option value="number" {{ $suggestedNextYear ? 'selected' : '' }}>Angka / Nilai (Numerik)</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Pilih jenis format data yang akan diisi pada kolom ini.</small>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-xs d-flex align-items-center gap-1.5">
                        <i class="bi bi-plus-lg"></i>
                        <span>Tambah Kolom</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: EDIT NAMA / TIPE KOLOM ==================== -->
<div class="modal fade" id="modalEditKolom" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}" id="formEditKolom">
                @csrf
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="table_id" value="{{ $activeTableId }}">
                <input type="hidden" name="column_id" id="editColId">
                <div class="modal-header bg-slate-900 text-white py-2">
                    <h6 class="modal-title small fw-bold"><i class="bi bi-pencil me-1"></i> Edit Kolom</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Kolom <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editColName" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Tipe Data Kolom</label>
                        <select name="type" id="editColType" class="form-select form-select-sm">
                            <option value="text">Teks / Tulisan</option>
                            <option value="number">Angka / Numerik</option>
                            <option value="percentage">Persentase (%)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: ATUR SUB-KOLOM (BARIS DI DALAM KOLOM) ==================== -->
<div class="modal fade" id="modalSubKolom" tabindex="-1" aria-labelledby="modalSubKolomLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}" id="formSubKolom">
                @csrf
                <input type="hidden" name="action" value="set_sub_columns">
                <input type="hidden" name="table_id" value="{{ $activeTableId }}">
                <input type="hidden" name="parent_col_id" id="subModalParentColId" value="">

                <div class="modal-header text-white py-3 px-4 border-0" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 p-1.5 bg-white bg-opacity-20 d-flex align-items-center justify-content-center">
                            <i class="bi bi-diagram-3-fill fs-5 text-white"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalSubKolomLabel">Atur Sub-Kolom</h6>
                            <small class="text-white-50" style="font-size: 0.75rem;">Tambahkan baris sub-kolom di bawah kolom: <strong id="subModalColName" class="text-white text-decoration-underline"></strong></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Rekomendasi Sesuai Tabel & Kolom Aktif -->
                    <div id="subColRecommendationBox" class="mb-3 p-3 rounded-3 border" style="background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%); border-color: #86efac !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-success-emphasis d-flex align-items-center gap-1.5">
                                <i class="bi bi-stars text-warning fs-6"></i>
                                <span>Rekomendasi Sesuai Tabel Ini:</span>
                            </span>
                            <span class="badge bg-success text-white px-2 py-0.5 rounded-pill" id="subRecBadgeText" style="font-size: 0.7rem;">Target & Realisasi</span>
                        </div>
                        <div id="subRecButtonsContainer" class="d-flex flex-column gap-1.5">
                            <!-- Tombol rekomendasi diinjeksi secara dinamis oleh JavaScript sesuai nama tabel dan kolom -->
                        </div>
                    </div>

                    <!-- Pilihan Preset Lengkap Lainnya (Accordion / Dropdown) -->
                    <div class="mb-3">
                        <button class="btn btn-sm btn-link text-decoration-none text-muted p-0 d-inline-flex align-items-center gap-1 small" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAllPresets" aria-expanded="false" style="font-size: 0.78rem;">
                            <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                            <span class="fw-semibold text-secondary">Lihat Pilihan Preset Lainnya (Pendidikan, Kesehatan, Fisik, dll)</span>
                            <i class="bi bi-chevron-down ms-0.5" style="font-size: 0.7rem;"></i>
                        </button>
                        <div class="collapse mt-2" id="collapseAllPresets">
                            <div class="p-2.5 bg-light rounded-3 border d-flex flex-column gap-2.5" style="max-height: 220px; overflow-y: auto;">
                                <!-- Kinerja, Target & Tahun -->
                                <div>
                                    <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Target, Realisasi & Tahun</span>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['Target', 'Realisasi', 'Capaian (%)'])">
                                            <span class="small fw-semibold"><i class="bi bi-bullseye me-1 text-success"></i> Target & Realisasi</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Target, Realisasi, Capaian (%)</small>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" id="btnPresetTahunAktif" onclick="applySubPreset(['2023', '2024', '2025', '2026', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-calendar3 me-1 text-primary"></i> Tahun-Tahun Aktif + Jumlah</span>
                                            <small class="text-muted" id="descPresetTahunAktif" style="font-size: 0.7rem;">2023, 2024, 2025, 2026, Jumlah</small>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['Fisik (%)', 'Keuangan (Rp)', 'Capaian (%)'])">
                                            <span class="small fw-semibold"><i class="bi bi-cash-stack me-1 text-warning"></i> Fisik & Keuangan</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Fisik (%), Keuangan (Rp), Capaian (%)</small>
                                        </button>
                                    </div>
                                </div>
                                <!-- Pendidikan & Kepegawaian -->
                                <div>
                                    <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Pendidikan & Kepegawaian</span>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['Negeri', 'Swasta', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-mortarboard-fill me-1 text-warning"></i> Status Sekolah (Negeri/Swasta)</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Negeri, Swasta, Jumlah</small>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['PNS', 'PPPK', 'Non-PNS', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-person-badge-fill me-1 text-secondary"></i> Status ASN / Pegawai</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">PNS, PPPK, Non-PNS, Jumlah</small>
                                        </button>
                                    </div>
                                </div>
                                <!-- Kependudukan & Gender -->
                                <div>
                                    <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Kependudukan & Gender</span>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['Laki-laki', 'Perempuan', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-people-fill me-1 text-info"></i> Jenis Kelamin</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Laki-laki, Perempuan, Jumlah</small>
                                        </button>
                                    </div>
                                </div>
                                <!-- Kesehatan & Faskes -->
                                <div>
                                    <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Kesehatan & Faskes</span>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['Pemerintah', 'Swasta', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-hospital-fill me-1 text-danger"></i> Kepemilikan Faskes</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Pemerintah, Swasta, Jumlah</small>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['Rawat Inap', 'Rawat Jalan', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-heart-pulse-fill me-1 text-danger"></i> Layanan Faskes</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Rawat Inap, Rawat Jalan, Jumlah</small>
                                        </button>
                                    </div>
                                </div>
                                <!-- Sarpras, Gedung & Infrastruktur -->
                                <div>
                                    <span class="text-uppercase text-muted fw-bold d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.5px;">Sarpras, Gedung & Infrastruktur</span>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['OPD', 'Kecamatan', 'Kelurahan', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-building-fill me-1 text-primary"></i> Instansi Pemerintah</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">OPD, Kecamatan, Kelurahan, Jumlah</small>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['Rusak Berat', 'Rusak Sedang', 'Rusak Ringan', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-tools me-1 text-warning"></i> Kondisi Ruang / Gedung</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Rusak Berat, Rusak Sedang, Rusak Ringan, Jumlah</small>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['RB', 'RS', 'RR', 'Jmlh'])">
                                            <span class="small fw-semibold"><i class="bi bi-alphabet-uppercase me-1 text-secondary"></i> Kondisi (Singkatan)</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">RB, RS, RR, Jmlh</small>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1 px-2.5 rounded-2 bg-white" onclick="applySubPreset(['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat', 'Jumlah'])">
                                            <span class="small fw-semibold"><i class="bi bi-signpost-split-fill me-1 text-success"></i> Kemantapan Fisik / Jalan</span>
                                            <small class="text-muted" style="font-size: 0.7rem;">Baik, Sedang, Rusak Ringan, Rusak Berat, Jumlah</small>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input List Sub-Kolom -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <label class="form-label small fw-bold text-dark mb-0">
                                <span>Daftar Sub-Kolom: <span class="text-danger">*</span></span>
                            </label>
                            <div class="d-flex gap-1.5">
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" onclick="window.clearSubColumnInputs()" title="Kosongkan semua input sub-kolom agar bisa mengetik dari awal">
                                    <i class="bi bi-eraser me-1"></i> Kosongkan
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0.5 px-2" onclick="window.addSubColumnInput('')">
                                    <i class="bi bi-plus"></i> Tambah Sub-Kolom
                                </button>
                            </div>
                        </div>
                        <div id="subColumnInputsContainer" class="d-flex flex-column gap-2">
                            <!-- Dynamic inputs inserted by JS -->
                        </div>
                        <small class="text-muted d-block mt-1.5" style="font-size: 0.72rem;">
                            <i class="bi bi-info-circle text-primary me-1"></i> Gunakan tombol panah <i class="bi bi-chevron-up"></i> <i class="bi bi-chevron-down"></i> atau seret baris untuk memindahkan posisi sub-kolom (misal menggeser tahun ke atas Jumlah) tanpa perlu rename manual.
                        </small>
                    </div>

                    <!-- Pilihan Terapkan ke Semua Tahun -->
                    <div class="form-check p-2.5 bg-light rounded-2 border" id="boxApplyAllYears">
                        <input class="form-check-input ms-1 me-2" type="checkbox" name="apply_to_all_years" value="1" id="checkApplyAllYears" checked>
                        <label class="form-check-label small fw-bold text-dark" for="checkApplyAllYears">
                            Terapkan sub-kolom ini ke semua kolom tahun lainnya
                            <small class="d-block text-muted fw-normal" style="font-size: 0.72rem;">
                                Seluruh kolom tahun pada tabel ini akan otomatis memiliki sub-kolom yang sama.
                            </small>
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnHapusSemuaSub" onclick="clearAllSubColumns()" title="Hapus seluruh sub-kolom dan kembalikan ke kolom biasa" style="display: none;">
                            <i class="bi bi-trash3 me-1"></i> Hapus Sub-Kolom
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-xs">
                            <i class="bi bi-check-lg me-1"></i> Simpan Sub-Kolom
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: TAMBAH SUB-BARIS / RINCIAN DI KOLOM URAIAN ==================== -->
<div class="modal fade" id="modalSubBaris" tabindex="-1" aria-labelledby="modalSubBarisLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form method="POST" action="" id="formSubBaris">
                @csrf
                <input type="hidden" name="parent_id" id="subBarisParentId" value="">

                <div class="modal-header text-white py-3 px-4 border-0" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-2 p-1.5 bg-white bg-opacity-20 d-flex align-items-center justify-content-center">
                            <i class="bi bi-node-plus-fill fs-5 text-white"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white" id="modalSubBarisLabel">Tambah Sub-Baris (Rincian)</h6>
                            <small class="text-white-50" style="font-size: 0.75rem;">Di bawah baris induk: <strong id="subBarisParentName" class="text-white text-decoration-underline"></strong></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Preset Cepat Sub-Baris -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1 mb-1.5">
                            <i class="bi bi-lightning-charge-fill text-warning"></i>
                            <span>Pilihan Cepat / Preset (Klik untuk mengisi otomatis):</span>
                        </label>
                        <div class="d-flex flex-column gap-1.5">
                            <button type="button" class="btn btn-sm btn-outline-info text-start d-flex align-items-center justify-content-between py-1.5 px-3 rounded-2" onclick="applySubBarisPresetHealth()">
                                <span class="fw-semibold"><i class="bi bi-hospital me-1 text-info"></i> Pelayanan (Rawat Inap & Rawat Jalan)</span>
                                <small class="text-muted" style="font-size: 0.72rem;" id="presetHealthDesc">Rawat Inap, Rawat Jalan</small>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success text-start d-flex align-items-center justify-content-between py-1.5 px-3 rounded-2" onclick="applySubBarisPresetGov()">
                                <span class="fw-semibold"><i class="bi bi-building me-1 text-success"></i> Kepemilikan (Pemerintah & Swasta)</span>
                                <small class="text-muted" style="font-size: 0.72rem;">Pemerintah, Swasta</small>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary text-start d-flex align-items-center justify-content-between py-1.5 px-3 rounded-2" onclick="applySubBarisPresetSchool()">
                                <span class="fw-semibold"><i class="bi bi-mortarboard-fill me-1 text-primary"></i> Status Sekolah (Negeri & Swasta)</span>
                                <small class="text-muted" style="font-size: 0.72rem;" id="presetSchoolDesc">Negeri, Swasta</small>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary text-start d-flex align-items-center justify-content-between py-1.5 px-3 rounded-2" onclick="applySubBarisPreset(['Laki-laki', 'Perempuan'])">
                                <span class="fw-semibold"><i class="bi bi-people me-1 text-secondary"></i> Jenis Kelamin (Laki-laki & Perempuan)</span>
                                <small class="text-muted" style="font-size: 0.72rem;">Laki-laki, Perempuan</small>
                            </button>
                        </div>
                    </div>

                    <!-- Input List Sub-Baris -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between mb-1.5">
                            <span>Daftar Sub-Baris yang Akan Dibuat: <span class="text-danger">*</span></span>
                            <button type="button" class="btn btn-xs btn-outline-primary py-0.5 px-2" onclick="addSubBarisInput('')">
                                <i class="bi bi-plus"></i> Tambah Baris
                            </button>
                        </label>
                        <div id="subBarisInputsContainer" class="d-flex flex-column gap-2">
                            <!-- Dynamic inputs inserted by JS -->
                        </div>
                        <small class="text-muted d-block mt-1.5" style="font-size: 0.72rem;">
                            Sub-baris akan otomatis berindentasi di bawah baris induk pada tabel.
                        </small>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-xs">
                        <i class="bi bi-check-lg me-1"></i> Simpan Sub-Baris
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL 1: TAMBAH INDIKATOR UTAMA ==================== -->
<div class="modal fade" id="modalTambahUtama" tabindex="-1" aria-labelledby="modalTambahUtamaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('indikator.store') }}">
                @csrf
                <input type="hidden" name="urusan_id" value="{{ $urusan->id }}">
                <input type="hidden" name="parent_id" value="">

                <div class="modal-header bg-slate-900 text-white">
                    <h6 class="modal-title fw-bold" id="modalTambahUtamaLabel">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Indikator Utama
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Urusan Pemerintahan</label>
                        <input type="text" class="form-control bg-light" value="{{ $urusan->formatted_nama }}" readonly>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Kode Indikator <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="text" name="kode_indikator" class="form-control" placeholder="Contoh: PEND-01">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">No. Urut</label>
                            <input type="number" name="urutan" class="form-control" value="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Indikator <span class="text-danger">*</span></label>
                        <textarea name="nama_indikator" class="form-control" rows="2" placeholder="Nama indikator utama..." required></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Satuan</label>
                            <input type="text" name="satuan" class="form-control" placeholder="Unit, Orang, %, Km, dll">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tipe Data <span class="text-danger">*</span></label>
                            <select name="tipe_data" class="form-select" required>
                                <option value="numerik" selected>Numerik (Angka)</option>
                                <option value="persentase">Persentase (%)</option>
                                <option value="teks">Teks / Kualitatif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Deskripsi / Definisi Operasional</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Penjelasan atau definisi cara perhitungan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Indikator</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL 2: TAMBAH SUBINDIKATOR ==================== -->
<div class="modal fade" id="modalTambahSub" tabindex="-1" aria-labelledby="modalTambahSubLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('indikator.store') }}">
                @csrf
                <input type="hidden" name="urusan_id" value="{{ $urusan->id }}">
                <input type="hidden" name="parent_id" id="subParentId">

                <div class="modal-header bg-slate-900 text-white">
                    <h6 class="modal-title fw-bold" id="modalTambahSubLabel">
                        <i class="bi bi-diagram-2 me-1"></i> Tambah Subindikator Bercabang
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>
                            Induk (Parent): <strong id="subParentName">-</strong>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Kode Subindikator <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="text" name="kode_indikator" class="form-control" placeholder="Contoh: PEND-01.1">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">No. Urut</label>
                            <input type="number" name="urutan" class="form-control" value="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Subindikator <span class="text-danger">*</span></label>
                        <textarea name="nama_indikator" class="form-control" rows="2" placeholder="Nama subindikator cabang..." required></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Satuan</label>
                            <input type="text" name="satuan" class="form-control" placeholder="Unit, Orang, %, Km, dll">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tipe Data <span class="text-danger">*</span></label>
                            <select name="tipe_data" class="form-select" required>
                                <option value="numerik" selected>Numerik (Angka)</option>
                                <option value="persentase">Persentase (%)</option>
                                <option value="teks">Teks / Kualitatif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Keterangan rincian subindikator..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">Simpan Subindikator</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL 3: EDIT INDIKATOR ==================== -->
<div class="modal fade" id="modalEditIndikator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formEditIndikator" action="">
                @csrf
                @method('PUT')

                <div class="modal-header bg-slate-900 text-white">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-1"></i> Edit Pengaturan Indikator
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold">Kode Indikator</label>
                            <input type="text" name="kode_indikator" id="editKode" class="form-control">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold">No. Urut</label>
                            <input type="number" name="urutan" id="editUrutan" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Indikator <span class="text-danger">*</span></label>
                        <textarea name="nama_indikator" id="editNama" class="form-control" rows="2" required></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Satuan</label>
                            <input type="text" name="satuan" id="editSatuan" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Tipe Data</label>
                            <select name="tipe_data" id="editTipeData" class="form-select">
                                <option value="numerik">Numerik (Angka)</option>
                                <option value="persentase">Persentase (%)</option>
                                <option value="teks">Teks</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="status_aktif" value="1" id="editStatusAktif">
                        <label class="form-check-label small fw-semibold" for="editStatusAktif">Status Indikator Aktif</label>
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

<!-- ==================== MODAL 4: KELOLA NILAI TAHUNAN & RIWAYAT ==================== -->
<div class="modal fade" id="modalManageNilai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-slate-900 text-white">
                <div>
                    <h6 class="modal-title fw-bold mb-0">
                        <i class="bi bi-bar-chart-steps me-1"></i> Kelola Nilai Data & Riwayat Tahunan
                    </h6>
                    <small class="text-slate-400" id="manageModalSubtitle">-</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- FORM INPUT CEPAT NILAI UNTUK TAHUN TERPILIH -->
                <div class="card bg-light border-0 p-3 mb-4">
                    <h6 class="fw-bold text-dark small mb-3">
                        <i class="bi bi-pencil me-1 text-primary"></i> Input / Perbarui Nilai untuk Tahun <span id="quickTahunLabel" class="badge bg-primary"></span>
                    </h6>
                    <form method="POST" action="{{ route('data.single') }}" id="formSingleData">
                        @csrf
                        <input type="hidden" name="indikator_id" id="quickIndikatorId">
                        <input type="hidden" name="tahun_id" id="quickTahunId">

                        @php
                            $hasNilaiCol = collect($urusan->effective_columns)->contains(fn($c) => $c['id'] === 'col_nilai' || strtolower($c['name']) === 'nilai');
                            $hasSumberCol = collect($urusan->effective_columns)->contains(fn($c) => $c['id'] === 'col_sumber' || strtolower($c['name']) === 'sumber data');
                            $otherCols = array_filter($urusan->effective_columns, fn($c) => $c['id'] !== 'col_nilai' && strtolower($c['name']) !== 'nilai' && $c['id'] !== 'col_sumber' && strtolower($c['name']) !== 'sumber data');
                        @endphp

                        <div class="row g-2 mb-2">
                            @if($hasNilaiCol)
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Nilai Data</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="nilai" id="quickNilai" class="form-control font-monospace fw-bold" placeholder="Contoh: 85 atau 12.5">
                                        <span class="input-group-text bg-white small" id="quickSatuanLabel">-</span>
                                    </div>
                                </div>
                            @endif
                            @if($hasSumberCol)
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold">Sumber Data</label>
                                    <input type="text" name="sumber_data" id="quickSumber" class="form-control form-control-sm" placeholder="Contoh: Dinas Terkait">
                                </div>
                            @endif
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold">Keterangan (Opsional)</label>
                                <input type="text" name="keterangan" id="quickKeterangan" class="form-control form-control-sm" placeholder="Catatan tambahan...">
                            </div>
                        </div>

                        @if(count($otherCols) > 0)
                            <div class="row g-2 mb-3 pt-2 border-top">
                                <div class="col-12">
                                    <span class="small fw-bold text-muted d-block mb-1"><i class="bi bi-layout-three-columns text-primary me-1"></i> Kolom Data Kustom:</span>
                                </div>
                                @foreach($otherCols as $cc)
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small fw-semibold text-secondary">{{ $cc['name'] }}</label>
                                        <input type="text" name="custom_fields[{{ $cc['id'] }}]" id="quickCustom_{{ $cc['id'] }}" class="form-control form-control-sm quick-custom-input" data-col-id="{{ $cc['id'] }}" placeholder="{{ $cc['name'] }}...">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Nilai Tahun Ini
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TABEL RIWAYAT SEMUA TAHUN -->
                <h6 class="fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-secondary"></i>
                    <span>Riwayat Data Seluruh Tahun (Multi-Tahun)</span>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;" class="text-center">Tahun</th>
                                <th style="width: 120px;" class="text-end">Nilai</th>
                                <th>Sumber Data</th>
                                <th>Keterangan</th>
                                <th style="width: 140px;">Terakhir Diperbarui</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Memuat data riwayat...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.currentColumnsUrl = '{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}';
    window.currentTableId = '{{ $activeTableId }}';
    window.currentUrusanId = '{{ $urusan->slug ?: $urusan->id }}';

    function toggleInlineAddRow() {
        const row = document.getElementById('inlineAddRow');
        if (!row) return;
        if (row.style.display === 'none' || row.style.display === '') {
            row.style.display = 'table-row';
            const nameInput = document.getElementById('inlineNama');
            if (nameInput) nameInput.focus();
        } else {
            row.style.display = 'none';
        }
    }

    function cancelInlineRow() {
        const row = document.getElementById('inlineAddRow');
        if (row) row.style.display = 'none';
        if (document.getElementById('inlineNama')) document.getElementById('inlineNama').value = '';
        if (document.getElementById('inlineKode')) document.getElementById('inlineKode').value = '';
        if (document.getElementById('inlineSatuan')) document.getElementById('inlineSatuan').value = '';
        if (document.getElementById('inlineNilai')) document.getElementById('inlineNilai').value = '';
        if (document.getElementById('inlineSumber')) document.getElementById('inlineSumber').value = '';
        document.querySelectorAll('.inline-custom-field').forEach(input => input.value = '');
    }

    function saveInlineRow() {
        const namaInput = document.getElementById('inlineNama');
        if (!namaInput || !namaInput.value.trim()) {
            alert('Nama indikator wajib diisi.');
            if (namaInput) namaInput.focus();
            return;
        }

        const btnSave = document.getElementById('btnSaveInline');
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
        }

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('urusan_id', '{{ $urusan->id }}');
        formData.append('nama_indikator', namaInput.value.trim());

        const kode = document.getElementById('inlineKode');
        if (kode && kode.value.trim()) formData.append('kode_indikator', kode.value.trim());

        const satuan = document.getElementById('inlineSatuan');
        if (satuan && satuan.value.trim()) formData.append('satuan', satuan.value.trim());

        const nilai = document.getElementById('inlineNilai');
        if (nilai && nilai.value.trim()) formData.append('nilai', nilai.value.trim());

        const sumber = document.getElementById('inlineSumber');
        if (sumber && sumber.value.trim()) formData.append('sumber_data', sumber.value.trim());

        document.querySelectorAll('.inline-custom-field').forEach(input => {
            const colId = input.dataset.colId;
            if (colId && input.value.trim()) {
                formData.append('custom_fields[' + colId + ']', input.value.trim());
            }
        });

        fetch('{{ route('indikator.store') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof window.refreshPageContent === 'function') {
                    window.refreshPageContent(window.location.href, true);
                } else if (typeof window.loadSpaContent === 'function') {
                    window.loadSpaContent(window.location.href, false, true);
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'Gagal menyimpan indikator.');
                if (btnSave) {
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<i class="bi bi-check-lg"></i> Simpan';
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan indikator.');
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = '<i class="bi bi-check-lg"></i> Simpan';
            }
        });
    }

    function openEditKolom(id, name, type) {
        document.getElementById('editColId').value = id;
        const colNameInput = document.getElementById('editColName');
        colNameInput.value = name;
        document.getElementById('editColType').value = type || 'text';
        const modalEl = document.getElementById('modalEditKolom');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            setTimeout(() => {
                if (colNameInput) {
                    colNameInput.focus();
                    colNameInput.select();
                }
            }, 150);
        }
    }

    function openSubModal(parentId, parentName) {
        document.getElementById('subParentId').value = parentId;
        document.getElementById('subParentName').innerText = parentName;
        const modal = new bootstrap.Modal(document.getElementById('modalTambahSub'));
        modal.show();
    }

    function openEditModal(id, nama, kode, deskripsi, satuan, tipeData, urutan, statusAktif) {
        document.getElementById('formEditIndikator').action = '/indikator/' + id;
        document.getElementById('editNama').value = nama;
        document.getElementById('editKode').value = kode || '';
        document.getElementById('editDeskripsi').value = deskripsi || '';
        document.getElementById('editSatuan').value = satuan || '';
        document.getElementById('editTipeData').value = tipeData || 'numerik';
        document.getElementById('editUrutan').value = urutan || 1;
        document.getElementById('editStatusAktif').checked = (statusAktif === 1 || statusAktif === true);

        const modal = new bootstrap.Modal(document.getElementById('modalEditIndikator'));
        modal.show();
    }

    function openManageNilaiModal(indikatorId, indikatorName, satuan, tipeData, tahunId, tahunNomor) {
        document.getElementById('manageModalSubtitle').innerText = indikatorName;
        document.getElementById('quickIndikatorId').value = indikatorId;
        document.getElementById('quickTahunId').value = tahunId;
        document.getElementById('quickTahunLabel').innerText = tahunNomor;
        document.getElementById('quickSatuanLabel').innerText = satuan || '-';
        document.getElementById('quickNilai').value = '';
        document.getElementById('quickSumber').value = '';
        document.getElementById('quickKeterangan').value = '';

        // Fetch history data
        fetchHistory(indikatorId, tahunId);

        const modal = new bootstrap.Modal(document.getElementById('modalManageNilai'));
        modal.show();
    }

    function openHistoryModal(indikatorId) {
        const tahunId = {{ $tahun->id ?? 1 }};
        const tahunNomor = {{ $tahun->tahun ?? 2025 }};
        openManageNilaiModal(indikatorId, 'Indikator #' + indikatorId, '', 'numerik', tahunId, tahunNomor);
    }

    function fetchHistory(indikatorId, currentTahunId) {
        const tbody = document.getElementById('historyTableBody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm me-1 text-primary"></div> Memuat riwayat tahunan...</td></tr>';

        fetch('/indikator/' + indikatorId)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.history) {
                    document.getElementById('manageModalSubtitle').innerText = data.indikator.nama_indikator;
                    document.getElementById('quickSatuanLabel').innerText = data.indikator.satuan || '-';

                    let rowsHtml = '';
                    data.history.forEach(item => {
                        const isCurrentYear = (item.tahun_id === currentTahunId);
                        if (isCurrentYear) {
                            if (document.getElementById('quickNilai')) document.getElementById('quickNilai').value = item.nilai || '';
                            if (document.getElementById('quickSumber')) document.getElementById('quickSumber').value = item.sumber_data || '';
                            if (document.getElementById('quickKeterangan')) document.getElementById('quickKeterangan').value = item.keterangan || '';

                            document.querySelectorAll('.quick-custom-input').forEach(input => {
                                const colId = input.dataset.colId;
                                input.value = (item.custom_fields && item.custom_fields[colId]) ? item.custom_fields[colId] : '';
                            });
                        }

                        const nilaiDisplay = item.nilai !== null && item.nilai !== ''
                            ? '<strong class="font-monospace text-dark">' + item.nilai + '</strong> ' + (data.indikator.satuan || '')
                            : '<span class="text-muted small fst-italic">Belum tersedia</span>';

                        const updateInfo = item.updated_at
                            ? '<small class="d-block text-muted" style="font-size:0.7rem;">' + item.updated_at + '</small><small class="text-secondary" style="font-size:0.65rem;">' + (item.updated_by || 'Sistem') + '</small>'
                            : '<span class="text-muted small">—</span>';

                        rowsHtml += `
                            <tr class="${isCurrentYear ? 'table-primary table-opacity-10 fw-semibold' : ''}">
                                <td class="text-center">
                                    <span class="badge ${isCurrentYear ? 'bg-primary text-white' : 'bg-light text-dark border'}">${item.tahun}</span>
                                </td>
                                <td class="text-end">${nilaiDisplay}</td>
                                <td><small class="text-muted">${item.sumber_data || '—'}</small></td>
                                <td><small class="text-muted">${item.keterangan || '—'}</small></td>
                                <td>${updateInfo}</td>
                            </tr>
                        `;
                    });

                    tbody.innerHTML = rowsHtml;
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Gagal memuat data.</td></tr>';
                }
            })
            .catch(err => {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Terjadi kesalahan memuat data.</td></tr>';
            });
</script>

@php
    $jsUrusanName = $urusan->nama_urusan ?? '';
    $jsTableName = $activeTable['name'] ?? ($urusan->nama_urusan ?? '');
    $jsTableId = $activeTableId ?? 1;
    $jsAvailableYears = $allAvailableYears ?? [2023, 2024, 2025, 2026];
@endphp

<script>
    // Toggle Urusan Side Menu & remember state in localStorage
    window.toggleUrusanSideMenu = function() {
        const sideMenu = document.getElementById('urusanSideMenu');
        const label = document.getElementById('labelToggleUrusanSideMenu');
        if (!sideMenu) return;

        const isNowCollapsed = !sideMenu.classList.contains('is-collapsed');
        if (isNowCollapsed) {
            sideMenu.classList.add('is-collapsed');
            if (label) label.textContent = 'Buka Menu';
            localStorage.setItem('urusan_side_menu_collapsed', '1');
        } else {
            sideMenu.classList.remove('is-collapsed');
            if (label) label.textContent = 'Menu Tabel';
            localStorage.setItem('urusan_side_menu_collapsed', '0');
        }
    };

    window.filterSiblingTables = function(keyword) {
        const q = (keyword || '').trim().toLowerCase();
        const items = document.querySelectorAll('#siblingTablesList .sibling-table-item');
        items.forEach(el => {
            const title = el.dataset.tableTitle || '';
            el.style.display = (title.includes(q)) ? '' : 'none';
        });
    };

    // Restore side menu collapsed state on load
    (function() {
        const saved = localStorage.getItem('urusan_side_menu_collapsed');
        if (saved === '1') {
            const sideMenu = document.getElementById('urusanSideMenu');
            const label = document.getElementById('labelToggleUrusanSideMenu');
            if (sideMenu) sideMenu.classList.add('is-collapsed');
            if (label) label.textContent = 'Buka Menu';
        }
    })();

    // Existing column data for Urusan (All configured columns of active table)
    window.existingColumns = @json($allColumns);
    var existingColumns = window.existingColumns;
    window.currentUrusanName = @json($jsUrusanName);
    window.currentTableName = @json($jsTableName);
    window.currentTableId = @json($jsTableId);
    window.availableYears = @json($jsAvailableYears);

    // Live Real-Time Word Search for Table Rows
    window.applyLiveSearch = function(query) {
        query = (query || '').trim().toLowerCase();
        const terms = query.split(/\s+/).filter(t => t.length > 0);
        const rows = document.querySelectorAll('.custom-data-table tbody tr.indikator-data-row');
        const resetBtn = document.getElementById('btnResetSearch');
        const emptyState = document.getElementById('emptyStateRow');

        if (resetBtn) {
            resetBtn.style.display = terms.length > 0 ? 'inline-flex' : 'none';
        }

        let matchCount = 0;
        rows.forEach(row => {
            // Collect text from all non-action cells
            const cells = row.querySelectorAll('td:not(.sticky-action-col)');
            let rowText = '';
            cells.forEach(td => {
                rowText += ' ' + (td.innerText || td.textContent || '');
            });
            rowText = rowText.toLowerCase();

            const isMatch = (terms.length === 0) || terms.every(t => rowText.includes(t));
            if (isMatch) {
                row.style.removeProperty('display');
                row.classList.remove('d-none');
                matchCount++;
            } else {
                row.style.setProperty('display', 'none', 'important');
                row.classList.add('d-none');
            }
        });

        // Search Not Found Message
        let noMatchRow = document.getElementById('clientNoMatchRow');
        if (terms.length > 0 && matchCount === 0) {
            if (emptyState) emptyState.style.display = 'none';
            if (!noMatchRow) {
                const tbody = document.querySelector('.custom-data-table tbody');
                if (tbody) {
                    noMatchRow = document.createElement('tr');
                    noMatchRow.id = 'clientNoMatchRow';
                    const colCount = document.querySelectorAll('.custom-data-table thead th').length || 10;
                    noMatchRow.innerHTML = `
                        <td colspan="${colCount}" class="text-center py-5 text-muted">
                            <div class="py-2">
                                <i class="bi bi-search fs-2 text-primary opacity-50 d-block mb-2"></i>
                                <h6 class="fw-bold text-dark">Data tidak ditemukan</h6>
                                <p class="small text-muted mb-3">Tidak ada baris data yang cocok dengan kata: <strong class="text-dark no-match-query">"${query}"</strong></p>
                                <button type="button" class="btn btn-sm btn-outline-primary shadow-xs fw-semibold px-3 py-1" onclick="window.clearSearchFilter()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Tampilkan Semua Data
                                </button>
                            </div>
                        </td>`;
                    tbody.appendChild(noMatchRow);
                }
            } else {
                noMatchRow.style.removeProperty('display');
                noMatchRow.classList.remove('d-none');
                const qEl = noMatchRow.querySelector('.no-match-query');
                if (qEl) qEl.innerText = `"${query}"`;
            }
        } else {
            if (noMatchRow) {
                noMatchRow.style.setProperty('display', 'none', 'important');
                noMatchRow.classList.add('d-none');
            }
        }
    };

    window.handleLiveSearchInput = function(val) {
        window.applyLiveSearch(val);
    };

    window.clearSearchFilter = function() {
        const input = document.getElementById('liveSearchInput');
        if (input) {
            input.value = '';
            input.focus();
        }
        window.applyLiveSearch('');
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.delete('search');
            window.history.replaceState({}, '', url.toString());
        }
    };

    window.handleSearchSubmit = function(e) {
        if (e) {
            if (typeof e.preventDefault === 'function') e.preventDefault();
            if (typeof e.stopPropagation === 'function') e.stopPropagation();
        }
        const input = document.getElementById('liveSearchInput');
        const query = input ? input.value.trim() : '';
        window.applyLiveSearch(query);
        if (window.history.replaceState) {
            const url = new URL(window.location.href);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            window.history.replaceState({}, '', url.toString());
        }
        return false;
    };

    window.applyTahunRangeFilter = function() {
        const form = document.getElementById('formFilterRentangTahun');
        const selAwal = document.getElementById('filterTahunAwal');
        const selAkhir = document.getElementById('filterTahunAkhir');
        if (!selAwal || !selAkhir || !form) return;

        let awal = parseInt(selAwal.value);
        let akhir = parseInt(selAkhir.value);

        if (akhir < awal) {
            selAkhir.value = awal;
            akhir = awal;
        }

        const actionTarget = form ? (form.getAttribute('action') || (typeof form.action === 'string' ? form.action : null) || window.location.href) : window.location.href;
        const url = new URL(actionTarget, window.location.origin);
        const params = new URLSearchParams(window.location.search);
        params.set('tahun_awal', awal);
        params.set('tahun_akhir', akhir);
        const searchInput = document.getElementById('liveSearchInput') || document.getElementById('tableSearchInput');
        if (searchInput && searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        } else {
            params.delete('search');
        }
        url.search = params.toString();

        const tableCard = document.querySelector('.card.border-0.shadow-xs') || document.querySelector('.table-responsive');
        if (tableCard) {
            tableCard.style.transition = 'opacity 0.15s ease';
            tableCard.style.opacity = '0.65';
        }

        if (typeof window.loadSpaContent === 'function') {
            window.loadSpaContent(url.toString(), true, true);
        } else {
            form.submit();
        }
    };

    function applyTahunRangeFilter() {
        if (typeof window.applyTahunRangeFilter === 'function') {
            window.applyTahunRangeFilter();
        }
    }

    function updateRowBadge() {
        const val = document.getElementById('inputNumRows').value || 0;
        document.getElementById('badgeNumRows').innerText = val + ' Baris';
    }

    // Persistent in-memory state untuk header kolom modal Atur Tabel
    window.modalColState = window.modalColState || [];

    function initModalColState() {
        window.modalColState = [];
        if (window.existingColumns && window.existingColumns.length > 0) {
            window.existingColumns.forEach((col, idx) => {
                const rawName = (col.name || '').trim();
                const isGeneric = !rawName || /^Kolom\s*\d+$/i.test(rawName);
                window.modalColState.push({
                    name: isGeneric ? '' : rawName,
                    type: col.type || 'text',
                    id: col.id || ''
                });
            });
        }
    }

    function syncModalColStateFromDOM() {
        const container = document.getElementById('colInputsContainer');
        if (!container) return;
        const currentGroups = container.querySelectorAll('.input-group');
        currentGroups.forEach((group, idx) => {
            const nameInput = group.querySelector('input[name="col_names[]"]');
            const typeSelect = group.querySelector('select[name="col_types[]"]');
            const idInput = group.querySelector('input[name="col_ids[]"]');
            if (nameInput) {
                if (!window.modalColState[idx]) {
                    window.modalColState[idx] = {};
                }
                window.modalColState[idx].name = nameInput.value;
                window.modalColState[idx].type = typeSelect ? typeSelect.value : 'text';
                if (idInput && idInput.value) {
                    window.modalColState[idx].id = idInput.value;
                }
            }
        });
    }

    function renderColInputs() {
        const inputNumColsEl = document.getElementById('inputNumCols');
        if (!inputNumColsEl) return;
        const numCols = parseInt(inputNumColsEl.value) || 0;
        const badgeCols = document.getElementById('badgeNumCols');
        if (badgeCols) badgeCols.innerText = numCols + ' Kolom';
        const container = document.getElementById('colInputsContainer');
        if (!container) return;

        // Ambil data yang sedang diisi/diketik oleh user di modal sebelum DOM di-render ulang
        syncModalColStateFromDOM();

        container.innerHTML = '';

        if (numCols <= 0) {
            container.innerHTML = '<div class="col-12 text-muted fst-italic small py-3 text-center bg-white rounded-3 border">0 kolom data dipilih. Tabel hanya akan menampilkan kolom indikator dan aksi.</div>';
            return;
        }

        for (let i = 0; i < numCols; i++) {
            let colName = '';
            let colType = 'text';
            let colId = '';

            // 1. Ambil dari memory modalColState yang sudah diisi / diketik user
            if (window.modalColState && window.modalColState[i] !== undefined) {
                colName = window.modalColState[i].name !== undefined ? window.modalColState[i].name : '';
                colType = window.modalColState[i].type || 'text';
                colId = window.modalColState[i].id || (window.existingColumns && window.existingColumns[i] ? window.existingColumns[i].id : '');
            }
            // 2. Jika belum ada di modalColState tapi kolom ini sudah ada di database tabel
            else if (window.existingColumns && window.existingColumns[i]) {
                const rawName = (window.existingColumns[i].name || '').trim();
                const isGeneric = !rawName || /^Kolom\s*\d+$/i.test(rawName);
                colName = isGeneric ? '' : rawName;
                colType = window.existingColumns[i].type || 'text';
                colId = window.existingColumns[i].id || '';

                window.modalColState[i] = { name: colName, type: colType, id: colId };
            }
            // 3. Kolom baru tambahan
            else {
                colName = '';
                colType = 'text';
                colId = '';

                window.modalColState[i] = { name: '', type: 'text', id: '' };
            }

            // Pastikan jika namanya kosong atau generic default "Kolom X", jadikan placeholder bukan value
            const isGenericName = !colName || /^Kolom\s*\d+$/i.test(colName.trim());
            const valAttr = isGenericName ? '' : colName;
            const placeholderText = 'Kolom ' + (i + 1);

            const colDiv = document.createElement('div');
            colDiv.className = 'col-12 col-md-6';
            colDiv.innerHTML = `
                <div class="input-group input-group-sm shadow-xs rounded-2 overflow-hidden border">
                    <span class="input-group-text bg-light text-slate-700 fw-bold border-0 px-2.5" style="width: 78px; font-size: 0.76rem;">Kolom ${i + 1}</span>
                    <input type="hidden" name="col_ids[]" value="${colId}">
                    <input type="text" name="col_names[]" class="form-control border-0 border-start bg-white col-name-input" data-col-idx="${i}" value="${valAttr.replace(/"/g, '&quot;')}" placeholder="${placeholderText}" style="font-size: 0.82rem;">
                    <select name="col_types[]" class="form-select border-0 border-start bg-light text-secondary fw-semibold col-type-select" data-col-idx="${i}" style="max-width: 105px; font-size: 0.76rem;">
                        <option value="text" ${colType === 'text' ? 'selected' : ''}>Teks</option>
                        <option value="number" ${colType === 'number' ? 'selected' : ''}>Angka</option>
                        <option value="percentage" ${colType === 'percentage' ? 'selected' : ''}>Persen (%)</option>
                    </select>
                </div>
            `;
            container.appendChild(colDiv);
        }
    }

    // Event listener modal Atur Tabel & tracking input
    document.addEventListener('DOMContentLoaded', function() {
        initModalColState();

        const modalAtur = document.getElementById('modalAturTabel');
        if (modalAtur) {
            // Ketika modal dibuka, inisialisasi state dari kolom yang sudah ada jika belum ada
            modalAtur.addEventListener('show.bs.modal', function () {
                if (!window.modalColState || window.modalColState.length === 0) {
                    initModalColState();
                }
                const inputNumColsEl = document.getElementById('inputNumCols');
                if (inputNumColsEl) {
                    const activeCount = (window.modalColState && window.modalColState.length > 0)
                        ? window.modalColState.length
                        : ((window.existingColumns && window.existingColumns.length > 0) ? window.existingColumns.length : 3);
                    inputNumColsEl.value = activeCount;
                }
                renderColInputs();
                updateRowBadge();
            });

            // Reset modal jika dibatalkan/ditutup tanpa simpan
            modalAtur.addEventListener('hidden.bs.modal', function () {
                const inputNumColsEl = document.getElementById('inputNumCols');
                const container = document.getElementById('colInputsContainer');
                initModalColState();
                if (inputNumColsEl && container) {
                    const savedCount = (window.existingColumns && window.existingColumns.length > 0) ? window.existingColumns.length : 3;
                    inputNumColsEl.value = savedCount;
                    renderColInputs();
                }
            });
        }

        // Live input tracking agar perubahan user langsung terekam tanpa menunggu re-render
        const container = document.getElementById('colInputsContainer');
        if (container) {
            container.addEventListener('input', function(e) {
                if (e.target && e.target.classList.contains('col-name-input')) {
                    const idx = parseInt(e.target.dataset.colIdx);
                    if (!isNaN(idx)) {
                        if (!window.modalColState[idx]) window.modalColState[idx] = {};
                        window.modalColState[idx].name = e.target.value;
                    }
                }
            });
            container.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('col-type-select')) {
                    const idx = parseInt(e.target.dataset.colIdx);
                    if (!isNaN(idx)) {
                        if (!window.modalColState[idx]) window.modalColState[idx] = {};
                        window.modalColState[idx].type = e.target.value;
                    }
                }
            });
        }

        // Sebelum submit form Atur Tabel, pastikan input yang kosong diisi nilai placeholdernya agar backend menerima nama default
        const formAtur = document.getElementById('formAturTabel');
        if (formAtur) {
            formAtur.addEventListener('submit', function() {
                const nameInputs = formAtur.querySelectorAll('input[name="col_names[]"]');
                nameInputs.forEach((inp, idx) => {
                    if (!inp.value || inp.value.trim() === '') {
                        inp.value = inp.placeholder || ('Kolom ' + (idx + 1));
                    }
                });
            });
        }
    });

    function clearAllTable() {
        window.showModernConfirm({
            title: 'Kosongkan Semua Kolom?',
            htmlMessage: 'Apakah Anda yakin ingin mengosongkan semua kolom kustom pada halaman tabel ini? Seluruh kolom dan data nilai di dalamnya akan dihapus.',
            confirmText: 'Ya, Kosongkan',
            confirmIcon: 'bi-trash3-fill',
            cancelText: 'Batal',
            onConfirm: function() {
                document.getElementById('actionFieldAturTabel').value = 'clear';
                if (typeof window.submitSpaForm === 'function') {
                    window.submitSpaForm(document.getElementById('formAturTabel'));
                } else {
                    document.getElementById('formAturTabel').submit();
                }
            }
        });
    }

    // Quick Add Row with Anti-Spam protection
    var isAddingQuickRow = false;
    function quickAddRow() {
        if (isAddingQuickRow) return;
        isAddingQuickRow = true;

        const addBtns = document.querySelectorAll('button[onclick="quickAddRow()"], #btnQuickAddRow');
        addBtns.forEach(b => {
            b.disabled = true;
            b.classList.add('disabled', 'opacity-50');
        });

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('table_id', '{{ $activeTableId }}');

        fetch('{{ route('urusan.quick-row', $urusan->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof window.refreshPageContent === 'function') {
                    window.refreshPageContent(window.location.href, true);
                } else if (typeof window.loadSpaContent === 'function') {
                    window.loadSpaContent(window.location.href, false, true);
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'Gagal menambahkan baris baru.');
                isAddingQuickRow = false;
                addBtns.forEach(b => {
                    b.disabled = false;
                    b.classList.remove('disabled', 'opacity-50');
                });
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat menambahkan baris.');
            isAddingQuickRow = false;
            addBtns.forEach(b => {
                b.disabled = false;
                b.classList.remove('disabled', 'opacity-50');
            });
        });
    }

    // Excel-style Keyboard Navigation (Enter: pindah ke baris bawahnya, Shift+Enter: baris atasnya, Tab: kolom berikutnya)
    function navigateTableCell(currentTd, direction) {
        if (!currentTd) return;
        const currentTr = currentTd.closest('tr.indikator-data-row');
        if (!currentTr) return;

        if (direction === 'down' || direction === 'up') {
            let targetTr = null;
            let sibling = direction === 'down' ? currentTr.nextElementSibling : currentTr.previousElementSibling;
            while (sibling) {
                if (sibling.matches && sibling.matches('tr.indikator-data-row') && sibling.style.display !== 'none') {
                    targetTr = sibling;
                    break;
                }
                sibling = direction === 'down' ? sibling.nextElementSibling : sibling.previousElementSibling;
            }

            if (targetTr) {
                const isNama = currentTd.classList.contains('cell-editable-nama') || currentTd.dataset.field === 'nama_indikator' || currentTd.dataset.colRole === 'nama';
                let targetCell = null;
                if (isNama) {
                    targetCell = targetTr.querySelector('.cell-editable-nama') || targetTr.querySelector('td[data-field="nama_indikator"]');
                } else if (currentTd.dataset.field) {
                    targetCell = targetTr.querySelector(`td[data-field="${currentTd.dataset.field}"]`);
                }

                if (!targetCell) {
                    const cellIndex = Array.from(currentTr.children).indexOf(currentTd);
                    if (cellIndex !== -1 && targetTr.children[cellIndex]) {
                        targetCell = targetTr.children[cellIndex];
                    }
                }

                if (targetCell) {
                    if (targetCell.classList.contains('table-induk-cell') && !targetCell.classList.contains('editable-cell') && !targetCell.classList.contains('cell-editable-nama')) {
                        return navigateTableCell(targetCell, direction);
                    }
                    if (targetCell.classList.contains('editable-cell') || targetCell.classList.contains('cell-editable-nama')) {
                        setTimeout(() => {
                            targetCell.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            targetCell.click();
                        }, 50);
                    }
                }
            }
        } else if (direction === 'right' || direction === 'left') {
            const allEditableCells = Array.from(document.querySelectorAll('tbody tr.indikator-data-row:not([style*="display: none"]) .editable-cell, tbody tr.indikator-data-row:not([style*="display: none"]) .cell-editable-nama'));
            const currentIndex = allEditableCells.indexOf(currentTd);
            if (currentIndex !== -1) {
                const nextIndex = direction === 'right' ? currentIndex + 1 : currentIndex - 1;
                if (nextIndex >= 0 && nextIndex < allEditableCells.length) {
                    const targetCell = allEditableCells[nextIndex];
                    setTimeout(() => {
                        targetCell.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        targetCell.click();
                    }, 50);
                }
            }
        }
    }

    // Live Inline Click-to-Edit Functionality with Excel-like Navigation
    function initInlineTableEditing() {
        // 1. Click to edit Nama Indikator
        document.querySelectorAll('.cell-editable-nama').forEach(el => {
            el.addEventListener('click', function(e) {
                if (this.querySelector('input')) return;
                const id = this.dataset.id;
                const currentVal = this.dataset.raw || this.innerText.trim();

                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'form-control form-control-sm cell-live-input';
                input.value = currentVal;
                input.style.minWidth = '220px';

                const originalHtml = this.innerHTML;
                this.innerHTML = '';
                this.appendChild(input);
                input.focus();
                input.select();

                let isSaved = false;
                let pendingNavDirection = null;

                const save = () => {
                    if (isSaved) return;
                    const targetDir = pendingNavDirection;
                    pendingNavDirection = null;

                    const newVal = input.value.trim();
                    if (newVal === '' || newVal === currentVal) {
                        this.innerHTML = originalHtml;
                        if (targetDir) {
                            navigateTableCell(this, targetDir);
                        }
                        return;
                    }

                    isSaved = true;
                    saveCellUpdate(id, 'nama_indikator', newVal, (success, data) => {
                        if (success) {
                            this.dataset.raw = newVal;
                            this.innerHTML = `${newVal} <i class="bi bi-pencil small text-primary opacity-0 hover-show ms-1" style="font-size: 0.7rem;"></i>`;
                            showCellSuccess(this);
                            if (targetDir) {
                                navigateTableCell(this, targetDir);
                            }
                        } else {
                            this.innerHTML = originalHtml;
                        }
                    });
                };

                input.addEventListener('blur', save);
                input.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        pendingNavDirection = ev.shiftKey ? 'up' : 'down';
                        input.blur();
                    } else if (ev.key === 'Tab') {
                        ev.preventDefault();
                        pendingNavDirection = ev.shiftKey ? 'left' : 'right';
                        input.blur();
                    } else if (ev.key === 'Escape') {
                        isSaved = true;
                        pendingNavDirection = null;
                        this.innerHTML = originalHtml;
                    }
                });
            });
        });

        // 2. Click to edit dynamic column cells
        document.querySelectorAll('.editable-cell').forEach(td => {
            td.addEventListener('click', function(e) {
                if (this.querySelector('input')) return;
                const id = this.dataset.id;
                const field = this.dataset.field;
                const parentField = this.dataset.parentField;
                const tahunId = this.dataset.tahunId;
                const isTotal = this.dataset.isTotal === 'true';
                const type = this.dataset.type || 'text';
                const currentRaw = this.dataset.raw || '';
                const isCenter = this.dataset.align === 'center' || this.dataset.isTotal === 'true' || this.classList.contains('text-center') || this.dataset.parentField !== undefined;
                const alignClass = isCenter ? 'text-center' : 'text-start';
                const justifyClass = isCenter ? 'justify-content-center' : 'justify-content-start';

                const input = document.createElement('input');
                input.type = 'text';
                input.className = `form-control form-control-sm cell-live-input ${alignClass}`;
                input.value = currentRaw;
                input.style.width = '100%';

                const wrapper = this.querySelector('.cell-wrapper');
                const badgeEl = wrapper ? wrapper.querySelector('.badge, .d-inline-flex') : null;
                const badgeHtml = badgeEl ? badgeEl.outerHTML + ' ' : '';
                const originalHtml = wrapper ? wrapper.innerHTML : this.innerHTML;
                this.innerHTML = '';
                this.appendChild(input);
                input.focus();
                input.select();

                let isSaved = false;
                let pendingNavDirection = null;

                const save = () => {
                    if (isSaved) return;
                    const targetDir = pendingNavDirection;
                    pendingNavDirection = null;

                    const newVal = input.value.trim();
                    if (newVal === currentRaw) {
                        this.innerHTML = `<div class="cell-wrapper rounded d-flex align-items-center ${justifyClass} gap-1">${originalHtml}</div>`;
                        if (targetDir) {
                            navigateTableCell(this, targetDir);
                        }
                        return;
                    }

                    isSaved = true;
                    saveCellUpdate(id, field, newVal, (success, res) => {
                        if (success) {
                            this.dataset.raw = res.value !== undefined ? res.value : newVal;
                            const displayVal = (res.display !== null && res.display !== undefined && res.display !== '') 
                                ? res.display 
                                : '<span class="text-muted fst-italic small opacity-40">+ Isi</span>';
                            this.innerHTML = `
                                <div class="cell-wrapper rounded d-flex align-items-center ${justifyClass} gap-1">
                                    ${badgeHtml}
                                    <span class="cell-val-display ${alignClass} ${isTotal ? 'text-primary fw-bold' : 'text-slate-800'}">${displayVal}</span>
                                    <i class="bi bi-pencil small text-primary opacity-0 cell-edit-icon" style="font-size: 0.60rem;"></i>
                                </div>
                            `;
                            showCellSuccess(this);

                            // Auto-sum for sibling Total cell if applicable
                            if (parentField && !isTotal) {
                                window.recalculateSubColumnTotal(this.closest('tr'), parentField, id, tahunId, true);
                            }

                            // Auto-sum for row Total cell (like standalone JUMLAH column)
                            if (typeof window.recalculateRowTotal === 'function') {
                                window.recalculateRowTotal(this.closest('tr'), id, tahunId, true);
                            }

                            // Auto-sum for parent row if this is a child row and a metric/value was changed
                            if (typeof window.recalculateParentRowTotals === 'function' && (parentField || this.dataset.type === 'number' || this.classList.contains('cell-metric'))) {
                                window.recalculateParentRowTotals(this.closest('tr'), true);
                            }

                            // Auto-sum for bottom total row if active
                            if (typeof window.recalculateFooterTotals === 'function') {
                                window.recalculateFooterTotals();
                            }

                            if (targetDir) {
                                navigateTableCell(this, targetDir);
                            }
                        } else {
                            this.innerHTML = `<div class="cell-wrapper rounded d-flex align-items-center ${justifyClass} gap-1">${originalHtml}</div>`;
                        }
                    }, tahunId);
                };

                // Realtime live calculation while typing
                input.addEventListener('input', () => {
                    const liveVal = input.value.trim();
                    this.dataset.raw = liveVal;

                    if (parentField && !isTotal) {
                        window.recalculateSubColumnTotal(this.closest('tr'), parentField, id, tahunId, false);
                    }
                    if (typeof window.recalculateRowTotal === 'function') {
                        window.recalculateRowTotal(this.closest('tr'), id, tahunId, false);
                    }
                    if (typeof window.recalculateParentRowTotals === 'function' && (parentField || this.dataset.type === 'number' || this.classList.contains('cell-metric'))) {
                        window.recalculateParentRowTotals(this.closest('tr'), false);
                    }
                    if (typeof window.recalculateFooterTotals === 'function') {
                        window.recalculateFooterTotals();
                    }
                });

                input.addEventListener('blur', save);
                input.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        pendingNavDirection = ev.shiftKey ? 'up' : 'down';
                        input.blur();
                    } else if (ev.key === 'Tab') {
                        ev.preventDefault();
                        pendingNavDirection = ev.shiftKey ? 'left' : 'right';
                        input.blur();
                    } else if (ev.key === 'Escape') {
                        isSaved = true;
                        pendingNavDirection = null;
                        this.dataset.raw = currentRaw;
                        this.innerHTML = `<div class="cell-wrapper rounded d-flex align-items-center ${justifyClass} gap-1">${originalHtml}</div>`;
                        if (parentField && !isTotal) {
                            window.recalculateSubColumnTotal(this.closest('tr'), parentField, id, tahunId, false);
                        }
                        if (typeof window.recalculateRowTotal === 'function') {
                            window.recalculateRowTotal(this.closest('tr'), id, tahunId, false);
                        }
                        if (typeof window.recalculateParentRowTotals === 'function' && (parentField || this.dataset.type === 'number' || this.classList.contains('cell-metric'))) {
                            window.recalculateParentRowTotals(this.closest('tr'), false);
                        }
                        if (typeof window.recalculateFooterTotals === 'function') {
                            window.recalculateFooterTotals();
                        }
                    }
                });
            });
        });
    }

    function saveCellUpdate(id, field, value, callback, tahunId) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('field', field);
        formData.append('value', value);
        formData.append('tahun_id', tahunId || '{{ $tahun->id ?? 1 }}');

        fetch('/indikator/' + id + '/cell', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                callback(true, data);
            } else {
                alert(data.message || 'Gagal menyimpan data.');
                callback(false);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan data.');
            callback(false);
        });
    }

    window.recalculateSubColumnTotal = function(rowEl, parentField, indId, tahunId, shouldSave = true) {
        if (!rowEl) return;
        const siblingCells = Array.from(rowEl.querySelectorAll(`td[data-parent-field="${parentField}"]`));
        siblingCells.forEach((cell, idx) => {
            if (cell.dataset.isTotal === 'true') {
                const componentCells = siblingCells.slice(0, idx).filter(c => c.dataset.isTotal !== 'true');
                let sum = 0;
                let hasValue = false;

                componentCells.forEach(c => {
                    const raw = (c.dataset.raw || '').trim().replace(',', '.');
                    if (raw !== '' && !isNaN(raw)) {
                        sum += parseFloat(raw);
                        hasValue = true;
                    }
                });

                const sumStr = hasValue ? (Number.isInteger(sum) ? sum.toString() : parseFloat(sum.toFixed(2)).toString()) : '0';
                cell.dataset.raw = sumStr;
                const displaySpan = cell.querySelector('.cell-val-display');
                if (displaySpan) {
                    displaySpan.innerText = sumStr;
                    displaySpan.classList.remove('fst-italic', 'opacity-40');
                }

                if (shouldSave) {
                    if (typeof showCellSuccess === 'function') {
                        showCellSuccess(cell);
                    }
                    saveCellUpdate(indId, cell.dataset.field, sumStr, () => {
                        if (typeof window.recalculateRowTotal === 'function') {
                            window.recalculateRowTotal(rowEl, indId, tahunId, true);
                        }
                        if (typeof window.recalculateParentRowTotals === 'function') {
                            window.recalculateParentRowTotals(rowEl, true);
                        }
                        if (typeof window.recalculateFooterTotals === 'function') {
                            window.recalculateFooterTotals();
                        }
                    }, tahunId);
                } else {
                    if (typeof window.recalculateRowTotal === 'function') {
                        window.recalculateRowTotal(rowEl, indId, tahunId, false);
                    }
                    if (typeof window.recalculateParentRowTotals === 'function') {
                        window.recalculateParentRowTotals(rowEl, false);
                    }
                    if (typeof window.recalculateFooterTotals === 'function') {
                        window.recalculateFooterTotals();
                    }
                }
            }
        });
    };

    window.recalculateRowTotal = function(rowEl, indId, tahunId, shouldSave = true) {
        if (!rowEl) return;
        const rowTotalCells = rowEl.querySelectorAll('td[data-is-row-total="true"]');
        if (rowTotalCells.length === 0) return;

        rowTotalCells.forEach(totalCell => {
            const allCells = Array.from(rowEl.querySelectorAll('td'));
            const targetIndex = allCells.indexOf(totalCell);
            if (targetIndex === -1) return;

            const precedingCells = allCells.slice(0, targetIndex);
            let sum = 0;
            let hasValue = false;

            precedingCells.forEach(cell => {
                if (
                    cell.dataset.isDescriptor === 'true' ||
                    cell.classList.contains('cell-descriptor') ||
                    cell.classList.contains('sticky-action-col') ||
                    cell.dataset.isTotal === 'true' ||
                    cell.dataset.isRowTotal === 'true'
                ) {
                    return;
                }

                const colRole = (cell.dataset.colRole || '').toLowerCase();
                if (['no', 'nama', 'satuan', 'sumber'].includes(colRole)) {
                    return;
                }

                const isSub = cell.dataset.isSub === 'true' || Boolean(cell.dataset.parentField);
                const isMetric = cell.dataset.type === 'number' || cell.classList.contains('cell-metric');

                if (isSub || isMetric) {
                    const raw = (cell.dataset.raw || '').trim().replace(',', '.');
                    if (raw !== '' && !isNaN(raw)) {
                        sum += parseFloat(raw);
                        hasValue = true;
                    }
                }
            });

            const sumStr = hasValue ? (Number.isInteger(sum) ? sum.toString() : parseFloat(sum.toFixed(2)).toString()) : '0';
            totalCell.dataset.raw = sumStr;
            const displaySpan = totalCell.querySelector('.cell-val-display');
            if (displaySpan) {
                displaySpan.innerText = sumStr;
                displaySpan.classList.remove('fst-italic', 'opacity-40', 'text-muted');
                displaySpan.classList.add('text-primary', 'fw-bold', 'font-monospace');
            }

            if (shouldSave) {
                if (typeof showCellSuccess === 'function') {
                    showCellSuccess(totalCell);
                }

                const totalField = totalCell.dataset.field;
                if (totalField && indId) {
                    saveCellUpdate(indId, totalField, sumStr, () => {
                        if (typeof window.recalculateParentRowTotals === 'function') {
                            window.recalculateParentRowTotals(rowEl, true);
                        }
                        if (typeof window.recalculateFooterTotals === 'function') {
                            window.recalculateFooterTotals();
                        }
                    }, tahunId);
                }
            } else {
                if (typeof window.recalculateParentRowTotals === 'function') {
                    window.recalculateParentRowTotals(rowEl, false);
                }
                if (typeof window.recalculateFooterTotals === 'function') {
                    window.recalculateFooterTotals();
                }
            }
        });
    };

    window.recalculateParentRowTotals = function(childTr, shouldAnimate = true) {
        if (!childTr) return;
        const parentId = childTr.dataset.parentId;
        if (!parentId) return;

        const parentTr = document.querySelector(`tr.indikator-data-row[data-id="${parentId}"]`);
        if (!parentTr) return;

        const childRows = document.querySelectorAll(`tr.indikator-data-row[data-parent-id="${parentId}"]`);
        if (childRows.length === 0) return;

        // Iterate ONLY over parent row cells that are metrics / sub-columns
        // STRICTLY IGNORE all descriptor cells (No, Uraian, Satuan, Sumber)
        const parentCells = parentTr.querySelectorAll('td[data-field]');
        parentCells.forEach(parentCell => {
            // 1. Explicit descriptor flags and classes
            if (parentCell.dataset.isDescriptor === 'true' || parentCell.classList.contains('cell-descriptor')) {
                return;
            }

            // 2. Col role check
            const colRole = (parentCell.dataset.colRole || '').toLowerCase();
            if (['no', 'nama', 'satuan', 'sumber'].includes(colRole)) {
                return;
            }

            // 3. Col name check
            const colName = (parentCell.dataset.colName || '').toLowerCase().trim();
            if (['no', 'no.', 'nomor', 'nama', 'indikator', 'nama indikator', 'uraian', 'nama/uraian', 'satuan', 'sumber data', 'sumber_data'].includes(colName)) {
                return;
            }

            // 4. Field check
            const field = parentCell.dataset.field || '';
            const lowerField = field.toLowerCase();
            if (!field || lowerField === 'col_no' || lowerField === 'nama_indikator' || lowerField === 'kode_indikator' || lowerField.includes('nama') || lowerField.includes('satuan') || lowerField.includes('sumber')) {
                return;
            }

            // 5. Must NOT contain the INDUK badge or Sub badge
            if (parentCell.querySelector('.badge') || parentCell.querySelector('.induk-badge')) {
                return;
            }

            // 6. Must be a sub-column or metric column
            const isSubCol = Boolean(parentCell.dataset.parentField) || parentCell.dataset.isSub === 'true' || colRole === 'sub_column';
            const isMetric = parentCell.dataset.type === 'number' || colRole === 'metric' || parentCell.classList.contains('cell-metric');
            if (!isSubCol && !isMetric) {
                return;
            }

            let sum = 0;
            let hasAnyValue = false;

            childRows.forEach(childRow => {
                const childCell = childRow.querySelector(`td[data-field="${field}"]`);
                if (childCell) {
                    const raw = (childCell.dataset.raw || '').trim().replace(',', '.');
                    if (raw !== '' && !isNaN(raw)) {
                        sum += parseFloat(raw);
                        hasAnyValue = true;
                    }
                }
            });

            const displaySpan = parentCell.querySelector('.cell-val-display');
            if (displaySpan) {
                let finalVal = '';
                if (hasAnyValue) {
                    finalVal = Number.isInteger(sum) ? sum.toString() : parseFloat(sum.toFixed(2)).toString();
                    parentCell.dataset.raw = finalVal;
                    displaySpan.innerText = finalVal;
                    displaySpan.classList.remove('text-muted', 'opacity-50');
                } else {
                    parentCell.dataset.raw = '';
                    displaySpan.innerHTML = '<span class="text-muted font-monospace opacity-50">—</span>';
                }
                if (shouldAnimate && typeof showCellSuccess === 'function') {
                    showCellSuccess(parentCell);
                }
            }
        });

        // Bubble up recursively to ancestor parent rows (e.g., Level 2 -> Level 1 -> Level 0)
        if (parentTr && parentTr.dataset.parentId && parentTr.dataset.parentId.trim() !== '') {
            window.recalculateParentRowTotals(parentTr, shouldAnimate);
        } else {
            if (typeof window.recalculateFooterTotals === 'function') {
                window.recalculateFooterTotals();
            }
        }
    };

    // Helper untuk menutup dropdown opsi baris footer
    window.closeFooterModeDropdown = function() {
        const wrap = document.getElementById('dropdownFooterModeWrap');
        const btn = document.getElementById('btnToggleFooterTotal');
        const menu = document.getElementById('menuFooterTotalDropdown');
        if (btn && typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            const bs = bootstrap.Dropdown.getInstance(btn);
            if (bs) {
                try { bs.hide(); } catch(e) {}
            }
        }
        if (menu) menu.classList.remove('show');
        if (btn) {
            btn.classList.remove('show');
            btn.setAttribute('aria-expanded', 'false');
        }
    };

    // Klik di mana saja di luar dropdown akan menutup menu
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('dropdownFooterModeWrap');
        if (wrap && !wrap.contains(e.target)) {
            window.closeFooterModeDropdown();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.closeFooterModeDropdown();
        }
    });

    window.setFooterMode = function(mode) {
        // Tutup dropdown seketika saat opsi dipilih
        window.closeFooterModeDropdown();

        let showTotal = true;
        let showJumlah = true;
        let showSubtotal = true;

        if (mode === 'none') {
            showTotal = false;
            showJumlah = false;
            showSubtotal = false;
        } else if (mode === 'jumlah') {
            showTotal = true;
            showJumlah = true;
            showSubtotal = false;
        } else if (mode === 'total') {
            showTotal = true;
            showJumlah = false;
            showSubtotal = true;
        } else if (mode === 'both') {
            showTotal = true;
            showJumlah = true;
            showSubtotal = true;
        }

        // Hapus key preferensi global lama agar tidak mencemari tabel lain
        try {
            localStorage.removeItem('pref_footer_mode');
            localStorage.removeItem('pref_show_footer_total');
            localStorage.removeItem('pref_show_footer_jumlah');
            localStorage.removeItem('pref_show_footer_subtotal');
        } catch(e) {}

        // Simpan preferensi HANYA untuk tabel yang sedang aktif saat ini
        const tableStorageKey = 'pref_footer_mode_{{ $urusan->id }}_{{ $activeTableId }}';
        try {
            localStorage.setItem(tableStorageKey, mode);
        } catch(e) {}

        // Optimistic UI updates untuk tabel yang aktif
        const tfoot = document.getElementById('tableFooterTotal');
        const rowJumlah = document.getElementById('tableFooterRowJumlah');
        const rowSubtotal = document.getElementById('tableFooterRowSubtotal');
        const labelStatus = document.getElementById('labelFooterStatus');
        const btn = document.getElementById('btnToggleFooterTotal');
        const hasRows = document.querySelectorAll('tbody tr.indikator-data-row').length > 0;

        if (tfoot) {
            tfoot.style.display = (showTotal && hasRows && (showJumlah || showSubtotal)) ? '' : 'none';
        }
        if (rowJumlah) {
            rowJumlah.style.display = showJumlah ? '' : 'none';
        }
        if (rowSubtotal) {
            rowSubtotal.style.display = showSubtotal ? '' : 'none';
        }

        let modeText = 'Jumlah & Total';
        if (mode === 'none') modeText = 'Nonaktif';
        else if (mode === 'jumlah') modeText = 'Jumlah Saja';
        else if (mode === 'total') modeText = 'Total Saja';

        if (labelStatus) {
            labelStatus.innerText = modeText;
        }

        if (btn) {
            if (showTotal) {
                btn.classList.add('btn-light', 'border-primary', 'border-opacity-30', 'text-primary', 'fw-bold');
                btn.classList.remove('btn-white', 'text-muted');
            } else {
                btn.classList.remove('btn-light', 'border-primary', 'border-opacity-30', 'text-primary', 'fw-bold');
                btn.classList.add('btn-white', 'text-muted');
            }
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = showTotal ? 'bi bi-calculator-fill text-primary' : 'bi bi-calculator-fill text-secondary opacity-50';
            }
        }

        // Update dropdown menu items active classes & check icons
        document.querySelectorAll('.footer-mode-item').forEach(item => {
            const isThis = item.dataset.mode === mode;
            item.classList.toggle('active', isThis);
            item.classList.toggle('fw-bold', isThis);
            const checkIcon = item.querySelector('.footer-mode-check');
            if (checkIcon) {
                checkIcon.classList.toggle('d-none', !isThis);
            }
        });

        // Update checkboxes in modalAturTabel
        const checkModal = document.getElementById('checkShowFooterTotal');
        const checkJumlah = document.getElementById('checkShowFooterJumlah');
        const checkSubtotal = document.getElementById('checkShowFooterSubtotal');
        const subOptionsWrap = document.getElementById('modalFooterSubOptions');
        if (checkModal) checkModal.checked = showTotal;
        if (checkJumlah) checkJumlah.checked = showJumlah;
        if (checkSubtotal) checkSubtotal.checked = showSubtotal;
        if (subOptionsWrap) subOptionsWrap.style.display = showTotal ? '' : 'none';

        if (showTotal && typeof window.recalculateFooterTotals === 'function') {
            window.recalculateFooterTotals();
        }

        const updateExportLinks = () => {
            const btnEx = document.getElementById('btnExportExcel');
            if (btnEx && btnEx.href) {
                try {
                    const u = new URL(btnEx.href, window.location.origin);
                    u.searchParams.set('show_footer_total', showTotal ? '1' : '0');
                    u.searchParams.set('show_footer_jumlah', showJumlah ? '1' : '0');
                    u.searchParams.set('show_footer_subtotal', showSubtotal ? '1' : '0');
                    btnEx.href = u.toString();
                } catch(e) {}
            }
            const btnPd = document.getElementById('btnExportPdf');
            if (btnPd && btnPd.href) {
                try {
                    const u = new URL(btnPd.href, window.location.origin);
                    u.searchParams.set('show_footer_total', showTotal ? '1' : '0');
                    u.searchParams.set('show_footer_jumlah', showJumlah ? '1' : '0');
                    u.searchParams.set('show_footer_subtotal', showSubtotal ? '1' : '0');
                    btnPd.href = u.toString();
                } catch(e) {}
            }
        };
        updateExportLinks();

        // Persist to backend HANYA untuk tabel yang aktif
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('action', 'toggle_footer_total');
        fd.append('table_id', '{{ $activeTableId }}');
        fd.append('show_footer_total', showTotal ? '1' : '0');
        fd.append('show_footer_jumlah', showJumlah ? '1' : '0');
        fd.append('show_footer_subtotal', showSubtotal ? '1' : '0');
        fd.append('footer_mode', mode);

        fetch('{{ route("urusan.columns", $urusan->slug ?: $urusan->id) }}', {
            method: 'POST',
            body: fd,
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.message && typeof window.showTopToast === 'function') {
                window.showTopToast(res.message, 'success', 1500);
            }
        })
        .catch(err => {
            console.error('Error setting footer mode:', err);
        });
    };

    window.toggleFooterTotal = function() {
        const tableStorageKey = 'pref_footer_mode_{{ $urusan->id }}_{{ $activeTableId }}';
        const curMode = localStorage.getItem(tableStorageKey) || '{{ $currentFooterMode }}';
        if (curMode !== 'none') {
            window.setFooterMode('none');
        } else {
            window.setFooterMode('both');
        }
    };

    window.onModalFooterTotalToggle = function(checked) {
        const subWrap = document.getElementById('modalFooterSubOptions');
        if (subWrap) subWrap.style.display = checked ? '' : 'none';
        if (checked) {
            const cJ = document.getElementById('checkShowFooterJumlah');
            const cS = document.getElementById('checkShowFooterSubtotal');
            if (cJ && cS && !cJ.checked && !cS.checked) {
                cJ.checked = true;
                cS.checked = true;
            }
        }
        window.onModalFooterSubOptionsChange();
    };

    window.onModalFooterSubOptionsChange = function() {
        const cTotal = document.getElementById('checkShowFooterTotal');
        const cJumlah = document.getElementById('checkShowFooterJumlah');
        const cSubtotal = document.getElementById('checkShowFooterSubtotal');

        if (!cTotal || !cTotal.checked) {
            window.setFooterMode('none');
            return;
        }

        const isJ = cJumlah ? cJumlah.checked : true;
        const isS = cSubtotal ? cSubtotal.checked : true;

        if (isJ && isS) {
            window.setFooterMode('both');
        } else if (isJ && !isS) {
            window.setFooterMode('jumlah');
        } else if (!isJ && isS) {
            window.setFooterMode('total');
        } else {
            window.setFooterMode('none');
        }
    };

    window.setModalFooterPreset = function(preset) {
        const cTotal = document.getElementById('checkShowFooterTotal');
        const cJumlah = document.getElementById('checkShowFooterJumlah');
        const cSubtotal = document.getElementById('checkShowFooterSubtotal');
        if (cTotal) cTotal.checked = true;
        if (preset === 'both') {
            if (cJumlah) cJumlah.checked = true;
            if (cSubtotal) cSubtotal.checked = true;
        } else if (preset === 'jumlah') {
            if (cJumlah) cJumlah.checked = true;
            if (cSubtotal) cSubtotal.checked = false;
        } else if (preset === 'total') {
            if (cJumlah) cJumlah.checked = false;
            if (cSubtotal) cSubtotal.checked = true;
        }
        window.onModalFooterSubOptionsChange();
    };

    window.applyFooterTotalPreference = function() {
        // Bersihkan key global lama agar tidak merusak tabel lain
        try {
            localStorage.removeItem('pref_footer_mode');
            localStorage.removeItem('pref_show_footer_total');
            localStorage.removeItem('pref_show_footer_jumlah');
            localStorage.removeItem('pref_show_footer_subtotal');
        } catch(e) {}

        // Baca preferensi HANYA jika ada preferensi khusus untuk tabel ini
        const tableStorageKey = 'pref_footer_mode_{{ $urusan->id }}_{{ $activeTableId }}';
        const prefMode = localStorage.getItem(tableStorageKey);

        // Jika tidak ada preferensi di localStorage khusus untuk tabel ini, JANGAN override tampilan server
        if (!prefMode) return;

        const mode = prefMode;
        const showTotal = mode !== 'none';
        const showJumlah = mode === 'both' || mode === 'jumlah';
        const showSubtotal = mode === 'both' || mode === 'total';

        const tfoot = document.getElementById('tableFooterTotal');
        const rowJumlah = document.getElementById('tableFooterRowJumlah');
        const rowSubtotal = document.getElementById('tableFooterRowSubtotal');
        const labelStatus = document.getElementById('labelFooterStatus');
        const btn = document.getElementById('btnToggleFooterTotal');
        const checkModal = document.getElementById('checkShowFooterTotal');
        const checkJumlah = document.getElementById('checkShowFooterJumlah');
        const checkSubtotal = document.getElementById('checkShowFooterSubtotal');
        const subOptionsWrap = document.getElementById('modalFooterSubOptions');
        const hasRows = document.querySelectorAll('tbody tr.indikator-data-row').length > 0;

        if (tfoot) {
            tfoot.style.display = (showTotal && hasRows && (showJumlah || showSubtotal)) ? '' : 'none';
        }
        if (rowJumlah) {
            rowJumlah.style.display = showJumlah ? '' : 'none';
        }
        if (rowSubtotal) {
            rowSubtotal.style.display = showSubtotal ? '' : 'none';
        }

        let modeText = 'Jumlah & Total';
        if (mode === 'none') modeText = 'Nonaktif';
        else if (mode === 'jumlah') modeText = 'Jumlah Saja';
        else if (mode === 'total') modeText = 'Total Saja';

        if (labelStatus) {
            labelStatus.innerText = modeText;
        }

        if (btn) {
            if (showTotal) {
                btn.classList.add('btn-light', 'border-primary', 'border-opacity-30', 'text-primary', 'fw-bold');
                btn.classList.remove('btn-white', 'text-muted');
            } else {
                btn.classList.remove('btn-light', 'border-primary', 'border-opacity-30', 'text-primary', 'fw-bold');
                btn.classList.add('btn-white', 'text-muted');
            }
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = showTotal ? 'bi bi-calculator-fill text-primary' : 'bi bi-calculator-fill text-secondary opacity-50';
            }
        }

        document.querySelectorAll('.footer-mode-item').forEach(item => {
            const isThis = item.dataset.mode === mode;
            item.classList.toggle('active', isThis);
            item.classList.toggle('fw-bold', isThis);
            const checkIcon = item.querySelector('.footer-mode-check');
            if (checkIcon) {
                checkIcon.classList.toggle('d-none', !isThis);
            }
        });

        if (checkModal) checkModal.checked = showTotal;
        if (checkJumlah) checkJumlah.checked = showJumlah;
        if (checkSubtotal) checkSubtotal.checked = showSubtotal;
        if (subOptionsWrap) subOptionsWrap.style.display = showTotal ? '' : 'none';

        const btnEx = document.getElementById('btnExportExcel');
        if (btnEx && btnEx.href) {
            try {
                const u = new URL(btnEx.href, window.location.origin);
                u.searchParams.set('show_footer_total', showTotal ? '1' : '0');
                u.searchParams.set('show_footer_jumlah', showJumlah ? '1' : '0');
                u.searchParams.set('show_footer_subtotal', showSubtotal ? '1' : '0');
                btnEx.href = u.toString();
            } catch(e) {}
        }
        const btnPd = document.getElementById('btnExportPdf');
        if (btnPd && btnPd.href) {
            try {
                const u = new URL(btnPd.href, window.location.origin);
                u.searchParams.set('show_footer_total', showTotal ? '1' : '0');
                u.searchParams.set('show_footer_jumlah', showJumlah ? '1' : '0');
                u.searchParams.set('show_footer_subtotal', showSubtotal ? '1' : '0');
                btnPd.href = u.toString();
            } catch(e) {}
        }
    };

    window.recalculateFooterTotals = function() {
        const tfoot = document.getElementById('tableFooterTotal');
        if (!tfoot || tfoot.style.display === 'none') return;

        const footerCells = tfoot.querySelectorAll('.footer-col-total[data-col-id]');
        if (footerCells.length === 0) return;

        const leafTotals = {};

        // 1. Hitung ulang Baris 1: Kolom Daun (Leaf Columns)
        footerCells.forEach(fCell => {
            const colId = fCell.dataset.colId;
            if (!colId) return;

            // Check if there is an explicit manual override on this cell
            if (fCell.dataset.hasOverride === 'true') {
                const curVal = (fCell.querySelector('.footer-total-val')?.innerText || '').trim().replace(/\./g, '').replace(',', '.');
                const norm = parseFloat(curVal);
                leafTotals[colId] = !isNaN(norm) ? norm : 0;
                return;
            }

            let sum = 0;
            let count = 0;

            // Find all cells with this data-field in root rows only
            const bodyCells = document.querySelectorAll(`tbody tr.indikator-data-row td[data-field="${colId}"]`);
            bodyCells.forEach(bCell => {
                const tr = bCell.closest('tr');
                if (tr && tr.dataset.parentId && tr.dataset.parentId.trim() !== '') {
                    return; // Skip child row to avoid double counting
                }

                const raw = (bCell.dataset.raw || '').trim().replace(/\./g, '').replace(',', '.');
                if (raw !== '' && !isNaN(raw)) {
                    sum += parseFloat(raw);
                    count++;
                }
            });

            let sumStr = '0';
            if (count > 0) {
                sumStr = Number.isInteger(sum) ? sum.toString() : parseFloat(sum.toFixed(2)).toString();
                leafTotals[colId] = sum;
            } else {
                leafTotals[colId] = 0;
            }

            const valSpan = fCell.querySelector('.footer-total-val');
            if (valSpan) {
                valSpan.innerText = sumStr;
            }
        });

        // 2. Hitung ulang Baris 2: Subtotal Grup Parent (misal Total Kolam + Sawah)
        const subtotalCells = tfoot.querySelectorAll('.footer-subtotal-cell[data-parent-id]');
        subtotalCells.forEach(sCell => {
            if (sCell.dataset.hasOverride === 'true') return;

            let leafIds = [];
            try {
                leafIds = JSON.parse(sCell.dataset.leafIds || '[]');
            } catch(e) {
                leafIds = [];
            }

            let pSum = 0;
            let hasCount = false;

            leafIds.forEach(lId => {
                if (typeof leafTotals[lId] !== 'undefined') {
                    pSum += leafTotals[lId];
                    hasCount = true;
                } else {
                    const targetLeafCell = tfoot.querySelector(`.footer-col-total[data-col-id="${lId}"] .footer-total-val`);
                    if (targetLeafCell) {
                        const txt = (targetLeafCell.innerText || '').trim().replace(/\./g, '').replace(',', '.');
                        const num = parseFloat(txt);
                        if (!isNaN(num)) {
                            pSum += num;
                            hasCount = true;
                        }
                    }
                }
            });

            let pSumStr = '0';
            if (hasCount) {
                pSumStr = Number.isInteger(pSum) ? pSum.toString() : parseFloat(pSum.toFixed(2)).toString();
            }

            const sValSpan = sCell.querySelector('.footer-subtotal-val');
            if (sValSpan) {
                sValSpan.innerText = pSumStr;
            }
        });
    };

    window.editFooterCell = function(cellEl) {
        if (!cellEl) return;
        const colId = cellEl.dataset.colId;
        const currentSpan = cellEl.querySelector('.footer-total-val');
        const currentVal = (currentSpan ? currentSpan.innerText : '').trim();

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm text-center font-monospace fw-bold py-0 px-1 border-primary';
        input.value = currentVal;
        input.style.maxWidth = '90px';
        input.placeholder = 'Otomatis';

        const originalHtml = cellEl.innerHTML;
        cellEl.innerHTML = '';
        cellEl.appendChild(input);
        input.focus();
        input.select();

        let isDone = false;
        const finish = () => {
            if (isDone) return;
            isDone = true;
            const newVal = input.value.trim();
            if (newVal === currentVal) {
                cellEl.innerHTML = originalHtml;
                return;
            }

            // Save override
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('action', 'set_footer_override');
            fd.append('table_id', '{{ $activeTableId }}');
            fd.append('col_id', colId);
            fd.append('value', newVal);

            fetch('{{ route("urusan.columns", $urusan->slug ?: $urusan->id) }}', {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(res => {
                cellEl.dataset.hasOverride = newVal !== '' ? 'true' : 'false';
                cellEl.innerHTML = `<span class="footer-total-val font-monospace fw-bold" style="font-size: 0.84rem;">${newVal !== '' ? newVal : '0'}</span>`;
                window.recalculateFooterTotals();
                if (typeof window.showTopToast === 'function') {
                    window.showTopToast('Nilai Jumlah berhasil disimpan.', 'success', 1500);
                }
            })
            .catch(() => {
                cellEl.innerHTML = originalHtml;
            });
        };

        input.addEventListener('blur', finish);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            } else if (e.key === 'Escape') {
                isDone = true;
                cellEl.innerHTML = originalHtml;
            }
        });
    };

    window.editFooterSubtotalCell = function(cellEl) {
        if (!cellEl) return;
        const pId = cellEl.dataset.parentId;
        if (!pId) return;

        const subtotalKey = 'subtotal_' + pId;
        const currentSpan = cellEl.querySelector('.footer-subtotal-val');
        const currentVal = (currentSpan ? currentSpan.innerText : '').trim();

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm text-center font-monospace fw-bold py-0 px-1 border-primary';
        input.value = currentVal;
        input.style.maxWidth = '110px';
        input.placeholder = 'Otomatis';

        const originalHtml = cellEl.innerHTML;
        cellEl.innerHTML = '';
        cellEl.appendChild(input);
        input.focus();
        input.select();

        let isDone = false;
        const finish = () => {
            if (isDone) return;
            isDone = true;
            const newVal = input.value.trim();
            if (newVal === currentVal) {
                cellEl.innerHTML = originalHtml;
                return;
            }

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('action', 'set_footer_override');
            fd.append('table_id', '{{ $activeTableId }}');
            fd.append('col_id', subtotalKey);
            fd.append('value', newVal);

            fetch('{{ route("urusan.columns", $urusan->slug ?: $urusan->id) }}', {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(res => {
                cellEl.dataset.hasOverride = newVal !== '' ? 'true' : 'false';
                cellEl.innerHTML = `<span class="footer-subtotal-val font-monospace fw-bold" style="font-size: 0.86rem;">${newVal !== '' ? newVal : '0'}</span>`;
                window.recalculateFooterTotals();
                if (typeof window.showTopToast === 'function') {
                    window.showTopToast('Nilai Total Gabungan berhasil disimpan.', 'success', 1500);
                }
            })
            .catch(() => {
                cellEl.innerHTML = originalHtml;
            });
        };

        input.addEventListener('blur', finish);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            } else if (e.key === 'Escape') {
                isDone = true;
                cellEl.innerHTML = originalHtml;
            }
        });
    };

    window.editFooterLabel = function(cellEl, rowType) {
        if (!cellEl) return;
        const strongEl = cellEl.querySelector('.footer-label-text') || cellEl.querySelector('strong');
        const currentVal = (strongEl ? strongEl.innerText : '').trim();

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm text-start fw-bold py-0 px-1 border-primary';
        input.value = currentVal;
        input.style.maxWidth = '160px';
        input.placeholder = rowType === 'row1' ? 'JUMLAH' : 'TOTAL';

        const originalHtml = cellEl.innerHTML;
        cellEl.innerHTML = '';
        cellEl.appendChild(input);
        input.focus();
        input.select();

        let isDone = false;
        const finish = () => {
            if (isDone) return;
            isDone = true;
            const newVal = input.value.trim();
            if (newVal === currentVal) {
                cellEl.innerHTML = originalHtml;
                return;
            }

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('action', 'set_footer_override');
            fd.append('table_id', '{{ $activeTableId }}');
            if (rowType === 'row1') {
                fd.append('footer_label', newVal);
            } else {
                fd.append('footer_subtotal_label', newVal);
            }

            fetch('{{ route("urusan.columns", $urusan->slug ?: $urusan->id) }}', {
                method: 'POST',
                body: fd,
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(res => {
                const finalVal = newVal !== '' ? newVal : (rowType === 'row1' ? 'JUMLAH' : 'TOTAL');
                cellEl.innerHTML = `<strong class="text-uppercase footer-label-text" style="letter-spacing: 0.4px;">${finalVal}</strong>`;
                if (typeof window.showTopToast === 'function') {
                    window.showTopToast('Label baris ringkasan berhasil diubah.', 'success', 1500);
                }
            })
            .catch(() => {
                cellEl.innerHTML = originalHtml;
            });
        };

        input.addEventListener('blur', finish);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur();
            } else if (e.key === 'Escape') {
                isDone = true;
                cellEl.innerHTML = originalHtml;
            }
        });
    };

    window.getContextualSubColumnPresets = function(colName, colId) {
        colName = (colName || '').trim();
        const tableName = (window.currentTableName || '').trim();
        const urusanName = (window.currentUrusanName || '').trim();
        const isYear = /^(19|20)\d{2}$/.test(colName);
        const availableYears = (window.availableYears && window.availableYears.length > 0)
            ? window.availableYears.map(String)
            : ['2023', '2024', '2025', '2026'];

        // 1. Sibling column check: does another column in the same table already have sub-columns?
        let siblingPreset = null;
        function findSiblingInTree(cols, excludeId, isYrTarget) {
            if (!cols || !Array.isArray(cols)) return null;
            let yearMatch = null;
            let generalMatch = null;
            for (const c of cols) {
                if (c.id !== excludeId && c.sub_columns && c.sub_columns.length > 0) {
                    const isYr = /^(19|20)\d{2}$/.test((c.name || '').trim());
                    if (isYrTarget && isYr) {
                        return c;
                    }
                    if (!generalMatch) generalMatch = c;
                    if (!yearMatch && isYr) yearMatch = c;
                }
                if (c.sub_columns && Array.isArray(c.sub_columns)) {
                    const subRes = findSiblingInTree(c.sub_columns, excludeId, isYrTarget);
                    if (subRes) {
                        if (isYrTarget && /^(19|20)\d{2}$/.test((subRes.name || '').trim())) return subRes;
                        if (!generalMatch) generalMatch = subRes;
                    }
                }
            }
            return isYrTarget ? (yearMatch || generalMatch) : generalMatch;
        }
        const siblingCol = findSiblingInTree(window.existingColumns || [], colId, isYear);
        if (siblingCol) {
            const sibSubs = siblingCol.sub_columns.map(s => s.name);
            siblingPreset = {
                id: 'sibling',
                category: 'Tiru Kolom Lain',
                title: `Tiru dari Kolom "${siblingCol.name}"`,
                description: sibSubs.join(', '),
                names: sibSubs,
                icon: 'bi-copy',
                isYearSibling: /^(19|20)\d{2}$/.test((siblingCol.name || '').trim())
            };
        }

        // 2. Build combined context string for semantic detection
        const ctx = (colName + ' ' + tableName + ' ' + urusanName).toLowerCase();
        const colLower = colName.toLowerCase();
        const isRealisasiOrTarget = ['realisasi', 'target', 'capaian', 'progres', 'kinerja', 'output'].some(k => colLower.includes(k));

        let detectedCategory = 'Kinerja & Target';
        let recommendations = [];

        // Check matching rules according to table, urusan, and column context
        if (/(perikanan|ikan|rtp|budidaya|tangkap|kolam|sawah|tambak|laut|perairan)/i.test(ctx)) {
            detectedCategory = 'Perikanan & Budidaya';
            recommendations.push({
                title: 'Tempat / Media Budidaya (Kolam & Sawah)',
                description: 'Kolam, Sawah',
                names: ['Kolam', 'Sawah'],
                icon: 'bi-water'
            });
            recommendations.push({
                title: 'Media Budidaya + Jumlah',
                description: 'Kolam, Sawah, Jumlah',
                names: ['Kolam', 'Sawah', 'Jumlah'],
                icon: 'bi-water'
            });
            recommendations.push({
                title: 'Media Budidaya Lengkap',
                description: 'Kolam, Sawah, Keramba, Tambak, Jumlah',
                names: ['Kolam', 'Sawah', 'Keramba', 'Tambak', 'Jumlah'],
                icon: 'bi-water'
            });
        } else if (isRealisasiOrTarget || ctx.includes('kumuh') || ctx.includes('kegiatan') || ctx.includes('program')) {
            detectedCategory = 'Target & Realisasi';
            if (isRealisasiOrTarget && !isYear) {
                recommendations.push({
                    title: 'Tahun-Tahun Aktif & Jumlah',
                    description: [...availableYears, 'Jumlah'].join(', '),
                    names: [...availableYears, 'Jumlah'],
                    icon: 'bi-calendar3'
                });
                recommendations.push({
                    title: 'Target, Realisasi & Capaian',
                    description: 'Target, Realisasi, Capaian (%)',
                    names: ['Target', 'Realisasi', 'Capaian (%)'],
                    icon: 'bi-bullseye'
                });
            } else {
                recommendations.push({
                    title: 'Target, Realisasi & Capaian',
                    description: 'Target, Realisasi, Capaian (%)',
                    names: ['Target', 'Realisasi', 'Capaian (%)'],
                    icon: 'bi-bullseye'
                });
                recommendations.push({
                    title: 'Fisik & Keuangan',
                    description: 'Fisik (%), Keuangan (Rp), Capaian (%)',
                    names: ['Fisik (%)', 'Keuangan (Rp)', 'Capaian (%)'],
                    icon: 'bi-cash-stack'
                });
            }
        } else if (/(siswa|murid|peserta didik|gender|kelamin|penduduk|kependudukan|jiwa|warga|anak)/i.test(ctx)) {
            detectedCategory = 'Gender & Kependudukan';
            recommendations.push({
                title: 'Jenis Kelamin (Laki-laki & Perempuan)',
                description: 'Laki-laki, Perempuan, Jumlah',
                names: ['Laki-laki', 'Perempuan', 'Jumlah'],
                icon: 'bi-people-fill'
            });
            recommendations.push({
                title: 'Kelompok Usia',
                description: 'Usia <18, Usia 18-59, Usia 60+, Jumlah',
                names: ['Usia <18', 'Usia 18-59', 'Usia 60+', 'Jumlah'],
                icon: 'bi-person-badge'
            });
        } else if (/(guru|pendidik|pegawai|asn|pns|pppk|dosen|karyawan)/i.test(ctx)) {
            detectedCategory = 'Pendidikan & Kepegawaian';
            recommendations.push({
                title: 'Status Sekolah (Negeri & Swasta)',
                description: 'Negeri, Swasta, Jumlah',
                names: ['Negeri', 'Swasta', 'Jumlah'],
                icon: 'bi-mortarboard-fill'
            });
            recommendations.push({
                title: 'Status Kepegawaian (PNS, PPPK, Honorer)',
                description: 'PNS, PPPK, Non-PNS, Jumlah',
                names: ['PNS', 'PPPK', 'Non-PNS', 'Jumlah'],
                icon: 'bi-briefcase-fill'
            });
            recommendations.push({
                title: 'Jenis Kelamin',
                description: 'Laki-laki, Perempuan, Jumlah',
                names: ['Laki-laki', 'Perempuan', 'Jumlah'],
                icon: 'bi-people'
            });
        } else if (/(sekolah|madrasah|paud|tk|sd|smp|sma|smk|pesantren|pendidikan)/i.test(ctx)) {
            detectedCategory = 'Pendidikan & Sekolah';
            recommendations.push({
                title: 'Status Sekolah (Negeri & Swasta)',
                description: 'Negeri, Swasta, Jumlah',
                names: ['Negeri', 'Swasta', 'Jumlah'],
                icon: 'bi-mortarboard-fill'
            });
            recommendations.push({
                title: 'Status Akreditasi',
                description: 'Akreditasi A, Akreditasi B, Akreditasi C, Belum, Jumlah',
                names: ['Akreditasi A', 'Akreditasi B', 'Akreditasi C', 'Belum Terakreditasi', 'Jumlah'],
                icon: 'bi-award-fill'
            });
        } else if (/(kesehatan|medis|nakes|faskes|rumah sakit|rsud|puskesmas|klinik|posyandu|dokter|perawat|bidan)/i.test(ctx)) {
            detectedCategory = 'Kesehatan & Fasilitas Medis';
            recommendations.push({
                title: 'Kepemilikan Faskes (Pemerintah & Swasta)',
                description: 'Pemerintah, Swasta, Jumlah',
                names: ['Pemerintah', 'Swasta', 'Jumlah'],
                icon: 'bi-hospital-fill'
            });
            recommendations.push({
                title: 'Layanan Faskes (Rawat Inap & Jalan)',
                description: 'Rawat Inap, Rawat Jalan, Jumlah',
                names: ['Rawat Inap', 'Rawat Jalan', 'Jumlah'],
                icon: 'bi-heart-pulse-fill'
            });
            recommendations.push({
                title: 'Profesi Tenaga Medis',
                description: 'Dokter, Perawat, Bidan, Tenaga Medis Lain, Jumlah',
                names: ['Dokter', 'Perawat', 'Bidan', 'Tenaga Medis Lain', 'Jumlah'],
                icon: 'bi-person-lines-fill'
            });
        } else if (/(gedung kantor|kantor pemerintah|opd|kecamatan|kelurahan|pemerintahan)/i.test(ctx)) {
            detectedCategory = 'Gedung Kantor Pemerintah';
            recommendations.push({
                title: 'Tingkat Instansi (OPD, Kecamatan, Kelurahan)',
                description: 'OPD, Kecamatan, Kelurahan, Jumlah',
                names: ['OPD', 'Kecamatan', 'Kelurahan', 'Jumlah'],
                icon: 'bi-building-fill-check'
            });
            recommendations.push({
                title: 'Kondisi Fisik Gedung',
                description: 'Rusak Berat, Rusak Sedang, Rusak Ringan, Jumlah',
                names: ['Rusak Berat', 'Rusak Sedang', 'Rusak Ringan', 'Jumlah'],
                icon: 'bi-tools'
            });
        } else if (/(ruang kelas|kerusakan ruang|sarpras|kondisi ruang)/i.test(ctx)) {
            detectedCategory = 'Kondisi Ruang Kelas';
            recommendations.push({
                title: 'Kondisi Kerusakan (Lengkap)',
                description: 'Rusak Berat, Rusak Sedang, Rusak Ringan, Jumlah',
                names: ['Rusak Berat', 'Rusak Sedang', 'Rusak Ringan', 'Jumlah'],
                icon: 'bi-tools'
            });
            recommendations.push({
                title: 'Kondisi (Singkatan)',
                description: 'RB, RS, RR, Jmlh',
                names: ['RB', 'RS', 'RR', 'Jmlh'],
                icon: 'bi-alphabet-uppercase'
            });
        } else if (/(jalan|jembatan|irigasi|drainase|infrastruktur|pupr|pekerjaan umum)/i.test(ctx)) {
            detectedCategory = 'Infrastruktur & Pekerjaan Umum';
            recommendations.push({
                title: 'Kemantapan Fisik / Kondisi Jalan',
                description: 'Baik, Sedang, Rusak Ringan, Rusak Berat, Jumlah',
                names: ['Baik', 'Sedang', 'Rusak Ringan', 'Rusak Berat', 'Jumlah'],
                icon: 'bi-signpost-split-fill'
            });
            recommendations.push({
                title: 'Target & Realisasi',
                description: 'Target, Realisasi, Capaian (%)',
                names: ['Target', 'Realisasi', 'Capaian (%)'],
                icon: 'bi-bullseye'
            });
        } else if (/(umkm|koperasi|usaha|pasar|pedagang|ekonomi|industri)/i.test(ctx)) {
            detectedCategory = 'Ekonomi & UMKM';
            recommendations.push({
                title: 'Skala Usaha',
                description: 'Mikro, Kecil, Menengah, Jumlah',
                names: ['Mikro', 'Kecil', 'Menengah', 'Jumlah'],
                icon: 'bi-shop'
            });
            recommendations.push({
                title: 'Status Keaktifan',
                description: 'Aktif, Tidak Aktif, Jumlah',
                names: ['Aktif', 'Tidak Aktif', 'Jumlah'],
                icon: 'bi-check-circle'
            });
        } else {
            if (isYear) {
                detectedCategory = 'Kinerja Per Tahun';
                recommendations.push({
                    title: 'Target & Realisasi',
                    description: 'Target, Realisasi, Capaian (%)',
                    names: ['Target', 'Realisasi', 'Capaian (%)'],
                    icon: 'bi-bullseye'
                });
            } else {
                detectedCategory = 'Kustom Sesuai Tabel';
            }
        }

        return {
            detectedCategory,
            siblingPreset,
            recommendations,
            isYear
        };
    };

    window.clearSubColumnInputs = function() {
        const container = document.getElementById('subColumnInputsContainer');
        if (!container) return;
        container.innerHTML = '';
        window.addSubColumnInput('', null, true);
        window.addSubColumnInput('', null, true);
    };

    window.renderSubColumnRecommendations = function(contextData) {
        const box = document.getElementById('subColRecommendationBox');
        const badge = document.getElementById('subRecBadgeText');
        const btnContainer = document.getElementById('subRecButtonsContainer');
        if (!box || !btnContainer) return;

        if (badge) {
            badge.innerText = contextData.detectedCategory || 'Sesuai Tabel';
        }

        btnContainer.innerHTML = '';

        // If sibling preset exists, prioritize it with an easy 1-click button
        if (contextData.siblingPreset) {
            const sib = contextData.siblingPreset;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-sm btn-outline-primary text-start d-flex align-items-center justify-content-between py-1.5 px-3 rounded-2 bg-white shadow-2xs';
            btn.innerHTML = `
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <i class="bi ${sib.icon} text-primary fs-6"></i>
                    <div>
                        <span class="fw-bold text-dark d-block text-truncate" style="font-size: 0.82rem;">${sib.title}</span>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">${sib.description}</small>
                    </div>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill ms-2 text-nowrap" style="font-size: 0.68rem;">
                    <i class="bi bi-magic me-0.5"></i> Terapkan
                </span>
            `;
            btn.onclick = () => window.applySubPreset(sib.names);
            btnContainer.appendChild(btn);
        }

        // Recommendations based on table and column
        if (contextData.recommendations && contextData.recommendations.length > 0) {
            contextData.recommendations.forEach((rec, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                const isFirst = (idx === 0 && !contextData.siblingPreset);
                btn.className = `btn btn-sm ${isFirst ? 'btn-outline-success border-success bg-white' : 'btn-outline-secondary bg-white'} text-start d-flex align-items-center justify-content-between py-1.5 px-3 rounded-2 shadow-2xs`;
                btn.innerHTML = `
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <i class="bi ${rec.icon} ${isFirst ? 'text-success' : 'text-secondary'} fs-6"></i>
                        <div>
                            <span class="fw-semibold text-dark d-block text-truncate" style="font-size: 0.82rem;">${rec.title}</span>
                            <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">${rec.description}</small>
                        </div>
                    </div>
                    <span class="badge ${isFirst ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border'} px-2 py-0.5 rounded-pill ms-2 text-nowrap" style="font-size: 0.68rem;">
                        ${isFirst ? 'Paling Sesuai' : 'Pilih'}
                    </span>
                `;
                btn.onclick = () => window.applySubPreset(rec.names);
                btnContainer.appendChild(btn);
            });
        } else if (!contextData.siblingPreset) {
            const emptyNotice = document.createElement('div');
            emptyNotice.className = 'small text-muted py-1';
            emptyNotice.innerHTML = '<i class="bi bi-pencil-square me-1"></i> Belum ada preset khusus untuk kolom ini. Anda dapat mengetik nama sub-kolom kustom secara bebas di bawah.';
            btnContainer.appendChild(emptyNotice);
        }

        // Update year preset desc if available
        const yearPresetDesc = document.getElementById('descPresetTahunAktif');
        if (yearPresetDesc && window.availableYears && window.availableYears.length > 0) {
            const yearsWithTotal = [...window.availableYears.map(String), 'Jumlah'];
            yearPresetDesc.innerText = yearsWithTotal.join(', ');
            const btnYears = document.getElementById('btnPresetTahunAktif');
            if (btnYears) {
                btnYears.onclick = () => window.applySubPreset(yearsWithTotal);
            }
        }
    };

    function findColInTree(cols, targetId) {
        if (!cols || !Array.isArray(cols)) return null;
        for (const col of cols) {
            if (col.id === targetId) return col;
            if (col.sub_columns && Array.isArray(col.sub_columns)) {
                const found = findColInTree(col.sub_columns, targetId);
                if (found) return found;
            }
        }
        return null;
    }

    window.openSubColumnModal = function(colId, colName) {
        const inputParentId = document.getElementById('subModalParentColId');
        const spanColName = document.getElementById('subModalColName');
        if (inputParentId) inputParentId.value = colId;
        if (spanColName) spanColName.innerText = colName;

        const colObj = findColInTree(window.existingColumns || [], colId);
        const existingSubs = (colObj && colObj.sub_columns) ? colObj.sub_columns.map(s => s.name) : [];

        // Contextual presets detection for this specific table & column
        const contextData = window.getContextualSubColumnPresets(colName, colId);
        window.renderSubColumnRecommendations(contextData);

        const container = document.getElementById('subColumnInputsContainer');
        if (container) {
            container.innerHTML = '';
            const btnHapus = document.getElementById('btnHapusSemuaSub');
            if (existingSubs.length > 0) {
                if (btnHapus) btnHapus.style.display = 'inline-block';
                existingSubs.forEach(name => window.addSubColumnInput(name));
            } else {
                if (btnHapus) btnHapus.style.display = 'none';

                // Contextual auto-fill according to table & column (no more hardcoded damage presets!)
                if (contextData.siblingPreset && contextData.isYear && contextData.siblingPreset.isYearSibling) {
                    window.applySubPreset(contextData.siblingPreset.names);
                } else if (contextData.recommendations && contextData.recommendations.length > 0) {
                    window.applySubPreset(contextData.recommendations[0].names);
                } else {
                    window.addSubColumnInput('', null, true);
                    window.addSubColumnInput('', null, true);
                }
            }
        }

        const boxAll = document.getElementById('boxApplyAllYears');
        const isYear = /^(19|20)\d{2}$/.test(colName.trim());
        if (boxAll) {
            boxAll.style.display = isYear ? 'block' : 'none';
        }

        const modalEl = document.getElementById('modalSubKolom');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };

    window.deleteSubColDirect = function(subId, subName) {
        const cleanName = (subName || '').trim();
        const safeSubName = cleanName.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        window.showModernConfirm({
            title: `Hapus Sub-Kolom &ldquo;${safeSubName}&rdquo;?`,
            htmlMessage: `Apakah Anda yakin ingin menghapus sub-kolom <strong>&ldquo;${safeSubName}&rdquo;</strong>? Data yang sudah diisi di sub-kolom ini akan ikut terhapus.`,
            confirmText: 'Ya, Hapus',
            confirmIcon: 'bi-trash3-fill',
            cancelText: 'Batal',
            onConfirm: function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}';
                
                const appendField = (n, v) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = n;
                    inp.value = v;
                    form.appendChild(inp);
                };

                appendField('_token', '{{ csrf_token() }}');
                appendField('action', 'delete_sub_column');
                appendField('table_id', '{{ $activeTableId }}');
                appendField('sub_col_id', subId);
                appendField('sub_col_name', subName);

                document.body.appendChild(form);
                if (typeof window.submitSpaForm === 'function') {
                    window.submitSpaForm(form);
                } else {
                    form.submit();
                }
            }
        });
    };

    var draggedSubItem = null;

    function handleSubDragStart(e) {
        draggedSubItem = this;
        this.classList.add('is-dragging');
        if (e.dataTransfer) {
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');
        }
    }

    function handleSubDragOver(e) {
        e.preventDefault();
        if (e.dataTransfer) {
            e.dataTransfer.dropEffect = 'move';
        }
        if (!draggedSubItem || draggedSubItem === this) return;

        const container = this.parentNode;
        const bounding = this.getBoundingClientRect();
        const offset = e.clientY - bounding.top;
        if (offset > bounding.height / 2) {
            container.insertBefore(draggedSubItem, this.nextSibling);
        } else {
            container.insertBefore(draggedSubItem, this);
        }
        if (this.classList.contains('sub-col-item')) {
            window.renumberSubCols();
        } else if (this.classList.contains('sub-baris-item')) {
            window.renumberSubBaris();
        }
    }

    function handleSubDragEnd(e) {
        this.classList.remove('is-dragging');
        draggedSubItem = null;
        if (this.classList.contains('sub-col-item')) {
            window.renumberSubCols();
        } else if (this.classList.contains('sub-baris-item')) {
            window.renumberSubBaris();
        }
    }

    function highlightMovedSubItem(el) {
        if (!el) return;
        el.classList.add('item-highlight-pulse');
        setTimeout(() => el.classList.remove('item-highlight-pulse'), 500);
        const inp = el.querySelector('input');
        if (inp) inp.focus();
    }

    window.moveSubCol = function(btn, direction) {
        const item = btn.closest('.sub-col-item');
        if (!item) return;
        const container = item.parentNode;
        if (!container) return;

        if (direction === 'up') {
            const prev = item.previousElementSibling;
            if (prev && prev.classList.contains('sub-col-item')) {
                container.insertBefore(item, prev);
                window.renumberSubCols();
                highlightMovedSubItem(item);
            }
        } else if (direction === 'down') {
            const next = item.nextElementSibling;
            if (next && next.classList.contains('sub-col-item')) {
                container.insertBefore(item, next.nextSibling);
                window.renumberSubCols();
                highlightMovedSubItem(item);
            }
        }
    };

    window.applySubPreset = function(names) {
        const container = document.getElementById('subColumnInputsContainer');
        if (!container) return;
        container.innerHTML = '';
        names.forEach(name => window.addSubColumnInput(name, null, true));
    };

    window.addSubColumnInput = function(val, insertBeforeEl = null, isPreset = false) {
        const container = document.getElementById('subColumnInputsContainer');
        if (!container) return;

        let autoVal = (val !== undefined && val !== null) ? val : '';
        const items = Array.from(container.querySelectorAll('.sub-col-item'));

        // Jika user klik tombol "+ Tambah Sub-Kolom" (tanpa nilai & bukan dari preset)
        if (!autoVal && !isPreset && !insertBeforeEl && items.length > 0) {
            const lastItem = items[items.length - 1];
            const lastInput = lastItem ? lastItem.querySelector('input[name="sub_names[]"]') : null;
            const lastVal = lastInput ? lastInput.value.trim().toLowerCase() : '';

            // Otomatis geser ke ATAS Jumlah jika kolom terakhir adalah Jumlah / Total
            if (['jumlah', 'total', 'jml', 'jmlh'].includes(lastVal)) {
                insertBeforeEl = lastItem;

                // Cek tahun sebelumnya (misal 2025 -> otomatis sarankan 2026)
                const prevEl = lastItem.previousElementSibling;
                if (prevEl) {
                    const prevInp = prevEl.querySelector('input[name="sub_names[]"]');
                    const prevVal = prevInp ? prevInp.value.trim() : '';
                    if (/^(19|20)\d{2}$/.test(prevVal)) {
                        autoVal = (parseInt(prevVal) + 1).toString();
                    }
                }
            } else if (/^(19|20)\d{2}$/.test(lastVal)) {
                // Jika item terakhir berupa tahun, sarankan tahun berikutnya
                autoVal = (parseInt(lastVal) + 1).toString();
            }
        }

        const div = document.createElement('div');
        div.className = 'd-flex align-items-center gap-2 sub-col-item bg-white p-1 rounded-2 border shadow-2xs';
        div.draggable = true;
        div.innerHTML = `
            <span class="drag-handle text-muted opacity-50 px-1" style="cursor: grab;" title="Tahan dan seret untuk ubah urutan">
                <i class="bi bi-grip-vertical"></i>
            </span>
            <span class="badge bg-secondary opacity-75 font-monospace text-center" style="width: 26px;">1</span>
            <input type="text" name="sub_names[]" class="form-control form-control-sm shadow-none fw-semibold" placeholder="Nama Sub-Kolom..." value="${autoVal}" required>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-1.5 border-0 btn-sub-up" onclick="window.moveSubCol(this, 'up')" title="Geser ke Atas">
                    <i class="bi bi-chevron-up"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-1.5 border-0 btn-sub-down" onclick="window.moveSubCol(this, 'down')" title="Geser ke Bawah">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2 border-0" onclick="this.closest('.sub-col-item').remove(); window.renumberSubCols();" title="Hapus item ini">
                <i class="bi bi-trash3 text-danger"></i>
            </button>
        `;

        div.addEventListener('dragstart', handleSubDragStart);
        div.addEventListener('dragover', handleSubDragOver);
        div.addEventListener('dragend', handleSubDragEnd);

        if (insertBeforeEl && insertBeforeEl.parentNode === container) {
            container.insertBefore(div, insertBeforeEl);
        } else {
            container.appendChild(div);
        }

        window.renumberSubCols();

        const input = div.querySelector('input');
        if (input) {
            input.focus();
            if (autoVal) input.select();
        }
    };

    window.renumberSubCols = function() {
        const container = document.getElementById('subColumnInputsContainer');
        if (!container) return;
        const items = container.querySelectorAll('.sub-col-item');
        items.forEach((item, idx) => {
            const badge = item.querySelector('.badge');
            if (badge) badge.innerText = idx + 1;

            const btnUp = item.querySelector('.btn-sub-up');
            const btnDown = item.querySelector('.btn-sub-down');
            if (btnUp) {
                btnUp.disabled = (idx === 0);
                btnUp.style.opacity = (idx === 0) ? '0.3' : '1';
                btnUp.style.cursor = (idx === 0) ? 'not-allowed' : 'pointer';
            }
            if (btnDown) {
                btnDown.disabled = (idx === items.length - 1);
                btnDown.style.opacity = (idx === items.length - 1) ? '0.3' : '1';
                btnDown.style.cursor = (idx === items.length - 1) ? 'not-allowed' : 'pointer';
            }
        });
    };

    window.clearAllSubColumns = function() {
        window.showModernConfirm({
            title: 'Hapus Seluruh Sub-Kolom?',
            htmlMessage: 'Apakah Anda yakin ingin menghapus seluruh sub-kolom dari kolom ini dan mengembalikannya menjadi kolom standar?',
            confirmText: 'Ya, Hapus',
            confirmIcon: 'bi-trash3-fill',
            cancelText: 'Batal',
            onConfirm: function() {
                const container = document.getElementById('subColumnInputsContainer');
                if (container) container.innerHTML = '';
                const form = document.getElementById('formSubKolom');
                if (typeof window.submitSpaForm === 'function') {
                    window.submitSpaForm(form);
                } else {
                    form.submit();
                }
            }
        });
    };

    window.currentParentRowName = '';
    window.openSubRowModal = function(indId, indName) {
        window.currentParentRowName = indName || '';
        const inputParentId = document.getElementById('subBarisParentId');
        const spanParentName = document.getElementById('subBarisParentName');
        const form = document.getElementById('formSubBaris');
        if (inputParentId) inputParentId.value = indId;
        if (spanParentName) spanParentName.innerText = indName;
        if (form) form.action = '/indikator/' + indId + '/sub-rows';

        const descSchool = document.getElementById('presetSchoolDesc');
        if (descSchool) {
            descSchool.innerText = `${indName} Negeri, ${indName} Swasta`;
        }
        const descHealth = document.getElementById('presetHealthDesc');
        if (descHealth) {
            descHealth.innerText = `${indName} Rawat Inap, ${indName} Rawat Jalan`;
        }

        const container = document.getElementById('subBarisInputsContainer');
        if (container) {
            container.innerHTML = '';
            const lower = (indName || '').toLowerCase();
            if (lower.includes('klinik') || lower.includes('rs') || lower.includes('rumah sakit') || lower.includes('puskesmas') || lower.includes('kesehatan') || lower.includes('pasien')) {
                window.applySubBarisPresetHealth();
            } else {
                window.applySubBarisPresetSchool();
            }
        }

        const modalEl = document.getElementById('modalSubBaris');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };

    window.applySubBarisPresetHealth = function() {
        const base = (window.currentParentRowName || '').trim();
        const n1 = base ? (base + ' Rawat Inap') : 'Rawat Inap';
        const n2 = base ? (base + ' Rawat Jalan') : 'Rawat Jalan';
        window.applySubBarisPreset([n1, n2]);
    };

    window.applySubBarisPresetGov = function() {
        const base = (window.currentParentRowName || '').trim();
        const n1 = base ? (base + ' Pemerintah') : 'Pemerintah';
        const n2 = base ? (base + ' Swasta') : 'Swasta';
        window.applySubBarisPreset([n1, n2]);
    };

    window.applySubBarisPresetSchool = function() {
        const base = (window.currentParentRowName || '').trim();
        const n1 = base ? (base + ' Negeri') : 'Negeri';
        const n2 = base ? (base + ' Swasta') : 'Swasta';
        window.applySubBarisPreset([n1, n2]);
    };

    window.applySubBarisPreset = function(names) {
        const container = document.getElementById('subBarisInputsContainer');
        if (!container) return;
        container.innerHTML = '';
        names.forEach(name => window.addSubBarisInput(name));
    };

    window.moveSubBaris = function(btn, direction) {
        const item = btn.closest('.sub-baris-item');
        if (!item) return;
        const container = item.parentNode;
        if (!container) return;

        if (direction === 'up') {
            const prev = item.previousElementSibling;
            if (prev && prev.classList.contains('sub-baris-item')) {
                container.insertBefore(item, prev);
                window.renumberSubBaris();
                highlightMovedSubItem(item);
            }
        } else if (direction === 'down') {
            const next = item.nextElementSibling;
            if (next && next.classList.contains('sub-baris-item')) {
                container.insertBefore(item, next.nextSibling);
                window.renumberSubBaris();
                highlightMovedSubItem(item);
            }
        }
    };

    window.addSubBarisInput = function(val, insertBeforeEl = null) {
        const container = document.getElementById('subBarisInputsContainer');
        if (!container) return;
        const div = document.createElement('div');
        div.className = 'd-flex align-items-center gap-2 sub-baris-item bg-white p-1 rounded-2 border shadow-2xs';
        div.draggable = true;
        div.innerHTML = `
            <span class="drag-handle text-muted opacity-50 px-1" style="cursor: grab;" title="Tahan dan seret untuk ubah urutan">
                <i class="bi bi-grip-vertical"></i>
            </span>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace text-center" style="width: 26px;">1</span>
            <input type="text" name="sub_names[]" class="form-control form-control-sm shadow-none fw-semibold" placeholder="Nama Sub-Baris..." value="${val || ''}" required>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-1.5 border-0 btn-sub-up" onclick="window.moveSubBaris(this, 'up')" title="Geser ke Atas">
                    <i class="bi bi-chevron-up"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-1.5 border-0 btn-sub-down" onclick="window.moveSubBaris(this, 'down')" title="Geser ke Bawah">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-2 border-0" onclick="this.closest('.sub-baris-item').remove(); window.renumberSubBaris();" title="Hapus item ini">
                <i class="bi bi-trash3 text-danger"></i>
            </button>
        `;

        div.addEventListener('dragstart', handleSubDragStart);
        div.addEventListener('dragover', handleSubDragOver);
        div.addEventListener('dragend', handleSubDragEnd);

        if (insertBeforeEl && insertBeforeEl.parentNode === container) {
            container.insertBefore(div, insertBeforeEl);
        } else {
            container.appendChild(div);
        }

        window.renumberSubBaris();
        const input = div.querySelector('input');
        if (input) {
            input.focus();
            if (val) input.select();
        }
    };

    window.renumberSubBaris = function() {
        const container = document.getElementById('subBarisInputsContainer');
        if (!container) return;
        const items = container.querySelectorAll('.sub-baris-item');
        items.forEach((item, idx) => {
            const badge = item.querySelector('.badge');
            if (badge) badge.innerText = idx + 1;

            const btnUp = item.querySelector('.btn-sub-up');
            const btnDown = item.querySelector('.btn-sub-down');
            if (btnUp) {
                btnUp.disabled = (idx === 0);
                btnUp.style.opacity = (idx === 0) ? '0.3' : '1';
                btnUp.style.cursor = (idx === 0) ? 'not-allowed' : 'pointer';
            }
            if (btnDown) {
                btnDown.disabled = (idx === items.length - 1);
                btnDown.style.opacity = (idx === items.length - 1) ? '0.3' : '1';
                btnDown.style.cursor = (idx === items.length - 1) ? 'not-allowed' : 'pointer';
            }
        });
    };

    function showCellSuccess(el) {
        el.classList.add('cell-success-pulse');
        setTimeout(() => {
            el.classList.remove('cell-success-pulse');
        }, 1000);
    }

    // ========================================================
    // RESIZABLE TABLE COLUMNS & ROWS (GARIS BISA DIGESER)
    // ========================================================
    function initTableResizing() {
        const table = document.querySelector('.custom-data-table');
        if (!table) return;

        const tableWrapper = table.closest('.table-resizable-wrap') || table.closest('.table-responsive');
        if (!tableWrapper) return;

        // 1. Ensure guide line elements exist in wrapper
        let guideCol = tableWrapper.querySelector('.table-resize-guide-col');
        if (!guideCol) {
            guideCol = document.createElement('div');
            guideCol.className = 'table-resize-guide-col';
            tableWrapper.appendChild(guideCol);
        }
        let guideRow = tableWrapper.querySelector('.table-resize-guide-row');
        if (!guideRow) {
            guideRow = document.createElement('div');
            guideRow.className = 'table-resize-guide-row';
            tableWrapper.appendChild(guideRow);
        }

        // Hapus cache ukuran lama yang tidak terisolasi per akun pengguna & hapus flag is_reset yang mengganggu
        try {
            Object.keys(localStorage).forEach(key => {
                if (key.includes('is_reset') || (key.startsWith('tbl_resize_u') && !key.startsWith('tbl_resize_usr_'))) {
                    localStorage.removeItem(key);
                }
            });
        } catch(e) {}

        const currentUserId = '{{ auth()->check() ? auth()->id() : 'guest' }}';
        const storagePrefix = 'tbl_resize_usr_' + currentUserId + '_u{{ $urusan->id }}_t{{ $activeTableId }}_';

        // ----------------------------------------------------
        // A. RESIZE KOLOM (GARIS VERTIKAL BISA DIGESER)
        // ----------------------------------------------------
        const thead = table.querySelector('thead');
        if (!thead) return;

        const headerRows = thead.querySelectorAll('tr');
        let leafThs = [];

        if (headerRows.length === 1) {
            leafThs = Array.from(headerRows[0].querySelectorAll('th'));
        } else if (headerRows.length >= 2) {
            const r1Ths = Array.from(headerRows[0].querySelectorAll('th'));
            const r2Ths = Array.from(headerRows[1].querySelectorAll('th'));
            let r2Index = 0;

            r1Ths.forEach(th => {
                const cs = parseInt(th.getAttribute('colspan') || '1', 10);
                if (cs > 1) {
                    for (let c = 0; c < cs; c++) {
                        if (r2Ths[r2Index]) {
                            leafThs.push(r2Ths[r2Index]);
                            r2Index++;
                        }
                    }
                } else {
                    leafThs.push(th);
                }
            });
        }

        leafThs.forEach((th, colIdx) => {
            // Kolom aksi tidak boleh di-resize agar ukurannya tetap fix 78px dan sticky di kanan
            if (th.classList.contains('sticky-action-col') || th.dataset.colId === 'col_aksi') {
                return;
            }

            const colKey = th.dataset.colId || ('col_' + colIdx);

            // Restore saved width if any
            const savedW = localStorage.getItem(storagePrefix + 'w_' + colKey);
            if (savedW) {
                applyColumnWidth(colIdx, parseInt(savedW, 10));
            }

            // Inject resizer handle on right border
            let colResizer = th.querySelector(':scope > .col-resizer-line');
            if (!colResizer) {
                colResizer = document.createElement('div');
                colResizer.className = 'col-resizer-line';
                colResizer.title = 'Geser garis kolom untuk mengatur lebar (Klik 2x untuk merapikan otomatis & simpan)';
                th.appendChild(colResizer);
            }

            // Double click untuk merapikan otomatis (auto-fit) dan MENYIMPANNYA ke localStorage
            colResizer.ondblclick = function(e) {
                e.stopPropagation();
                autoFitColumn(colIdx);
            };

            // Drag to resize column
            colResizer.onmousedown = function(e) {
                e.preventDefault();
                e.stopPropagation();

                const startX = e.pageX;
                const startW = th.offsetWidth;
                colResizer.classList.add('is-active');
                document.body.classList.add('is-resizing-col');

                const wrapRect = tableWrapper.getBoundingClientRect();
                guideCol.style.display = 'block';
                guideCol.style.left = (e.pageX - wrapRect.left + tableWrapper.scrollLeft) + 'px';
                guideCol.style.top = '0px';
                guideCol.style.height = table.offsetHeight + 'px';

                function onColMouseMove(ev) {
                    const diff = ev.pageX - startX;
                    const newW = Math.max(35, startW + diff);

                    guideCol.style.left = (ev.pageX - wrapRect.left + tableWrapper.scrollLeft) + 'px';
                    applyColumnWidth(colIdx, newW);
                }

                function onColMouseUp(ev) {
                    document.removeEventListener('mousemove', onColMouseMove);
                    document.removeEventListener('mouseup', onColMouseUp);

                    colResizer.classList.remove('is-active');
                    document.body.classList.remove('is-resizing-col');
                    guideCol.style.display = 'none';

                    const finalW = th.offsetWidth;
                    localStorage.setItem(storagePrefix + 'w_' + colKey, finalW);
                }

                document.addEventListener('mousemove', onColMouseMove);
                document.addEventListener('mouseup', onColMouseUp);
            };
        });

        function applyColumnWidth(cIdx, widthPx) {
            const leafTh = leafThs[cIdx];
            if (leafTh) {
                leafTh.style.width = widthPx + 'px';
                leafTh.style.minWidth = widthPx + 'px';
                leafTh.style.maxWidth = widthPx + 'px';
            }
            const tds = table.querySelectorAll('tbody tr td:nth-child(' + (cIdx + 1) + '), tfoot tr td:nth-child(' + (cIdx + 1) + ')');
            tds.forEach(td => {
                td.style.width = widthPx + 'px';
                td.style.minWidth = widthPx + 'px';
                td.style.maxWidth = widthPx + 'px';
            });
        }

        function resetColumnWidth(cIdx) {
            const leafTh = leafThs[cIdx];
            if (leafTh) {
                leafTh.style.removeProperty('width');
                leafTh.style.removeProperty('min-width');
                leafTh.style.removeProperty('max-width');
            }
            const tds = table.querySelectorAll('tbody tr td:nth-child(' + (cIdx + 1) + '), tfoot tr td:nth-child(' + (cIdx + 1) + ')');
            tds.forEach(td => {
                td.style.removeProperty('width');
                td.style.removeProperty('min-width');
                td.style.removeProperty('max-width');
            });
        }

        function autoFitColumn(cIdx) {
            const leafTh = leafThs[cIdx];
            if (!leafTh) return;

            const colKey = leafTh.dataset.colId || ('col_' + cIdx);

            // Bersihkan properti sementara untuk mengukur lebar alami konten
            leafTh.style.removeProperty('width');
            leafTh.style.removeProperty('min-width');
            leafTh.style.removeProperty('max-width');

            const tds = table.querySelectorAll('tbody tr td:nth-child(' + (cIdx + 1) + '), tfoot tr td:nth-child(' + (cIdx + 1) + ')');
            tds.forEach(td => {
                td.style.removeProperty('width');
                td.style.removeProperty('min-width');
                td.style.removeProperty('max-width');
            });

            // Hitung lebar maksimal konten di kolom ini (header + seluruh sel isi)
            let maxContentW = leafTh.scrollWidth;
            const thInner = leafTh.querySelector('div, span');
            if (thInner) {
                maxContentW = Math.max(maxContentW, thInner.scrollWidth + 24);
            }

            tds.forEach(td => {
                const cellInner = td.querySelector('.cell-wrapper') || td.querySelector('div') || td;
                const cellW = (cellInner.scrollWidth || td.scrollWidth) + 20;
                if (cellW > maxContentW) {
                    maxContentW = cellW;
                }
            });

            let minAllowed = 45;
            const thText = (leafTh.textContent || '').toLowerCase().trim();
            if (thText.includes('uraian') || thText.includes('indikator') || thText.includes('nama')) {
                minAllowed = 120;
            } else if (thText.includes('satuan')) {
                minAllowed = 75;
            } else if (thText.includes('no')) {
                minAllowed = 45;
            } else {
                minAllowed = 68;
            }

            const finalW = Math.max(minAllowed, Math.ceil(maxContentW));

            // Terapkan dan SIMPAN permanen ke localStorage
            applyColumnWidth(cIdx, finalW);
            localStorage.setItem(storagePrefix + 'w_' + colKey, finalW);

            if (typeof window.showTopToast === 'function') {
                window.showTopToast('Ukuran kolom dirapikan & disimpan (' + finalW + 'px)', 'success', 2000);
            } else if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top',
                    showConfirmButton: false,
                    timer: 800,
                    timerProgressBar: false,
                    didOpen: (toast) => {
                        toast.addEventListener('click', () => Swal.close());
                    }
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Ukuran kolom dirapikan & disimpan (' + finalW + 'px)'
                });
            }
        }

        // ----------------------------------------------------
        // B. RESIZE BARIS (GARIS HORIZONTAL BISA DIGESER)
        // ----------------------------------------------------
        const dataRows = table.querySelectorAll('tbody tr.indikator-data-row');
        dataRows.forEach((tr, rIdx) => {
            const rowId = tr.dataset.id || ('r_' + rIdx);

            // Restore saved height
            const savedH = localStorage.getItem(storagePrefix + 'h_' + rowId);
            if (savedH) {
                applyRowHeight(tr, parseInt(savedH, 10));
            }

            // Attach resizer handles to each cell in the row
            tr.querySelectorAll('td').forEach(td => {
                let rHandle = td.querySelector(':scope > .row-resizer-line');
                if (!rHandle) {
                    rHandle = document.createElement('div');
                    rHandle.className = 'row-resizer-line';
                    rHandle.title = 'Geser garis baris untuk mengatur tinggi (Klik 2x untuk merapikan otomatis & simpan)';
                    td.appendChild(rHandle);
                }

                rHandle.ondblclick = function(e) {
                    e.stopPropagation();
                    autoFitRow(tr, rowId);
                };

                rHandle.onmousedown = function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const startY = e.pageY;
                    const startH = tr.offsetHeight;
                    rHandle.classList.add('is-active');
                    document.body.classList.add('is-resizing-row');

                    const wrapRect = tableWrapper.getBoundingClientRect();
                    guideRow.style.display = 'block';
                    guideRow.style.top = (e.pageY - wrapRect.top + tableWrapper.scrollTop) + 'px';
                    guideRow.style.left = '0px';
                    guideRow.style.width = table.offsetWidth + 'px';

                    function onRowMouseMove(ev) {
                        const diff = ev.pageY - startY;
                        const newH = Math.max(26, startH + diff);

                        guideRow.style.top = (ev.pageY - wrapRect.top + tableWrapper.scrollTop) + 'px';
                        applyRowHeight(tr, newH);
                    }

                    function onRowMouseUp(ev) {
                        document.removeEventListener('mousemove', onRowMouseMove);
                        document.removeEventListener('mouseup', onRowMouseUp);

                        rHandle.classList.remove('is-active');
                        document.body.classList.remove('is-resizing-row');
                        guideRow.style.display = 'none';

                        const finalH = tr.offsetHeight;
                        localStorage.setItem(storagePrefix + 'h_' + rowId, finalH);
                    }

                    document.addEventListener('mousemove', onRowMouseMove);
                    document.addEventListener('mouseup', onRowMouseUp);
                };
            });
        });

        function applyRowHeight(tr, heightPx) {
            tr.style.height = heightPx + 'px';
            tr.querySelectorAll('td').forEach(td => {
                td.style.height = heightPx + 'px';
            });
        }

        function resetRowHeight(tr) {
            tr.style.removeProperty('height');
            tr.querySelectorAll('td').forEach(td => {
                td.style.removeProperty('height');
            });
        }

        function autoFitRow(tr, rowId) {
            tr.style.removeProperty('height');
            tr.querySelectorAll('td').forEach(td => td.style.removeProperty('height'));

            const naturalH = Math.max(30, tr.scrollHeight || 30);
            applyRowHeight(tr, naturalH);
            localStorage.setItem(storagePrefix + 'h_' + rowId, naturalH);

            if (typeof window.showTopToast === 'function') {
                window.showTopToast('Tinggi baris dirapikan & disimpan', 'success', 2000);
            } else if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top',
                    showConfirmButton: false,
                    timer: 800,
                    timerProgressBar: false,
                    didOpen: (toast) => {
                        toast.addEventListener('click', () => Swal.close());
                    }
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Tinggi baris dirapikan & disimpan'
                });
            }
        }
    }

    window.initTableResizing = initTableResizing;

    window.resetAllTableSizes = function() {
        const currentUserId = '{{ auth()->check() ? auth()->id() : 'guest' }}';
        const storagePrefix = 'tbl_resize_usr_' + currentUserId + '_u{{ $urusan->id }}_t{{ $activeTableId }}_';

        // 1. Kumpulkan seluruh key lama secara aman dan hapus tuntas (agar semua urusan bersih & standar)
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const k = localStorage.key(i);
            if (!k) continue;
            if (
                k.startsWith('tbl_resize_') ||
                k.includes('tbl_resize') ||
                k.includes('is_reset')
            ) {
                keysToRemove.push(k);
            }
        }
        keysToRemove.forEach(k => localStorage.removeItem(k));

        // 2. Bersihkan seluruh inline styles ukuran dari DOM KECUALI kolom aksi sticky
        const table = document.querySelector('.custom-data-table');
        if (table) {
            table.querySelectorAll('th:not(.sticky-action-col), td:not(.sticky-action-col), tr').forEach(el => {
                el.style.removeProperty('width');
                el.style.removeProperty('min-width');
                el.style.removeProperty('max-width');
                el.style.removeProperty('height');
            });
        }
        if (typeof window.showTopToast === 'function') {
            window.showTopToast('Ukuran kolom dan baris telah dikembalikan ke standar', 'info', 2500);
        } else if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 800,
                timerProgressBar: false,
                didOpen: (toast) => {
                    toast.addEventListener('click', () => Swal.close());
                }
            });
            Toast.fire({
                icon: 'info',
                title: 'Ukuran kolom dan baris telah dikembalikan ke standar'
            });
        }
    };

    // ========================================================
    // HORIZONTAL SCROLLBAR & MOUSE DRAG PANNING SYSTEM
    // ========================================================
    function initTableScrollSystem() {
        const table = document.querySelector('.custom-data-table');
        const tableWrap = document.querySelector('.table-responsive.table-resizable-wrap') || document.getElementById('mainTableResponsiveWrapper');

        if (!table || !tableWrap) return;

        // Mouse Drag-to-Scroll (Klik dan seret tabel langsung dengan mouse)
        if (!tableWrap.dataset.boundMouseDrag) {
            tableWrap.dataset.boundMouseDrag = 'true';

            let isMouseDown = false;
            let startX = 0;
            let scrollStartLeft = 0;
            let hasDragged = false;
            let dragDistance = 0;

            tableWrap.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return; // Hanya klik kiri mouse

                // Jangan aktifkan drag jika user mengklik kontrol/input/tombol/resizer
                if (e.target.closest('.col-resizer-line, .row-resizer-line, button, input, select, textarea, a, .dropdown, .col-header-opt-btn, .cell-live-input, .btn-action')) {
                    return;
                }

                isMouseDown = true;
                hasDragged = false;
                dragDistance = 0;
                startX = e.pageX;
                scrollStartLeft = tableWrap.scrollLeft;
                tableWrap.classList.add('is-mouse-dragging');
            });

            document.addEventListener('mousemove', function(e) {
                if (!isMouseDown || !tableWrap) return;
                const diffX = e.pageX - startX;
                dragDistance = Math.abs(diffX);

                if (dragDistance > 4) {
                    hasDragged = true;
                    tableWrap.scrollLeft = scrollStartLeft - diffX;
                }
            });

            document.addEventListener('mouseup', function(e) {
                if (!isMouseDown) return;
                isMouseDown = false;
                if (tableWrap) tableWrap.classList.remove('is-mouse-dragging');
                setTimeout(() => { hasDragged = false; }, 60);
            });

            tableWrap.addEventListener('click', function(e) {
                if (hasDragged) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);
        }
    }
    window.initTableScrollSystem = initTableScrollSystem;

    function initUrusanPage() {
        if (typeof window.applyFooterTotalPreference === 'function') {
            window.applyFooterTotalPreference();
        }
        if (typeof initModalColState === 'function') {
            initModalColState();
        }
        renderColInputs();
        updateRowBadge();
        initInlineTableEditing();
        initTableResizing();
        initTableScrollSystem();

        if (typeof window.recalculateFooterTotals === 'function') {
            window.recalculateFooterTotals();
        }

        // Setup live search auto-filtering and event listener
        const liveInput = document.getElementById('liveSearchInput');
        if (liveInput) {
            if (!liveInput.dataset.boundLiveSearch) {
                liveInput.dataset.boundLiveSearch = 'true';
                liveInput.addEventListener('input', function () {
                    if (typeof window.applyLiveSearch === 'function') {
                        window.applyLiveSearch(this.value);
                    }
                });
                liveInput.addEventListener('keydown', function (ev) {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        if (typeof window.handleSearchSubmit === 'function') {
                            window.handleSearchSubmit(ev);
                        }
                        return false;
                    }
                });
            }
            if (liveInput.value && liveInput.value.trim() !== '') {
                if (typeof window.applyLiveSearch === 'function') {
                    window.applyLiveSearch(liveInput.value.trim());
                }
            }
        }

        const modalTambahKolomEl = document.getElementById('modalTambahKolom');
        if (modalTambahKolomEl && !modalTambahKolomEl.dataset.spaBound) {
            modalTambahKolomEl.dataset.spaBound = 'true';
            modalTambahKolomEl.addEventListener('shown.bs.modal', function () {
                const inputName = document.getElementById('inputNamaKolomBaru');
                if (inputName) {
                    inputName.focus();
                    inputName.select();
                }
            });
        }
    }

    window.initUrusanPage = initUrusanPage;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUrusanPage);
    } else {
        initUrusanPage();
    }

    function applyColSuggestion(name, type) {
        const inputName = document.getElementById('inputNamaKolomBaru');
        const inputType = document.getElementById('inputTipeKolomBaru');
        if (inputName) {
            inputName.value = name;
            inputName.focus();
            inputName.select();
        }
        if (inputType && type) {
            inputType.value = type;
        }
    }

    function openEditColumnModal(colId, colName, colType) {
        const colIdInput = document.getElementById('editColId');
        const colNameInput = document.getElementById('editColName');
        const colTypeInput = document.getElementById('editColType');
        if (colIdInput) colIdInput.value = colId;
        if (colNameInput) colNameInput.value = colName;
        if (colTypeInput && colType) colTypeInput.value = colType;

        const modalEl = document.getElementById('modalEditKolom');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
            setTimeout(() => {
                if (colNameInput) {
                    colNameInput.focus();
                    colNameInput.select();
                }
            }, 150);
        }
    }

    function deleteColumn(colId, colName, targetColumnsUrl, targetTableId) {
        const confirmDelete = function() {
            // Tampilkan loading dialog
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menghapus Kolom...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            const columnsUrl = targetColumnsUrl || window.currentColumnsUrl || '{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}';
            const tableId = targetTableId || window.currentTableId || '{{ $activeTableId }}';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('action', 'delete_column');
            formData.append('table_id', tableId);
            formData.append('column_id', colId);

            fetch(columnsUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json().catch(() => null);
                if (!res.ok) {
                    if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                        Swal.close();
                    }
                    const errMsg = (data && data.message) ? data.message : ('Gagal menghapus kolom (Status: ' + res.status + ')');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', errMsg, 'error');
                    } else {
                        alert(errMsg);
                    }
                    return;
                }

                if (data && data.success) {
                    if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                        Swal.close();
                    }
                    const toastMsg = (data && data.message) ? data.message : `Kolom "${colName}" berhasil dihapus.`;
                    if (typeof window.showTopToast === 'function') {
                        window.showTopToast(toastMsg, 'success', 1800);
                    } else if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top',
                            showConfirmButton: false,
                            timer: 1200,
                            timerProgressBar: false
                        });
                        Toast.fire({
                            icon: 'success',
                            title: toastMsg
                        });
                    }

                    if (data.redirect_url) {
                        if (typeof window.refreshPageContent === 'function') {
                            window.refreshPageContent(data.redirect_url, true);
                        } else if (typeof window.loadSpaContent === 'function') {
                            window.loadSpaContent(data.redirect_url, true, false);
                        } else {
                            window.location.href = data.redirect_url;
                        }
                    } else if (typeof window.refreshPageContent === 'function') {
                        const cleanUrl = new URL(window.location.href);
                        cleanUrl.searchParams.delete('tahun_awal');
                        cleanUrl.searchParams.delete('tahun_akhir');
                        cleanUrl.searchParams.delete('tahun');
                        window.refreshPageContent(cleanUrl.toString(), true);
                    } else if (typeof window.loadSpaContent === 'function') {
                        const cleanUrl = new URL(window.location.href);
                        cleanUrl.searchParams.delete('tahun_awal');
                        cleanUrl.searchParams.delete('tahun_akhir');
                        cleanUrl.searchParams.delete('tahun');
                        window.loadSpaContent(cleanUrl.toString(), true, false);
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', (data && data.message) ? data.message : 'Gagal menghapus kolom.', 'error');
                    } else {
                        alert((data && data.message) ? data.message : 'Gagal menghapus kolom.');
                    }
                }
            })
            .catch(err => {
                console.error('Delete column AJAX error, falling back to form submit:', err);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = columnsUrl;

                const appendField = (n, v) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = n;
                    inp.value = v;
                    form.appendChild(inp);
                };

                appendField('_token', '{{ csrf_token() }}');
                appendField('action', 'delete_column');
                appendField('table_id', tableId);
                appendField('column_id', colId);

                document.body.appendChild(form);
                if (typeof window.submitSpaForm === 'function') {
                    window.submitSpaForm(form);
                } else {
                    form.submit();
                }
            });
        };

        const safeColName = (colName || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                html: `
                    <div class="swal2-modern-content">
                        <div class="swal2-modern-icon-badge">
                            <i class="bi bi-trash3-fill"></i>
                        </div>
                        <h4 class="swal2-modern-title">Hapus Kolom &ldquo;${safeColName}&rdquo;?</h4>
                        <div class="swal2-modern-alert-box">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-exclamation-triangle-fill text-danger fs-5 flex-shrink-0 mt-0.5"></i>
                                <div class="text-start">
                                    <strong class="d-block text-danger mb-1" style="font-size: 0.88rem; font-weight: 700;">Perhatian Penghapusan Kolom</strong>
                                    <span class="text-muted" style="font-size: 0.82rem; line-height: 1.45;">
                                        Kolom <strong>&ldquo;${safeColName}&rdquo;</strong> dan seluruh data nilai di dalamnya akan ikut dihapus.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Kolom',
                cancelButtonText: '<i class="bi bi-x-lg me-1"></i> Batal',
                reverseButtons: false,
                focusCancel: false,
                customClass: {
                    popup: 'swal2-modern-delete-popup',
                    confirmButton: 'btn-swal-confirm-delete',
                    cancelButton: 'btn-swal-cancel',
                    actions: 'swal2-modern-actions'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    confirmDelete();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menghapus kolom "' + colName + '" dari tabel ini?')) {
                confirmDelete();
            }
        }
    }

    window.confirmDeleteTable = function(e, form, tableName) {
        if (e) {
            e.preventDefault();
            if (e.stopPropagation) e.stopPropagation();
        }
        const cleanName = (tableName || '').trim() || 'halaman ini';
        const safeName = cleanName.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

        window.showModernConfirm({
            title: `Hapus Halaman &ldquo;${safeName}&rdquo;?`,
            htmlMessage: `Apakah Anda yakin ingin menghapus halaman <strong>&ldquo;${safeName}&rdquo;</strong> beserta seluruh baris indikator dan data di dalamnya?`,
            confirmText: 'Ya, Hapus Halaman',
            confirmIcon: 'bi-trash3-fill',
            cancelText: 'Batal',
            onConfirm: function() {
                if (typeof window.submitSpaForm === 'function') {
                    window.submitSpaForm(form);
                } else {
                    form.submit();
                }
            }
        });
        return false;
    };

    window.confirmDeleteIndikator = function(e, form, name, hasChildren) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();
        }

        let cleanName = (name || '').trim();
        if (!cleanName || cleanName === '""') {
            const tr = form ? form.closest('tr') : null;
            const noCell = tr ? tr.querySelector('td:first-child') : null;
            const rowNo = noCell ? noCell.textContent.trim() : '';
            cleanName = rowNo ? `No. ${rowNo}` : 'ini';
        }

        const safeName = cleanName.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        const isNoLabel = cleanName.startsWith('No.') || cleanName === 'ini';
        const displayTitle = isNoLabel ? `Hapus Baris ${safeName}?` : `Hapus Baris &ldquo;${safeName}&rdquo;?`;
        const displayTarget = isNoLabel ? `baris <strong>${safeName}</strong>` : `baris <strong>&ldquo;${safeName}&rdquo;</strong>`;
        const title = isNoLabel ? `Hapus Baris ${cleanName}?` : `Hapus Baris "${cleanName}"?`;

        const executeDelete = async function() {
            try {
                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action') || form.action;
                const res = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json().catch(() => null);

                if (res.ok && (!data || data.success !== false)) {
                    // Refresh HANYA area tabel/halaman (sidebar tidak ter-refresh)
                    if (typeof window.refreshPageContent === 'function') {
                        await window.refreshPageContent(window.location.href, true);
                    } else if (typeof window.loadSpaContent === 'function') {
                        await window.loadSpaContent(window.location.href, false);
                    } else {
                        window.location.reload();
                    }

                    // Tampilkan notifikasi toast di tengah atas secara cepat (800ms, tanpa progress bar)
                    if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                        Swal.close();
                    }
                    const toastMsg = (data && data.message) ? data.message : `Baris ${isNoLabel ? safeName : `"${safeName}"`} berhasil dihapus.`;
                    if (typeof window.showTopToast === 'function') {
                        window.showTopToast(toastMsg, 'success', 1800);
                    } else if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top',
                            showConfirmButton: false,
                            timer: 1200,
                            timerProgressBar: false
                        });
                        Toast.fire({
                            icon: 'success',
                            title: toastMsg
                        });
                    }
                } else {
                    const errMsg = (data && data.message) ? data.message : 'Gagal menghapus baris.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', errMsg, 'error');
                    } else {
                        alert(errMsg);
                    }
                }
            } catch (err) {
                console.error('Error deleting indikator:', err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus baris.', 'error');
                } else {
                    alert('Terjadi kesalahan saat menghapus baris.');
                }
            }
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                html: `
                    <div class="swal2-modern-content">
                        <div class="swal2-modern-icon-badge">
                            <i class="bi ${hasChildren ? 'bi-exclamation-triangle-fill' : 'bi-trash3-fill'}"></i>
                        </div>
                        <h4 class="swal2-modern-title">${displayTitle}</h4>
                        ${hasChildren ? `
                            <div class="swal2-modern-alert-box">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5 flex-shrink-0 mt-0.5"></i>
                                    <div class="text-start">
                                        <strong class="d-block text-danger mb-1" style="font-size: 0.88rem; font-weight: 700;">Perhatian: Baris Induk</strong>
                                        <span class="text-muted" style="font-size: 0.82rem; line-height: 1.45;">
                                            Baris ini adalah baris <strong>Induk</strong>. Seluruh sub-baris di dalamnya juga akan <strong class="text-danger">ikut terhapus permanen</strong> dari database!
                                        </span>
                                    </div>
                                </div>
                            </div>
                        ` : `
                            <div class="swal2-modern-info-box">
                                <span>Apakah Anda yakin ingin menghapus ${displayTarget} dari tabel urusan ini?</span>
                            </div>
                        `}
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Ya, Hapus',
                cancelButtonText: '<i class="bi bi-x-lg me-1"></i> Batal',
                reverseButtons: false,
                focusCancel: false,
                customClass: {
                    popup: 'swal2-modern-delete-popup',
                    confirmButton: 'btn-swal-confirm-delete',
                    cancelButton: 'btn-swal-cancel',
                    actions: 'swal2-modern-actions'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    executeDelete();
                }
            });
        } else {
            const rawText = hasChildren 
                ? 'Perhatian: Baris ini adalah baris Induk. Seluruh sub-baris di dalamnya juga akan ikut terhapus permanen!' 
                : 'Baris ini akan dihapus dari tabel urusan ini.';
            if (confirm(`${title}\n\n${rawText}`)) {
                executeDelete();
            }
        }
        return false;
    };

    // Helper functions for Excel File Import
    window.handleExcelFileSelect = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const nameEl = document.getElementById('excelFileName');
            const sizeEl = document.getElementById('excelFileSize');
            const defState = document.getElementById('dropzoneDefaultState');
            const selState = document.getElementById('dropzoneSelectedState');
            if (nameEl) nameEl.textContent = file.name;
            if (sizeEl) sizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';
            if (defState) defState.style.display = 'none';
            if (selState) selState.style.display = 'block';
        }
    };

    window.resetExcelFile = function() {
        const input = document.getElementById('fileInputExcel');
        if (input) input.value = '';
        const defState = document.getElementById('dropzoneDefaultState');
        const selState = document.getElementById('dropzoneSelectedState');
        if (defState) defState.style.display = 'block';
        if (selState) selState.style.display = 'none';
    };

    window.handleImportSubmit = function(e) {
        const btn = document.getElementById('btnSubmitImport');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> Memproses Import...';
        }
    };

    window.toggleDuplicateOptions = function(isDuplicate) {
        const sel = document.getElementById('duplicateSourceSelectContainer');
        const blk = document.getElementById('customTableBlankContainer');
        if (sel) sel.style.display = isDuplicate ? 'block' : 'none';
        if (blk) blk.style.display = isDuplicate ? 'none' : 'block';
    };

    window.openRenameTableModal = function(tblId, tblName, tblYr) {
        const idInput = document.getElementById('renameTableId');
        const nameInput = document.getElementById('inputRenameTableName');
        const yrInput = document.getElementById('inputRenameTableTahun');
        if (idInput) idInput.value = tblId;
        if (nameInput) nameInput.value = tblName;
        if (yrInput) yrInput.value = tblYr || '';
        const modalEl = document.getElementById('modalRenameTable');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        }
    };

    window.switchTahunTunggal = function(sel) {
        if (!sel) return;
        const form = sel.closest('form') || document.getElementById('formFilterTahunTunggal');
        const opt = sel.options ? sel.options[sel.selectedIndex] : null;
        const targetTableId = opt ? opt.getAttribute('data-table-id') : null;
        const tahunVal = sel.value;

        const tblInput = document.getElementById('filterTahunTabelId');
        if (tblInput && targetTableId) {
            tblInput.value = targetTableId;
        }

        const baseUrl = form ? (form.getAttribute('action') || (typeof form.action === 'string' ? form.action : null) || window.location.href) : window.location.href;
        const url = new URL(baseUrl, window.location.origin);
        const params = new URLSearchParams(window.location.search);

        if (targetTableId) {
            params.set('tabel', targetTableId);
        }
        if (tahunVal) {
            params.set('tahun', tahunVal);
        }

        const searchInput = document.getElementById('liveSearchInput') || (form ? form.querySelector('input[name="search"]') : null);
        if (searchInput && searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        } else {
            params.delete('search');
        }

        url.search = params.toString();

        // Feedback visual halus pada tabel saat berpindah tahun (hanya tabel yang di-refresh, sidebar tetap diam)
        if (sel) {
            sel.style.opacity = '0.65';
        }
        const tableCard = document.querySelector('.card.border-0.shadow-xs') || document.querySelector('.table-responsive');
        if (tableCard) {
            tableCard.style.transition = 'opacity 0.15s ease';
            tableCard.style.opacity = '0.65';
        }

        if (typeof window.loadSpaContent === 'function') {
            window.loadSpaContent(url.toString(), true, true);
        } else if (form) {
            form.submit();
        } else {
            window.location.href = url.toString();
        }
    };

    // Handler AJAX untuk Edit Kolom dan Tambah Kolom (hanya me-refresh tabel tanpa me-refresh sidebar)
    (function initColumnFormsAjax() {
        const formEdit = document.getElementById('formEditKolom');
        if (formEdit) {
            const editColInput = document.getElementById('editColName');
            if (editColInput) {
                editColInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (typeof formEdit.requestSubmit === 'function') {
                            formEdit.requestSubmit();
                        } else {
                            formEdit.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                        }
                    }
                });
            }

            formEdit.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const colId = (document.getElementById('editColId')?.value || '').trim();
                const inputVal = (document.getElementById('editColName')?.value || '').trim();
                const lowerVal = inputVal.toLowerCase();

                if (!inputVal) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nama Kolom Wajib Diisi',
                            text: 'Silakan masukkan nama kolom terlebih dahulu.',
                            confirmButtonColor: '#3085d6',
                        });
                    } else {
                        alert('Silakan masukkan nama kolom terlebih dahulu.');
                    }
                    document.getElementById('editColName')?.focus();
                    return false;
                }

                const isDuplicate = (window.existingColumns || []).some(function(c) {
                    return (c.id || '') !== colId && (c.name || '').trim().toLowerCase() === lowerVal;
                });

                if (isDuplicate) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nama Kolom Sudah Digunakan',
                            text: `Nama kolom "${inputVal}" sudah digunakan pada tabel ini. Harap gunakan nama kolom yang berbeda agar tidak terjadi duplikasi.`,
                            confirmButtonColor: '#3085d6',
                        });
                    } else {
                        alert(`Nama kolom "${inputVal}" sudah digunakan pada tabel ini. Harap gunakan nama kolom yang berbeda agar tidak terjadi duplikasi.`);
                    }
                    return false;
                }

                const submitBtn = formEdit.querySelector('button[type="submit"]');
                const originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'Simpan';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...';
                }

                try {
                    const actionUrl = formEdit.getAttribute('action') || '{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}';
                    const formData = new FormData(formEdit);
                    const response = await fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json().catch(() => null);

                    if (!response.ok || (data && !data.success)) {
                        const errMsg = (data && data.message) ? data.message : ('Gagal memperbarui kolom (Status: ' + response.status + ')');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Gagal', errMsg, 'error');
                        } else {
                            alert(errMsg);
                        }
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                        }
                        return false;
                    }

                    // Tutup modal edit kolom
                    const modalEl = document.getElementById('modalEditKolom');
                    if (modalEl) {
                        const modalInst = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                        if (modalInst) modalInst.hide();
                    }
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.body.style.removeProperty('overflow');

                    // Notifikasi sukses
                    const toastMsg = (data && data.message) ? data.message : `Kolom "${inputVal}" berhasil diperbarui.`;
                    if (typeof window.showTopToast === 'function') {
                        window.showTopToast(toastMsg, 'success', 1800);
                    } else if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: false
                        });
                        Toast.fire({ icon: 'success', title: toastMsg });
                    }

                    // Refresh HANYA area tabel tanpa me-refresh sidebar
                    const targetUrl = (data && data.redirect_url) ? data.redirect_url : window.location.href;
                    if (typeof window.refreshPageContent === 'function') {
                        await window.refreshPageContent(targetUrl, true);
                    } else if (typeof window.loadSpaContent === 'function') {
                        await window.loadSpaContent(targetUrl, false, true);
                    } else {
                        window.location.reload();
                    }
                } catch (err) {
                    console.error('Edit column AJAX error:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', 'Terjadi kesalahan: ' + (err.message || err), 'error');
                    } else {
                        alert('Terjadi kesalahan: ' + (err.message || err));
                    }
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                }
            });
        }

        const formTambah = document.getElementById('formTambahKolom');
        if (formTambah) {
            const inputNamaKolomBaru = document.getElementById('inputNamaKolomBaru');
            if (inputNamaKolomBaru) {
                inputNamaKolomBaru.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (typeof formTambah.requestSubmit === 'function') {
                            formTambah.requestSubmit();
                        } else {
                            formTambah.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                        }
                    }
                });
            }

            formTambah.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const inputVal = (document.getElementById('inputNamaKolomBaru')?.value || '').trim();
                const lowerVal = inputVal.toLowerCase();

                if (!inputVal) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nama Kolom Wajib Diisi',
                            text: 'Silakan masukkan nama kolom baru terlebih dahulu.',
                            confirmButtonColor: '#3085d6',
                        });
                    } else {
                        alert('Silakan masukkan nama kolom baru terlebih dahulu.');
                    }
                    document.getElementById('inputNamaKolomBaru')?.focus();
                    return false;
                }

                const isDuplicate = (window.existingColumns || []).some(function(c) {
                    return (c.name || '').trim().toLowerCase() === lowerVal;
                });

                if (isDuplicate) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kolom Sudah Ada',
                            text: `Kolom "${inputVal}" sudah ada pada tabel ini. Harap gunakan nama kolom yang berbeda.`,
                            confirmButtonColor: '#3085d6',
                        });
                    } else {
                        alert(`Kolom "${inputVal}" sudah ada pada tabel ini. Harap gunakan nama kolom yang berbeda.`);
                    }
                    return false;
                }

                const submitBtn = formTambah.querySelector('button[type="submit"]');
                const originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'Tambah Kolom';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menambahkan...';
                }

                try {
                    const actionUrl = formTambah.getAttribute('action') || '{{ route('urusan.columns', $urusan->slug ?: $urusan->id) }}';
                    const formData = new FormData(formTambah);
                    const response = await fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json().catch(() => null);

                    if (!response.ok || (data && !data.success)) {
                        const errMsg = (data && data.message) ? data.message : ('Gagal menambahkan kolom (Status: ' + response.status + ')');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Gagal', errMsg, 'error');
                        } else {
                            alert(errMsg);
                        }
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                        }
                        return false;
                    }

                    // Tutup modal tambah kolom
                    const modalEl = document.getElementById('modalTambahKolom');
                    if (modalEl) {
                        const modalInst = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
                        if (modalInst) modalInst.hide();
                    }
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.body.style.removeProperty('overflow');

                    // Notifikasi toast
                    const toastMsg = (data && data.message) ? data.message : `Kolom "${inputVal}" berhasil ditambahkan.`;
                    if (typeof window.showTopToast === 'function') {
                        window.showTopToast(toastMsg, 'success', 1800);
                    } else if (typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: false
                        });
                        Toast.fire({ icon: 'success', title: toastMsg });
                    }

                    // Refresh HANYA area tabel tanpa me-refresh sidebar
                    const targetUrl = (data && data.redirect_url) ? data.redirect_url : window.location.href;
                    if (typeof window.refreshPageContent === 'function') {
                        await window.refreshPageContent(targetUrl, true);
                    } else if (typeof window.loadSpaContent === 'function') {
                        await window.loadSpaContent(targetUrl, false, true);
                    } else {
                        window.location.reload();
                    }
                } catch (err) {
                    console.error('Add column AJAX error:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', 'Terjadi kesalahan: ' + (err.message || err), 'error');
                    } else {
                        alert('Terjadi kesalahan: ' + (err.message || err));
                    }
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                }
            });
        }
    })();
</script>
@endpush
