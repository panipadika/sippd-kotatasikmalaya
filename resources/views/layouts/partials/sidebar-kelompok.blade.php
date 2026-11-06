@php
    $currentUrusanId = (string) request()->route('id');
    $currentKelompokId = request()->routeIs('kelompok-urusan.show') ? (string) request()->route('id') : null;
    $createdKelompokId = session('created_kelompok_id');

    $containsActiveUrusan = function ($group) use (&$containsActiveUrusan, $currentUrusanId) {
        $hasActiveUrusan = $group->urusans->contains(function ($urusan) use ($currentUrusanId) {
            return (string) $urusan->id === $currentUrusanId || (string) $urusan->slug === $currentUrusanId;
        });

        return $hasActiveUrusan || $group->children->contains(function ($child) use (&$containsActiveUrusan) {
            return $containsActiveUrusan($child);
        });
    };

    $containsActiveKelompok = function ($group) use (&$containsActiveKelompok, $currentKelompokId) {
        if (!$currentKelompokId) return false;
        if ((string) $group->id === $currentKelompokId) return true;
        return $group->children->contains(function ($child) use (&$containsActiveKelompok, $currentKelompokId) {
            return $containsActiveKelompok($child);
        });
    };

    $containsCreatedKelompok = function ($group) use (&$containsCreatedKelompok, $createdKelompokId) {
        if ((string) $group->id === (string) $createdKelompokId) {
            return true;
        }
        return $group->children->contains(function ($child) use (&$containsCreatedKelompok, $createdKelompokId) {
            return (string) $child->id === (string) $createdKelompokId || $containsCreatedKelompok($child);
        });
    };

    $hasChildren = $kelompok->children && $kelompok->children->count() > 0;

    $isDirectlyActive = (request()->routeIs('kelompok-urusan.show') && (string) request()->route('id') === (string) $kelompok->id)
        || (request()->routeIs('urusan.show') && $kelompok->urusans->contains(function ($u) use ($currentUrusanId) {
            return (string) $u->id === $currentUrusanId || (string) $u->slug === $currentUrusanId;
        }));

    $isGroupOpen = false;
    if (request()->routeIs('urusan.show') && $containsActiveUrusan($kelompok)) {
        $isGroupOpen = true;
    }
    if (request()->routeIs('kelompok-urusan.show') && $containsActiveKelompok($kelompok)) {
        $isGroupOpen = true;
    }
    if ($createdKelompokId && $containsCreatedKelompok($kelompok)) {
        $isGroupOpen = true;
    }

    $urusanNamesString = $kelompok->urusans->pluck('nama_urusan')->implode('; ');
@endphp

@if($hasChildren)
    {{-- Intermediate group / sub-folder with children --}}
    <li class="kelompok-block mb-1"
        data-kelompok-id="{{ $kelompok->id }}"
        data-kelompok-kode="{{ $kelompok->kode }}"
        data-kelompok-nama="{{ $kelompok->nama }}"
        data-kelompok-keterangan="{{ $kelompok->keterangan }}"
        data-urusan-names="{{ $urusanNamesString }}">
        <div class="kelompok-header-row kelompok-normal-view d-flex align-items-center justify-content-between rounded-2 px-2 py-1"
             data-kelompok-header>
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
                <span class="text-truncate fw-semibold text-dark tree-label"
                      title="{{ $kelompok->nama }}">
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

        <div class="collapse {{ $isGroupOpen ? 'show' : '' }} kelompok-collapse" id="collapseKelompok{{ $kelompok->id }}">
            <ul class="list-unstyled urusan-list">
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
                                <input type="text" name="nama" id="inlineKelompokInput{{ $kelompok->id }}"
                                       class="form-control border-0 shadow-none px-1 py-1 text-dark"
                                       style="font-size: 0.8rem;" placeholder="Nama folder baru..." maxlength="255" required
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
            </ul>
        </div>
    </li>
@else
    {{-- Leaf Bidang / Sub Urusan: Renders as direct link to catalog page --}}
    <li class="kelompok-block mb-1 {{ $isDirectlyActive ? 'urusan-item--active' : '' }}"
        id="kelompokRow{{ $kelompok->id }}"
        data-kelompok-id="{{ $kelompok->id }}"
        data-kelompok-kode="{{ $kelompok->kode }}"
        data-kelompok-nama="{{ $kelompok->nama }}"
        data-kelompok-keterangan="{{ $kelompok->keterangan }}"
        data-urusan-names="{{ $urusanNamesString }}">
        <div class="kelompok-header-row kelompok-normal-view d-flex align-items-center justify-content-between rounded-2 px-2 py-1"
             data-kelompok-header>
            <a class="d-flex align-items-center gap-1.5 text-decoration-none flex-grow-1 py-1 text-truncate tree-leaf-link {{ $isDirectlyActive ? 'fw-bold text-primary' : 'text-dark' }}"
               href="{{ route('kelompok-urusan.show', $kelompok->id) }}"
               title="{{ $kelompok->nama }}"
               style="min-width: 0;">
                <span class="tree-item-spacer"></span>
                <span class="kelompok-badge" aria-hidden="true" style="color: {{ $isDirectlyActive ? '#2563eb' : '#64748b' }};">
                    <i class="bi {{ $isDirectlyActive ? 'bi-folder2-open' : 'bi-folder2' }}"></i>
                </span>
                <span class="text-truncate tree-label" style="font-size: 0.82rem;">
                    {{ $kelompok->nama }}
                </span>
                @if($kelompok->urusans->count() > 0)
                    <span class="badge rounded-pill bg-light text-muted border ms-auto px-1.5 py-0.5" style="font-size: 0.68rem; font-weight: 500;" title="{{ $kelompok->urusans->count() }} Tabel Data">
                        {{ $kelompok->urusans->count() }}
                    </span>
                @endif
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
    </li>
@endif
