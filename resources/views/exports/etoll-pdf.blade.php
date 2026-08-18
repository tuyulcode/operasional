<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Rekap E-Toll</title>
  <style>
    @page { margin: 20px 24px; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #1f2937; }

    .header-title { text-align: center; font-size: 15px; font-weight: bold; margin: 0; }
    .header-sub { text-align: center; font-size: 12px; margin: 2px 0 14px; }

    table { width: 100%; border-collapse: collapse; }

    .kategori td {
      background-color: #dbeafe;
      color: #1f2937;
      font-weight: bold;
      text-align: center;
      padding: 5px 6px;
      border: 1px solid #000000;
    }

    thead th {
      background-color: #e9ecef;
      color: #1f2937;
      padding: 5px 6px;
      border: 1px solid #000000;
      text-align: center;
      font-size: 10.5px;
    }

    tbody td {
      padding: 4px 6px;
      border: 1px solid #000000;
      font-size: 10.5px;
    }

    td.no { text-align: center; width: 24px; }
    td.nama { text-align: left; }
    td.angka { text-align: right; }

    tfoot td {
      padding: 5px 6px;
      border: 1px solid #000000;
      font-weight: bold;
      background-color: #f1f3f5;
      color: #1f2937;
    }
  </style>
</head>
<body>

  <p class="header-title">Rekap E-Toll</p>
  <p class="header-sub">Periode Bulan {{ $bulanNama }} {{ $tahun }}</p>

  <table>
    <tr class="kategori">
      <td colspan="8">A. Roda Empat</td>
    </tr>
    <thead>
      <tr>
        <th>No.</th>
        <th>Nama</th>
        <th>Minggu-1</th>
        <th>Minggu-2</th>
        <th>Minggu-3</th>
        <th>Minggu-4</th>
        <th>Minggu-5</th>
        <th>Jumlah (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $i => $row)
      <tr>
        <td class="no">{{ $i + 1 }}</td>
        <td class="nama">{{ $row['nama'] }}</td>
        @foreach($row['minggu'] as $val)
          <td class="angka">{{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}</td>
        @endforeach
        <td class="angka">{{ $row['jumlah'] > 0 ? number_format($row['jumlah'], 0, ',', '.') : '-' }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="2">Jumlah</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td class="angka">{{ $totalKeseluruhan > 0 ? number_format($totalKeseluruhan, 0, ',', '.') : '-' }}</td>
      </tr>
    </tfoot>
  </table>

</body>
</html>