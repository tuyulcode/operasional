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
  </div>

  @if($errors->any())
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="GET" action="{{ route('pemakaian-bbm.pertanggungjawaban') }}" id="form-ptj">
        <div class="form-group" style="max-width:260px;">
          <label for="bulan_label">Label Bulan</label>
          <input type="text" id="bulan_label" name="bulan_label" class="form-control"
                 placeholder="Contoh: Agustus 2026"
                 value="{{ $bulanLabel ?? '' }}" required>
        </div>

        <div id="minggu-container" style="margin-top:14px;">
          @php $mingguRows = !empty($minggus) ? $minggus : [['tanggal_awal' => '', 'tanggal_akhir' => '']]; @endphp
          @foreach($mingguRows as $i => $m)
            <div class="minggu-row" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:10px;">
              <div class="form-group" style="margin:0;">
                <label class="minggu-label">Minggu {{ $i + 1 }} - Tanggal Awal</label>
                <input type="date" name="minggu[{{ $i }}][tanggal_awal]" class="form-control"
                       value="{{ $m['tanggal_awal'] ?? '' }}" required>
              </div>
              <div class="form-group" style="margin:0;">
                <label>Tanggal Akhir</label>
                <input type="date" name="minggu[{{ $i }}][tanggal_akhir]" class="form-control"
                       value="{{ $m['tanggal_akhir'] ?? '' }}" required>
              </div>
              <button type="button" class="btn btn-danger btn-remove-minggu" style="height:38px;" title="Hapus minggu ini">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          @endforeach
        </div>

        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:6px;">
          <button type="button" id="btn-add-minggu" class="btn btn-secondary">
            <i class="fa-solid fa-plus"></i> Tambah Minggu
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Tampilkan
          </button>

          @if(!empty($weeks))
            <a class="btn btn-success"
               href="{{ route('pemakaian-bbm.export-pertanggungjawaban-excel', request()->query()) }}">
              <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
            <a class="btn btn-danger"
               href="{{ route('pemakaian-bbm.export-pertanggungjawaban-pdf', request()->query()) }}">
              <i class="fa-solid fa-file-pdf"></i> Export PDF
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

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

@push('scripts')
<script>
  (function () {
    const container = document.getElementById('minggu-container');
    const btnAdd = document.getElementById('btn-add-minggu');

    function reindex() {
      container.querySelectorAll('.minggu-row').forEach((row, i) => {
        row.querySelector('.minggu-label').textContent = 'Minggu ' + (i + 1) + ' - Tanggal Awal';
        row.querySelectorAll('input').forEach((input) => {
          input.name = input.name.replace(/minggu\[\d+\]/, 'minggu[' + i + ']');
        });
      });
    }

    btnAdd.addEventListener('click', function () {
      const idx = container.querySelectorAll('.minggu-row').length;
      const row = document.createElement('div');
      row.className = 'minggu-row';
      row.style.cssText = 'display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:10px;';
      row.innerHTML = `
        <div class="form-group" style="margin:0;">
          <label class="minggu-label">Minggu ${idx + 1} - Tanggal Awal</label>
          <input type="date" name="minggu[${idx}][tanggal_awal]" class="form-control" required>
        </div>
        <div class="form-group" style="margin:0;">
          <label>Tanggal Akhir</label>
          <input type="date" name="minggu[${idx}][tanggal_akhir]" class="form-control" required>
        </div>
        <button type="button" class="btn btn-danger btn-remove-minggu" style="height:38px;" title="Hapus minggu ini">
          <i class="fa-solid fa-trash"></i>
        </button>
      `;
      container.appendChild(row);
    });

    container.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-remove-minggu');
      if (!btn) return;
      const rows = container.querySelectorAll('.minggu-row');
      if (rows.length <= 1) return;
      btn.closest('.minggu-row').remove();
      reindex();
    });
  })();
</script>
@endpush