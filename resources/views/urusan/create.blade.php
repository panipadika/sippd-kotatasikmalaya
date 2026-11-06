@extends('layouts.app')

@section('title', 'Tambah Urusan Pemerintahan')

@section('content')
<div class="container-fluid p-0" style="max-width: 800px;">
    <div class="mb-4">
        <a href="{{ route('dashboard') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
        <h3 class="fw-bold text-slate-900 mb-1">Tambah Urusan Pemerintahan Baru</h3>
        <p class="text-muted small mb-0">Tambahkan bidang urusan baru secara dinamis ke dalam sistem.</p>
    </div>

    <div class="card border-0 shadow-xs">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('urusan.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Kelompok Urusan <span class="text-danger">*</span></label>
                    <select name="kelompok_urusan_id" class="form-select @error('kelompok_urusan_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kelompok Urusan (A s.d. H) --</option>
                        @foreach($kelompoks as $kel)
                            <option value="{{ $kel->id }}" {{ old('kelompok_urusan_id') == $kel->id ? 'selected' : '' }}>
                                {{ $kel->kode }}. {{ $kel->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Nomor Urusan <span class="text-muted fw-normal">(1–28 untuk Kelompok A–C)</span></label>
                        <input type="number" name="nomor" class="form-control" value="{{ old('nomor') }}" placeholder="Contoh: 29">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Kode Urusan</label>
                        <input type="text" name="kode_urusan" class="form-control" value="{{ old('kode_urusan') }}" placeholder="Contoh: A.1 atau 1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Ikon Bootstrap</label>
                        <input type="text" name="icon" class="form-control" value="{{ old('icon', 'bi-folder2') }}" placeholder="bi-folder2">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Urusan Pemerintahan <span class="text-danger">*</span></label>
                    <input type="text" name="nama_urusan" class="form-control @error('nama_urusan') is-invalid @enderror" value="{{ old('nama_urusan') }}" placeholder="Contoh: Statistik atau Perindustrian" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Deskripsi / Keterangan</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Keterangan singkat mengenai lingkup urusan ini...">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">Batal</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">Simpan Urusan Baru</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
