<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Data Profil Pembangunan Daerah') - SIPD Sektoral</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v=2">
    <link rel="preload" as="image" href="{{ asset('logo.png') }}?v=2" fetchpriority="high">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons 1.11.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Early Sidebar Width Restore to Prevent Flash -->
    <script>
        (function() {
            try {
                // Restore sidebar width
                const savedWidth = localStorage.getItem('sidebar_width');
                if (savedWidth && window.innerWidth >= 992) {
                    const width = parseInt(savedWidth, 10);
                    if (width >= 200 && width <= 700) {
                        document.documentElement.style.setProperty('--sidebar-width', width + 'px');
                    }
                }

                // Ensure zoom is reset to normal and legacy display scale setting is cleared
                localStorage.removeItem('app_display_scale');
                document.documentElement.style.zoom = '';
            } catch(e) {}
        })();
    </script>

    <!-- Custom Theme Styles -->
    <style>
        :root {
            --bs-font-sans-serif: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --bs-primary: #1e40af;
            --bs-primary-rgb: 30, 64, 175;
            --sidebar-width: 250px;
            --sidebar-bg: #ffffff;
            --sidebar-hover: #f8fafc;
            --sidebar-border: #e2e8f0;
            --topbar-height: 48px;
        }

        /* Global Pagination SVG Size Safeguard */
        .pagination svg,
        nav[role="navigation"] svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
            display: inline-block !important;
        }

        /* ========================================================
           HAPUS SEMUA SCROLLBAR (TETAP DAPAT DISCROLL LANCAR)
           ======================================================== */
        *, *::before, *::after, html, body {
            scrollbar-width: none !important; /* Firefox */
            -ms-overflow-style: none !important; /* IE & Edge */
        }
        *::-webkit-scrollbar, html::-webkit-scrollbar, body::-webkit-scrollbar {
            width: 0px !important;
            height: 0px !important;
            display: none !important; /* Chrome / Safari / Edge */
        }

        html {
            font-size: 13.5px; /* Proporsi kompak untuk laptop/PC */
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            line-height: 1.42;
            overflow-x: hidden;
        }

        /* Spacing helper utilities */
        .gap-1\.5 { gap: 0.375rem !important; }
        .gap-2\.5 { gap: 0.625rem !important; }
        .px-2\.5 { padding-left: 0.625rem !important; padding-right: 0.625rem !important; }
        .py-1\.5 { padding-top: 0.375rem !important; padding-bottom: 0.375rem !important; }
        .p-2\.5 { padding: 0.625rem !important; }

        /* Sidebar Stacking Context */
        .sidebar {
            isolation: isolate;
        }

        /* Resizing state */
        body.is-resizing {
            user-select: none !important;
            -webkit-user-select: none !important;
            cursor: col-resize !important;
        }
        body.is-resizing .sidebar,
        body.is-resizing .main-wrapper {
            transition: none !important;
        }

        /* Sidebar Layout */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1040;
            background-color: var(--sidebar-bg);
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .main-wrapper {
            flex: 1;
            min-width: 0;
            width: calc(100% - var(--sidebar-width));
            max-width: calc(100vw - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            box-sizing: border-box;
        }

        /* Collapsed Sidebar on Desktop */
        body.sidebar-collapsed .sidebar {
            margin-left: calc(-1 * var(--sidebar-width));
        }
        body.sidebar-collapsed .main-wrapper {
            margin-left: 0;
            width: 100%;
            max-width: 100vw;
        }

        /* Animate only during explicit toggle click, never on initial page load / navigation */
        body.is-toggling .sidebar,
        body.is-toggling .main-wrapper {
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Mobile Sidebar */
        @media (max-width: 991.98px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
                transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .main-wrapper {
                margin-left: 0;
                width: 100%;
                max-width: 100vw;
            }
            body.sidebar-mobile-open .sidebar {
                margin-left: 0;
            }
            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.6);
                backdrop-filter: blur(2px);
                z-index: 1030;
                display: none;
            }
            body.sidebar-mobile-open .sidebar-backdrop {
                display: block;
            }
        }

        /* Nav Links styling */
        .sidebar .nav-link {
            font-size: 0.85rem;
            color: #94a3b8;
            transition: all 0.15s ease-in-out;
        }
        .sidebar .nav-link:hover {
            color: #f8fafc;
            background-color: var(--sidebar-hover);
        }
        .sidebar .nav-link.active {
            background-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: 600;
        }
        .sidebar .sub-nav-link {
            font-size: 0.8rem;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }

        /* Colors */
        .bg-slate-800 { background-color: #1e293b !important; }
        .bg-slate-900 { background-color: #0f172a !important; }
        .bg-slate-950 { background-color: #020617 !important; }
        .text-slate-300 { color: #cbd5e1 !important; }
        .text-slate-400 { color: #94a3b8 !important; }

        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.1);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        /* Hierarchy Tree Style */
        .tree-line-wrapper {
            position: relative;
        }
        .tree-indent-spacer {
            display: inline-block;
            width: 24px;
            height: 100%;
        }
        .tree-connector {
            position: absolute;
            left: -14px;
            top: 50%;
            width: 12px;
            height: 1px;
            background-color: #94a3b8;
        }

        /* Cards & Shadow */
        /* Cards & Shadow */
        .card {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
            margin-bottom: 0.75rem;
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.5rem 0.85rem;
        }
        .card-body {
            padding: 0.85rem 1rem;
        }

        /* Tables */
        .table {
            vertical-align: middle;
            font-size: 0.8125rem;
        }
        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }
        .table th {
            font-weight: 600;
            color: #475569;
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            padding: 0.45rem 0.65rem;
        }
        .table td {
            padding: 0.45rem 0.65rem;
            color: #1e293b;
        }

        /* Compact form controls & buttons */
        .form-control, .form-select {
            font-size: 0.8125rem;
            padding: 0.35rem 0.6rem;
        }

        /* ========================================================
           MODERN SWEETALERT2 STYLING (CLEAN, ELEGAN & USER-FRIENDLY)
           ======================================================== */
        .swal2-modern-delete-popup {
            border-radius: 20px !important;
            padding: 1.75rem 1.5rem !important;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25), 0 0 0 1px rgba(15, 23, 42, 0.05) !important;
            border: none !important;
            font-family: inherit !important;
            max-width: 440px !important;
            background: #ffffff !important;
        }
        .swal2-modern-icon-badge {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #fef2f2;
            border: 3px solid #fee2e2;
            color: #dc2626;
            font-size: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.15rem auto;
            box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.2);
        }
        .swal2-modern-title {
            font-size: 1.28rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.65rem;
            line-height: 1.35;
            text-align: center;
        }
        .swal2-modern-alert-box {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            margin: 0.85rem 0 0.5rem 0;
            text-align: left;
        }
        .swal2-modern-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            margin: 0.85rem 0 0.5rem 0;
            text-align: center;
            color: #475569;
            font-size: 0.88rem;
        }
        .swal2-modern-actions {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            margin-top: 1.25rem !important;
            width: 100% !important;
        }
        /* Tombol 'Ya, Hapus' di sebelah KIRI */
        .btn-swal-confirm-delete {
            order: 1 !important;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            border: 1px solid #b91c1c !important;
            padding: 0.60rem 1.35rem !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
        }
        .btn-swal-confirm-delete:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35) !important;
            color: #ffffff !important;
        }
        .btn-swal-confirm-delete:active {
            transform: translateY(0) !important;
        }
        /* Tombol 'Batal' di sebelah KANAN */
        .btn-swal-cancel {
            order: 2 !important;
            background: #ffffff !important;
            color: #475569 !important;
            border: 1.5px solid #cbd5e1 !important;
            padding: 0.60rem 1.35rem !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            transition: all 0.2s ease !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
        }
        .btn-swal-cancel:hover {
            background: #f8fafc !important;
            color: #0f172a !important;
            border-color: #94a3b8 !important;
            transform: translateY(-1px) !important;
        }
        .btn-swal-cancel:active {
            transform: translateY(0) !important;
        }
            border-color: #cbd5e1;
            border-radius: 0.375rem;
        }
        .form-control-sm, .form-select-sm {
            font-size: 0.78rem;
            padding: 0.22rem 0.5rem;
            border-radius: 0.35rem;
        }
        .btn {
            font-size: 0.8125rem;
            padding: 0.32rem 0.75rem;
            border-radius: 0.375rem;
        }
        .btn-sm {
            font-size: 0.76rem;
            padding: 0.22rem 0.6rem;
            border-radius: 0.35rem;
        }
        .badge {
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        /* ========================================
           TOP-CENTER FLOATING TOAST NOTIFICATION
           ======================================== */
        .top-toast-container {
            position: fixed;
            top: 14px;
            left: 0;
            right: 0;
            margin: 0 auto;
            z-index: 1095;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            pointer-events: none;
            width: 100%;
        }

        .top-toast {
            position: relative;
            pointer-events: auto;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 6px 16px;
            border-radius: 9999px;
            border: 1px solid transparent;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12), 0 1px 3px rgba(15, 23, 42, 0.06);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            width: auto;
            max-width: fit-content;
            overflow: hidden;
            cursor: pointer;
            animation: topToastIn 0.10s ease forwards;
            transition: opacity 0.10s ease, transform 0.10s ease;
        }

        /* ========================================================
           SWEETALERT2 NOTIFICATIONS: TOAST VS MODAL SEPARATION
           ======================================================== */
        /* 1. SweetAlert2 Toast (Fast, Centered at Top, Non-blocking) */
        body.swal2-toast-shown .swal2-container,
        .swal2-container.swal2-top,
        .swal2-container:has(.swal2-toast) {
            position: fixed !important;
            top: 14px !important;
            left: 0 !important;
            right: 0 !important;
            bottom: auto !important;
            height: auto !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            justify-content: center !important;
            align-items: flex-start !important;
            pointer-events: none !important;
            z-index: 99999 !important;
            transform: none !important;
            background: transparent !important;
        }

        body.swal2-toast-shown .swal2-container .swal2-popup.swal2-toast.swal2-hide,
        .swal2-container.swal2-top > .swal2-popup.swal2-toast.swal2-hide,
        .swal2-container:has(.swal2-toast) > .swal2-popup.swal2-toast.swal2-hide,
        .swal2-popup.swal2-toast.swal2-hide {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        body.swal2-toast-shown .swal2-container .swal2-popup.swal2-toast:not(.swal2-hide),
        .swal2-container.swal2-top > .swal2-popup.swal2-toast:not(.swal2-hide),
        .swal2-container:has(.swal2-toast) > .swal2-popup.swal2-toast:not(.swal2-hide) {
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            width: auto !important;
            min-width: 0 !important;
            max-width: fit-content !important;
            flex: 0 0 auto !important;
            margin: 0 auto !important;
            pointer-events: auto !important;
            cursor: pointer !important;
            box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.15), 0 2px 4px -1px rgba(15, 23, 42, 0.06) !important;
            border-radius: 9999px !important;
            padding: 6px 18px !important;
            font-size: 0.88rem !important;
            font-weight: 500 !important;
            animation: topToastIn 0.10s ease forwards !important;
            transition: opacity 0.10s ease, transform 0.10s ease !important;
        }

        .swal2-popup.swal2-toast .swal2-icon {
            width: 1.35em !important;
            min-width: 1.35em !important;
            height: 1.35em !important;
            margin: 0 8px 0 0 !important;
        }

        .swal2-popup.swal2-toast .swal2-title {
            margin: 0 !important;
            font-size: 0.86rem !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            white-space: nowrap !important;
        }

        /* 2. SweetAlert2 Modal Dialogs (Delete confirmations, Alerts, Popups) */
        /* MUST be full-screen backdrop, perfectly vertically & horizontally centered, and fully interactive */
        body:not(.swal2-toast-shown) .swal2-container:not(:has(.swal2-toast)) {
            position: fixed !important;
            inset: 0 !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            pointer-events: auto !important;
            z-index: 99999 !important;
            background-color: rgba(15, 23, 42, 0.5) !important;
            backdrop-filter: blur(2px) !important;
            -webkit-backdrop-filter: blur(2px) !important;
        }

        body:not(.swal2-toast-shown) .swal2-container:not(:has(.swal2-toast)) .swal2-popup {
            margin: auto !important;
            pointer-events: auto !important;
            align-self: center !important;
            justify-self: center !important;
        }

        /* ========================================================
           CUSTOM DATA TABLE & STICKY ACTION COLUMN (GLOBAL)
           ======================================================== */
        .table-responsive {
            overflow-x: auto !important;
            position: relative;
            max-width: 100%;
            -webkit-overflow-scrolling: touch;
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
        .custom-data-table tr.table-induk-row td.sticky-action-col {
            background-color: #f1f5f9 !important;
        }
        .custom-data-table tr.table-induk-row:hover td.sticky-action-col {
            background-color: #e2e8f0 !important;
        }
        .custom-data-table tr.table-sub-induk-row td.sticky-action-col {
            background-color: #f8fafc !important;
        }
        .custom-data-table tr.table-sub-induk-row:hover td.sticky-action-col {
            background-color: #f1f5f9 !important;
        }

        .top-toast.toast-hiding {
            animation: topToastOut 0.15s ease forwards !important;
        }

        @keyframes topToastIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes topToastOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        /* Success Toast */
        .top-toast--success {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .top-toast--success .top-toast-icon {
            color: #059669;
        }

        /* Error Toast */
        .top-toast--error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .top-toast--error .top-toast-icon {
            color: #dc2626;
        }

        /* Warning Toast */
        .top-toast--warning {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }
        .top-toast--warning .top-toast-icon {
            color: #d97706;
        }

        .top-toast-icon {
            font-size: 1.25rem;
            line-height: 1;
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }

        .top-toast-content {
            flex-grow: 1;
            font-size: 0.92rem;
            font-weight: 500;
            line-height: 1.4;
            word-break: break-word;
        }

        .top-toast-close {
            background: transparent;
            border: none;
            color: currentColor;
            opacity: 0.6;
            padding: 4px 6px;
            line-height: 1;
            font-size: 0.85rem;
            cursor: pointer;
            border-radius: 6px;
            transition: opacity 0.15s, background-color 0.15s;
            flex-shrink: 0;
        }
        .top-toast-close:hover {
            opacity: 1;
            background: rgba(0, 0, 0, 0.06);
        }
    </style>

    @stack('styles')
</head>
<body class="{{ request()->routeIs('urusan.show') ? 'sidebar-collapsed' : '' }}">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Floating Top-Center Notification Container -->
    <div class="top-toast-container" id="topToastContainer" aria-live="polite" aria-atomic="true">
        @if(session('success'))
            <div class="top-toast top-toast--success" role="alert" data-auto-dismiss="800">
                <div class="top-toast-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="top-toast-content">
                    {{ session('success') }}
                </div>
                <button type="button" class="top-toast-close" aria-label="Tutup" onclick="closeTopToast(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="top-toast top-toast--error" role="alert" data-auto-dismiss="800">
                <div class="top-toast-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="top-toast-content">
                    {{ session('error') }}
                </div>
                <button type="button" class="top-toast-close" aria-label="Tutup" onclick="closeTopToast(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="top-toast top-toast--warning" role="alert" data-auto-dismiss="800">
                <div class="top-toast-icon">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div class="top-toast-content">
                    <div class="fw-semibold mb-1">Terdapat beberapa kesalahan:</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="top-toast-close" aria-label="Tutup" onclick="closeTopToast(this)">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endif
    </div>

    <div class="app-layout">
        <!-- Sidebar Navigation -->
        @include('layouts.sidebar')

        <!-- Main Content Area -->
        <div class="main-wrapper">
            <!-- Topbar Header -->
            @include('layouts.header')

            <!-- Main Content Container -->
            <main class="content-body flex-grow-1 px-3 py-2.5">
                @yield('content')
            </main>

        </div>
    </div>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js 4.4.2 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <!-- SweetAlert2 11 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Core App Scripts -->
    <script id="coreAppScript">
        document.addEventListener('DOMContentLoaded', function () {
            // Sidebar Toggle Desktop & Mobile
            const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
            const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            if (sidebarToggleBtn) {
                sidebarToggleBtn.addEventListener('click', function () {
                    document.body.classList.add('is-toggling');
                    if (window.innerWidth >= 992) {
                        document.body.classList.toggle('sidebar-collapsed');
                    } else {
                        document.body.classList.toggle('sidebar-mobile-open');
                    }
                    setTimeout(() => {
                        document.body.classList.remove('is-toggling');
                    }, 300);
                });
            }

            if (sidebarCloseBtn) {
                sidebarCloseBtn.addEventListener('click', function () {
                    document.body.classList.remove('sidebar-mobile-open');
                });
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function () {
                    document.body.classList.remove('sidebar-mobile-open');
                });
            }

            // ========================================================
            // GLOBAL MODERN CONFIRMATION DIALOG (SweetAlert2)
            // ========================================================
            window.showModernConfirm = function(options) {
                const {
                    title = 'Konfirmasi Tindakan',
                    htmlMessage = '',
                    isAlert = false,
                    alertTitle = 'Perhatian',
                    alertDesc = '',
                    confirmText = 'Ya, Hapus',
                    confirmIcon = 'bi-trash3-fill',
                    cancelText = 'Batal',
                    cancelIcon = 'bi-x-lg',
                    onConfirm = () => {}
                } = options;

                if (typeof Swal !== 'undefined') {
                    let contentHtml = '';
                    if (isAlert) {
                        contentHtml = `
                            <div class="swal2-modern-alert-box">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5 flex-shrink-0 mt-0.5"></i>
                                    <div class="text-start">
                                        <strong class="d-block text-danger mb-1" style="font-size: 0.88rem; font-weight: 700;">${alertTitle}</strong>
                                        <span class="text-muted" style="font-size: 0.82rem; line-height: 1.45;">
                                            ${alertDesc}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (htmlMessage) {
                        contentHtml = `
                            <div class="swal2-modern-info-box">
                                <span>${htmlMessage}</span>
                            </div>
                        `;
                    }

                    Swal.fire({
                        html: `
                            <div class="swal2-modern-content">
                                <div class="swal2-modern-icon-badge">
                                    <i class="bi ${confirmIcon}"></i>
                                </div>
                                <h4 class="swal2-modern-title">${title}</h4>
                                ${contentHtml}
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: `<i class="bi ${confirmIcon} me-1"></i> ${confirmText}`,
                        cancelButtonText: `<i class="bi ${cancelIcon} me-1"></i> ${cancelText}`,
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
                            onConfirm();
                        }
                    });
                } else {
                    const plainMsg = alertDesc || (htmlMessage ? htmlMessage.replace(/<[^>]+>/g, '') : '');
                    if (confirm(`${title}\n\n${plainMsg}`)) {
                        onConfirm();
                    }
                }
            };

            // Universal delete confirmation with SweetAlert2 (Delegated for dynamic SPA elements)
            document.addEventListener('click', function (e) {
                const button = e.target.closest('.btn-confirm-delete');
                if (!button) return;

                e.preventDefault();
                e.stopPropagation();
                const form = button.closest('form');
                const title = button.getAttribute('data-title') || 'Konfirmasi Penghapusan';
                const message = button.getAttribute('data-message') || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
                
                window.showModernConfirm({
                    title: title,
                    htmlMessage: message,
                    confirmText: 'Ya, Hapus',
                    onConfirm: () => {
                        if (form) {
                            if (typeof window.submitSpaForm === 'function') {
                                window.submitSpaForm(form);
                            } else {
                                form.submit();
                            }
                        }
                    }
                });
            });

            // Sidebar Resizing Logic (Drag to resize width)
            const sidebar = document.getElementById('sidebar');
            const resizer = document.getElementById('sidebarResizer');

            if (sidebar && resizer) {
                let isResizing = false;
                let startX = 0;
                let startWidth = 0;
                const minWidth = 220;
                const maxWidth = 650;
                const defaultWidth = 250;

                resizer.addEventListener('mousedown', function (e) {
                    if (window.innerWidth < 992) return;
                    if (document.body.classList.contains('sidebar-collapsed')) return;

                    isResizing = true;
                    startX = e.clientX;
                    startWidth = sidebar.getBoundingClientRect().width;

                    document.body.classList.add('is-resizing');
                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                    e.preventDefault();
                });

                // Double click to reset to default width (280px)
                resizer.addEventListener('dblclick', function () {
                    if (window.innerWidth < 992) return;
                    document.documentElement.style.setProperty('--sidebar-width', defaultWidth + 'px');
                    try {
                        localStorage.removeItem('sidebar_width');
                    } catch (e) {}
                });

                function onMouseMove(e) {
                    if (!isResizing) return;
                    const deltaX = e.clientX - startX;
                    let newWidth = startWidth + deltaX;

                    if (newWidth < minWidth) newWidth = minWidth;
                    if (newWidth > maxWidth) newWidth = maxWidth;

                    document.documentElement.style.setProperty('--sidebar-width', newWidth + 'px');
                }

                function onMouseUp() {
                    if (!isResizing) return;
                    isResizing = false;
                    document.body.classList.remove('is-resizing');
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);

                    const finalWidth = Math.round(sidebar.getBoundingClientRect().width);
                    try {
                        localStorage.setItem('sidebar_width', finalWidth);
                    } catch (e) {}
                }
            }

            // ========================================
            // TOP-CENTER TOAST NOTIFICATIONS
            // ========================================
            function closeTopToast(element) {
                const toast = element ? (element.classList.contains('top-toast') ? element : element.closest('.top-toast')) : null;
                if (!toast || toast.classList.contains('toast-hiding')) return;
                toast.classList.add('toast-hiding');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
            window.closeTopToast = closeTopToast;

            function initTopToastBehavior(toast) {
                const duration = parseInt(toast.dataset.autoDismiss, 10) || 3500;

                let dismissTimeout = setTimeout(() => {
                    closeTopToast(toast);
                }, duration);

                // Pause timer on hover
                toast.addEventListener('mouseenter', () => {
                    clearTimeout(dismissTimeout);
                });

                // Resume timer on mouse leave
                toast.addEventListener('mouseleave', () => {
                    dismissTimeout = setTimeout(() => {
                        closeTopToast(toast);
                    }, 1200);
                });
            }

            // Initialize any server-rendered toast notifications
            document.querySelectorAll('.top-toast').forEach(initTopToastBehavior);

            // Global helper if other scripts trigger a toast
            window.showTopToast = function(message, type = 'success', duration = 3500) {
                let container = document.getElementById('topToastContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'topToastContainer';
                    container.className = 'top-toast-container';
                    document.body.prepend(container);
                }
                const toast = document.createElement('div');
                toast.className = `top-toast top-toast--${type}`;
                toast.setAttribute('role', 'alert');
                toast.dataset.autoDismiss = duration;

                let iconClass = 'bi-check-circle-fill';
                if (type === 'error' || type === 'danger') iconClass = 'bi-exclamation-triangle-fill';
                if (type === 'warning') iconClass = 'bi-exclamation-circle-fill';
                if (type === 'info') iconClass = 'bi-info-circle-fill';

                toast.innerHTML = `
                    <div class="top-toast-icon"><i class="bi ${iconClass}"></i></div>
                    <div class="top-toast-content">${message}</div>
                    <button type="button" class="top-toast-close" aria-label="Tutup" onclick="closeTopToast(this)">
                        <i class="bi bi-x-lg"></i>
                    </button>
                `;
                container.appendChild(toast);
                initTopToastBehavior(toast);
            };

            // Global safety: dismiss any Swal toast on click so it never stays stuck
            document.addEventListener('click', function(e) {
                const swalToast = e.target.closest('.swal2-toast');
                if (swalToast) {
                    if (typeof Swal !== 'undefined') {
                        try { Swal.close(); } catch (_) {}
                    }
                    swalToast.remove();
                    const container = document.querySelector('.swal2-container:has(.swal2-toast), .swal2-container.swal2-top');
                    if (container && !container.querySelector('.swal2-modal')) {
                        container.remove();
                    }
                }
            });

            // Universal Double-Submit & Spam-Enter Protection
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (!form || form.tagName !== 'FORM') return;

                if (form.dataset.isSubmitting === 'true') {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }

                form.dataset.isSubmitting = 'true';

                const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitBtns.forEach(btn => {
                    btn.disabled = true;
                    btn.classList.add('disabled', 'opacity-50');
                });

                setTimeout(() => {
                    form.dataset.isSubmitting = 'false';
                    submitBtns.forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('disabled', 'opacity-50');
                    });
                }, 3500);
            }, true);

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const form = e.target.closest('form');
                    if (form && form.dataset.isSubmitting === 'true') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                }
            }, true);
        });
    </script>

    <!-- Seamless Fast SPA Content Navigator (Sidebar remains 100% stationary, only content body refreshes) -->
    <script id="spaEngineScript">
        (function() {
            let activeAbortController = null;
            let progressTimer = null;
            let progressFailsafe = null;

            function startProgress() {
                clearTimeout(progressFailsafe);
                clearTimeout(progressTimer);

                let el = document.getElementById('spaProgressBar');
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'spaProgressBar';
                    el.style.cssText = 'position:fixed;top:0;left:0;height:2.5px;width:0%;background:linear-gradient(90deg,#2563eb,#38bdf8);z-index:99999;transition:width 0.12s ease-out,opacity 0.2s ease;box-shadow:0 0 8px rgba(37,99,235,0.6);pointer-events:none;';
                    document.body.appendChild(el);
                }
                el.style.opacity = '1';
                el.style.width = '35%';

                progressTimer = setTimeout(() => {
                    if (el && el.parentElement) el.style.width = '75%';
                }, 60);

                // Failsafe auto-clear: loading bar will NEVER get stuck under any circumstance
                progressFailsafe = setTimeout(() => {
                    finishProgress();
                }, 4000);
            }

            function finishProgress() {
                clearTimeout(progressFailsafe);
                clearTimeout(progressTimer);
                const el = document.getElementById('spaProgressBar');
                if (el) {
                    el.style.width = '100%';
                    setTimeout(() => {
                        el.style.opacity = '0';
                        setTimeout(() => {
                            if (el && el.parentElement) el.remove();
                        }, 200);
                    }, 80);
                }
            }

            function isInternalSpaUrl(urlObj) {
                if (!urlObj || urlObj.origin !== window.location.origin) return false;
                const p = urlObj.pathname;
                if (p === '/logout' || p === '/login' || p === '/register') return false;
                if (p.includes('/pdf') || p.includes('/excel') || p.includes('/export') || p.includes('/download') || p.includes('/template')) return false;
                return true;
            }

            async function loadSpaContent(url, pushState = true, preserveScroll = false) {
                if (activeAbortController) {
                    activeAbortController.abort();
                }
                activeAbortController = new AbortController();

                startProgress();

                try {
                    let prevScrollY = 0;
                    let prevMainScrollTop = 0;
                    const sidebar = document.getElementById('sidebar');
                    const prevSidebarScrollTop = sidebar ? sidebar.scrollTop : 0;

                    if (preserveScroll) {
                        prevScrollY = window.scrollY || window.pageYOffset || 0;
                        const mainWrapper = document.querySelector('.main-wrapper');
                        if (mainWrapper) prevMainScrollTop = mainWrapper.scrollTop || 0;
                    }

                    const response = await fetch(url, {
                        signal: activeAbortController.signal,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-SPA-Request': 'true'
                        }
                    });

                    if (!response.ok) {
                        finishProgress();
                        window.location.href = url;
                        return;
                    }

                    const htmlText = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(htmlText, 'text/html');

                    const newContentBody = doc.querySelector('main.content-body');
                    const currentContentBody = document.querySelector('main.content-body');

                    if (!newContentBody || !currentContentBody) {
                        finishProgress();
                        window.location.href = url;
                        return;
                    }

                    // Close any open modals and cleanup backdrops
                    document.querySelectorAll('.modal.show').forEach(m => {
                        const inst = bootstrap.Modal.getInstance(m);
                        if (inst) inst.hide();
                    });
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.body.style.removeProperty('overflow');

                    // 1. Update document title
                    if (doc.title) {
                        document.title = doc.title;
                    }

                    // 2. Update topbar breadcrumb
                    const newBreadcrumb = doc.querySelector('#topbarBreadcrumb');
                    const currentBreadcrumb = document.querySelector('#topbarBreadcrumb');
                    if (newBreadcrumb && currentBreadcrumb) {
                        currentBreadcrumb.innerHTML = newBreadcrumb.innerHTML;
                    }

                    // 2b. Sync server-flashed session toasts if any
                    const newToasts = doc.querySelectorAll('#topToastContainer .top-toast');
                    if (newToasts && newToasts.length > 0) {
                        const currentToastContainer = document.getElementById('topToastContainer');
                        if (currentToastContainer) {
                            newToasts.forEach(t => {
                                currentToastContainer.appendChild(t);
                                if (typeof initTopToastBehavior === 'function') {
                                    initTopToastBehavior(t);
                                }
                            });
                        }
                    }

                    // 3. Swap only the main content area (sidebar remains completely untouched)
                    currentContentBody.innerHTML = newContentBody.innerHTML;

                    // 4. Update browser URL history
                    if (pushState) {
                        window.history.pushState({ spa: true, url: url }, '', url);
                    }

                    // 4b. Auto collapse sidebar on urusan table page, and auto open on dashboard/catalog pages
                    const isTablePage = (doc.body && doc.body.classList.contains('sidebar-collapsed'))
                        || url.includes('/urusan/')
                        || Boolean(doc.querySelector('#tableTabs, .custom-data-table'));

                    if (window.innerWidth >= 992) {
                        document.body.classList.add('is-toggling');
                        if (isTablePage) {
                            document.body.classList.add('sidebar-collapsed');
                        } else {
                            document.body.classList.remove('sidebar-collapsed');
                        }
                        setTimeout(() => {
                            document.body.classList.remove('is-toggling');
                        }, 260);
                    } else {
                        // On mobile, close drawer when navigating
                        document.body.classList.remove('sidebar-mobile-open');
                    }

                    // 5. Update sidebar active status smoothly without closing any folders
                    updateSidebarActiveState(url);

                    // 6. Execute scripts for the new page
                    executePageScripts(doc);

                    // 7. Restore scroll position
                    if (sidebar && prevSidebarScrollTop) {
                        sidebar.scrollTop = prevSidebarScrollTop;
                    }
                    if (preserveScroll) {
                        window.scrollTo({ top: prevScrollY, behavior: 'instant' });
                        const mainWrapper = document.querySelector('.main-wrapper');
                        if (mainWrapper) mainWrapper.scrollTop = prevMainScrollTop;
                    } else if (pushState) {
                        window.scrollTo({ top: 0, behavior: 'instant' });
                        const mainWrapper = document.querySelector('.main-wrapper');
                        if (mainWrapper) mainWrapper.scrollTop = 0;
                    }

                } catch (err) {
                    if (err.name === 'AbortError') return;
                    console.error('SPA load error, falling back to full navigation:', err);
                    window.location.href = url;
                } finally {
                    finishProgress();
                }
            }
            window.loadSpaContent = loadSpaContent;

            // Universal Partial Content Refresh Helper:
            // Refreshes ONLY the main content area, keeping the sidebar 100% steady and untouched!
            window.refreshPageContent = async function(customUrl = null, preserveScroll = true) {
                const target = customUrl || window.location.href;
                return await loadSpaContent(target, false, preserveScroll);
            };

            function updateSidebarActiveState(url) {
                const urlObj = new URL(url, window.location.origin);
                const path = urlObj.pathname;

                const isDashboard = path === '/dashboard' || path === '/';
                const dashboardLink = document.querySelector('.sidebar-dashboard-link');

                document.querySelectorAll('.urusan-item.urusan-item--active').forEach(el => {
                    el.classList.remove('urusan-item--active');
                });

                if (isDashboard) {
                    if (dashboardLink) dashboardLink.classList.add('active');
                } else {
                    if (dashboardLink) dashboardLink.classList.remove('active');

                    const segments = path.split('/').filter(Boolean);
                    if (segments[0] === 'urusan' && segments[1]) {
                        const urusanKey = decodeURIComponent(segments[1]);
                        let targetRow = document.querySelector(`.urusan-item[data-urusan-slug="${urusanKey}"]`)
                            || document.querySelector(`.urusan-item[data-urusan-id="${urusanKey}"]`);

                        if (!targetRow) {
                            const matchingLink = document.querySelector(`.urusan-item a[href*="/urusan/${urusanKey}"]`);
                            if (matchingLink) targetRow = matchingLink.closest('.urusan-item');
                        }

                        if (targetRow) {
                            targetRow.classList.add('urusan-item--active');

                            // Open parent folder if collapsed
                            const parentCollapse = targetRow.closest('.kelompok-collapse');
                            if (parentCollapse && !parentCollapse.classList.contains('show')) {
                                const bsCol = bootstrap.Collapse.getOrCreateInstance(parentCollapse, { toggle: false });
                                bsCol.show();
                            }

                            if (typeof setSidebarActionTarget === 'function') {
                                setSidebarActionTarget(targetRow);
                            }
                        }
                    }
                }
            }

            function executePageScripts(doc) {
                // 1. Preserve & inject dynamic view style tags from the new document
                doc.querySelectorAll('style[id^="view-style"], .content-body style').forEach(st => {
                    if (st.id && document.getElementById(st.id)) {
                        document.getElementById(st.id).textContent = st.textContent;
                        return;
                    }
                    const newSt = document.createElement('style');
                    if (st.id) newSt.id = st.id;
                    newSt.textContent = st.textContent;
                    document.head.appendChild(newSt);
                });

                // 2. Extract ONLY scripts belonging to the newly loaded page:
                // - Scripts placed inside main.content-body
                // - Scripts placed inside #pageScriptsContainer
                // Layout, core app, and sidebar scripts are static and MUST NEVER be re-run!
                const scriptElements = [];
                const newContentBody = doc.querySelector('main.content-body');
                if (newContentBody) {
                    newContentBody.querySelectorAll('script').forEach(s => {
                        if (!s.src) scriptElements.push(s);
                    });
                }

                const newScriptsContainer = doc.getElementById('pageScriptsContainer');
                if (newScriptsContainer) {
                    newScriptsContainer.querySelectorAll('script').forEach(s => {
                        if (!s.src) scriptElements.push(s);
                    });
                }

                // Sync current pageScriptsContainer DOM if exists
                const currentScriptsContainer = document.getElementById('pageScriptsContainer');
                if (currentScriptsContainer && newScriptsContainer) {
                    currentScriptsContainer.innerHTML = newScriptsContainer.innerHTML;
                }

                // 3. Execute page scripts safely inside isolated function scope
                scriptElements.forEach(oldScript => {
                    try {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.textContent = `(function(){\ntry {\n${oldScript.textContent}\n} catch(err){\nconsole.warn('Page script execution notice:', err);\n}\n})();`;
                        document.body.appendChild(newScript);
                        setTimeout(() => newScript.remove(), 100);
                    } catch (e) {
                        console.warn('Script element injection notice:', e);
                    }
                });

                // Re-initialize standard interactive components if present
                if (typeof window.initUrusanPage === 'function') {
                    try { window.initUrusanPage(); } catch(e) { console.error(e); }
                }
                if (typeof window.initTableResizing === 'function') {
                    try { window.initTableResizing(); } catch(e) { console.error(e); }
                }
                if (typeof window.applyFooterTotalPreference === 'function') {
                    try { window.applyFooterTotalPreference(); } catch(e) { console.error(e); }
                }
                if (typeof window.initDashboardCharts === 'function') {
                    try { window.initDashboardCharts(); } catch(e) { console.error(e); }
                }

                document.querySelectorAll('.content-body [data-bs-toggle="tooltip"]').forEach(el => {
                    try { new bootstrap.Tooltip(el); } catch (_) {}
                });
            }

            // Keyboard shortcut interception for Refresh (F5 / Ctrl+R / Cmd+R)
            // Ensures refresh updates ONLY the page content without touching/reloading the sidebar
            window.addEventListener('keydown', function(e) {
                if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && (e.key === 'r' || e.key === 'R'))) {
                    e.preventDefault();
                    window.refreshPageContent(window.location.href, true);
                }
            });

            // Universal Link Click Interception across entire app
            document.addEventListener('click', function(e) {
                if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

                const anchor = e.target.closest('a');
                if (!anchor) return;

                const href = anchor.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                if (anchor.getAttribute('target') === '_blank' || anchor.hasAttribute('download') || anchor.hasAttribute('data-no-spa') || anchor.getAttribute('data-no-spa') === 'true') return;

                if (anchor.getAttribute('data-bs-toggle') || anchor.classList.contains('btn-confirm-delete')) return;
                if (href.includes('/pdf') || href.includes('/export') || href.includes('/download') || href.includes('/excel') || href.includes('/template')) return;

                let targetUrl;
                try {
                    targetUrl = new URL(anchor.href, window.location.origin);
                } catch(err) {
                    return;
                }

                if (isInternalSpaUrl(targetUrl)) {
                    e.preventDefault();
                    loadSpaContent(targetUrl.href, true);
                }
            });

            // Safe extractor for form action attribute that cannot be shadowed by child elements like select[name="action"]
            function getSafeFormAction(form) {
                if (!form) return window.location.href;
                const rawAction = form.getAttribute('action');
                if (rawAction && typeof rawAction === 'string' && rawAction.trim() !== '') {
                    return rawAction;
                }
                if (typeof form.action === 'string' && form.action.trim() !== '' && !form.action.includes('[object')) {
                    return form.action;
                }
                return window.location.href;
            }

            // Helper to programmatically submit forms via SPA without reloading sidebar
            window.submitSpaForm = async function(form) {
                if (!form) return;
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    const ev = new Event('submit', { cancelable: true, bubbles: true });
                    form.dispatchEvent(ev);
                }
            };

            // Helper to handle onchange on dropdown filters via SPA
            window.submitSpaFilter = function(form) {
                if (!form) return;
                const method = (form.method || 'GET').toUpperCase();
                if (method === 'GET') {
                    let formUrl;
                    try {
                        formUrl = new URL(getSafeFormAction(form), window.location.origin);
                    } catch (e) {
                        window.submitSpaForm(form);
                        return;
                    }
                    const formData = new FormData(form);
                    const searchParams = new URLSearchParams();
                    for (const [key, value] of formData.entries()) {
                        if (value !== '') {
                            searchParams.append(key, value);
                        }
                    }
                    formUrl.search = searchParams.toString();
                    loadSpaContent(formUrl.href, true, true);
                    return;
                }
                window.submitSpaForm(form);
            };

            // Universal Form Submit Interception:
            // Intercepts both GET (filters, search, pagination) and POST/PUT/DELETE
            // so that ANY interaction on the page only updates page content and NEVER reloads the sidebar!
            document.addEventListener('submit', async function(e) {
                if (e.defaultPrevented) return;
                const form = e.target;
                if (!form) return;
                if (form.hasAttribute('data-no-spa')) return;

                let formUrl;
                try {
                    formUrl = new URL(getSafeFormAction(form), window.location.origin);
                } catch(err) {
                    return;
                }

                if (!isInternalSpaUrl(formUrl)) return;

                // Never intercept forms inside sidebar or sidebar modals (Tambah Urusan, Tambah Kelompok, Rename/Edit)
                // so that tree changes in sidebar can update naturally
                if (form.closest('#sidebar') || form.closest('#modalTambahUrusanSidebar') || form.closest('#modalEditKelompokSidebar') || form.closest('#modalTambahKelompokSidebar')) return;

                const method = (form.method || 'GET').toUpperCase();

                // GET Form handling (e.g. search, filters)
                if (method === 'GET') {
                    if (form.id === 'tableSearchForm') return; // Handled client-side live search

                    e.preventDefault();
                    const formData = new FormData(form);
                    const searchParams = new URLSearchParams();
                    for (const [key, value] of formData.entries()) {
                        if (value !== '') {
                            searchParams.append(key, value);
                        }
                    }

                    formUrl.search = searchParams.toString();
                    loadSpaContent(formUrl.href, true, true);
                    return;
                }

                // POST / PUT / DELETE Form handling (e.g. Tambah Baris, Tambah Kolom, Atur Tabel, Sub-Kolom, Sub-Baris, Delete, etc.)
                e.preventDefault();

                const submitBtns = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitBtns.forEach(btn => {
                    btn.dataset.origHtml = btn.innerHTML;
                    btn.disabled = true;
                });

                // Hide any active modal smoothly
                const parentModal = form.closest('.modal');
                if (parentModal) {
                    const modalInstance = bootstrap.Modal.getInstance(parentModal);
                    if (modalInstance) modalInstance.hide();
                }

                try {
                    startProgress();
                    const formData = new FormData(form);

                    const response = await fetch(formUrl.href, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-SPA-Request': 'true',
                            'Accept': 'application/json, text/html'
                        }
                    });

                    const contentType = response.headers.get('content-type') || '';
                    let redirectUrl = null;
                    let successMsg = null;

                    if (contentType.includes('application/json')) {
                        const data = await response.json();
                        if (data.redirect_url) {
                            redirectUrl = data.redirect_url;
                        }
                        if (data.message) {
                            successMsg = data.message;
                        }
                        if (data.success === false) {
                            finishProgress();
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                            } else {
                                alert(data.message || 'Terjadi kesalahan.');
                            }
                            submitBtns.forEach(btn => {
                                if (btn.dataset.origHtml) btn.innerHTML = btn.dataset.origHtml;
                                btn.disabled = false;
                            });
                            return;
                        }
                    }

                    // Close any leftover backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.querySelectorAll('.modal.show').forEach(m => {
                        m.classList.remove('show');
                        m.style.display = 'none';
                    });
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.body.style.removeProperty('overflow');

                    // Refresh content via SPA without touching the sidebar
                    let targetUrl = redirectUrl;
                    if (!targetUrl && response.redirected) {
                        targetUrl = response.url;
                    }
                    if (!targetUrl) {
                        targetUrl = window.location.href;
                    }
                    await loadSpaContent(targetUrl, false, true);

                    if (successMsg) {
                        if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                            Swal.close();
                        }
                        if (typeof window.showTopToast === 'function') {
                            window.showTopToast(successMsg, 'success', 1800);
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
                                title: successMsg
                            });
                        }
                    }
                } catch (err) {
                    console.error('SPA Form submission error:', err);
                    finishProgress();
                    form.submit(); // fallback to normal submit if unexpected network exception
                } finally {
                    submitBtns.forEach(btn => {
                        if (btn.dataset.origHtml) btn.innerHTML = btn.dataset.origHtml;
                        btn.disabled = false;
                    });
                }
            });

            window.addEventListener('popstate', function(e) {
                loadSpaContent(window.location.href, false);
            });
        })();
    </script>

    <div id="pageScriptsContainer" style="display:none;" data-spa-dynamic="true">
        @stack('scripts')
    </div>
</body>
</html>
