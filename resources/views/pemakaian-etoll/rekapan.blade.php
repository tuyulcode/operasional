@php
  $tanggalAwalRaw = $report['tanggalAwalRaw'];
  $tanggalAkhirRaw = $report['tanggalAkhirRaw'];
  $periodeLabel = $report['periodeLabel'];
  $pemegangs = $report['pemegangs'];
  $bulanGroups = $report['bulanGroups'];
  $maxDateCount = $report['maxDateCount'];
  $totalKeseluruhan = $report['totalKeseluruhan'];

  // Lebar kolom Nama FIXED (px), dihitung dari nama terpanjang di data pemegang
  // kendaraan — supaya nama tidak kepotong tapi juga tidak buang-buang ruang.
  $namaMaxLen = $pemegangs->pluck('nama')->map(fn($n) => mb_strlen($n))->max() ?: 4;
  $namaWidthPx = max(90, min(220, $namaMaxLen * 8 + 24));

  // Kolom Jumlah & kolom tanggal tetap pakai persentase dari sisa lebar tabel.
  $jumlahPct = 8;
  $sisaPct = 100 - $jumlahPct;
  $tanggalPct = $maxDateCount > 0 ? $sisaPct / $maxDateCount : $sisaPct;

  // Semakin banyak kolom tanggal, semakin kecil font supaya tetap terbaca.
  $fontSize = match(true) {
      $maxDateCount <= 10 => 12,
      $maxDateCount <= 20 => 10,
      default => 8,
  };

  $fmt = fn ($val) => (!$val || $val <= 0) ? '-' : number_format($val, 0, ',', '.');
@endphp

{{-- FILTER REKAPAN --}}
<div class="card">
  <div class="card-header">
    <div class="card-header-title">
      <h3>Filter Rekapan</h3>
      <p>Tanggal awal & tanggal akhir wajib dipilih</p>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('pemakaian-etoll.index') }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
      <input type="hidden" name="tab" value="rekapan">

      <div class="form-group" style="margin-bottom: 0; min-width: 160px;">
        <label for="tanggal_awal">Tanggal Awal <span style="color: #e11d48;">*</span></label>
        <input type="date" id="tanggal_awal" name="tanggal_awal" class="form-control"
               value="{{ $tanggalAwalRaw }}" required>
      </div>

      <div class="form-group" style="margin-bottom: 0; min-width: 160px;">
        <label for="tanggal_akhir">Tanggal Akhir <span style="color: #e11d48;">*</span></label>
        <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="form-control"
               value="{{ $tanggalAkhirRaw }}" required>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-filter"></i> Lihat Rekapan
      </button>

      @if($report['awal'])
        <a href="{{ route('pemakaian-etoll.export-excel', ['tanggal_awal' => $tanggalAwalRaw, 'tanggal_akhir' => $tanggalAkhirRaw]) }}"
           class="btn btn-excel">
          <i class="fa-solid fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('pemakaian-etoll.export-pdf', ['tanggal_awal' => $tanggalAwalRaw, 'tanggal_akhir' => $tanggalAkhirRaw]) }}"
           target="_blank"
           class="btn btn-pdf">
          <i class="fa-solid fa-file-pdf"></i> Export PDF
        </a>
      @endif
    </form>
  </div>
</div>

@if(!$report['awal'])

  <div class="card">
    <div class="card-body" style="text-align: center; padding: 40px; color: #999;">
      <i class="fa-solid fa-calendar-days" style="font-size: 2.5rem; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
      Pilih tanggal awal & tanggal akhir di atas untuk menampilkan rekapan.
    </div>
  </div>

@else

  <div class="card">
    <div class="card-body">
      <h4 style="text-align: center; margin-bottom: 4px;">Rekap E-Toll</h4>
      <p style="text-align: center; margin-bottom: 16px;">Periode {{ $periodeLabel }}</p>

      <div class="table-responsive" style="overflow-x: auto;">
        <table style="border-collapse: collapse; table-layout: fixed; width: 100%; font-size: {{ $fontSize }}px; margin: 0;" border="1" cellpadding="4" cellspacing="0">
          <tr>
            <td colspan="{{ $maxDateCount + 2 }}" style="background-color: #dbeafe; color: #1f2937; font-weight: bold; text-align: center;">
              A. Roda Empat
            </td>
          </tr>

          @foreach($bulanGroups as $group)
            <tr>
              <td colspan="{{ $maxDateCount + 2 }}" style="background-color: #eef4ff; color: #1f2937; font-weight: bold; text-align: center;">
                {{ $group['label'] }}
              </td>
            </tr>
            <tr style="background-color: #e9ecef; color: #1f2937; font-weight: bold; text-align: center;">
              <th style="width: {{ $namaWidthPx }}px; white-space: nowrap;">Nama</th>
              @foreach($group['rows'] as $row)
                <th style="width: {{ $tanggalPct }}%;">{{ $row['tanggal'] }}</th>
              @endforeach
              <th style="width: {{ $jumlahPct }}%; white-space: nowrap;">Jumlah</th>
            </tr>
            @foreach($pemegangs as $p)
            <tr>
              <td style="width: {{ $namaWidthPx }}px; white-space: nowrap;">{{ $p->nama }}</td>
              @foreach($group['rows'] as $row)
                <td style="width: {{ $tanggalPct }}%; text-align: right;">{{ $fmt($row['nilai'][$p->id] ?? 0) }}</td>
              @endforeach
              <td style="width: {{ $jumlahPct }}%; text-align: right; font-weight: bold;">{{ $fmt($group['totalPerPemegang'][$p->id] ?? 0) }}</td>
            </tr>
            @endforeach
          @endforeach

          <tr style="background-color: #f1f3f5; color: #1f2937; font-weight: bold;">
            <td colspan="{{ $maxDateCount + 1 }}" style="text-align: center;">Total</td>
            <td style="width: {{ $jumlahPct }}%; text-align: right;">{{ $fmt($totalKeseluruhan) }}</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

@endif