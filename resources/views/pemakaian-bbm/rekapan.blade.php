@extends('layouts.app')

@section('title', 'Rekap Pemakaian BBM')

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
          <a class="btn btn-success"
             href="{{ route('pemakaian-bbm.export-excel', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir]) }}">
            <i class="fa-solid fa-file-excel"></i> Export Excel
          </a>
          <a class="btn btn-danger"
             href="{{ route('pemakaian-bbm.export-pdf', ['tanggal_awal' => $tanggalAwal, 'tanggal_akhir' => $tanggalAkhir]) }}">
            <i class="fa-solid fa-file-pdf"></i> Export PDF
          </a>
        @endif
      </form>
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