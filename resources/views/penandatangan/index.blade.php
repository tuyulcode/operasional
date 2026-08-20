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
        <h3><i class="fa-solid fa-file-signature" style="color: var(--primary-color); margin-right: 8px;"></i> Penandatangan Laporan</h3>
      </div>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('penandatangan.update') }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
          @foreach($penandatangan as $i => $row)
            <div class="ttd-unit" style="border-left: 4px solid {{ $i % 2 === 0 ? 'var(--primary-color)' : 'var(--danger-color)' }}; background: rgba(64, 153, 255, 0.04); border-radius: 10px; padding: 16px 18px;">
              <div class="ttd-unit-title" style="margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color);">
                <i class="fa-solid fa-building-columns" style="color: var(--primary-color); margin-right: 6px;"></i>
                <span style="font-weight: 600; color: #2c3e50;">Penandatangan {{ $i + 1 }}</span>
              </div>

              <div class="form-group" style="margin-bottom: 12px;">
                <label><i class="fa-solid fa-user-tie" style="color: #999; margin-right: 5px;"></i> Jabatan</label>
                <input type="text" class="form-control" value="{{ $row->jabatan }}" readonly>
              </div>

              <div class="form-group" style="margin-bottom: 0;">
                <label for="nama_{{ $row->id }}"><i class="fa-solid fa-user-pen" style="color: #999; margin-right: 5px;"></i> Nama</label>
                <input type="text" id="nama_{{ $row->id }}" name="nama[{{ $row->id }}]" class="form-control"
                       placeholder="Nama pejabat"
                       value="{{ old('nama.' . $row->id, $row->nama ?? '') }}">
              </div>
            </div>
          @endforeach
        </div>

        <div class="form-grid" style="margin-top: 22px;">
          <div class="form-group">
            <label for="tempat"><i class="fa-solid fa-location-dot" style="color: #999; margin-right: 5px;"></i> Tempat</label>
            <input type="text" id="tempat" name="tempat" class="form-control"
                   placeholder="Contoh: Paiton"
                   value="{{ old('tempat', $penandatangan->first()->tempat ?? '') }}">
          </div>

          <div class="form-group">
            <label for="tanggal_cetak"><i class="fa-solid fa-calendar-days" style="color: #999; margin-right: 5px;"></i> Tanggal</label>
            <input type="date" id="tanggal_cetak" name="tanggal_cetak" class="form-control"
                   value="{{ old('tanggal_cetak', $penandatangan->first()->tanggal_cetak ?? now()->format('Y-m-d')) }}">
          </div>
        </div>

        <div class="form-actions" style="margin-top: 4px;">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>

      <div style="margin-top: 26px; padding-top: 20px; border-top: 1px solid var(--border-color);">
        <div style="font-size: 0.85rem; font-weight: 600; color: #999; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px;">
          <i class="fa-solid fa-eye" style="margin-right: 6px;"></i> Preview pada Laporan
        </div>

        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 10px; padding: 22px 26px; max-width: 640px;">
          <div style="text-align: center; color: #94a3b8; font-size: 0.8rem;">Mengetahui / Menyetujui</div>

          <div style="display: flex; justify-content: space-between; gap: 24px; margin-top: 18px;">
            @foreach($penandatangan as $i => $row)
              <div style="flex: 1; text-align: center;">
                <div style="font-size: 0.82rem; font-weight: 600; color: #475569;">{{ $row->jabatan }}</div>
              </div>
            @endforeach
          </div>

          <div style="height: 80px;"></div>

          <div style="display: flex; justify-content: space-between; gap: 24px;">
            @foreach($penandatangan as $i => $row)
              <div style="flex: 1; text-align: center;">
                <div style="border-top: 1px dashed #94a3b8; padding-top: 8px; font-size: 0.9rem; color: #1e293b;">{{ $row->nama ?? '...................................' }}</div>
              </div>
            @endforeach
          </div>

          <div style="text-align: center; color: #64748b; font-size: 0.85rem; margin-top: 24px; padding-top: 14px; border-top: 1px solid #e2e8f0;">
            @php
              $previewTempat = $penandatangan->first()->tempat ?? '';
              $previewTanggalRaw = old('tanggal_cetak', $penandatangan->first()->tanggal_cetak ?? now()->format('Y-m-d'));
              $previewTanggal = \Carbon\Carbon::parse($previewTanggalRaw)->locale('id')->translatedFormat('d F Y');
            @endphp
            {{ ($previewTempat ? $previewTempat . ', ' : '') . $previewTanggal }}
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection