@extends('layouts.app')

@section('title', 'Rekap Pemakaian BBM')

@section('content')

  <div class="page-header">
    <div class="page-title">Rekap Pemakaian BBM</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Pemakaian BBM</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Rekap</li>
    </ul>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="GET" action="{{ route('pemakaian-bbm.rekap') }}" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
        <div class="form-group" style="margin:0;">
          <label for="tanggal_awal">Tanggal Awal</label>
          <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control"
                 value="{{ $tanggal_awal ?? '' }}" required>
        </div>
        <div class="form-group" style="margin:0;">
          <label for="tanggal_akhir">Tanggal Akhir</label>
          <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control"
                 value="{{ $tanggal_akhir ?? '' }}" required>
        </div>
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
        </button>

        @if(!empty($tanggal_awal) && !empty($tanggal_akhir))
          <a class="btn btn-success"
             href="{{ route('pemakaian-bbm.export-excel', ['tanggal_awal' => $tanggal_awal, 'tanggal_akhir' => $tanggal_akhir]) }}">
            <i class="fa-solid fa-file-excel"></i> Export Excel
          </a>
          <a class="btn btn-danger"
             href="{{ route('pemakaian-bbm.export-pdf', ['tanggal_awal' => $tanggal_awal, 'tanggal_akhir' => $tanggal_akhir]) }}">
            <i class="fa-solid fa-file-pdf"></i> Export PDF
          </a>
        @endif
      </form>
    </div>
  </div>

  @if(!empty($tanggal_awal))
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