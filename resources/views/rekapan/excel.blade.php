<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Rekapan Tagihan Air {{ $periodeLabel }}</title>
  <style>
    body { font-family: Calibri, sans-serif; }
    h2 { margin-bottom: 4px; }
    h4 { margin: 18px 0 6px 0; }
    table { border-collapse: collapse; width: 100%; }
    table, th, td { border: 1px solid #555; }
    th, td { padding: 5px 8px; text-align: left; }
    th { background-color: #d9e2f3; }
    .right { text-align: right; }
    .grand { font-weight: bold; background-color: #e2efda; }
  </style>
</head>
<body>
  <h2>Rekapan Tagihan Air</h2>
  <p>Periode: <b>{{ $periodeLabel }}</b> &nbsp; ({!! request('area_id') ? 'Area: ' . ($data->firstWhere('area.id', request('area_id'))['area']->nama ?? '-') : 'Semua Area' !!})</p>

  @foreach($data as $area)
    <h4>{{ $area['area']->nama }}</h4>
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Titik Meter</th>
          <th class="right">Meter Lalu</th>
          <th class="right">Meter Ini</th>
          <th class="right">Pemakaian (m3)</th>
          <th class="right">Tarif</th>
          <th class="right">Jumlah</th>
        </tr>
      </thead>
      <tbody>
        @foreach($area['rows'] as $i => $row)
          @continue(!$row['tagihan'])
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $row['titik_meter']->nama }}</td>
            <td class="right">{{ number_format($row['tagihan']->meter_lalu, 2, ',', '.') }}</td>
            <td class="right">{{ number_format($row['tagihan']->meter_ini, 2, ',', '.') }}</td>
            <td class="right">{{ number_format($row['tagihan']->pemakaian, 2, ',', '.') }}</td>
            <td class="right">{{ number_format($row['tagihan']->tarif, 0, ',', '.') }}</td>
            <td class="right">{{ number_format($row['tagihan']->jumlah, 0, ',', '.') }}</td>
          </tr>
        @endforeach
        <tr>
          <td colspan="5"><b>Subtotal {{ $area['area']->nama }}</b></td>
          <td></td>
          <td class="right"><b>{{ number_format($area['subtotal'], 0, ',', '.') }}</b></td>
        </tr>
        @if($area['kena_ppn'])
          <tr>
            <td colspan="5"><b>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</b></td>
            <td></td>
            <td class="right">{{ number_format($area['ppn'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td colspan="5"><b>Total {{ $area['area']->nama }}</b></td>
            <td></td>
            <td class="right"><b>{{ number_format($area['total'], 0, ',', '.') }}</b></td>
          </tr>
        @endif
      </tbody>
    </table>
  @endforeach

  <h4 class="grand">Grand Total Semua Area: {{ number_format($grandTotal, 0, ',', '.') }}</h4>
</body>
</html>