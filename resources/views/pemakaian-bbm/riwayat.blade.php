@extends('layouts.app')

@section('title', 'Riwayat Pertanggung Jawaban BBM')

@section('content')

  <div class="page-header">
    <div class="page-title">Riwayat Pertanggung Jawaban BBM</div>
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

  @if(session('success'))
    <div class="alert-custom alert-success">
      <i class="fa-solid fa-circle-check"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Semua Periode Laporan Pertanggungjawaban</h3>
        <p>Daftar rentang tanggal yang sudah pernah dipakai untuk bikin laporan.</p>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table" style="width:100%; max-width:900px;">
          <colgroup>
            <col style="width:70px;">
            <col style="width:220px;">
            <col style="width:180px;">
            <col style="width:180px;">
            <col style="width:100px;">
          </colgroup>
          <thead>
            <tr>
              <th style="text-align:center;">No.</th>
              <th style="text-align:center;">Bulan</th>
              <th style="text-align:center;">Tanggal Awal</th>
              <th style="text-align:center;">Tanggal Akhir</th>
              @auth
                @if(auth()->user()->isAdmin())
                  <th style="text-align:center;">Aksi</th>
                @endif
              @endauth
            </tr>
          </thead>
          <tbody>
            @forelse($periodes as $i => $periode)
              <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td style="text-align:center;">
                  <a href="{{ route('pemakaian-bbm.pertanggungjawaban', ['bulan_label' => $periode->bulan_label]) }}">
                    {{ $periode->bulan_label }}
                  </a>
                </td>
                <td style="text-align:center;">{{ $periode->tanggal_awal->format('d-m-Y') }}</td>
                <td style="text-align:center;">{{ $periode->tanggal_akhir->format('d-m-Y') }}</td>
                @auth
                  @if(auth()->user()->isAdmin())
                    <td style="text-align:center;">
                      <form method="POST" action="{{ route('pemakaian-bbm.pertanggungjawaban.periode.destroy', $periode->id) }}"
                            onsubmit="return confirm('Hapus periode ini? Tanggalnya akan bisa dipilih lagi.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus periode">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </form>
                    </td>
                  @endif
                @endauth
              </tr>
            @empty
              <tr>
                <td colspan="5" style="text-align:center; padding:16px;">Belum ada periode yang dibuat.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection