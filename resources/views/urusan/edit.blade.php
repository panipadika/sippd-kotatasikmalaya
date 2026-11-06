@extends('layouts.app')

@section('title', 'Edit Urusan - ' . $urusan->formatted_nama)

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <a href="{{ route('urusan.show', $urusan->slug ?: $urusan->id) }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Kembali ke {{ $urusan->formatted_nama }}
        </a>
        <h3 class="fw-bold text-slate-900 mb-1">Edit Urusan Pemerintahan</h3>
        <p class="text-muted small mb-0">Perbarui informasi dan pengaturan urusan <strong>{{ $urusan->formatted_nama }}</strong>.</p>
    </div>

    <div class="card border-0 shadow-xs">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('urusan.update', $urusan->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Kelompok Urusan <span class="text-danger">*</span></label>
                    <select name="kelompok_urusan_id" class="form-select @error('kelompok_urusan_id') is-invalid @enderror" required>
                        @foreach($kelompoks as $kel)
                            <option value="{{ $kel->id }}" {{ old('kelompok_urusan_id', $urusan->kelompok_urusan_id) == $kel->id ? 'selected' : '' }}>
                                {{ $kel->kode }}. {{ $kel->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Nomor Urusan</label>
                        <input type="number" name="nomor" class="form-control" value="{{ old('nomor', $urusan->nomor) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kode Urusan</label>
                        <input type="text" name="kode_urusan" class="form-control" value="{{ old('kode_urusan', $urusan->kode_urusan) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Ikon Bootstrap</label>
                        <input type="text" name="icon" class="form-control" value="{{ old('icon', $urusan->icon) }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Urusan Pemerintahan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_urusan" class="form-control @error('nama_urusan') is-invalid @enderror" value="{{ old('nama_urusan', $urusan->nama_urusan) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Deskripsi / Keterangan</label>
                    <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $urusan->deskripsi) }}</textarea>
                </div>

                <div class="form-check form-switch mb-4">
                    <input type="hidden" name="status_aktif" value="0">
                    <input class="form-check-input" type="checkbox" name="status_aktif" value="1" id="statusAktifSwitch" {{ $urusan->status_aktif ? 'checked' : '' }}>
                    <label class="form-check-label small fw-semibold" for="statusAktifSwitch">Status Aktif</label>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <a href="{{ route('urusan.show', $urusan->slug ?: $urusan->id) }}" class="btn btn-outline-secondary btn-sm">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
