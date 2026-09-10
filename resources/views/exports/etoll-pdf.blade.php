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

    table { border-collapse: collapse; margin: 0; table-layout: fixed; width: 100%; }

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

    .bulan-label {
      background-color: #eef4ff;
      color: #1f2937;
      font-weight: bold;
      text-align: center;
      padding: 3px;
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
    // Lebar kolom Nama dihitung dari nama terpanjang (estimasi px), lalu
    // dikonversi ke PERSEN — semua kolom di tabel pakai satuan % yang sama,
    // supaya dompdf tidak salah hitung lebar (dompdf kurang bisa diandalkan
    // kalau satu tabel table-layout:fixed dicampur satuan px & % sekaligus).
    $namaMaxLen = $pemegangs->pluck('nama')->map(fn($n) => mb_strlen($n))->max() ?: 4;
    $namaWidthPxEstimasi = max(45, min(90, $namaMaxLen * 3.5 + 12));
    $lebarHalamanPx = 1000; // estimasi lebar area cetak A4 landscape (dikurangi margin)
    $namaPct = max(6, min(18, ($namaWidthPxEstimasi / $lebarHalamanPx) * 100));

    $jumlahPct = 8;
    $sisaPct = 100 - $namaPct - $jumlahPct;
    $tanggalPct = $maxDateCount > 0 ? $sisaPct / $maxDateCount : $sisaPct;

    $fontSize = match(true) {
        $maxDateCount <= 10 => 8,
        $maxDateCount <= 20 => 7,
        default => 6,
    };

    $fmt = fn ($val) => (!$val || $val <= 0) ? '-' : number_format($val, 0, ',', '.');
  @endphp

  <p class="header-title">Rekap E-Toll</p>
  <p class="header-sub">Periode {{ $periodeLabel }}</p>

  <table style="font-size: {{ $fontSize }}px;">
    <tr>
      <td colspan="{{ $maxDateCount + 2 }}" class="kategori">A. Roda Empat</td>
    </tr>

    @foreach($bulanGroups as $group)
      <tr>
        <td colspan="{{ $maxDateCount + 2 }}" class="bulan-label">{{ $group['label'] }}</td>
      </tr>
      <tr>
        <td class="kolom-header col-nama" style="width: {{ $namaPct }}%;">Nama</td>
        @foreach($group['rows'] as $row)
          <td class="kolom-header col-tgl" style="width: {{ $tanggalPct }}%;">{{ $row['tanggal'] }}</td>
        @endforeach
        <td class="kolom-header col-jumlah" style="width: {{ $jumlahPct }}%;">Jumlah</td>
      </tr>
      @foreach($pemegangs as $p)
      <tr>
        <td class="nama col-nama" style="width: {{ $namaPct }}%;">{{ $p->nama }}</td>
        @foreach($group['rows'] as $row)
          <td class="angka col-tgl" style="width: {{ $tanggalPct }}%;">{{ $fmt($row['nilai'][$p->id] ?? 0) }}</td>
        @endforeach
        <td class="angka col-jumlah" style="width: {{ $jumlahPct }}%; font-weight: bold;">{{ $fmt($group['totalPerPemegang'][$p->id] ?? 0) }}</td>
      </tr>
      @endforeach
    @endforeach

    <tr class="total-row">
      <td colspan="{{ $maxDateCount + 1 }}" style="text-align: center;">Total</td>
      <td class="angka" style="width: {{ $jumlahPct }}%;">{{ $fmt($totalKeseluruhan) }}</td>
    </tr>
  </table>

</body>
</html>