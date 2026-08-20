<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekapan Tagihan Air</title>
  <style>
    @page { margin: 24px 24px; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #111; }

    .doc-header table { width: 100%; border-collapse: collapse; }
    .doc-header .logo-cell { width: 22%; text-align: left; vertical-align: middle; }
    .doc-header .logo-cell img { width: 80px; height: auto; }
    .doc-header .org-cell { text-align: left; vertical-align: middle; }
    .doc-header .org-name { font-size: 16px; font-weight: bold; }
    .doc-header .org-sub { font-size: 12px; margin-top: 3px; }

    .area-title { text-align: center; font-weight: bold; font-size: 13px; margin: 12px 0 2px; }
    .area-bulan { text-align: center; font-size: 11px; margin-bottom: 8px; }

    .area-name { font-weight: bold; font-size: 11px; margin: 12px 0 5px; }

    table.grid { width: 100%; border-collapse: collapse; }
    table.grid th, table.grid td { border: 1px solid #333; padding: 3px 5px; font-size: 10px; }
    table.grid thead th { background: #e9ecf5; text-align: center; font-weight: bold; }
    .r { text-align: right; }
    .c { text-align: center; }
    .bold { font-weight: bold; }
    .subtotal-bg { background: #eef5ec; }
    td.foto { text-align: center; }
    td.foto img { width: 20mm; height: auto; }

    table.vbox { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.vbox td { border: 1px solid #333; padding: 4px 6px; font-size: 10px; }
    table.vbox .v-title { text-align: center; font-weight: bold; background: #e9ecf5; }

    table.infobox { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.infobox td { border: 1px solid #333; padding: 4px 6px; font-size: 10px; }

    td.m3 { width: 8%; text-align: center; background: #f7f7f7; }

    .foto-meter { margin-top: 10px; }
    .foto-meter img { width: 70mm; height: auto; border: 1px solid #888; }
    .foto-empty { margin-top: 10px; color: #555; }

    .foto-gallery { margin-top: 10px; }
    .foto-gallery table { margin: 0 auto; border-collapse: collapse; }
    .foto-gallery td { padding: 4px; text-align: center; }
    .foto-gallery img { border: 1px solid #888; }
    .foto-gallery.count-1 img { width: 70mm; }
    .foto-gallery.count-2 img { width: 62mm; }
    .foto-gallery.count-3 img { width: 55mm; }

    .grand-total { font-weight: bold; font-size: 11px; margin-top: 10px; }

    .sign { margin-top: 60px; }
    .signature-table { width: 100%; border-collapse: collapse; }
    .signature-table td { width: 50%; vertical-align: top; text-align: left; padding: 0; }
    .sign-title { font-weight: bold; }
    .sign-jabatan { font-weight: bold; }
    .signature-space { height: 65px; }
  </style>
</head>
<body>

  <div class="doc-header">
    <table>
      <tr>
        <td class="logo-cell">
          @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" alt="Logo">
          @endif
        </td>
        <td class="org-cell">
          <div class="org-name">PT. PLN NUSANTARA POWER</div>
          <div class="org-sub">UNIT PEMBANGKITAN PAITON</div>
        </td>
      </tr>
    </table>
  </div>

  @foreach($data as $i => $area)
    <?php
      $format = $area['area']->format_rekap
        ?: ((($area['jml_titik'] ?? $area['rows']->count()) === 1) ? 'standar' : 'list');
    ?>

    @if($i > 0)
      <div style="page-break-before: always;"></div>
    @endif

    <div class="area-title">BIAYA PEMAKAIAN AIR</div>
    <div class="area-bulan">Bulan : {{ $periodeLabel }}</div>

    @if($format === 'standar')

      <?php
        $row1 = $area['rows']->first();
        $tg = $row1['tagihan'] ?? null;
        $ini = $tg ? (int) round((float) $tg->meter_ini) : 0;
        $lalu = $tg ? (int) round((float) $tg->meter_lalu) : 0;
        $faktor = $tg ? (float) $tg->meter_faktor : 0;
        $fotos = $tg ? $tg->fotos : collect();
        $lokasiFm = $row1['titik_meter']->lokasi_flow_meter ?:
          $row1['titik_meter']->nama;
      ?>

      <table class="infobox">
        <tr>
          <td>NAMA : {{ $area['area']->nama }}</td>
          <td>LOKASI FLOW METER : {{ $lokasiFm }}</td>
        </tr>
        <tr>
          <td>ALAMAT : {{ $area['area']->alamat ?: '-' }}</td>
          <td></td>
        </tr>
      </table>

      <table class="vbox">
        <tr><td colspan="3" class="v-title">PERHITUNGAN PEMAKAIAN</td></tr>
        <tr><td>Bulan ini ( a )</td><td class="r">{{ $ini }}</td><td class="m3">M&sup3;</td></tr>
        <tr><td>Bulan lalu ( b )</td><td class="r">{{ $lalu }}</td><td class="m3">M&sup3;</td></tr>
        <tr><td>Jumlah Pengambilan ( c = a - b )</td><td class="r">{{ $ini - $lalu }}</td><td class="m3">M&sup3;</td></tr>
        <tr><td>Meter Faktor ( d )</td><td class="r">{{ $tg ? number_format($faktor, 0, ',', '.') : '0' }}</td><td class="m3"></td></tr>
        <tr><td>Jumlah Pengambilan ( e = c x d )</td><td class="r">{{ $tg ? (int) round((float) $tg->pemakaian) : 0 }}</td><td class="m3">M&sup3;</td></tr>
        <tr><td>Tarif / M3</td><td class="r">Rp {{ number_format($tg->tarif ?? 0, 2, ',', '.') }}</td><td class="m3"></td></tr>
        <tr><td class="bold">Jumlah (Rp)</td><td class="r bold">Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</td><td class="m3"></td></tr>
        @if($area['kena_ppn'])
          <tr><td>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td><td class="r">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td><td class="m3"></td></tr>
          <tr><td class="bold">Jumlah (Rp)</td><td class="r bold">Rp {{ number_format($area['total'], 0, ',', '.') }}</td><td class="m3"></td></tr>
        @endif
      </table>

      @if($tg && $fotos->count())
        <div class="foto-meter">
          <strong>FOTO METER :</strong><br>
          <div class="foto-gallery count-{{ min($fotos->count(), 3) }}">
            <table>
              @foreach($fotos->chunk(3) as $chunk)
                <tr>
                  @foreach($chunk as $foto)
                    <td>
                      @if($foto->file_path && is_file($foto->file_path))
                        <img src="{{ $foto->file_path }}" alt="Foto meter">
                      @else
                        <em style="color: #888;">file tidak ditemukan</em>
                      @endif
                    </td>
                  @endforeach
                  @for($j = $chunk->count(); $j < 3; $j++)
                    <td></td>
                  @endfor
                </tr>
              @endforeach
            </table>
          </div>
        </div>
      @elseif($tg && $tg->fotos->isEmpty() && $tg->foto)
        <div class="foto-empty">Foto meter ada di database tetapi file tidak ditemukan.</div>
      @endif

    @elseif($format === 'multikolom')

      <?php
        $cols = [];
        foreach ($area['rows'] as $row) {
            if (($row['titik_meter']->status ?? '') !== 'aktif') {
                continue;
            }
            $t = $row['tagihan'] ?? null;
            $cols[] = [
                'nama' => $row['titik_meter']->nama,
                'ini' => $t ? (int) round((float) $t->meter_ini) : 0,
                'lalu' => $t ? (int) round((float) $t->meter_lalu) : 0,
                'faktor' => $t ? (float) $t->meter_faktor : 0,
                'pemakaian' => $t ? (int) round((float) $t->pemakaian) : 0,
                'tarif' => $t ? (float) $t->tarif : 0,
                'jumlah' => $t ? (float) $t->jumlah : 0,
            ];
        }

        $sumIni = array_sum(array_column($cols, 'ini'));
        $sumLalu = array_sum(array_column($cols, 'lalu'));
        $sumPengambilan = array_sum(array_column($cols, 'pemakaian'));
        $sumJumlah = array_sum(array_column($cols, 'jumlah'));
        $sumPpn = $area['kena_ppn'] ? round($sumJumlah * $area['persen_ppn'] / 100, 2) : 0;
        $sumTotal = $sumJumlah + $sumPpn;

        $lokasiList = collect($area['rows'])
            ->where('titik_meter.status', 'aktif')
            ->pluck('titik_meter.lokasi_flow_meter')
            ->filter()
            ->unique()
            ->values()
            ->all();
      ?>

      <table class="infobox">
        <tr>
          <td>NAMA : {{ $area['area']->nama }}</td>
          <td>ALAMAT : {{ $area['area']->alamat ?: '-' }}</td>
        </tr>
        <tr>
          <td>LOKASI FLOW METER : {{ $lokasiList ? implode(', ', $lokasiList) : '-' }}</td>
          <td></td>
        </tr>
      </table>

      <table class="vbox">
        <tr>
          <td class="v-title" style="width: 26%;">KETERANGAN</td>
          @foreach($cols as $col)
            <td class="v-title">{{ $col['nama'] }}</td>
          @endforeach
          <td class="v-title">Jumlah</td>
        </tr>
        <tr>
          <td>Bulan ini ( a )</td>
          @foreach($cols as $col)
            <td class="r">{{ $col['ini'] }}</td>
          @endforeach
          <td class="r bold">{{ $sumIni }}</td>
        </tr>
        <tr>
          <td>Bulan lalu ( b )</td>
          @foreach($cols as $col)
            <td class="r">{{ $col['lalu'] }}</td>
          @endforeach
          <td class="r bold">{{ $sumLalu }}</td>
        </tr>
        <tr>
          <td>Jumlah Pengambilan ( c = a - b )</td>
          @foreach($cols as $col)
            <td class="r">{{ $col['ini'] - $col['lalu'] }}</td>
          @endforeach
          <td class="r bold">{{ $sumIni - $sumLalu }}</td>
        </tr>
        <tr>
          <td>Meter Faktor ( d )</td>
          @foreach($cols as $col)
            <td class="r">{{ number_format($col['faktor'], 0, ',', '.') }}</td>
          @endforeach
          <td class="r bold"></td>
        </tr>
        <tr>
          <td>Jumlah Pengambilan ( e = c x d )</td>
          @foreach($cols as $col)
            <td class="r">{{ $col['pemakaian'] }}</td>
          @endforeach
          <td class="r bold">{{ $sumPengambilan }}</td>
        </tr>
        <tr>
          <td>Tarif / M3</td>
          @foreach($cols as $col)
            <td class="r">Rp {{ number_format($col['tarif'], 2, ',', '.') }}</td>
          @endforeach
          <td class="r bold"></td>
        </tr>
        <tr>
          <td class="bold">Jumlah (Rp)</td>
          @foreach($cols as $col)
            <td class="r bold">Rp {{ number_format($col['jumlah'], 0, ',', '.') }}</td>
          @endforeach
          <td class="r bold">Rp {{ number_format($sumJumlah, 0, ',', '.') }}</td>
        </tr>
        @if($area['kena_ppn'])
          <tr>
            <td>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td>
            @foreach($cols as $col)
              <td class="r"></td>
            @endforeach
            <td class="r">Rp {{ number_format($sumPpn, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td class="bold">Jumlah (Rp)</td>
            @foreach($cols as $col)
              <td class="r"></td>
            @endforeach
            <td class="r bold">Rp {{ number_format($sumTotal, 0, ',', '.') }}</td>
          </tr>
        @endif
      </table>

      <?php $adaFotoKol = $area['rows']->contains(function ($r) { return $r['tagihan'] && $r['tagihan']->fotos->count() > 0; }); ?>
      @if($adaFotoKol)
        <p style="margin-top: 14px; font-weight: bold;">Foto Meter :</p>
        <table class="grid">
          <thead>
            <tr><th>No</th><th>Nama Titik Meter</th><th style="width: 60%;">Foto</th></tr>
          </thead>
          <tbody>
            @foreach($area['rows'] as $i => $row)
              @continue(!$row['tagihan'])
              <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $row['titik_meter']->nama }}</td>
                <td class="foto">
                  @if($row['tagihan']->fotos->count())
                    @foreach($row['tagihan']->fotos as $foto)
                      @if($foto->file_path && is_file($foto->file_path))
                        <img src="{{ $foto->file_path }}" alt="Foto meter" style="width: 34mm; margin: 2px;">
                      @endif
                    @endforeach
                  @else
                    &mdash;
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif

    @else

      <table class="grid">
        <thead>
          <tr>
            <th style="width: 8%;">No. Urut</th>
            <th style="width: 26%;">Nama Titik Meter</th>
            <th colspan="2" style="width: 22%;">COUNTER M3</th>
            <th style="width: 12%;">Jumlah Pengambilan</th>
            <th style="width: 14%;">Tarif Rp/M3</th>
            <th style="width: 18%;">Jumlah (Rp)</th>
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
        </thead>
        <tbody>
          @foreach($area['rows'] as $i => $row)
            @continue(!$row['tagihan'])
            <tr>
              <td class="c">{{ $i + 1 }}</td>
              <td>{{ $row['titik_meter']->nama }}</td>
              <td class="r">{{ (int) round((float) $row['tagihan']->meter_ini) }}</td>
              <td class="r">{{ (int) round((float) $row['tagihan']->meter_lalu) }}</td>
              <td class="r">{{ (int) round((float) $row['tagihan']->pemakaian) }}</td>
              <td class="r">{{ number_format($row['tagihan']->tarif, 2, ',', '.') }}</td>
              <td class="r bold">{{ number_format($row['tagihan']->jumlah, 0, ',', '.') }}</td>
            </tr>
          @endforeach
          <tr class="subtotal-bg bold">
            <td colspan="5">Jumlah</td>
            <td></td>
            <td class="r">Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</td>
          </tr>
          @if($area['kena_ppn'])
            <tr class="bold">
              <td colspan="5">PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td>
              <td></td>
              <td class="r">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td>
            </tr>
            <tr class="subtotal-bg bold">
              <td colspan="5">Jumlah</td>
              <td></td>
              <td class="r">Rp {{ number_format($area['total'], 0, ',', '.') }}</td>
            </tr>
          @endif
        </tbody>
      </table>

      <?php $adaFoto = $area['rows']->contains(function ($r) { return $r['tagihan'] && $r['tagihan']->fotos->count() > 0; }); ?>
      @if($adaFoto)
        <p style="margin-top: 14px; font-weight: bold;">Foto Meter :</p>
        <table class="grid">
          <thead>
            <tr><th>No</th><th>Nama Titik Meter</th><th style="width: 60%;">Foto</th></tr>
          </thead>
          <tbody>
            @foreach($area['rows'] as $i => $row)
              @continue(!$row['tagihan'])
              <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $row['titik_meter']->nama }}</td>
                <td class="foto">
                  @if($row['tagihan']->fotos->count())
                    @foreach($row['tagihan']->fotos as $foto)
                      @if($foto->file_path && is_file($foto->file_path))
                        <img src="{{ $foto->file_path }}" alt="Foto meter" style="width: 34mm; margin: 2px;">
                      @endif
                    @endforeach
                  @else
                    &mdash;
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif

    @endif
  @endforeach

  <div class="grand-total">
    GRAND TOTAL : Rp {{ number_format($grandTotal ?? 0, 0, ',', '.') }}
    ({{ number_format($grandPemakaian ?? 0, 0, ',', '.') }} m3)
  </div>

  <?php
    $ttd = collect($penandatangan)->values();
    $ttdKiri = $ttd[0] ?? null;
    $ttdKanan = $ttd[1] ?? null;
    $tempatTtd = $ttdKiri ? $ttdKiri->tempat : '';
    $tanggalTtd = ($ttdKiri && $ttdKiri->tanggal_cetak)
        ? \Carbon\Carbon::parse($ttdKiri->tanggal_cetak)->locale('id')->translatedFormat('d F Y')
        : now()->locale('id')->translatedFormat('d F Y');
  ?>
  @if($ttdKiri || $ttdKanan)
    <div class="sign">
      <table class="signature-table">
        <tr>
          <td class="sign-title">Mengetahui / Menyetujui</td>
          <td>{{ ($tempatTtd ? $tempatTtd . ', ' : '') . $tanggalTtd }}</td>
        </tr>
        <tr>
          <td class="sign-jabatan">{{ $ttdKiri ? $ttdKiri->jabatan : '' }}</td>
          <td class="sign-jabatan">{{ $ttdKanan ? $ttdKanan->jabatan : '' }}</td>
        </tr>
        <tr>
          <td class="signature-space">&nbsp;</td>
          <td class="signature-space">&nbsp;</td>
        </tr>
        <tr>
          <td class="sign-nama">{{ $ttdKiri ? ($ttdKiri->nama ?: '.....................................' ) : '' }}</td>
          <td class="sign-nama">{{ $ttdKanan ? ($ttdKanan->nama ?: '.....................................' ) : '' }}</td>
        </tr>
      </table>
    </div>
  @endif

</body>
</html>