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

    @if($area['jml_titik'] === 1)
      @php($row1 = $area['rows']->first())
      @php($tg = $row1['tagihan'] ?? null)
      @php($ini = $tg ? (int) round((float) $tg->meter_ini) : 0)
      @php($lalu = $tg ? (int) round((float) $tg->meter_lalu) : 0)
      @php($faktor = $tg ? (float) $tg->meter_faktor : 0)
      <table>
        <tr>
          <td colspan="2"><b>BIAYA PEMAKAIAN AIR</b></td>
        </tr>
        <tr><td>Bulan</td><td>:</td><td>{{ $periodeLabel }}</td></tr>
        <tr><td>NAMA</td><td>:</td><td>{{ $area['area']->nama }}</td></tr>
        <tr><td>ALAMAT</td><td>:</td><td>{{ $area['area']->alamat ?: '-' }}</td></tr>
        <tr><td>LOKASI FLOW METER</td><td>:</td><td>{{ $row1['titik_meter']->nama }}</td></tr>
        <tr><td colspan="3"><b>PERHITUNGAN PEMAKAIAN</b></td></tr>
        <tr><td>Bulan ini</td><td>:</td><td>{{ $ini }}</td></tr>
        <tr><td>Bulan lalu</td><td>:</td><td>{{ $lalu }}</td></tr>
        <tr><td>Jumlah Pengambilan</td><td>:</td><td>{{ $ini - $lalu }}</td></tr>
        <tr><td>Meter Faktor</td><td>:</td><td>{{ $tg ? number_format($faktor, 0, ',', '.') : '0' }}</td></tr>
        <tr><td>Jumlah Pengambilan</td><td>:</td><td>{{ $tg ? (int) round((float) $tg->pemakaian) : 0 }}</td></tr>
        <tr><td>Tarif / M3</td><td>:</td><td>Rp {{ number_format($tg->tarif ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td><b>Jumlah (Rp)</b></td><td>:</td><td><b>Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</b></td></tr>
        @if($area['kena_ppn'])
          <tr><td>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td><td>:</td><td>Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td></tr>
          <tr><td><b>Jumlah (Rp)</b></td><td>:</td><td><b>Rp {{ number_format($area['total'], 0, ',', '.') }}</b></td></tr>
        @endif
      </table>
    @else
    <table>
      <tr>
        <th>No. Urut</th>
        <th>Nama Titik Meter</th>
        <th colspan="2">COUNTER M3</th>
        <th>Pengambilan</th>
        <th>Tarif (Rp/M3)</th>
        <th>Jumlah (Rp)</th>
      </tr>
      <tr>
        <th></th>
        <th></th>
        <th>Bulan Ini</th>
        <th>Bulan Lalu</th>
        <th></th>
        <th></th>
        <th></th>
      </tr>
      @foreach($area['rows'] as $i => $row)
        @continue(!$row['tagihan'])
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $row['titik_meter']->nama }}</td>
          <td>{{ (int) round((float) $row['tagihan']->meter_ini) }}</td>
          <td>{{ (int) round((float) $row['tagihan']->meter_lalu) }}</td>
          <td>{{ (int) round((float) $row['tagihan']->pemakaian) }}</td>
          <td>Rp {{ number_format($row['tagihan']->tarif, 0, ',', '.') }}</td>
          <td>Rp {{ number_format($row['tagihan']->jumlah, 0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="4"><b>Subtotal {{ $area['area']->nama }}</b></td>
        <td>{{ $area['total_pemakaian'] ? (int) round($area['total_pemakaian']) : '-' }}</td>
        <td></td>
        <td><b>Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</b></td>
      </tr>
      @if($area['kena_ppn'])
        <tr>
          <td colspan="4"><b>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</b></td>
          <td></td>
          <td></td>
          <td>Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td colspan="4"><b>Total {{ $area['area']->nama }}</b></td>
          <td></td>
          <td></td>
          <td><b>Rp {{ number_format($area['total'], 0, ',', '.') }}</b></td>
        </tr>
      @endif
    </table>
    @endif
  @endforeach

  <h4 class="grand">Grand Total Semua Area: {{ number_format($grandTotal, 0, ',', '.') }}</h4>
</body>
</html>