@extends('layouts.app')

@section('title', 'Pengaturan Tanda Tangan')

@section('content')

  <div class="page-header">
    <div class="page-title">Pengaturan Tanda Tangan</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Pengaturan</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Tanda Tangan</li>
    </ul>
  </div>

  @if($errors->any())
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Penandatangan Laporan</h3>
        <p>Nama otomatis dipakai pada laporan Rekapan. Jabatan sudah tetap, hanya nama yang perlu diperbarui bila orangnya berganti.</p>
      </div>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('penandatangan.update') }}">
        @csrf
        @method('PUT')

        @foreach($penandatangan as $i => $row)
          <div class="form-grid" style="margin-bottom: 24px;">
            <div class="form-group">
              <label>Jabatan</label>
              <input type="text" class="form-control" value="{{ $row->jabatan }}" readonly>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
              <label for="nama_{{ $row->id }}">Nama</label>
              <input type="text" id="nama_{{ $row->id }}" name="nama[{{ $row->id }}]" class="form-control"
                     placeholder="Nama pejabat"
                     value="{{ old('nama.' . $row->id, $row->nama ?? '') }}">
            </div>
          </div>
        @endforeach

        <div class="form-group">
          <label for="tempat">Tempat Cetak</label>
          <input type="text" id="tempat" name="tempat" class="form-control"
                 placeholder="Contoh: Bandung"
                 value="{{ old('tempat', $penandatangan->first()->tempat ?? '') }}">
          <small style="color: #999;">Ditampilkan sebagai "Tempat, tanggal cetak" di bagian tanda tangan laporan.</small>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

@endsection