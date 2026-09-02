@extends('layouts.app')

@section('title', 'Rekap BBM & Consumable')

@section('content')

  <div class="page-header">
    <div class="page-title">Pemakaian BBM</div>
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

  <div class="card">
    <div class="card-body">
      <form method="GET" action="{{ route('pemakaian-bbm.rekapan') }}" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <div class="form-group" style="margin:0;">
          <label for="tanggal_awal">Tanggal Awal</label>
          <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control"
                 value="{{ $tanggalAwal ?? '' }}" required>
        </div>
        <div class="form-group" style="margin:0;">
          <label for="tanggal_akhir">Tanggal Akhir</label>
          <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control"
                 value="{{ $tanggalAkhir ?? '' }}" required>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
        </button>

        @if(!empty($tanggalAwal) && !empty($tanggalAkhir))
          <button type="button" id="btnExportLaporan" class="btn btn-success" onclick="openExportConfirm()">
            <i class="fa-solid fa-file-export"></i> Export Laporan
          </button>
        @endif
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
          <h3 class="modal-confirm-title">Export Laporan?</h3>
          <p class="modal-confirm-text">Apakah Anda yakin ingin export laporan rekapan untuk periode ini?</p>
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
             href="{{ route('pemakaian-bbm.export-excel', ['tanggal_awal' => $tanggalAwal ?? '', 'tanggal_akhir' => $tanggalAkhir ?? '']) }}">
            <i class="fa-solid fa-file-excel"></i> Export Excel
          </a>
          <a class="btn btn-danger"
             href="{{ route('pemakaian-bbm.export-pdf', ['tanggal_awal' => $tanggalAwal ?? '', 'tanggal_akhir' => $tanggalAkhir ?? '']) }}">
            <i class="fa-solid fa-file-pdf"></i> Export PDF
          </a>
        </div>
        <div class="modal-footer" style="justify-content:center; padding-top:0;">
          <button type="button" class="btn btn-secondary" onclick="closeExportConfirm()">Tutup</button>
        </div>
      </div>

    </div>
  </div>

  @if(!empty($tanggalAwal))
  <div class="card">
    <div class="card-body">
      <h4 style="text-align:center;">PEMAKAIAN BBM KENDARAAN DINAS</h4>
      <p style="text-align:center; margin-bottom:16px;">Periode {{ $periodeLabel }}</p>
      <div class="table-responsive">
        @include('rekapan.pemakaian-bbm._rekap-table')
      </div>
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
  .modal-confirm { max-width: 420px; }
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