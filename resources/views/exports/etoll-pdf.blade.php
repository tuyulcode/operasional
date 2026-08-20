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

    td.tanggal { text-align: center; width: 26px; }
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
  <p class="header-sub">Periode Bulan {{ $bulanNama }} {{ $tahun }}</p>

  <table>
    <tr class="kategori">
      <td colspan="{{ $pemegangs->count() + 2 }}">A. Roda Empat</td>
    </tr>
    <thead>
      <tr>
        <th>Tanggal</th>
        @foreach($pemegangs as $p)
          <th>{{ $p->nama }}</th>
        @endforeach
        <th>Jumlah</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $row)
      <tr>
        <td class="tanggal">{{ $row['tanggal'] }}</td>
        @foreach($pemegangs as $p)
          <td class="angka">{{ ($row['nilai'][$p->id] ?? 0) > 0 ? number_format($row['nilai'][$p->id], 0, ',', '.') : '-' }}</td>
        @endforeach
        <td class="angka">{{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') : '-' }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="{{ $pemegangs->count() + 1 }}" style="text-align: center;">Total</td>
        <td class="angka">{{ number_format($totalKeseluruhan, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

</body>
</html>