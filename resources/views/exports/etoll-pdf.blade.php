<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Rekap E-Toll</title>
  <style>
    @page { margin: 16px 18px; }
    body { font-family: Arial, sans-serif; color: #1f2937; }

    .header-title { text-align: center; font-size: 14px; font-weight: bold; margin: 0; }
    .header-sub { text-align: center; font-size: 11px; margin: 2px 0 12px; }

    table { border-collapse: collapse; margin: 0; table-layout: fixed; }

    td, th { border: 1px solid #000000; padding: 2px; }

    .col-nama { white-space: nowrap; }
    .col-tgl { text-align: center; }
    .col-jumlah { white-space: nowrap; text-align: center; }

    .kategori {
      background-color: #dbeafe;
      color: #1f2937;
      font-weight: bold;
      text-align: center;
      padding: 4px;
    }

    .kolom-header {
      background-color: #e9ecef;
      color: #1f2937;
      font-weight: bold;
      text-align: center;
      padding: 3px 2px;
    }

    td.nama { text-align: left; white-space: nowrap; }
    td.angka { text-align: right; }

    .total-row td {
      font-weight: bold;
      background-color: #f1f3f5;
      color: #1f2937;
      padding: 3px 2px;
    }
  </style>
</head>
<body>

  @php
    $namaPct = 12;
    $jumlahPct = 12;

    $totalDateCols = 0;
    foreach ($bulanGroups as $group) {
      $totalDateCols += count($group['rows']);
    }

    $sisaPct = 100 - $namaPct - $jumlahPct;
    $tanggalPct = $totalDateCols > 0 ? round($sisaPct / $totalDateCols, 2) : $sisaPct;

    $fontSize = match(true) {
        $totalDateCols <= 10 => 8,
        $totalDateCols <= 20 => 7,
        default => 6,
    };

    $fmt = fn ($val) => (!$val || $val <= 0) ? '-' : number_format($val, 0, ',', '.');

    $grandTotalPerPemegang = [];
    foreach ($pemegangs as $p) {
      $grandTotalPerPemegang[$p->id] = 0;
      foreach ($bulanGroups as $group) {
        $grandTotalPerPemegang[$p->id] += $group['totalPerPemegang'][$p->id] ?? 0;
      }
    }
  @endphp

  <p class="header-title">Rekap E-Toll</p>
  <p class="header-sub">Periode {{ $periodeLabel }}</p>

  <table style="width: 100%; font-size: {{ $fontSize }}px;">
    <tr>
      <td colspan="{{ $totalDateCols + 2 }}" class="kategori">A. Roda Empat</td>
    </tr>

    <tr class="kolom-header">
      <td rowspan="3" class="kolom-header col-nama" style="width: {{ $namaPct }}%;">Nama</td>
      @foreach($bulanGroups as $group)
        <td colspan="{{ count($group['rows']) }}" class="kolom-header">{{ $group['label'] }}</td>
      @endforeach
      <td rowspan="3" class="kolom-header col-jumlah" style="width: {{ $jumlahPct }}%;">Jumlah</td>
    </tr>

    <tr class="kolom-header">
      @foreach($bulanGroups as $group)
        <td colspan="{{ count($group['rows']) }}" class="kolom-header">Tanggal</td>
      @endforeach
    </tr>

    <tr class="kolom-header">
      @foreach($bulanGroups as $group)
        @foreach($group['rows'] as $row)
          <td class="kolom-header col-tgl" style="width: {{ $tanggalPct }}%;">{{ $row['tanggal'] }}</td>
        @endforeach
      @endforeach
    </tr>

    @foreach($pemegangs as $p)
    <tr>
      <td class="nama col-nama" style="width: {{ $namaPct }}%;">{{ $p->nama }}</td>
      @foreach($bulanGroups as $group)
        @foreach($group['rows'] as $row)
          <td class="angka col-tgl" style="width: {{ $tanggalPct }}%;">{{ $fmt($row['nilai'][$p->id] ?? 0) }}</td>
        @endforeach
      @endforeach
      <td class="angka col-jumlah" style="width: {{ $jumlahPct }}%; font-weight: bold;">{{ $fmt($grandTotalPerPemegang[$p->id]) }}</td>
    </tr>
    @endforeach

    <tr class="total-row">
      <td colspan="{{ $totalDateCols + 1 }}" style="text-align: center;">Total</td>
      <td class="angka" style="width: {{ $jumlahPct }}%;">{{ $fmt($totalKeseluruhan) }}</td>
    </tr>
  </table>

</body>
</html>
