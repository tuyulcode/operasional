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
    .foto-table { margin-top: 10px; width: auto; }
    .foto-table td { border: 1px solid #555; text-align: center; vertical-align: middle; padding: 4px 6px; word-wrap: break-word; }
    .foto-table td.foto-label { background-color: #d9e2f3; font-weight: bold; text-align: left; }
    .foto-table img { width: 100%; max-width: 45mm; display: block; margin: 0 auto 4px; }
  </style>
</head>
<body>
  <h2>Rekapan Tagihan Air</h2>
  <p>Periode: <b>{{ $periodeLabel }}</b> &nbsp; ({!! request('area_id') ? 'Area: ' . ($data->firstWhere('area.id', request('area_id'))['area']->nama ?? '-') : 'Semua Area' !!})</p>

  @foreach($data as $area)

    <?php
      // Format sama seperti PDF (rekap.blade.php):
      // 1 titik meter        -> 'standar'
      // 2-3 titik meter      -> 'multikolom'
      // lebih dari 3 titik   -> 'list'
      $jmlTitikExcel = $area['jml_titik'] ?? $area['rows']->count();

      if ($jmlTitikExcel === 1) {
          $formatExcel = 'standar';
      } elseif ($jmlTitikExcel <= 3) {
          $formatExcel = 'multikolom';
      } else {
          $formatExcel = 'list';
      }
    ?>

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

    @if($formatExcel === 'standar')
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
            <td><img src="{{ $fotoPath }}" style="max-height: 220px; max-width: none; width: auto;"></td>
          </tr>
        </table>
      @endif

    @elseif($formatExcel === 'multikolom')
      <?php
        $colsMulti = [];
        foreach ($area['rows'] as $row) {
            if (($row['titik_meter']->status ?? '') !== 'aktif') {
                continue;
            }
            $t = $row['tagihan'] ?? null;
            $colsMulti[] = [
                'titik_meter' => $row['titik_meter'],
                'tagihan' => $t,
                'nama' => $row['titik_meter']->nama,
                'ini' => $t ? (int) round((float) $t->meter_ini) : 0,
                'lalu' => $t ? (int) round((float) $t->meter_lalu) : 0,
                'faktor' => $t ? (float) $t->meter_faktor : 0,
                'pemakaian' => $t ? (int) round((float) $t->pemakaian) : 0,
                'tarif' => $t ? (float) $t->tarif : 0,
                'jumlah' => $t ? (float) $t->jumlah : 0,
            ];
        }
        $sumIniM = array_sum(array_column($colsMulti, 'ini'));
        $sumLaluM = array_sum(array_column($colsMulti, 'lalu'));
        $sumPengambilanM = array_sum(array_column($colsMulti, 'pemakaian'));
        $sumJumlahM = array_sum(array_column($colsMulti, 'jumlah'));
      ?>
      <table>
        <tr>
          <td colspan="{{ count($colsMulti) + 2 }}"><b>BIAYA PEMAKAIAN AIR</b></td>
        </tr>
        <tr><td>Bulan</td><td>:</td><td colspan="{{ count($colsMulti) }}">{{ $periodeLabel }}</td></tr>
        <tr><td>NAMA</td><td>:</td><td colspan="{{ count($colsMulti) }}">{{ $area['area']->nama }}</td></tr>
        <tr><td>ALAMAT</td><td>:</td><td colspan="{{ count($colsMulti) }}">{{ $area['area']->alamat ?: '-' }}</td></tr>
        <tr>
          <th colspan="2">PERHITUNGAN PEMAKAIAN</th>
          @foreach($colsMulti as $col)
            <th class="center">{{ $col['nama'] }}</th>
          @endforeach
        </tr>
        <tr>
          <td colspan="2">Bulan ini ( a )</td>
          @foreach($colsMulti as $col)<td class="center">{{ $col['ini'] }}</td>@endforeach
        </tr>
        <tr>
          <td colspan="2">Bulan lalu ( b )</td>
          @foreach($colsMulti as $col)<td class="center">{{ $col['lalu'] }}</td>@endforeach
        </tr>
        <tr>
          <td colspan="2">Jumlah Pengambilan ( c = a - b )</td>
          @foreach($colsMulti as $col)<td class="center">{{ $col['ini'] - $col['lalu'] }}</td>@endforeach
        </tr>
        <tr>
          <td colspan="2">Meter Faktor ( d )</td>
          @foreach($colsMulti as $col)<td class="center">{{ number_format($col['faktor'], 0, ',', '.') }}</td>@endforeach
        </tr>
        <tr>
          <td colspan="2">Jumlah Pengambilan ( e = c x d )</td>
          @foreach($colsMulti as $col)<td class="center">{{ $col['pemakaian'] }}</td>@endforeach
        </tr>
        <tr>
          <td colspan="2">Tarif / M3</td>
          @foreach($colsMulti as $col)<td class="center">Rp {{ number_format($col['tarif'], 0, ',', '.') }}</td>@endforeach
        </tr>
        <tr>
          <td colspan="2"><b>Jumlah (Rp)</b></td>
          @foreach($colsMulti as $col)<td class="center"><b>Rp {{ number_format($col['jumlah'], 0, ',', '.') }}</b></td>@endforeach
        </tr>
        <tr class="grand">
          <td colspan="2"><b>Subtotal {{ $area['area']->nama }}</b></td>
          <td colspan="{{ count($colsMulti) }}" class="center"><b>Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</b></td>
        </tr>
        @if($area['kena_ppn'])
          <tr>
            <td colspan="2"><b>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</b></td>
            <td colspan="{{ count($colsMulti) }}" class="center">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td>
          </tr>
          <tr class="grand">
            <td colspan="2"><b>Total {{ $area['area']->nama }}</b></td>
            <td colspan="{{ count($colsMulti) }}" class="center"><b>Rp {{ number_format($area['total'], 0, ',', '.') }}</b></td>
          </tr>
        @endif
      </table>

      {{-- FOTO METER: satu foto per titik, sejajar per kolom titik meter --}}
      <?php
        $barisFotoMulti = collect($colsMulti)->filter(fn ($c) => $c['tagihan'])->values();
        $adaFotoMulti = $barisFotoMulti->contains(fn ($c) => $c['tagihan']->fotos->isNotEmpty());
      ?>
      @if($adaFotoMulti)
        <table class="foto-table">
          <tr>
            @foreach($barisFotoMulti as $col)
              <td class="foto-label">{{ $col['nama'] }}</td>
            @endforeach
          </tr>
          <tr>
            @foreach($barisFotoMulti as $col)
              <?php
                $fotoPathMulti = null;
                if ($col['tagihan']->fotos->isNotEmpty()) {
                    $rawPathMulti = $col['tagihan']->fotos->first()->path_foto;
                    $fotoPathMulti = str_starts_with($rawPathMulti, 'uploads/')
                        ? public_path($rawPathMulti)
                        : storage_path('app/public/' . $rawPathMulti);
                }
              ?>
              <td>
                @if($fotoPathMulti && file_exists($fotoPathMulti))
                  <img src="{{ $fotoPathMulti }}">
                @else
                  <em>tidak ada foto</em>
                @endif
              </td>
            @endforeach
          </tr>
        </table>
      @endif

    @else
      {{-- FORMAT LIST: lebih dari 3 titik meter --}}
      <table>
        <tr>
          <th rowspan="2">No. Urut</th>
          <th rowspan="2">Nama Titik Meter</th>
          <th colspan="2">COUNTER M3</th>
          <th rowspan="2">Pengambilan</th>
          <th rowspan="2">Tarif (Rp/M3)</th>
          <th rowspan="2">Jumlah (Rp)</th>
        </tr>
        <tr>
          <th>Bulan Ini</th>
          <th>Bulan Lalu</th>
        </tr>
        <?php $noUrutExcel = 0; ?>
        @foreach($area['rows'] as $row)
          @if(($row['titik_meter']->status ?? 'aktif') !== 'aktif')
            @continue
          @endif
          <?php $tgList = $row['tagihan'] ?? null; $noUrutExcel++; ?>
          <tr>
            <td>{{ $noUrutExcel }}</td>
            <td>{{ $row['titik_meter']->nama }}</td>
            <td class="center">{{ $tgList ? (int) round((float) $tgList->meter_ini) : '' }}</td>
            <td class="center">{{ $tgList ? (int) round((float) $tgList->meter_lalu) : '' }}</td>
            <td class="center">{{ $tgList ? (int) round((float) $tgList->pemakaian) : '' }}</td>
            <td class="center">{{ $tgList ? 'Rp ' . number_format($tgList->tarif, 0, ',', '.') : 'Rp ' . number_format($row['titik_meter']->tarif_harga ?? 0, 0, ',', '.') }}</td>
            <td class="center"><b>{{ $tgList ? 'Rp ' . number_format($tgList->jumlah, 0, ',', '.') : '' }}</b></td>
          </tr>
        @endforeach
        <tr class="grand">
          <td colspan="5"><b>Subtotal {{ $area['area']->nama }}</b></td>
          <td></td>
          <td class="center"><b>Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</b></td>
        </tr>
        @if($area['kena_ppn'])
          <tr>
            <td colspan="5"><b>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</b></td>
            <td></td>
            <td class="center">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td>
          </tr>
          <tr class="grand">
            <td colspan="5"><b>Total {{ $area['area']->nama }}</b></td>
            <td></td>
            <td class="center"><b>Rp {{ number_format($area['total'], 0, ',', '.') }}</b></td>
          </tr>
        @endif
      </table>

      {{-- FOTO METER: grid 4 kolom per baris, sisa kolom tanpa titik di-merge --}}
      <?php
        $barisFotoList = $area['rows']->filter(fn ($row) => $row['tagihan'])->values();
        $adaFotoList = $barisFotoList->contains(fn ($row) => $row['tagihan']->fotos->isNotEmpty());
        $fotoChunksList = $barisFotoList->chunk(4)->values();
      ?>
      @if($adaFotoList)
        @foreach($fotoChunksList as $chunkIdxL => $chunkL)
          <?php $chunkArrL = $chunkL->values(); $chunkCountL = $chunkArrL->count(); ?>
          <table class="foto-table">
            <tr>
              @foreach($chunkArrL as $idxL => $rowL)
                <?php
                  $noLabelL = $chunkIdxL * 4 + $idxL + 1;
                  $spanL = ($idxL === $chunkCountL - 1) ? (4 - $chunkCountL + 1) : 1;
                ?>
                <td class="foto-label" colspan="{{ $spanL }}" style="width: {{ $spanL * 25 }}%;">{{ $noLabelL }}. {{ $rowL['titik_meter']->nama }}</td>
              @endforeach
            </tr>
            <tr>
              @foreach($chunkArrL as $idxL => $rowL)
                <?php
                  $spanL = ($idxL === $chunkCountL - 1) ? (4 - $chunkCountL + 1) : 1;
                  $fotoPathL = null;
                  if ($rowL['tagihan']->fotos->isNotEmpty()) {
                      $rawPathL = $rowL['tagihan']->fotos->first()->path_foto;
                      $fotoPathL = str_starts_with($rawPathL, 'uploads/')
                          ? public_path($rawPathL)
                          : storage_path('app/public/' . $rawPathL);
                  }
                ?>
                <td colspan="{{ $spanL }}" style="width: {{ $spanL * 25 }}%;">
                  @if($fotoPathL && file_exists($fotoPathL))
                    <img src="{{ $fotoPathL }}">
                  @else
                    <em>tidak ada foto</em>
                  @endif
                </td>
              @endforeach
            </tr>
          </table>
        @endforeach
      @endif
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