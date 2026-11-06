<header class="topbar bg-white border-bottom shadow-xs px-3 py-1 d-flex align-items-center justify-content-between sticky-top" style="min-height: 44px;">
    <!-- Sidebar toggle & Breadcrumb -->
    <div class="d-flex align-items-center gap-1.5 min-w-0">
        <button type="button" class="btn btn-outline-light text-dark border-0 p-1 flex-shrink-0" id="sidebarToggleBtn" title="Buka/Tutup Sidebar">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div id="topbarBreadcrumb" class="d-none d-md-flex align-items-center min-w-0 ms-1">
            @yield('breadcrumb')
        </div>
    </div>

    <!-- User Dropdown -->
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-sm btn-light border-0 d-flex align-items-center gap-1.5 py-0.5 px-2 rounded-pill shadow-xs" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 0.72rem;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="text-start d-none d-lg-block pe-1">
                    <span class="d-block fw-semibold text-dark" style="font-size: 0.78rem; line-height: 1.1;">{{ auth()->user()->name ?? 'Pengguna' }}</span>
                    <span class="badge {{ auth()->user()->role_badge_class }}" style="font-size: 0.55rem; padding: 0.15em 0.4em;">{{ auth()->user()->role_label }}</span>
                </div>
                <i class="bi bi-chevron-down text-muted" style="font-size: 0.65rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1 py-1.5" style="min-width: 210px; font-size: 0.8rem;">
                <li class="px-3 py-1.5 border-bottom mb-1">
                    <span class="d-block fw-bold text-dark" style="font-size: 0.82rem;">{{ auth()->user()->name ?? 'Pengguna' }}</span>
                    <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">{{ auth()->user()->email ?? '-' }}</small>
                    <span class="badge {{ auth()->user()->role_badge_class }} mt-1 text-uppercase" style="font-size: 0.62rem;">
                        Peran: {{ auth()->user()->role_label }}
                    </span>
                </li>
                <li>
                    <a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2 text-dark" href="{{ route('profile') }}">
                        <i class="bi bi-person text-secondary"></i> Profil & Sandi
                    </a>
                </li>
                @if(auth()->check() && auth()->user()->isSuperAdmin())
                    <li>
                        <a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2 text-dark" href="{{ route('users.index') }}">
                            <i class="bi bi-people text-secondary"></i> Kelola Pengguna
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2 text-dark" href="{{ route('activity.index') }}">
                            <i class="bi bi-clock-history text-secondary"></i> Log Aktivitas Sistem
                        </a>
                    </li>
                @endif
                @if(auth()->check() && auth()->user()->isSuperAdmin())
                    <li>
                        <a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2 text-dark" href="{{ route('settings.index') }}">
                            <i class="bi bi-sliders text-secondary"></i> Pengaturan Portal Login
                        </a>
                    </li>
                @endif
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="m-0" onsubmit="try { sessionStorage.clear(); } catch(e) {}">
                        @csrf
                        <button type="submit" class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2 text-danger">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
