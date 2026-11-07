<aside id="sidebar" class="sidebar bg-white border-end">
    <!-- Brand / Header -->
    <div class="sidebar-header d-flex align-items-center justify-content-start px-3 py-2 border-bottom" style="min-height: 52px; flex-shrink: 0;">
        <a href="{{ route('dashboard') }}" class="sidebar-brand-link d-flex align-items-center text-decoration-none text-truncate flex-grow-1" style="gap: 8px;">
            <div class="brand-logo-wrap d-flex align-items-center justify-content-center flex-shrink-0">
                <img src="{{ asset('logo.png') }}?v=2" alt="Logo BAPPERIDA" width="28" height="32" fetchpriority="high" style="object-fit: contain;">
            </div>
            <div class="brand-text d-flex align-items-center text-truncate">
                <span class="brand-title text-truncate fw-bold text-dark text-nowrap" style="font-size: 0.80rem; letter-spacing: 0.02em;" title="BAPPERIDA KOTA TASIKMALAYA">BAPPERIDA KOTA TASIKMALAYA</span>
            </div>
        </a>
        <button type="button" class="btn btn-sm btn-link text-secondary d-lg-none ms-1 p-0" id="sidebarCloseBtn" style="flex-shrink: 0;" title="Tutup Sidebar">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <!-- Navigation: Dashboard -->
    <div class="px-2 py-1.5" style="flex-shrink: 0;">
        <a href="{{ route('dashboard') }}" class="sidebar-dashboard-link rounded-2 px-2 d-flex align-items-center gap-2 text-decoration-none {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <div class="dashboard-icon-wrap d-flex align-items-center justify-content-center rounded-2">
                <i class="bi bi-speedometer2"></i>
            </div>
            <span class="dashboard-text text-truncate flex-grow-1">Dashboard</span>
        </a>
    </div>

    <!-- Live Search Urusan -->
    <div class="px-2 pb-1.5 pt-0.5" style="flex-shrink: 0;">
        <div class="position-relative">
            <div class="input-group input-group-sm rounded-2 overflow-hidden sidebar-search-box">
                <span class="input-group-text bg-transparent border-0 pe-1 text-slate-400 py-1 ps-2.5" style="font-size: 0.78rem;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                       id="sidebarSearchInput"
                       class="form-control form-control-sm bg-transparent border-0 shadow-none py-1 ps-1 pe-1 text-dark"
                       style="font-size: 0.78rem;"
                       placeholder="Cari nama urusan..."
                       autocomplete="off"
                       aria-label="Cari nama urusan"
                       oninput="handleSidebarSearch(this.value)"
                       onkeydown="if(event.key==='Escape') clearSidebarSearch()">
                <button type="button" 
                        class="btn btn-sm btn-link text-slate-400 text-decoration-none p-0 pe-2.5 border-0 d-none" 
                        id="sidebarSearchClearBtn" 
                        onclick="clearSidebarSearch()" 
                        title="Hapus pencarian">
                    <i class="bi bi-x-circle-fill" style="font-size: 0.78rem;"></i>
                </button>
            </div>
            <div id="sidebarSearchCount" class="d-none px-2 mt-1 d-flex align-items-center justify-content-between text-muted" style="font-size: 0.68rem; font-weight: 600;">
                <span id="sidebarSearchCountText"></span>
                <a href="javascript:void(0)" onclick="clearSidebarSearch()" class="text-primary text-decoration-none" style="font-size: 0.68rem;">Reset</a>
            </div>
        </div>
    </div>

    <!-- Section Header: URUSAN PEMERINTAHAN -->
    <div class="sidebar-section-header d-flex align-items-center justify-content-between px-3 py-1.5" style="flex-shrink: 0;">
        <span class="text-uppercase fw-bold ps-2" style="font-size: 0.68rem; letter-spacing: 0.08em; color: #64748b;">
            Urusan Pemerintahan
        </span>
        <!-- Aksi kontekstual mengikuti urusan yang di-hover atau sedang dibuka -->
        <div id="sidebarContextActions" class="d-flex align-items-center gap-1.5 pe-1">
            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <button type="button"
                        class="sidebar-icon-btn sidebar-icon-btn--teal"
                        onclick="runSidebarAddChildGroup()"
                        title="Tambah folder pada lokasi yang dipilih">
                    <i class="bi bi-folder-plus"></i>
                </button>
                <button type="button"
                        id="sidebarContextAdd"
                        class="sidebar-icon-btn sidebar-icon-btn--blue"
                        onclick="runSidebarContextAdd()"
                        title="Tambah urusan ke kelompok ini"
                        disabled>
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button type="button"
                        id="sidebarContextEdit"
                        class="sidebar-icon-btn sidebar-icon-btn--amber"
                        onclick="runSidebarContextEdit()"
                        title="Edit urusan"
                        disabled>
                    <i class="bi bi-pencil"></i>
                </button>
                <button type="button"
                        id="sidebarContextDelete"
                        class="sidebar-icon-btn sidebar-icon-btn--red"
                        onclick="runSidebarContextDelete()"
                        title="Hapus urusan"
                        disabled>
                    <i class="bi bi-trash3"></i>
                </button>
            @endif
            <button type="button"
                    class="sidebar-icon-btn sidebar-icon-btn--gray"
                    onclick="toggleAllKelompok()"
                    title="Buka / Tutup Semua">
                <i class="bi bi-arrows-collapse"></i>
            </button>
        </div>
    </div>

    <!-- Daftar Kelompok Urusan -->
    <div class="sidebar-menu-wrapper overflow-y-auto flex-grow-1 pb-2" style="background-color: #ffffff; width: 100%; overflow-x: hidden;">
        <!-- State Pencarian Kosong / Tidak Ditemukan -->
        <div id="sidebarSearchEmpty" class="text-center py-4 px-3 text-muted d-none">
            <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center p-2.5 mb-2 shadow-xs">
                <i class="bi bi-search text-secondary opacity-50 fs-5"></i>
            </div>
            <span class="small fw-bold d-block text-dark" style="font-size: 0.78rem;">Urusan Tidak Ditemukan</span>
            <span class="d-block mt-0.5 text-muted" style="font-size: 0.70rem;">Tidak ada urusan yang cocok dengan pencarian Anda</span>
        </div>

        @php
            $createdKelompokId = session('created_kelompok_id');
        @endphp
        @if(isset($sidebarKelompok) && $sidebarKelompok->count() > 0)
            @foreach($sidebarKelompok as $kelompok)
                @php
                    $isGroupOpen = false;

                    $containsCreatedKelompok = function ($group) use (&$containsCreatedKelompok, $createdKelompokId) {
                        if ((string) $group->id === (string) $createdKelompokId) {
                            return true;
                        }
                        return $group->children->contains(function ($child) use (&$containsCreatedKelompok, $createdKelompokId) {
                            return (string) $child->id === (string) $createdKelompokId || $containsCreatedKelompok($child);
                        });
                    };

                    if (request()->routeIs('urusan.show')) {
                        $currentUrusanId = (string) request()->route('id');
                        $containsActiveUrusan = function ($group) use (&$containsActiveUrusan, $currentUrusanId) {
                            $hasActiveUrusan = $group->urusans->contains(function ($u) use ($currentUrusanId) {
                                return (string) $u->id === $currentUrusanId || (string) $u->slug === $currentUrusanId;
                            });
                            return $hasActiveUrusan || $group->children->contains(function ($child) use (&$containsActiveUrusan) {
                                return $containsActiveUrusan($child);
                            });
                        };
                        if ($containsActiveUrusan($kelompok)) {
                            $isGroupOpen = true;
                        }
                    }

                    if (request()->routeIs('kelompok-urusan.show')) {
                        $currentKelompokId = (string) request()->route('id');
                        $containsActiveKelompok = function ($group) use (&$containsActiveKelompok, $currentKelompokId) {
                            if ((string) $group->id === $currentKelompokId) return true;
                            return $group->children->contains(function ($child) use (&$containsActiveKelompok, $currentKelompokId) {
                                return $containsActiveKelompok($child);
                            });
                        };
                        if ($containsActiveKelompok($kelompok)) {
                            $isGroupOpen = true;
                        }
                    }

                    if ($createdKelompokId && $containsCreatedKelompok($kelompok)) {
                        $isGroupOpen = true;
                    }
                @endphp

                <div class="kelompok-block px-2 mb-1"
                     data-kelompok-id="{{ $kelompok->id }}"
                     data-kelompok-kode="{{ $kelompok->kode }}"
                     data-kelompok-nama="{{ $kelompok->nama }}"
                     data-kelompok-keterangan="{{ $kelompok->keterangan }}">
                    <!-- Baris Kelompok -->
                    <div class="kelompok-header-row kelompok-normal-view d-flex align-items-center justify-content-between rounded-2 px-2 py-1"
                         data-kelompok-header>
                        <!-- Toggle accordion -->
                        <a class="d-flex align-items-center gap-1.5 text-decoration-none flex-grow-1 py-0.5 text-truncate tree-folder-toggle {{ $isGroupOpen ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse"
                           href="#collapseKelompok{{ $kelompok->id }}"
                           role="button"
                           aria-expanded="{{ $isGroupOpen ? 'true' : 'false' }}"
                           aria-controls="collapseKelompok{{ $kelompok->id }}"
                           style="min-width: 0;">
                            <i class="bi bi-chevron-right tree-chevron" aria-hidden="true"></i>
                            <span class="kelompok-badge" aria-hidden="true">
                                <i class="bi bi-folder-fill folder-icon--closed"></i>
                                <i class="bi bi-folder2-open folder-icon--open"></i>
                            </span>
                            <span class="text-truncate fw-semibold text-dark tree-label" title="{{ $kelompok->nama }}">
                                {{ $kelompok->nama }}
                            </span>
                        </a>
                    </div>

                    <!-- Form Edit Inline Kelompok -->
                    <div class="kelompok-edit-view d-none px-1 py-1" id="kelompokEditView{{ $kelompok->id }}" style="border-radius: 6px; background: #fffbeb;">
                        <form method="POST" action="/kelompok-urusan/{{ $kelompok->id }}" class="d-flex align-items-center gap-2">
                            @csrf @method('PUT')
                            <input type="hidden" name="kode" value="{{ $kelompok->kode }}">
                            <input type="hidden" name="keterangan" value="{{ $kelompok->keterangan }}">
                            <div class="d-flex align-items-center gap-1 flex-grow-1 bg-white rounded-2 border border-warning overflow-hidden px-2 shadow-sm">
                                <span class="kelompok-badge" style="width: 18px; height: 18px; min-width: 18px; font-size: 0.65rem;"><i class="bi bi-folder-fill text-warning"></i></span>
                                <input type="text"
                                       name="nama"
                                       id="editInlineKelompokInput{{ $kelompok->id }}"
                                       class="form-control border-0 shadow-none px-1 py-0.5 text-dark"
                                       style="font-size: 0.8rem;"
                                       value="{{ $kelompok->nama }}"
                                       placeholder="Nama folder..."
                                       required
                                       onkeydown="if(event.key==='Escape') hideInlineEditKelompok({{ $kelompok->id }})">
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm px-2 py-0.5 text-dark fw-bold" title="Simpan" style="font-size: 0.75rem; flex-shrink: 0;">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-light btn-sm px-2 py-0.5 border" title="Batal" onclick="hideInlineEditKelompok({{ $kelompok->id }})" style="font-size: 0.75rem; flex-shrink: 0;">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Sub-menu Urusan -->
                    <div class="collapse {{ $isGroupOpen ? 'show' : '' }} kelompok-collapse" id="collapseKelompok{{ $kelompok->id }}">
                        <ul class="list-unstyled urusan-list">
                            {{-- Subfolder ditampilkan di dalam urusan-list agar sejajar dengan urusan --}}
                            @foreach($kelompok->children as $childKelompok)
                                @include('layouts.partials.sidebar-kelompok', ['kelompok' => $childKelompok])
                            @endforeach


                            <li class="mt-1 d-none" id="inlineKelompokFormRow{{ $kelompok->id }}">
                                <div style="background: #f0f9ff; border-radius: 6px; padding: 4px;">
                                    <form method="POST" action="{{ route('kelompok-urusan.store') }}" class="d-flex align-items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $kelompok->id }}">
                                        <div class="d-flex align-items-center gap-1 flex-grow-1 bg-white rounded-2 border border-primary overflow-hidden px-2 shadow-sm">
                                            <i class="bi bi-plus-circle text-primary" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                            <input type="text"
                                                   name="nama"
                                                   id="inlineKelompokInput{{ $kelompok->id }}"
                                                   class="form-control border-0 shadow-none px-1 py-1 text-dark"
                                                   style="font-size: 0.8rem;"
                                                   placeholder="Nama folder baru..."
                                                   maxlength="255"
                                                   required
                                                   onkeydown="if(event.key==='Escape') hideInlineKelompokForm({{ $kelompok->id }})">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm px-2 py-1 fw-bold" title="Simpan folder" style="font-size: 0.78rem; flex-shrink: 0;">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm px-2 py-1 border" title="Batalkan" aria-label="Batalkan tambah folder" onclick="hideInlineKelompokForm({{ $kelompok->id }})" style="font-size: 0.78rem; flex-shrink: 0;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>

                            <!-- Form Inline Tambah Urusan -->
                            <li class="mt-1 d-none" id="inlineFormRow{{ $kelompok->id }}">
                                <div style="background: #f0f9ff; border-radius: 6px; padding: 4px;">
                                    <form method="POST" action="{{ route('urusan.store') }}" class="d-flex align-items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="kelompok_urusan_id" value="{{ $kelompok->id }}">
                                        <div class="d-flex align-items-center gap-1 flex-grow-1 bg-white rounded-2 border border-primary overflow-hidden px-2 shadow-sm">
                                            <i class="bi bi-plus-circle text-primary" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                            <input type="text"
                                                   name="nama_urusan"
                                                   id="inlineInput{{ $kelompok->id }}"
                                                   class="form-control border-0 shadow-none px-1 py-1 text-dark"
                                                   style="font-size: 0.8rem;"
                                                   placeholder="Nama urusan baru..."
                                                   required
                                                   onkeydown="if(event.key==='Escape') hideInlineForm({{ $kelompok->id }})">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm px-2 py-1 fw-bold" title="Simpan" style="font-size: 0.78rem; flex-shrink: 0;">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm px-2 py-1 border" title="Batalkan" aria-label="Batalkan tambah urusan" onclick="hideInlineForm({{ $kelompok->id }})" style="font-size: 0.78rem; flex-shrink: 0;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            @endforeach
        @else
            <div class="px-3 py-2 text-secondary small fst-italic">Belum ada kelompok urusan</div>
        @endif
        <div class="d-none px-2 mt-1" id="inlineKelompokRootFormRow">
            <div style="background: #f0f9ff; border-radius: 6px; padding: 4px;">
                <form method="POST" action="{{ route('kelompok-urusan.store') }}" class="d-flex align-items-center gap-2">
                    @csrf
                    <input type="hidden" name="parent_id" value="">
                    <div class="d-flex align-items-center gap-1 flex-grow-1 bg-white rounded-2 border border-primary overflow-hidden px-2 shadow-sm">
                        <i class="bi bi-plus-circle text-primary" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                        <input type="text"
                               name="nama"
                               id="inlineKelompokRootInput"
                               class="form-control border-0 shadow-none px-1 py-1 text-dark"
                               style="font-size: 0.8rem;"
                               placeholder="Nama folder baru..."
                               maxlength="255"
                               required
                               onkeydown="if(event.key==='Escape') hideInlineKelompokForm(null)">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-2 py-1 fw-bold" title="Simpan folder" style="font-size: 0.78rem; flex-shrink: 0;">
                        <i class="bi bi-check-lg"></i>
                    </button>
                    <button type="button" class="btn btn-light btn-sm px-2 py-1 border" title="Batalkan" aria-label="Batalkan tambah folder" onclick="hideInlineKelompokForm(null)" style="font-size: 0.78rem; flex-shrink: 0;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Footer: Keluar -->
    <div class="sidebar-footer border-top" style="background: #ffffff; border-color: #e2e8f0 !important; flex-shrink: 0; padding: 12px 14px 16px 14px;">
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2 py-1.5 fw-semibold" style="font-size: 0.84rem; border-radius: 6px;">
                <i class="bi bi-box-arrow-right" style="font-size: 0.95rem;"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>

    <!-- Drag Resizer Handle -->
    <div id="sidebarResizer" class="sidebar-resizer" title="Tarik untuk mengubah lebar sidebar (Klik 2x untuk reset)"></div>
</aside>

<!-- Hidden delete forms -->
<form id="formDeleteUrusanSidebar" method="POST" action="" class="d-none">
    @csrf @method('DELETE')
</form>
<form id="formDeleteKelompokSidebar" method="POST" action="" class="d-none">
    @csrf @method('DELETE')
</form>

<div class="modal fade" id="modalEditKelompokSidebar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-dark" style="border-radius: 12px; overflow: hidden;">
            <form id="formEditKelompokSidebar" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-0 px-4 pt-4 pb-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
                    <h6 class="modal-title fw-bold text-white mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Kelompok Urusan</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="mb-3">
                        <label for="editSidebarKelompokKode" class="form-label fw-semibold">Kode Kelompok</label>
                        <input id="editSidebarKelompokKode" name="kode" type="text" class="form-control" maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label for="editSidebarKelompokNama" class="form-label fw-semibold">Nama Kelompok</label>
                        <input id="editSidebarKelompokNama" name="nama" type="text" class="form-control" maxlength="255" required>
                    </div>
                    <div>
                        <label for="editSidebarKelompokKeterangan" class="form-label fw-semibold">Keterangan</label>
                        <textarea id="editSidebarKelompokKeterangan" name="keterangan" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL TAMBAH URUSAN ===== -->
<div class="modal fade" id="modalTambahUrusanSidebar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-dark" style="border-radius: 12px; overflow: hidden;">
            <form method="POST" action="{{ route('urusan.store') }}">
                @csrf
                <!-- Header -->
                <div class="modal-header border-0 px-4 pt-4 pb-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: rgba(59,130,246,0.2);">
                            <i class="bi bi-file-earmark-plus fs-5" style="color: #60a5fa;"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Tambah Urusan Pemerintahan</h6>
                            <small class="text-white opacity-50" style="font-size: 0.72rem;">Tambahkan bidang urusan ke dalam kelompok</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <!-- Body -->
                <div class="modal-body px-4 py-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark mb-1" style="font-size: 0.85rem;">Kelompok Urusan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-collection text-primary"></i>
                            </span>
                            <select name="kelompok_urusan_id" id="sidebarSelectKelompok" class="form-select border-start-0 ps-0" required>
                                @if(isset($sidebarKelompok))
                                    @foreach($sidebarKelompok as $kel)
                                        <option value="{{ $kel->id }}">{{ $kel->kode }}. {{ $kel->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark mb-1" style="font-size: 0.85rem;">Nama Urusan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-card-text text-primary"></i>
                            </span>
                            <input type="text" name="nama_urusan" class="form-control border-start-0 ps-0" placeholder="Contoh: Kelautan dan Perikanan" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark mb-1" style="font-size: 0.85rem;">Deskripsi <span class="text-muted fw-normal">(Opsional)</span></label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Keterangan singkat urusan..."></textarea>
                    </div>
                </div>
                <!-- Footer -->
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Simpan Urusan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL EDIT URUSAN ===== -->
<div class="modal fade" id="modalEditUrusanSidebar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-dark" style="border-radius: 12px; overflow: hidden;">
            <form id="formEditUrusanSidebar" method="POST" action="">
                @csrf @method('PUT')
                <!-- Header -->
                <div class="modal-header border-0 px-4 pt-4 pb-3" style="background: linear-gradient(135deg, #0f172a 0%, #451a03 100%);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 40px; height: 40px; background: rgba(251,191,36,0.2);">
                            <i class="bi bi-pencil-square fs-5" style="color: #fbbf24;"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold text-white mb-0">Edit Urusan Pemerintahan</h6>
                            <small class="text-white opacity-50" style="font-size: 0.72rem;">Perbarui data bidang urusan</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <!-- Body -->
                <div class="modal-body px-4 py-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark mb-1" style="font-size: 0.85rem;">Kelompok Urusan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-collection text-warning"></i>
                            </span>
                            <select name="kelompok_urusan_id" id="editSelectKelompok" class="form-select border-start-0 ps-0" required>
                                @if(isset($sidebarKelompok))
                                    @foreach($sidebarKelompok as $kel)
                                        <option value="{{ $kel->id }}">{{ $kel->kode }}. {{ $kel->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark mb-1" style="font-size: 0.85rem;">Nama Urusan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-card-text text-warning"></i>
                            </span>
                            <input type="text" name="nama_urusan" id="editNamaUrusan" class="form-control border-start-0 ps-0" required placeholder="Nama urusan">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark mb-1" style="font-size: 0.85rem;">Deskripsi <span class="text-muted fw-normal">(Opsional)</span></label>
                        <textarea name="deskripsi" id="editDeskripsiUrusan" class="form-control" rows="2" placeholder="Keterangan singkat urusan..."></textarea>
                    </div>
                    <!-- Hidden inputs preserved for server -->
                    <input type="hidden" name="nomor" id="editNomorUrusan">
                    <input type="hidden" name="icon" id="editIconUrusan">
                </div>
                <!-- Footer -->
                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-4 fw-semibold text-dark">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ========================================
   SIDEBAR LIVE SEARCH
   ======================================== */
.sidebar-search-box {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease-in-out;
}
.sidebar-search-box:focus-within {
    background-color: #ffffff;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
}
.sidebar-search-box input {
    font-size: 0.78rem;
}
.sidebar-search-box input::placeholder {
    color: #94a3b8;
    font-size: 0.76rem;
}
.sidebar-search-box #sidebarSearchClearBtn {
    color: #94a3b8;
    transition: color 0.15s ease;
}
.sidebar-search-box #sidebarSearchClearBtn:hover {
    color: #475569;
}
.sidebar-search-highlight {
    background-color: #fef08a !important;
    color: #0f172a !important;
    padding: 0 2px;
    border-radius: 2px;
    font-weight: 700;
}

/* ========================================
   BRAND HEADER
   ======================================== */
.sidebar-header {
    position: relative;
    justify-content: flex-start !important;
    background: #ffffff;
    border-color: #e2e8f0 !important;
}
.sidebar-brand-link {
    flex: 1 1 auto !important;
    justify-content: flex-start !important;
    gap: 12px !important;
    transition: opacity 0.15s ease;
}
.sidebar-brand-link:hover {
    opacity: 0.9;
}
.sidebar-header #sidebarCloseBtn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
}
.brand-logo-wrap {
    width: 32px;
    height: 36px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.brand-logo-wrap img {
    display: block;
    max-height: 36px;
    max-width: 32px;
    width: auto;
    height: auto;
    object-fit: contain;
}
.brand-title {
    color: #0f172a;
    font-size: 0.80rem;
    font-weight: 800 !important;
    letter-spacing: 0.02em;
    line-height: 1.2;
    white-space: nowrap;
}

.sidebar-footer {
    padding: 12px 14px 16px 14px !important;
    background: #ffffff !important;
    border-top: 1px solid #e2e8f0 !important;
    margin-top: auto;
}

/* ========================================
   SIDEBAR ICON BUTTON SYSTEM
   Selalu terlihat, berbeda warna per fungsi
   ======================================== */

/* Base icon button */
.sidebar-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.72rem;
    width: 20px;
    height: 20px;
    transition: all 0.15s ease;
    flex-shrink: 0;
    padding: 0;
}
/* Small variant (kelompok row) */
.sidebar-icon-btn--sm {
    width: 18px;
    height: 18px;
    font-size: 0.68rem;
}
/* Extra small variant (urusan row) */
.sidebar-icon-btn--xs {
    width: 20px;
    height: 20px;
    font-size: 0.7rem;
    border-radius: 4px;
}

/* Color variants */
.sidebar-icon-btn--blue {
    background: rgba(59, 130, 246, 0.15);
    color: #2563eb;
}
.sidebar-icon-btn--blue:hover {
    background: rgba(59, 130, 246, 0.35);
    color: #1d4ed8;
}

.sidebar-icon-btn--teal {
    background: rgba(20, 184, 166, 0.15);
    color: #0d9488;
}
.sidebar-icon-btn--teal:hover {
    background: rgba(20, 184, 166, 0.35);
    color: #0f766e;
}

.sidebar-icon-btn--gray {
    background: rgba(100, 116, 139, 0.15);
    color: #64748b;
}
.sidebar-icon-btn--gray:hover {
    background: rgba(100, 116, 139, 0.3);
    color: #475569;
}

.sidebar-icon-btn--green {
    background: rgba(34, 197, 94, 0.15);
    color: #16a34a;
}
.sidebar-icon-btn--green:hover {
    background: rgba(34, 197, 94, 0.35);
    color: #15803d;
}

.sidebar-icon-btn--amber {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}
.sidebar-icon-btn--amber:hover {
    background: #fde68a;
    color: #92400e;
}

.sidebar-icon-btn--red {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
.sidebar-icon-btn--red:hover {
    background: #fecaca;
    color: #b91c1c;
}

/* ========================================
   SECTION HEADER
   ======================================== */
.sidebar-section-header {
    border-top: 1px solid rgba(100, 116, 139, 0.15);
    border-bottom: 1px solid rgba(100, 116, 139, 0.1);
    background: #f8fafc;
}

/* ========================================
   ANTIGRAVITY / VS CODE TREE VIEW
   ======================================== */
.tree-chevron {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    min-width: 16px;
    font-size: 0.72rem;
    color: #94a3b8;
    transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1), color 0.15s ease;
    flex-shrink: 0;
    line-height: 1;
}
/* When collapsed: points right (0deg) */
.tree-folder-toggle.collapsed .tree-chevron,
.tree-folder-toggle[aria-expanded="false"] .tree-chevron {
    transform: rotate(0deg);
}
/* When expanded: points down (90deg) */
.tree-folder-toggle:not(.collapsed) .tree-chevron,
.tree-folder-toggle[aria-expanded="true"] .tree-chevron {
    transform: rotate(90deg);
    color: #475569;
}
.kelompok-header-row:hover .tree-chevron {
    color: #1e293b;
}

.kelompok-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #f59e0b;
    font-size: 0.92rem;
    width: 18px;
    height: 18px;
    min-width: 18px;
    flex-shrink: 0;
    line-height: 1;
}
.kelompok-badge .folder-icon--open {
    display: none;
}
.kelompok-badge .folder-icon--closed {
    display: inline-block;
}
.tree-folder-toggle:not(.collapsed) .kelompok-badge .folder-icon--open,
.tree-folder-toggle[aria-expanded="true"] .kelompok-badge .folder-icon--open {
    display: inline-block;
}
.tree-folder-toggle:not(.collapsed) .kelompok-badge .folder-icon--closed,
.tree-folder-toggle[aria-expanded="true"] .kelompok-badge .folder-icon--closed {
    display: none;
}

.tree-item-spacer {
    width: 16px;
    min-width: 16px;
    height: 16px;
    display: inline-block;
    flex-shrink: 0;
}

.tree-file-icon {
    font-size: 0.84rem;
    width: 18px;
    min-width: 18px;
    text-align: center;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.tree-label {
    min-width: 0;
    font-size: 0.85rem;
    line-height: 1.4;
}

.kelompok-header-row {
    transition: background-color 0.12s ease;
    cursor: pointer;
    border-radius: 6px;
    min-height: 34px;
}
.kelompok-header-row:hover {
    background: #f1f5f9;
}
.kelompok-header-row.kelompok-active {
    background: #eff6ff !important;
    border: 1px solid #bfdbfe !important;
}
.kelompok-header-row.kelompok-active span.tree-label {
    color: #1d4ed8 !important;
    font-weight: 700 !important;
}
.kelompok-header-row.kelompok-active .kelompok-badge {
    color: #2563eb !important;
}

/* ========================================
   URUSAN LIST
   ======================================== */
.urusan-list {
    list-style: none;
    margin: 2px 0 4px 10px;
    padding: 0 0 0 10px;
    border-left: 1px solid #e2e8f0;
}
.urusan-list:hover {
    border-left-color: #cbd5e1;
}

.urusan-item {
    transition: background-color 0.12s ease;
}
.urusan-item > div {
    transition: background-color 0.12s ease;
    border-radius: 6px;
    min-height: 30px;
}
.urusan-item a {
    color: #1e293b;
    font-weight: 500;
}
.urusan-item:hover > div {
    background: #f1f5f9;
}
.urusan-item:hover a {
    color: #0f172a;
}
.urusan-item--active > div {
    background: #eff6ff !important;
    border-radius: 6px;
}
.urusan-item--active a {
    color: #1d4ed8 !important;
    font-weight: 600 !important;
}
.urusan-item--active a i.tree-file-icon {
    color: #2563eb !important;
    opacity: 1 !important;
}

.sidebar-section-header button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ========================================
   TAMBAH URUSAN LINK
   ======================================== */
.btn-tambah-urusan-link {
    background: none;
    border: none;
    color: #3b82f6;
    cursor: pointer;
    transition: color 0.15s, transform 0.15s;
    padding: 2px 6px;
}
.btn-tambah-urusan-link:hover {
    color: #7dd3fc;
    transform: translateX(2px);
    background: rgba(56, 189, 248, 0.08);
}

/* ========================================
   NAV LINK (Dashboard Utama)
   ======================================== */
.sidebar-dashboard-link {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #0f172a !important;
    font-size: 0.8rem;
    height: 32px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    transition: background-color 0.12s ease, border-color 0.12s ease, color 0.12s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    flex-shrink: 0;
}
.sidebar-dashboard-link .dashboard-icon-wrap {
    width: 20px;
    height: 20px;
    min-width: 20px;
    background: #e0f2fe;
    color: #0284c7;
    font-size: 0.78rem;
    transition: background-color 0.12s ease, color 0.12s ease;
    flex-shrink: 0;
}
.sidebar-dashboard-link .dashboard-text {
    color: #0f172a;
    font-weight: 600;
    font-size: 0.8rem;
    letter-spacing: -0.01em;
    line-height: 1;
}
.sidebar-dashboard-link:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a !important;
}
.sidebar-dashboard-link:hover .dashboard-icon-wrap {
    background: #bae6fd;
    color: #0369a1;
}
.sidebar-dashboard-link.active {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #1d4ed8 !important;
}
.sidebar-dashboard-link.active .dashboard-icon-wrap {
    background: #2563eb;
    color: #ffffff;
}
.sidebar-dashboard-link.active .dashboard-text {
    color: #1d4ed8;
}

/* ========================================
   SIDEBAR RESIZER HANDLE
   ======================================== */
.sidebar-resizer {
    position: absolute;
    top: 0;
    right: -3px;
    width: 6px;
    height: 100%;
    cursor: col-resize;
    z-index: 1050;
    background: transparent;
    transition: background-color 0.2s ease;
}
.sidebar-resizer:hover,
body.is-resizing .sidebar-resizer {
    background-color: #3b82f6;
}
@media (max-width: 991.98px) {
    .sidebar-resizer {
        display: none !important;
    }
}

.sidebar-menu-wrapper {
    background-color: #ffffff;
    overscroll-behavior: contain;
}
</style>

<script id="sidebarScript">
    let sidebarActionTarget = null;
    let sidebarActionGroupTarget = null;
    let sidebarActionGroupId = null;
    let sidebarLastOpenedGroup = null;

    // ========================================
    // SIDEBAR LIVE SEARCH
    // ========================================
    let preSearchOpenFolders = null;

    function handleSidebarSearch(query) {
        const q = (query || '').trim().toLowerCase();
        const clearBtn = document.getElementById('sidebarSearchClearBtn');
        const countContainer = document.getElementById('sidebarSearchCount');
        const countText = document.getElementById('sidebarSearchCountText');
        const emptyState = document.getElementById('sidebarSearchEmpty');
        const urusanItems = document.querySelectorAll('.sidebar-menu-wrapper .urusan-item');
        const allKelompokBlocks = document.querySelectorAll('.sidebar-menu-wrapper .kelompok-block');

        if (!q) {
            if (clearBtn) clearBtn.classList.add('d-none');
            if (countContainer) countContainer.classList.add('d-none');
            if (emptyState) emptyState.classList.add('d-none');

            // Restore semua urusan item
            urusanItems.forEach(item => {
                item.style.display = '';
                const treeLabel = item.querySelector('.tree-label');
                if (treeLabel && item.dataset.originalLabel) {
                    treeLabel.textContent = item.dataset.originalLabel;
                    delete item.dataset.originalLabel;
                }
            });

            // Restore semua kelompok block
            allKelompokBlocks.forEach(block => {
                block.style.display = '';
                const treeLabel = block.querySelector('.tree-label');
                if (treeLabel && block.dataset.originalLabel) {
                    treeLabel.textContent = block.dataset.originalLabel;
                    delete block.dataset.originalLabel;
                }
            });

            // Restore status open/close accordion seperti sebelum search
            if (preSearchOpenFolders !== null) {
                document.querySelectorAll('.kelompok-collapse').forEach(col => {
                    const id = col.id.replace('collapseKelompok', '');
                    const shouldBeOpen = preSearchOpenFolders.includes(id);
                    const toggle = document.querySelector(`[aria-controls="${col.id}"]`);
                    if (shouldBeOpen) {
                        col.classList.add('show');
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'true');
                            toggle.classList.remove('collapsed');
                        }
                    } else {
                        col.classList.remove('show');
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                            toggle.classList.add('collapsed');
                        }
                    }
                });
                preSearchOpenFolders = null;
            }
            return;
        }

        // Simpan folder yang sedang terbuka sebelum user mengetik jika belum tersimpan
        if (preSearchOpenFolders === null) {
            preSearchOpenFolders = Array.from(document.querySelectorAll('.kelompok-collapse.show'))
                .map(col => col.id.replace('collapseKelompok', ''))
                .filter(Boolean);
        }

        if (clearBtn) clearBtn.classList.remove('d-none');

        const terms = q.split(/\s+/).filter(Boolean);
        const matchedUrusanSet = new Set();
        const matchedKelompokIds = new Set();
        const matchedLeafBlocks = new Set();

        // 1. Filter urusan items (jika masih ada)
        urusanItems.forEach(item => {
            const urusanName = (item.dataset.urusanName || '').toLowerCase();
            const treeLabel = item.querySelector('.tree-label');
            if (treeLabel && !item.dataset.originalLabel) {
                item.dataset.originalLabel = treeLabel.textContent;
            }
            const labelText = (item.dataset.originalLabel || '').toLowerCase();

            const isMatch = terms.every(term => urusanName.includes(term) || labelText.includes(term));

            if (isMatch) {
                matchedUrusanSet.add(item);
                item.style.display = '';

                if (treeLabel && item.dataset.originalLabel) {
                    const rawText = item.dataset.originalLabel;
                    const escapedTerms = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
                    const regex = new RegExp(`(${escapedTerms.join('|')})`, 'gi');
                    treeLabel.innerHTML = rawText.replace(regex, '<mark class="sidebar-search-highlight">$1</mark>');
                }

                let parent = item.parentElement;
                while (parent && !parent.classList.contains('sidebar-menu-wrapper')) {
                    if (parent.classList.contains('kelompok-block')) {
                        const kId = parent.dataset.kelompokId;
                        if (kId) matchedKelompokIds.add(kId);
                    }
                    parent = parent.parentElement;
                }
            } else {
                item.style.display = 'none';
                if (treeLabel && item.dataset.originalLabel) {
                    treeLabel.textContent = item.dataset.originalLabel;
                }
            }
        });

        // 2. Filter kelompok blocks (nama bidang atau nama tabel urusan di dalamnya)
        allKelompokBlocks.forEach(block => {
            const kNama = (block.dataset.kelompokNama || '').toLowerCase();
            const uNames = (block.dataset.urusanNames || '').toLowerCase();
            const treeLabel = block.querySelector('.tree-label');
            if (treeLabel && !block.dataset.originalLabel) {
                block.dataset.originalLabel = treeLabel.textContent;
            }

            const isMatch = terms.every(term => kNama.includes(term) || uNames.includes(term));
            if (isMatch) {
                const kId = block.dataset.kelompokId;
                if (kId) {
                    matchedKelompokIds.add(kId);
                    matchedLeafBlocks.add(kId);

                    // Beri highlight jika nama kelompok mengandung kata kunci
                    if (treeLabel && block.dataset.originalLabel && terms.some(term => kNama.includes(term))) {
                        const rawText = block.dataset.originalLabel;
                        const escapedTerms = terms.map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
                        const regex = new RegExp(`(${escapedTerms.join('|')})`, 'gi');
                        treeLabel.innerHTML = rawText.replace(regex, '<mark class="sidebar-search-highlight">$1</mark>');
                    }

                    // Buka juga semua ancestor folder
                    let parent = block.parentElement;
                    while (parent && !parent.classList.contains('sidebar-menu-wrapper')) {
                        if (parent.classList.contains('kelompok-block')) {
                            const pId = parent.dataset.kelompokId;
                            if (pId) matchedKelompokIds.add(pId);
                        }
                        parent = parent.parentElement;
                    }
                }
            } else {
                if (treeLabel && block.dataset.originalLabel) {
                    treeLabel.textContent = block.dataset.originalLabel;
                }
            }
        });

        // 3. Tampilkan kelompok yang cocok dan buka accordions-nya
        allKelompokBlocks.forEach(block => {
            const kId = block.dataset.kelompokId;
            if (matchedKelompokIds.has(kId)) {
                block.style.display = '';
                const collapse = document.getElementById('collapseKelompok' + kId);
                if (collapse) {
                    collapse.classList.add('show');
                    const toggle = block.querySelector('.tree-folder-toggle');
                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'true');
                        toggle.classList.remove('collapsed');
                    }
                }
            } else {
                block.style.display = 'none';
            }
        });

        const matchCount = matchedLeafBlocks.size + matchedUrusanSet.size;

        if (countContainer && countText) {
            countContainer.classList.remove('d-none');
            countText.textContent = `${matchCount} urusan ditemukan`;
        }

        if (emptyState) {
            if (matchCount === 0) {
                emptyState.classList.remove('d-none');
            } else {
                emptyState.classList.add('d-none');
            }
        }
    }

    function clearSidebarSearch() {
        const searchInput = document.getElementById('sidebarSearchInput');
        if (searchInput) {
            searchInput.value = '';
        }
        handleSidebarSearch('');
        if (searchInput) {
            searchInput.focus();
        }
    }

    window.handleSidebarSearch = handleSidebarSearch;
    window.clearSidebarSearch = clearSidebarSearch;

    // Helper untuk session persistence open folders
    function getStoredOpenFolders() {
        try {
            return JSON.parse(sessionStorage.getItem('sidebar_open_folders') || '[]');
        } catch(e) {
            return [];
        }
    }
    function saveStoredOpenFolders(list) {
        try {
            sessionStorage.setItem('sidebar_open_folders', JSON.stringify(list));
        } catch(e) {}
    }

    function getKelompokCollapse(block) {
        if (!block || !block.dataset.kelompokId) return null;
        return document.getElementById('collapseKelompok' + block.dataset.kelompokId);
    }

    function getKelompokHeader(block) {
        if (!block) return null;
        return Array.from(block.querySelectorAll('[data-kelompok-header]')).find(h => h.closest('.kelompok-block') === block) || null;
    }

    function isSidebarGroupOpenAndVisible(block) {
        const collapse = getKelompokCollapse(block);
        if (!collapse || !collapse.classList.contains('show')) return false;

        let parentCollapse = collapse.parentElement?.closest('.kelompok-collapse');
        while (parentCollapse) {
            if (!parentCollapse.classList.contains('show')) return false;
            parentCollapse = parentCollapse.parentElement?.closest('.kelompok-collapse');
        }
        return true;
    }

    function getOpenSidebarGroupTarget() {
        if (sidebarLastOpenedGroup?.isConnected && isSidebarGroupOpenAndVisible(sidebarLastOpenedGroup)) {
            return sidebarLastOpenedGroup;
        }

        const openCollapses = Array.from(document.querySelectorAll('.kelompok-collapse.show'));
        const visibleOpenBlocks = openCollapses
            .map((collapse) => collapse.closest('.kelompok-block'))
            .filter((block) => Boolean(block) && isSidebarGroupOpenAndVisible(block));

        return visibleOpenBlocks.pop() || null;
    }

    function setSidebarActionGroupTarget(block) {
        sidebarActionTarget = null;
        sidebarActionGroupTarget = block || null;
        sidebarActionGroupId = block?.dataset.kelompokId || null;

        // Bersihkan status aktif pada SEMUA urusan
        document.querySelectorAll('.urusan-item.urusan-item--active').forEach(el => {
            el.classList.remove('urusan-item--active');
        });

        // Bersihkan status aktif pada semua header kelompok
        document.querySelectorAll('.kelompok-header-row.kelompok-active').forEach(el => {
            el.classList.remove('kelompok-active');
        });

        // Tandai header kelompok yang aktif/dipilih
        if (block) {
            const header = getKelompokHeader(block);
            if (header) {
                header.classList.add('kelompok-active');
            }
        }

        updateSidebarContextButtons();
    }

    function setSidebarActionTarget(row) {
        sidebarActionTarget = row || null;
        sidebarActionGroupTarget = row ? row.closest('.kelompok-block') : null;
        if (sidebarActionGroupTarget) {
            sidebarActionGroupId = sidebarActionGroupTarget.dataset.kelompokId;
        } else {
            sidebarActionGroupId = null;
        }

        // Bersihkan status aktif kelompok karena urusan spesifik dipilih
        document.querySelectorAll('.kelompok-header-row.kelompok-active').forEach(el => {
            el.classList.remove('kelompok-active');
        });

        // Pastikan hanya urusan yang dipilih yang memiliki status aktif
        document.querySelectorAll('.urusan-item').forEach(el => {
            if (el === row) {
                el.classList.add('urusan-item--active');
            } else {
                el.classList.remove('urusan-item--active');
            }
        });

        updateSidebarContextButtons();
    }

    function updateSidebarContextButtons() {
        const addButton = document.getElementById('sidebarContextAdd');
        const editButton = document.getElementById('sidebarContextEdit');
        const deleteButton = document.getElementById('sidebarContextDelete');
        const hasGroupTarget = Boolean(sidebarActionGroupId);
        const hasUrusanTarget = Boolean(sidebarActionTarget);

        if (addButton) addButton.disabled = !hasGroupTarget;
        if (editButton) editButton.disabled = !(hasUrusanTarget || sidebarActionGroupTarget);
        if (deleteButton) deleteButton.disabled = !(hasUrusanTarget || sidebarActionGroupTarget);

        if (hasUrusanTarget) {
            const uName = sidebarActionTarget.dataset.urusanName || '';
            if (editButton) editButton.title = `Edit urusan: ${uName}`;
            if (deleteButton) deleteButton.title = `Hapus urusan: ${uName}`;
        } else if (sidebarActionGroupTarget) {
            const gName = sidebarActionGroupTarget.dataset.kelompokNama || '';
            if (editButton) editButton.title = `Edit kelompok: ${gName}`;
            if (deleteButton) deleteButton.title = `Hapus kelompok: ${gName}`;
        } else {
            if (editButton) editButton.title = 'Edit';
            if (deleteButton) deleteButton.title = 'Hapus';
        }
    }

    // Restore and track sidebar scroll position
    const menuWrapper = document.querySelector('.sidebar-menu-wrapper');
    if (menuWrapper) {
        const savedScroll = sessionStorage.getItem('sidebar_scroll_pos');
        if (savedScroll !== null) {
            menuWrapper.scrollTop = parseInt(savedScroll, 10);
        }
        menuWrapper.addEventListener('scroll', () => {
            sessionStorage.setItem('sidebar_scroll_pos', menuWrapper.scrollTop);
        }, { passive: true });
    }

    // Sync session open folders:
    // Ketika baru login atau membuka dashboard, semua kelompok urusan otomatis tertutup
    @if(request()->routeIs('dashboard'))
        try {
            sessionStorage.removeItem('sidebar_open_folders');
            sessionStorage.removeItem('sidebar_scroll_pos');
        } catch(e) {}
        document.querySelectorAll('.kelompok-collapse').forEach(col => {
            col.classList.remove('show');
            const toggle = document.querySelector(`[aria-controls="${col.id}"]`);
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
                toggle.classList.add('collapsed');
            }
        });
        saveStoredOpenFolders([]);
    @else
        let openFoldersList = getStoredOpenFolders();
        document.querySelectorAll('.kelompok-collapse.show').forEach(c => {
            const id = c.id.replace('collapseKelompok', '');
            if (id && !openFoldersList.includes(id)) openFoldersList.push(id);
        });
        openFoldersList.forEach(id => {
            const col = document.getElementById('collapseKelompok' + id);
            if (col && !col.classList.contains('show')) {
                col.classList.add('show');
                const toggle = document.querySelector(`[aria-controls="collapseKelompok${id}"]`);
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'true');
                    toggle.classList.remove('collapsed');
                }
            }
        });
        saveStoredOpenFolders(openFoldersList);
    @endif

    let isSidebarInitializing = true;
    setTimeout(() => { isSidebarInitializing = false; }, 300);

    function attachKelompokBlockListeners(block) {
        if (!block || block.dataset.hasListeners) return;
        block.dataset.hasListeners = 'true';

        const groupHeader = getKelompokHeader(block);
        const collapse = getKelompokCollapse(block);

        groupHeader?.addEventListener('click', () => {
            const isCurrentlyOpen = collapse?.classList.contains('show');
            if (!isCurrentlyOpen) {
                sidebarLastOpenedGroup = block;
                setSidebarActionGroupTarget(block);
            }
        });

        collapse?.addEventListener('show.bs.collapse', (event) => {
            if (event.target !== collapse) return;
            const toggle = block.querySelector('.tree-folder-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'true');
                toggle.classList.remove('collapsed');
            }
        });

        collapse?.addEventListener('hide.bs.collapse', (event) => {
            if (event.target !== collapse) return;
            const toggle = block.querySelector('.tree-folder-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
                toggle.classList.add('collapsed');
            }
        });

        collapse?.addEventListener('shown.bs.collapse', (event) => {
            if (event.target !== collapse) return;
            const toggle = block.querySelector('.tree-folder-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'true');
                toggle.classList.remove('collapsed');
            }
            const id = collapse.id.replace('collapseKelompok', '');
            let list = getStoredOpenFolders();
            if (id && !list.includes(id)) {
                list.push(id);
                saveStoredOpenFolders(list);
            }
            if (isSidebarInitializing) return;
            sidebarLastOpenedGroup = block;
            setSidebarActionGroupTarget(block);
        });

        collapse?.addEventListener('hidden.bs.collapse', (event) => {
            if (event.target !== collapse) return;
            const toggle = block.querySelector('.tree-folder-toggle');
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'false');
                toggle.classList.add('collapsed');
            }
            const id = collapse.id.replace('collapseKelompok', '');
            let list = getStoredOpenFolders().filter(item => item !== id);
            saveStoredOpenFolders(list);
            if (sidebarLastOpenedGroup === block) sidebarLastOpenedGroup = null;
            if (sidebarActionGroupTarget === block) {
                setSidebarActionGroupTarget(null);
            }
        });
    }

    function attachUrusanItemListeners(row) {
        if (!row || row.dataset.hasListeners) return;
        row.dataset.hasListeners = 'true';
        row.addEventListener('click', () => {
            setSidebarActionTarget(row);
        });
        row.addEventListener('focusin', () => setSidebarActionTarget(row));
    }

    document.querySelectorAll('.kelompok-block').forEach(attachKelompokBlockListeners);
    document.querySelectorAll('.urusan-item').forEach(attachUrusanItemListeners);

    document.querySelector('.sidebar-menu-wrapper')?.addEventListener('click', (e) => {
        if (!e.target.closest('.kelompok-block') && !e.target.closest('.urusan-item') && !e.target.closest('#inlineKelompokRootFormRow')) {
            setSidebarActionGroupTarget(null);
        }
    });

    const createdKelompokId = "{{ session('created_kelompok_id') }}";
    const currentKelompokRouteId = "{{ request()->routeIs('kelompok-urusan.show') ? (string) request()->route('id') : '' }}";
    const activeUrusan = document.querySelector('.urusan-item--active');

    if (createdKelompokId) {
        const createdBlock = document.querySelector(`.kelompok-block[data-kelompok-id="${createdKelompokId}"]`);
        if (createdBlock) {
            setSidebarActionGroupTarget(createdBlock);
            setTimeout(() => {
                createdBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        } else if (activeUrusan) {
            setSidebarActionTarget(activeUrusan);
        } else {
            updateSidebarContextButtons();
        }
    } else if (currentKelompokRouteId) {
        const curKelompokBlock = document.querySelector(`.kelompok-block[data-kelompok-id="${currentKelompokRouteId}"]`);
        if (curKelompokBlock) {
            setSidebarActionGroupTarget(curKelompokBlock);
            if (menuWrapper) {
                setTimeout(() => {
                    curKelompokBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 100);
            }
        } else {
            updateSidebarContextButtons();
        }
    } else if (activeUrusan) {
        setSidebarActionTarget(activeUrusan);
        if (menuWrapper) {
            const wrapRect = menuWrapper.getBoundingClientRect();
            const itemRect = activeUrusan.getBoundingClientRect();
            if (itemRect.top < wrapRect.top + 20 || itemRect.bottom > wrapRect.bottom - 20) {
                activeUrusan.scrollIntoView({ behavior: 'auto', block: 'nearest' });
                sessionStorage.setItem('sidebar_scroll_pos', menuWrapper.scrollTop);
            }
        }
    } else {
        updateSidebarContextButtons();
    }

    function runSidebarContextAdd() {
        if (!sidebarActionGroupTarget && !sidebarActionGroupId) return;
        const targetId = sidebarActionGroupId || sidebarActionGroupTarget?.dataset.kelompokId;
        
        // Coba cari form inline baris urusan di sidebar
        const formRow = document.getElementById('inlineFormRow' + targetId);
        if (formRow) {
            showInlineForm(targetId);
        } else {
            // Jika sub-kelompok tidak memiliki inline form row, buka modal tambah urusan
            const modalEl = document.getElementById('modalTambahUrusan') || document.getElementById('modalTambahUrusanSidebar');
            if (modalEl) {
                const selectKelompok = modalEl.querySelector('select[name="kelompok_urusan_id"]') || modalEl.querySelector('input[name="kelompok_urusan_id"]');
                if (selectKelompok && targetId) {
                    selectKelompok.value = targetId;
                }
                const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                bsModal.show();
            } else {
                openTambahUrusanModal(targetId);
            }
        }
    }

    function runSidebarAddChildGroup() {
        // Jika ada kelompok yang sedang dipilih secara aktif, tambahkan subfolder di dalamnya.
        // Jika tidak ada kelompok yang dipilih, buat folder root utama (tidak membuka folder lain).
        const target = sidebarActionGroupTarget;
        showInlineKelompokForm(target?.dataset.kelompokId || null);
    }

    function showInlineEditKelompok(id, nama) {
        const block = document.querySelector(`.kelompok-block[data-kelompok-id="${id}"]`);
        if (!block) return;
        const normalView = Array.from(block.querySelectorAll('.kelompok-normal-view')).find(el => el.closest('.kelompok-block') === block);
        const editView = document.getElementById('kelompokEditView' + id);
        if (!normalView || !editView) return;

        const input = document.getElementById('editInlineKelompokInput' + id);
        if (input) input.value = nama || block.dataset.kelompokNama || '';

        normalView.classList.add('d-none');
        editView.classList.remove('d-none');

        setTimeout(() => {
            if (input) { input.focus(); input.select(); }
        }, 50);
    }

    function hideInlineEditKelompok(id) {
        const block = document.querySelector(`.kelompok-block[data-kelompok-id="${id}"]`);
        if (!block) return;
        const normalView = Array.from(block.querySelectorAll('.kelompok-normal-view')).find(el => el.closest('.kelompok-block') === block);
        const editView = document.getElementById('kelompokEditView' + id);
        if (normalView) normalView.classList.remove('d-none');
        if (editView) editView.classList.add('d-none');
    }

    function runSidebarContextEdit() {
        if (sidebarActionTarget) {
            showInlineEditUrusan(
                sidebarActionTarget.dataset.urusanId,
                sidebarActionTarget.dataset.urusanName,
                sidebarActionTarget.dataset.kelompokId
            );
            return;
        }
        if (!sidebarActionGroupTarget) return;

        showInlineEditKelompok(
            sidebarActionGroupTarget.dataset.kelompokId,
            sidebarActionGroupTarget.dataset.kelompokNama
        );
    }

    function runSidebarContextDelete() {
        if (sidebarActionTarget) {
            confirmDeleteUrusan(
                sidebarActionTarget.dataset.urusanId,
                sidebarActionTarget.dataset.urusanName,
                sidebarActionTarget.classList.contains('urusan-item--active')
            );
            return;
        }
        if (!sidebarActionGroupTarget) return;
        confirmDeleteKelompok(
            sidebarActionGroupTarget.dataset.kelompokId,
            sidebarActionGroupTarget.dataset.kelompokKode,
            sidebarActionGroupTarget.dataset.kelompokNama,
            sidebarActionGroupTarget
        );
    }

    function openTambahUrusanModal(kelompokId, kelompokName) {
        const select = document.getElementById('sidebarSelectKelompok');
        if (select && kelompokId) {
            select.value = kelompokId;
        }
        new bootstrap.Modal(document.getElementById('modalTambahUrusanSidebar')).show();
    }

    // Inline Edit Urusan (VS Code style rename)
    function showInlineEditUrusan(id, nama, kelompokId) {
        const row = document.getElementById('urusanRow' + id);
        if (!row) return;
        const normalView = row.querySelector('.urusan-normal-view');
        const editView = row.querySelector('.urusan-edit-view');
        if (!normalView || !editView) return;

        // Isi input dengan nama saat ini
        const input = document.getElementById('editInlineInput' + id);
        if (input) input.value = nama || '';

        // Sembunyikan tampilan normal, tampilkan form edit
        normalView.classList.add('d-none');
        editView.classList.remove('d-none');

        // Fokus & select semua teks
        setTimeout(() => {
            if (input) { input.focus(); input.select(); }
        }, 50);
    }

    function hideInlineEditUrusan(id) {
        const row = document.getElementById('urusanRow' + id);
        if (!row) return;
        const normalView = row.querySelector('.urusan-normal-view');
        const editView = row.querySelector('.urusan-edit-view');
        if (normalView) normalView.classList.remove('d-none');
        if (editView) editView.classList.add('d-none');
    }

    // Tetap tersedia untuk kompatibilitas (tidak digunakan lagi)
    function openEditUrusanModal(id, nama, nomor, icon, deskripsi, kelompokId) {
        showInlineEditUrusan(id, nama, kelompokId);
    }

    function openTambahIndikatorDariSidebar(urusanId, urusanNama) {
        const targetUrl = '/urusan/' + urusanId + '?tambah_indikator=1';
        if (typeof window.loadSpaContent === 'function') {
            window.loadSpaContent(targetUrl, true);
        } else {
            window.location.href = targetUrl;
        }
    }

    function confirmDeleteUrusan(id, name, isCurrentPage) {
        const safeName = (name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        Swal.fire({
            html: `
                <div class="swal2-modern-content">
                    <div class="swal2-modern-icon-badge">
                        <i class="bi bi-trash3-fill"></i>
                    </div>
                    <h4 class="swal2-modern-title">Hapus Urusan &ldquo;${safeName}&rdquo;?</h4>
                    <div class="swal2-modern-alert-box">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5 flex-shrink-0 mt-0.5"></i>
                            <div class="text-start">
                                <strong class="d-block text-danger mb-1" style="font-size: 0.88rem; font-weight: 700;">Perhatian Hapus Urusan</strong>
                                <span class="text-muted" style="font-size: 0.82rem; line-height: 1.45;">
                                    Semua indikator, sub-indikator, dan data tahunannya akan <strong class="text-danger">ikut dihapus permanen</strong>.
                                </span>
                            </div>
                        </div>
                    </div>
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
                const form = document.getElementById('formDeleteUrusanSidebar');
                form.action = '/urusan/' + id;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(async (response) => {
                        const payload = await response.json();
                        if (!response.ok) {
                            throw new Error(payload.message || 'Urusan gagal dihapus.');
                        }
                        return payload;
                    })
                    .then((payload) => {
                        const row = document.getElementById('urusanRow' + id);
                        if (row) {
                            const wasActionTarget = sidebarActionTarget === row;
                            row.remove();
                            if (wasActionTarget) {
                                setSidebarActionTarget(document.querySelector('.urusan-item--active'));
                            }
                        }

                        if (isCurrentPage) {
                            history.replaceState({}, '', '/dashboard');
                            document.title = 'Dashboard - SIPD Sektoral';

                            const breadcrumb = document.querySelector('.topbar nav[aria-label="breadcrumb"] ol');
                            if (breadcrumb) {
                                breadcrumb.replaceChildren();
                                const homeItem = document.createElement('li');
                                homeItem.className = 'breadcrumb-item active text-dark fw-semibold';
                                homeItem.textContent = 'Dashboard';
                                breadcrumb.append(homeItem);
                            }

                            const content = document.querySelector('.content-body');
                            if (content) {
                                content.replaceChildren();
                                const notice = document.createElement('div');
                                notice.className = 'alert alert-success d-flex align-items-center gap-2 shadow-xs';
                                notice.setAttribute('role', 'status');
                                const icon = document.createElement('i');
                                icon.className = 'bi bi-check-circle-fill fs-5';
                                const message = document.createElement('span');
                                message.textContent = payload.message;
                                notice.append(icon, message);

                                const dashLink = document.createElement('a');
                                dashLink.href = '/dashboard';
                                dashLink.className = 'btn btn-primary btn-sm';
                                dashLink.textContent = 'Kembali ke Dashboard';
                                content.append(notice, dashLink);
                            }
                        }

                        if (typeof window.showTopToast === 'function') {
                            window.showTopToast(payload.message || 'Urusan berhasil dihapus.', 'success', 1800);
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Urusan dihapus',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal menghapus urusan',
                            text: error.message || 'Terjadi kesalahan saat menghapus urusan.'
                        });
                    });
            }
        });
    }

    function confirmDeleteKelompok(id, kode, name, groupBlock) {
        const safeName = (name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        Swal.fire({
            html: `
                <div class="swal2-modern-content">
                    <div class="swal2-modern-icon-badge">
                        <i class="bi bi-trash3-fill"></i>
                    </div>
                    <h4 class="swal2-modern-title">Hapus Kelompok &ldquo;${safeName}&rdquo;?</h4>
                    <div class="swal2-modern-alert-box">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-exclamation-octagon-fill text-danger fs-5 flex-shrink-0 mt-0.5"></i>
                            <div class="text-start">
                                <strong class="d-block text-danger mb-1" style="font-size: 0.88rem; font-weight: 700;">Perhatian Hapus Kelompok</strong>
                                <span class="text-muted" style="font-size: 0.82rem; line-height: 1.45;">
                                    Semua urusan, indikator, dan seluruh data di dalamnya akan <strong class="text-danger">ikut dihapus permanen</strong>.
                                </span>
                            </div>
                        </div>
                    </div>
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
                const form = document.getElementById('formDeleteKelompokSidebar');
                form.action = '/kelompok-urusan/' + id;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(async (response) => {
                        const payload = await response.json();
                        if (!response.ok) throw new Error(payload.message || 'Kelompok gagal dihapus.');
                        return payload;
                    })
                    .then((payload) => {
                        const containsCurrentPage = groupBlock?.querySelector('.urusan-item--active') !== null;
                        groupBlock?.remove();

                        if (sidebarActionGroupTarget === groupBlock) {
                            setSidebarActionGroupTarget(null);
                        }

                        if (containsCurrentPage) {
                            history.replaceState({}, '', '/dashboard');
                            document.title = 'Dashboard - SIPD Sektoral';
                            const content = document.querySelector('.content-body');
                            if (content) {
                                content.replaceChildren();
                                const notice = document.createElement('div');
                                notice.className = 'alert alert-success d-flex align-items-center gap-2 shadow-xs';
                                notice.setAttribute('role', 'status');
                                notice.textContent = payload.message;
                                const dashLink = document.createElement('a');
                                dashLink.href = '/dashboard';
                                dashLink.className = 'btn btn-primary btn-sm';
                                dashLink.textContent = 'Kembali ke Dashboard';
                                content.append(notice, dashLink);
                            }
                        }

                        if (typeof window.showTopToast === 'function') {
                            window.showTopToast('Folder berhasil dihapus.', 'success', 1800);
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Folder berhasil dihapus',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal menghapus kelompok',
                            text: error.message || 'Terjadi kesalahan saat menghapus kelompok.'
                        });
                    });
            }
        });
    }

    function toggleAllKelompok() {
        const collapses = document.querySelectorAll('.kelompok-collapse');
        let anyOpen = Array.from(collapses).some(c => c.classList.contains('show'));
        collapses.forEach(c => {
            const bsC = bootstrap.Collapse.getOrCreateInstance(c, { toggle: false });
            anyOpen ? bsC.hide() : bsC.show();
        });
    }

    function closeAllKelompok() {
        saveStoredOpenFolders([]);
        const collapses = document.querySelectorAll('.kelompok-collapse');
        collapses.forEach(c => {
            const bsC = bootstrap.Collapse.getOrCreateInstance(c, { toggle: false });
            bsC.hide();
        });
        document.querySelectorAll('.tree-folder-toggle').forEach(t => {
            t.setAttribute('aria-expanded', 'false');
            t.classList.add('collapsed');
        });
        setSidebarActionGroupTarget(null);
    }
    window.closeAllKelompok = closeAllKelompok;
</script>

<script id="sidebarInlineActionScript">
    // Inline Add Form VS Code style untuk Urusan
    function showInlineForm(kelompokId, e) {
        if(e) e.stopPropagation();
        
        // Ensure parent collapse is open
        const collapseEl = document.getElementById('collapseKelompok' + kelompokId);
        if (collapseEl && !collapseEl.classList.contains('show')) {
            const bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: false });
            bsCollapse.show();
        }

        // Show inline form row
        const formRow = document.getElementById('inlineFormRow' + kelompokId);
        if(formRow) formRow.classList.remove('d-none');
        
        // Focus input
        setTimeout(() => {
            const input = document.getElementById('inlineInput' + kelompokId);
            if(input) input.focus();
        }, 300); // wait for collapse to open
    }

    function hideInlineForm(kelompokId) {
        // Delay a bit so if it's blurring because of form submit, it can submit first
        setTimeout(() => {
            const input = document.getElementById('inlineInput' + kelompokId);
            if(input) input.value = ''; // Reset
            
            const formRow = document.getElementById('inlineFormRow' + kelompokId);
            if(formRow) formRow.classList.add('d-none');
        }, 150);
    }

    function showInlineKelompokForm(kelompokId) {
        if (!kelompokId) {
            const formRow = document.getElementById('inlineKelompokRootFormRow');
            const input = document.getElementById('inlineKelompokRootInput');
            if (!formRow || !input) return;
            formRow.classList.remove('d-none');
            input.focus();
            return;
        }

        const collapseEl = document.getElementById('collapseKelompok' + kelompokId);
        if (collapseEl && !collapseEl.classList.contains('show')) {
            bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
        }

        const formRow = document.getElementById('inlineKelompokFormRow' + kelompokId);
        const input = document.getElementById('inlineKelompokInput' + kelompokId);
        if (!formRow || !input) return;

        formRow.classList.remove('d-none');
        setTimeout(() => input.focus(), 300);
    }

    function hideInlineKelompokForm(kelompokId) {
        if (!kelompokId) {
            const formRow = document.getElementById('inlineKelompokRootFormRow');
            const input = document.getElementById('inlineKelompokRootInput');
            if (input) input.value = '';
            if (formRow) formRow.classList.add('d-none');
            return;
        }

        const formRow = document.getElementById('inlineKelompokFormRow' + kelompokId);
        const input = document.getElementById('inlineKelompokInput' + kelompokId);
        if (input) input.value = '';
        if (formRow) formRow.classList.add('d-none');
    }

    // AJAX handling for creating & editing Kelompok & Urusan without refreshing sidebar
    document.getElementById('sidebar')?.addEventListener('submit', async function(e) {
        const form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        const action = form.getAttribute('action') || '';
        const isStoreKelompok = action.includes('/kelompok-urusan') && !form.querySelector('input[name="_method"]');
        const isUpdateKelompok = action.includes('/kelompok-urusan') && form.querySelector('input[name="_method"][value="PUT"]');
        const isStoreUrusan = action.includes('/urusan') && !form.querySelector('input[name="_method"]');
        const isUpdateUrusan = action.includes('/urusan') && form.querySelector('input[name="_method"][value="PUT"]');

        if (!isStoreKelompok && !isUpdateKelompok && !isStoreUrusan && !isUpdateUrusan) return;

        e.preventDefault();
        e.stopPropagation();

        if (form.dataset.submitting === 'true') return;
        form.dataset.submitting = 'true';

        const submitBtn = form.querySelector('button[type="submit"]');
        const textInput = form.querySelector('input[type="text"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('disabled', 'opacity-50');
        }
        if (textInput) {
            textInput.readOnly = true;
        }

        try {
            const formData = new FormData(form);
            const res = await fetch(action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await res.json().catch(() => null);
            if (!res.ok || (data && data.success === false)) {
                throw new Error((data && data.message) ? data.message : 'Terjadi kesalahan saat memproses data.');
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

            if (isStoreKelompok && data && data.kelompok) {
                const k = data.kelompok;
                const parentId = k.parent_id;

                const newBlock = document.createElement('div');
                newBlock.className = 'kelompok-block mb-1';
                newBlock.dataset.kelompokId = k.id;
                newBlock.dataset.kelompokKode = k.kode;
                newBlock.dataset.kelompokNama = k.nama;
                newBlock.dataset.kelompokKeterangan = k.keterangan || '';

                newBlock.innerHTML = `
                    <div class="kelompok-header-row kelompok-normal-view d-flex align-items-center justify-content-between rounded-2 px-2 py-1" data-kelompok-header>
                        <a class="d-flex align-items-center gap-1.5 text-decoration-none flex-grow-1 py-0.5 text-truncate tree-folder-toggle collapsed"
                           data-bs-toggle="collapse"
                           href="#collapseKelompok${k.id}"
                           role="button"
                           aria-expanded="false"
                           aria-controls="collapseKelompok${k.id}"
                           style="min-width: 0;">
                            <i class="bi bi-chevron-right tree-chevron" aria-hidden="true"></i>
                            <span class="kelompok-badge" aria-hidden="true">
                                <i class="bi bi-folder-fill folder-icon--closed"></i>
                                <i class="bi bi-folder2-open folder-icon--open"></i>
                            </span>
                            <span class="text-truncate fw-semibold text-dark tree-label" title="${k.nama}">
                                ${k.nama}
                            </span>
                        </a>
                    </div>
                    <div class="kelompok-edit-view d-none px-1 py-1" id="kelompokEditView${k.id}" style="border-radius: 6px; background: #fffbeb;">
                        <form method="POST" action="/kelompok-urusan/${k.id}" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="_token" value="${csrf}">
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="kode" value="${k.kode}">
                            <input type="hidden" name="keterangan" value="${k.keterangan || ''}">
                            <div class="d-flex align-items-center gap-1 flex-grow-1 bg-white rounded-2 border border-warning overflow-hidden px-2 shadow-sm">
                                <span class="kelompok-badge" style="width: 18px; height: 18px; min-width: 18px; font-size: 0.65rem;"><i class="bi bi-folder-fill text-warning"></i></span>
                                <input type="text" name="nama" id="editInlineKelompokInput${k.id}" class="form-control border-0 shadow-none px-1 py-0.5 text-dark" style="font-size: 0.8rem;" value="${k.nama}" placeholder="Nama folder..." required onkeydown="if(event.key==='Escape') hideInlineEditKelompok(${k.id})">
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm px-2 py-0.5 text-dark fw-bold" title="Simpan" style="font-size: 0.75rem; flex-shrink: 0;"><i class="bi bi-check-lg"></i></button>
                            <button type="button" class="btn btn-light btn-sm px-2 py-0.5 border" title="Batal" onclick="hideInlineEditKelompok(${k.id})" style="font-size: 0.75rem; flex-shrink: 0;"><i class="bi bi-x"></i></button>
                        </form>
                    </div>
                    <div class="collapse kelompok-collapse" id="collapseKelompok${k.id}">
                        <ul class="list-unstyled urusan-list">
                            <li class="mt-1 d-none" id="inlineKelompokFormRow${k.id}">
                                <div style="background: #f0f9ff; border-radius: 6px; padding: 4px;">
                                    <form method="POST" action="{{ route('kelompok-urusan.store') }}" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="_token" value="${csrf}">
                                        <input type="hidden" name="parent_id" value="${k.id}">
                                        <div class="d-flex align-items-center gap-1 flex-grow-1 bg-white rounded-2 border border-primary overflow-hidden px-2 shadow-sm">
                                            <i class="bi bi-plus-circle text-primary" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                            <input type="text" name="nama" id="inlineKelompokInput${k.id}" class="form-control border-0 shadow-none px-1 py-1 text-dark" style="font-size: 0.8rem;" placeholder="Nama folder baru..." maxlength="255" required onkeydown="if(event.key==='Escape') hideInlineKelompokForm(${k.id})">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm px-2 py-1 fw-bold" title="Simpan folder" style="font-size: 0.78rem; flex-shrink: 0;"><i class="bi bi-check-lg"></i></button>
                                        <button type="button" class="btn btn-light btn-sm px-2 py-1 border" title="Batalkan" aria-label="Batalkan tambah folder" onclick="hideInlineKelompokForm(${k.id})" style="font-size: 0.78rem; flex-shrink: 0;"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </div>
                            </li>
                            <li class="mt-1 d-none" id="inlineFormRow${k.id}">
                                <div style="background: #f0f9ff; border-radius: 6px; padding: 4px;">
                                    <form method="POST" action="{{ route('urusan.store') }}" class="d-flex align-items-center gap-2">
                                        <input type="hidden" name="_token" value="${csrf}">
                                        <input type="hidden" name="kelompok_urusan_id" value="${k.id}">
                                        <div class="d-flex align-items-center gap-1 flex-grow-1 bg-white rounded-2 border border-primary overflow-hidden px-2 shadow-sm">
                                            <i class="bi bi-plus-circle text-primary" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                            <input type="text" name="nama_urusan" id="inlineInput${k.id}" class="form-control border-0 shadow-none px-1 py-1 text-dark" style="font-size: 0.8rem;" placeholder="Nama urusan baru..." required onkeydown="if(event.key==='Escape') hideInlineForm(${k.id})">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm px-2 py-1 fw-bold" title="Simpan" style="font-size: 0.78rem; flex-shrink: 0;"><i class="bi bi-check-lg"></i></button>
                                        <button type="button" class="btn btn-light btn-sm px-2 py-1 border" title="Batalkan" aria-label="Batalkan tambah urusan" onclick="hideInlineForm(${k.id})" style="font-size: 0.78rem; flex-shrink: 0;"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </div>
                `;

                if (parentId) {
                    const parentRow = document.getElementById('inlineKelompokFormRow' + parentId);
                    if (parentRow && parentRow.parentNode) {
                        parentRow.parentNode.insertBefore(newBlock, parentRow);
                    }
                    hideInlineKelompokForm(parentId);
                } else {
                    const rootRow = document.getElementById('inlineKelompokRootFormRow');
                    if (rootRow && rootRow.parentNode) {
                        rootRow.parentNode.insertBefore(newBlock, rootRow);
                    }
                    hideInlineKelompokForm(null);
                }

                attachKelompokBlockListeners(newBlock);
                setSidebarActionGroupTarget(newBlock);
                newBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                if (typeof window.showTopToast === 'function') {
                    window.showTopToast('Folder "' + k.nama + '" berhasil ditambahkan.', 'success', 1800);
                }
            } else if (isStoreUrusan && data && data.urusan) {
                const u = data.urusan;
                const kId = u.kelompok_urusan_id;
                const parentRow = document.getElementById('inlineKelompokFormRow' + kId) || document.getElementById('inlineFormRow' + kId);

                const newLi = document.createElement('li');
                newLi.className = 'urusan-item mb-1';
                newLi.id = 'urusanRow' + u.id;
                newLi.dataset.urusanId = u.id;
                newLi.dataset.urusanName = u.nama_urusan;
                newLi.dataset.urusanSlug = u.slug;
                newLi.dataset.kelompokId = kId;

                newLi.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between rounded-2 px-2 py-1 urusan-normal-view">
                        <a href="${u.url}" class="d-flex align-items-center gap-1.5 text-decoration-none text-truncate flex-grow-1 py-0.5" title="${u.nama_urusan}" style="min-width: 0;">
                            <span class="tree-item-spacer"></span>
                            <i class="bi bi-file-earmark-text text-secondary opacity-75 tree-file-icon"></i>
                            <span class="text-truncate tree-label">${u.formatted_nama || u.nama_urusan}</span>
                        </a>
                    </div>
                    <div class="urusan-edit-view d-none px-1 py-1" style="border-radius: 6px; background: #fffbeb;">
                        <form method="POST" action="/urusan/${u.id}" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="_token" value="${csrf}">
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="kelompok_urusan_id" value="${kId}">
                            <div class="d-flex align-items-center gap-1 flex-grow-1 bg-white rounded-2 border border-warning overflow-hidden px-2 shadow-sm">
                                <i class="bi bi-pencil text-warning" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                <input type="text" name="nama_urusan" id="editInlineInput${u.id}" class="form-control border-0 shadow-none px-1 py-1 text-dark" style="font-size: 0.8rem;" value="${u.nama_urusan}" placeholder="Nama urusan..." required onkeydown="if(event.key==='Escape') hideInlineEditUrusan(${u.id})">
                            </div>
                            <button type="submit" class="btn btn-warning btn-sm px-2 py-1 text-dark fw-bold" title="Simpan" style="font-size: 0.78rem; flex-shrink: 0;"><i class="bi bi-check-lg"></i></button>
                            <button type="button" class="btn btn-light btn-sm px-2 py-1" title="Batal" onclick="hideInlineEditUrusan(${u.id})" style="font-size: 0.78rem; flex-shrink: 0;"><i class="bi bi-x"></i></button>
                        </form>
                    </div>
                `;

                if (parentRow && parentRow.parentNode) {
                    parentRow.parentNode.insertBefore(newLi, parentRow);
                }
                hideInlineForm(kId);

                attachUrusanItemListeners(newLi);

                // Load new urusan page content via SPA
                if (typeof window.loadSpaContent === 'function') {
                    window.loadSpaContent(u.url, true);
                } else {
                    window.location.href = u.url;
                }

                if (typeof window.showTopToast === 'function') {
                    window.showTopToast('Urusan "' + u.nama_urusan + '" berhasil ditambahkan.', 'success', 1800);
                }
            } else if (isUpdateKelompok && data && data.kelompok) {
                const k = data.kelompok;
                const block = document.querySelector(`.kelompok-block[data-kelompok-id="${k.id}"]`);
                if (block) {
                    block.dataset.kelompokNama = k.nama;
                    const label = block.querySelector('[data-kelompok-header] .tree-label');
                    if (label) {
                        label.textContent = k.nama;
                        label.title = k.nama;
                    }
                    hideInlineEditKelompok(k.id);
                }
                if (typeof window.showTopToast === 'function') {
                    window.showTopToast('Nama folder berhasil diperbarui.', 'success', 1800);
                }
            } else if (isUpdateUrusan && data && data.urusan) {
                const u = data.urusan;
                const row = document.getElementById('urusanRow' + u.id);
                if (row) {
                    row.dataset.urusanName = u.nama_urusan;
                    row.dataset.urusanSlug = u.slug;
                    const label = row.querySelector('.tree-label');
                    if (label) {
                        label.textContent = u.formatted_nama || u.nama_urusan;
                    }
                    const link = row.querySelector('a');
                    if (link) {
                        link.title = u.nama_urusan;
                        link.href = u.url;
                    }
                    hideInlineEditUrusan(u.id);
                }
                if (typeof window.showTopToast === 'function') {
                    window.showTopToast('Nama urusan berhasil diperbarui.', 'success', 1800);
                }
            }
        } catch (err) {
            console.error('Sidebar AJAX error:', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Terjadi kesalahan.' });
            } else {
                alert(err.message || 'Terjadi kesalahan.');
            }
        } finally {
            form.dataset.submitting = 'false';
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('disabled', 'opacity-50');
            }
            if (textInput) {
                textInput.readOnly = false;
            }
        }
    });
</script>
