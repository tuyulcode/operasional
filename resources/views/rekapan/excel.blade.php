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

    .doc-header { width: 100%; margin-bottom: 6px; }
    .doc-header td { border: none; padding: 0; }
    .doc-header .logo-cell { width: 70px; }
    .doc-header .logo-cell img { height: 55px; }
    .doc-header .company-name { font-size: 15px; font-weight: bold; }
    .doc-header .company-unit { font-size: 13px; }

    .ttd-table { width: 100%; margin-top: 14px; border: none; }
    .ttd-table td { border: none; padding: 4px 8px; vertical-align: top; }
    .ttd-space { height: 60px; }
    .ttd-title { font-weight: bold; margin-bottom: 4px; }

    .center { text-align: center; }
    .foto-table { margin-top: 10px; }
    .foto-table td { border: none; text-align: center; }
  </style>
</head>
<body>
  <h2>Rekapan Tagihan Air</h2>
  <p>Periode: <b>{{ $periodeLabel }}</b> &nbsp; ({!! request('area_id') ? 'Area: ' . ($data->firstWhere('area.id', request('area_id'))['area']->nama ?? '-') : 'Semua Area' !!})</p>

  @foreach($data as $area)

    {{-- HEADER LOGO + NAMA PERUSAHAAN --}}
    <table class="doc-header">
      <tr>
        <td class="logo-cell">
          <img src="{{ asset('images/logo-pln2.png') }}" alt="Logo">
        </td>
        <td>
          <div class="company-name">PT. PLN NUSANTARA POWER</div>
          <div class="company-unit">UNIT PEMBANGKITAN PAITON</div>
        </td>
      </tr>
    </table>

    <h4>{{ $area['area']->nama }}</h4>

    @if($area['jml_titik'] === 1)
      @php($row1 = $area['rows']->first())
      @php($tg = $row1['tagihan'] ?? null)
      @php($ini = $tg ? (int) round((float) $tg->meter_ini) : 0)
      @php($lalu = $tg ? (int) round((float) $tg->meter_lalu) : 0)
      @php($faktor = $tg ? (float) $tg->meter_faktor : 0)
      <table>
        <tr>
          <td colspan="4"><b>BIAYA PEMAKAIAN AIR</b></td>
        </tr>
        <tr><td>Bulan</td><td>:</td><td colspan="2">{{ $periodeLabel }}</td></tr>
        <tr><td>NAMA</td><td>:</td><td colspan="2">{{ $area['area']->nama }}</td></tr>
        <tr><td>ALAMAT</td><td>:</td><td colspan="2">{{ $area['area']->alamat ?: '-' }}</td></tr>
        <tr><td>LOKASI FLOW METER</td><td>:</td><td colspan="2">{{ $row1['titik_meter']->nama }}</td></tr>
        <tr><td colspan="4"><b>PERHITUNGAN PEMAKAIAN</b></td></tr>
        <tr><td>Bulan ini</td><td>:</td><td class="center">{{ $ini }}</td><td>M3</td></tr>
        <tr><td>Bulan lalu</td><td>:</td><td class="center">{{ $lalu }}</td><td>M3</td></tr>
        <tr><td>Jumlah Pengambilan</td><td>:</td><td class="center">{{ $ini - $lalu }}</td><td>M3</td></tr>
        <tr><td>Meter Faktor</td><td>:</td><td class="center">{{ $tg ? number_format($faktor, 0, ',', '.') : '0' }}</td><td></td></tr>
        <tr><td>Jumlah Pengambilan</td><td>:</td><td class="center">{{ $tg ? (int) round((float) $tg->pemakaian) : 0 }}</td><td>M3</td></tr>
        <tr><td>Tarif / M3</td><td>:</td><td class="center">Rp {{ number_format($tg->tarif ?? 0, 0, ',', '.') }}</td><td></td></tr>
        <tr><td><b>Jumlah (Rp)</b></td><td>:</td><td class="center"><b>Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</b></td><td></td></tr>
        @if($area['kena_ppn'])
          <tr><td>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td><td>:</td><td class="center">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td><td></td></tr>
          <tr><td><b>Jumlah (Rp)</b></td><td>:</td><td class="center"><b>Rp {{ number_format($area['total'], 0, ',', '.') }}</b></td><td></td></tr>
        @endif
      </table>

      {{-- FOTO METER --}}
      @php
        $fotoPath = null;
        if ($tg && $tg->fotos->isNotEmpty()) {
            $rawPath = $tg->fotos->first()->path_foto;
            $fotoPath = str_starts_with($rawPath, 'uploads/')
                ? public_path($rawPath)
                : storage_path('app/public/' . $rawPath);
        }
      @endphp
      @if($fotoPath && file_exists($fotoPath))
        <table class="foto-table">
          <tr>
            <td><img src="{{ $fotoPath }}" style="max-height: 220px;"></td>
          </tr>
        </table>
      @endif
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

    {{-- TTD PER AREA --}}
    <table class="ttd-table">
      <tr>
        <td colspan="{{ $report['penandatangan']->count() }}" class="ttd-title">
          Mengetahui / Menyetujui
        </td>
      </tr>
      <tr>
        @foreach($report['penandatangan'] as $row)
          <td>
            {{ $row->jabatan }}
            <div class="ttd-space"></div>
            <b>{{ $row->nama ?: '...................................' }}</b>
          </td>
        @endforeach
      </tr>
      <tr>
        <td colspan="{{ $report['penandatangan']->count() }}">
          @php($ttdRow = $report['penandatangan']->first())
          @php($tempat = $ttdRow->tempat ?? '')
          @php($tanggal = now()->locale('id')->translatedFormat('d F Y'))
          {{ ($tempat ? $tempat . ', ' : '') . $tanggal }}
        </td>
      </tr>
    </table>

  @endforeach

  <h4 class="grand">Grand Total Semua Area: {{ number_format($grandTotal, 0, ',', '.') }}</h4>
</body>
</html>