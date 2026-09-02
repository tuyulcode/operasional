@extends('layouts.app')

@section('title', 'Pertanggungjawaban Pemakaian BBM')

@section('content')

  <div class="page-header">
    <div class="page-title">Pertanggungjawaban BBM</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Transaksi</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Pemakaian BBM</li>
    </ul>
  </div>

  <div class="tabs">
    <a href="{{ route('pemakaian-bbm.index') }}"
       class="tab-link {{ request()->routeIs('pemakaian-bbm.index') ? 'active' : '' }}">
      <i class="fa-solid fa-table-list"></i> Input Data
    </a>
    <a href="{{ route('pemakaian-bbm.rekapan') }}"
       class="tab-link {{ request()->routeIs('pemakaian-bbm.rekapan') ? 'active' : '' }}">
      <i class="fa-solid fa-file-invoice"></i> Rekapan
    </a>
    <a href="{{ route('pemakaian-bbm.pertanggungjawaban') }}"
       class="tab-link {{ request()->routeIs('pemakaian-bbm.pertanggungjawaban') ? 'active' : '' }}">
      <i class="fa-solid fa-file-signature"></i> Pertanggungjawaban
    </a>
    <a href="{{ route('pemakaian-bbm.riwayat') }}"
       class="tab-link {{ request()->routeIs('pemakaian-bbm.riwayat') ? 'active' : '' }}">
      <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
    </a>
  </div>

  @if($errors->any())
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  @if(session('success'))
    <div class="alert-custom alert-success">
      <i class="fa-solid fa-circle-check"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  {{-- Filter bulan + export --}}
  <div class="card">
    <div class="card-body">
      <form method="GET" action="{{ route('pemakaian-bbm.pertanggungjawaban') }}"
            style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <div class="form-group" style="margin:0; max-width:260px;">
          <label for="bulan_label_filter">Label Bulan</label>
          <input type="text" id="bulan_label_filter" name="bulan_label" class="form-control" list="bulan-options"
                 placeholder="Contoh: Agustus 2026" value="{{ $bulanLabel ?? '' }}">
          <datalist id="bulan-options">
            @foreach($bulanOptions as $opt)
              <option value="{{ $opt }}">
            @endforeach
          </datalist>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
        </button>

        @if(!empty($weeks))
          <button type="button" id="btnExportLaporan" class="btn btn-success" onclick="openExportConfirm()">
            <i class="fa-solid fa-file-export"></i> Export Pertanggung Jawaban
          </button>
        @endif

        <a href="{{ route('pemakaian-bbm.riwayat') }}" class="btn btn-secondary" style="margin-left:auto;">
          <i class="fa-solid fa-clock-rotate-left"></i> Lihat Semua Riwayat Periode
        </a>
      </form>
    </div>
  </div>

  {{-- MODAL EXPORT (konfirmasi -> pilihan format) --}}
  <div class="modal-overlay" id="exportConfirmModal">
    <div class="modal modal-confirm">

      <div id="exportConfirmStep">
        <div class="modal-body modal-confirm-body">
          <div class="modal-confirm-icon">
            <i class="fa-solid fa-file-export"></i>
          </div>
          <h3 class="modal-confirm-title">Export Pertanggung Jawaban?</h3>
          <p class="modal-confirm-text">Apakah Anda yakin ingin export laporan pertanggungjawaban untuk bulan ini?</p>
        </div>
        <div class="modal-footer modal-confirm-footer">
          <button type="button" class="btn btn-secondary" onclick="closeExportConfirm()">Tidak</button>
          <button type="button" class="btn btn-primary" onclick="showExportFormatStep()">Ya</button>
        </div>
      </div>

      <div id="exportFormatStep" style="display:none;">
        <div class="modal-body modal-confirm-body">
          <div class="modal-confirm-icon">
            <i class="fa-solid fa-file-export"></i>
          </div>
          <h3 class="modal-confirm-title">Pilih Format Export</h3>
          <p class="modal-confirm-text">Silakan pilih format laporan yang ingin diunduh.</p>
        </div>
        <div class="modal-footer modal-confirm-footer">
          <a class="btn btn-success"
             href="{{ route('pemakaian-bbm.export-pertanggungjawaban-excel', ['bulan_label' => $bulanLabel ?? '']) }}">
            <i class="fa-solid fa-file-excel"></i> Export Excel
          </a>
          <a class="btn btn-danger"
             href="{{ route('pemakaian-bbm.export-pertanggungjawaban-pdf', ['bulan_label' => $bulanLabel ?? '']) }}">
            <i class="fa-solid fa-file-pdf"></i> Export PDF
          </a>
        </div>
        <div class="modal-footer" style="justify-content:center; padding-top:0;">
          <button type="button" class="btn btn-secondary" onclick="closeExportConfirm()">Tutup</button>
        </div>
      </div>

    </div>
  </div>

  {{-- Tambah periode baru: semua user boleh, biar bisa generate laporan sendiri --}}
  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3><i class="fa-solid fa-calendar-plus" style="color: var(--primary-color); margin-right: 8px;"></i> Tambah Periode</h3>
      </div>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('pemakaian-bbm.pertanggungjawaban.periode.store') }}"
            style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        @csrf
        <div class="form-group" style="margin:0; max-width:220px;">
          <label for="bulan_label">Label Bulan</label>
          <input type="text" id="bulan_label" name="bulan_label" class="form-control" list="bulan-options"
                 placeholder="Contoh: Agustus 2026"
                 value="{{ old('bulan_label', $bulanLabel ?? '') }}" required>
        </div>
        <div class="form-group" style="margin:0;">
          <label for="tanggal_awal">Tanggal Awal</label>
          <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control"
                 value="{{ old('tanggal_awal') }}" required>
        </div>
        <div class="form-group" style="margin:0;">
          <label for="tanggal_akhir">Tanggal Akhir</label>
          <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control"
                 value="{{ old('tanggal_akhir') }}" required>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-plus"></i> Tambah Periode
        </button>
      </form>
      <p style="margin-top:10px; color:#94a3b8; font-size:0.85rem;">
        <i class="fa-solid fa-circle-info"></i>
        Tanggal yang sudah dipakai periode lain otomatis tidak bisa dipakai lagi.
        Kalau salah input, minta admin hapus periode-nya di tab <strong>Riwayat</strong> supaya tanggalnya bisa dipilih ulang.
      </p>
    </div>
  </div>

  {{-- Preview laporan --}}
  @if(!empty($weeks))
    <div class="card">
      <div class="card-body">
        @include('rekapan.pemakaian-bbm._pertanggungjawaban-report', [
          'weeks'         => $weeks,
          'bulanLabel'    => $bulanLabel,
          'keterangan'    => $keterangan,
          'penandatangan' => $penandatangan,
        ])
      </div>
    </div>
  @endif

@endsection

@push('styles')
<style>
  .modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  .modal-overlay.show { display: flex; }
  .modal { background: #fff; border-radius: 12px; width: 90%; }
  .modal-confirm { max-width: 380px; }
  .modal-confirm-body { text-align: center; padding: 32px 24px 8px; }
  .modal-confirm-icon {
    width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%;
    background: #ecfdf5; color: #059669; display: flex; align-items: center;
    justify-content: center; font-size: 1.5rem;
  }
  .modal-confirm-title { margin: 0 0 8px; font-size: 1.1rem; font-weight: 700; color: #1f2937; }
  .modal-confirm-text { margin: 0; color: #6b7280; font-size: 0.9rem; line-height: 1.5; }
  .modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 20px; }
  .modal-confirm-footer { justify-content: center; padding-top: 20px; padding-bottom: 24px; }
</style>
@endpush

@push('scripts')
<script>
  function openExportConfirm() {
    document.getElementById('exportConfirmStep').style.display = 'block';
    document.getElementById('exportFormatStep').style.display = 'none';
    document.getElementById('exportConfirmModal').classList.add('show');
  }
  function closeExportConfirm() {
    document.getElementById('exportConfirmModal').classList.remove('show');
  }
  function showExportFormatStep() {
    document.getElementById('exportConfirmStep').style.display = 'none';
    document.getElementById('exportFormatStep').style.display = 'block';
  }
  document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('exportConfirmModal');
    if (overlay) {
      overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeExportConfirm();
      });
    }
  });
</script>
@endpush