<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Rekap E-Toll</title>
  <style>
    @page { margin: 16px 18px; }
    body { font-family: Arial, sans-serif; font-size: 8px; color: #1f2937; }

    .header-title { text-align: center; font-size: 14px; font-weight: bold; margin: 0; }
    .header-sub { text-align: center; font-size: 11px; margin: 2px 0 12px; }

    table { width: 100%; border-collapse: collapse; }

    .kategori td {
      background-color: #dbeafe;
      color: #1f2937;
      font-weight: bold;
      text-align: center;
      padding: 4px;
      border: 1px solid #000000;
    }

    thead th {
      background-color: #e9ecef;
      color: #1f2937;
      padding: 3px 2px;
      border: 1px solid #000000;
      text-align: center;
    }

    tbody td {
      padding: 2px;
      border: 1px solid #000000;
    }

    td.nama { text-align: left; white-space: nowrap; }
    td.angka { text-align: right; }

    tfoot td {
      padding: 3px 2px;
      border: 1px solid #000000;
      font-weight: bold;
      background-color: #f1f3f5;
      color: #1f2937;
    }
  </style>
</head>
<body>

  <p class="header-title">Rekap E-Toll</p>
  <p class="header-sub">Periode {{ $periodeLabel }}</p>

  <table>
    <tr class="kategori">
      <td colspan="{{ count($rows) + 2 }}">A. Roda Empat</td>
    </tr>
    <thead>
      <tr>
        <th>Nama</th>
        @foreach($rows as $row)
          <th>{{ $row['tanggal'] }}</th>
        @endforeach
        <th>Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @foreach($pemegangs as $p)
      <tr>
        <td class="nama">{{ $p->nama }}</td>
        @foreach($rows as $row)
          <td class="angka">{{ ($row['nilai'][$p->id] ?? 0) > 0 ? number_format($row['nilai'][$p->id], 0, ',', '.') : '-' }}</td>
        @endforeach
        <td class="angka" style="font-weight: bold;">{{ ($totalPerPemegang[$p->id] ?? 0) > 0 ? number_format($totalPerPemegang[$p->id], 0, ',', '.') : '-' }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="{{ count($rows) + 1 }}" style="text-align: center;">Total</td>
        <td class="angka">{{ number_format($totalKeseluruhan, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

</body>
</html>