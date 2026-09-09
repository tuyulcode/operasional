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
    .doc-header .logo-cell img { width: 135px; height: auto; display: block; }
    .doc-header .org-cell { text-align: left; vertical-align: middle; }
    .doc-header .org-name { font-size: 15px; font-weight: bold; }
    .doc-header .org-sub { font-size: 11px; margin-top: 2px; }

    .area-title { text-align: center; font-weight: bold; font-size: 13px; margin: 10px 0 2px; }
    .area-bulan { text-align: center; font-size: 11px; margin-bottom: 6px; }

    table.grid { width: 100%; border-collapse: collapse; }
    table.grid th, table.grid td { border: 1px solid #333; padding: 3px 5px; font-size: 10px; }
    table.grid thead th { background: #e9ecf5; text-align: center; font-weight: bold; }
    .r { text-align: right; }
    .c { text-align: center; }
    .bold { font-weight: bold; }
    .subtotal-bg { background: #eef5ec; }

    table.vbox { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.vbox td { border: 1px solid #333; padding: 4px 6px; font-size: 10px; }
    table.vbox .v-title { text-align: center; font-weight: bold; background: #e9ecf5; }

    table.infobox { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.infobox td { border: 1px solid #333; padding: 4px 6px; font-size: 10px; }

    td.m3 { width: 8%; text-align: center; background: #f7f7f7; }

    .foto-section { margin-top: 10px; page-break-inside: avoid; }
    .foto-title { font-weight: bold; font-size: 10px; margin-bottom: 4px; }
    table.foto-group { width: 100%; border-collapse: collapse; margin-bottom: 4px; table-layout: fixed; page-break-inside: avoid; }
    table.foto-group td { border: 1px solid #333; padding: 3px 4px; font-size: 9px; word-wrap: break-word; overflow-wrap: break-word; }
    table.foto-group td.foto-empty-slot { border: none; background: transparent; }
    table.foto-group td.foto-label-cell { background: #f7f7f7; font-weight: bold; text-align: left; }
    table.foto-group td.foto-cell { text-align: center; vertical-align: middle; height: 30mm; }
    table.foto-group td.foto-cell img { max-width: 40mm; max-height: 27mm; width: auto; height: auto; display: block; margin: 0 auto; border: 1px solid #888; }
    .foto-empty-row { color: #888; text-align: center; font-style: italic; }

    .sign { margin-top: 16px; page-break-inside: avoid; }
    .signature-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .signature-table td { width: 50%; vertical-align: top; text-align: center; padding: 0; }
    .sign-title { font-weight: bold; text-align: center; font-size: 10px; }
    .sign-date { text-align: center; font-size: 10px; }
    .sign-jabatan { font-weight: bold; text-align: center; font-size: 10px; line-height: 1.3; }
    .sign-nama { font-weight: bold; text-align: center; font-size: 10px; }
    .signature-space { height: 48px; }
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
          <div class="org-name">PT PLN NUSANTARA POWER</div>
          <div class="org-sub">UNIT PEMBANGKITAN PAITON</div>
        </td>
      </tr>
    </table>
  </div>

  @foreach($data as $i => $area)
    <?php
      $jmlTitik = $area['jml_titik'] ?? $area['rows']->count();
      $format = $area['area']->format_rekap
        ?: ($jmlTitik === 1 ? 'standar' : ($jmlTitik <= 3 ? 'multikolom' : 'list'));

      $namaArea = str_replace('PT.', 'PT', $area['area']->nama);
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
        $lokasiFm = $row1['titik_meter']->lokasi_flow_meter ?: $row1['titik_meter']->nama;
      ?>

      <table class="infobox">
        <tr>
          <td>NAMA : {{ $namaArea }}</td>
          <td>LOKASI FLOW METER : {{ $lokasiFm }}</td>
        </tr>
        <tr>
          <td>ALAMAT : {{ $area['area']->alamat ?: '-' }}</td>
          <td></td>
        </tr>
      </table>

      <table class="vbox">
        <tr><td colspan="3" class="v-title">PERHITUNGAN PEMAKAIAN</td></tr>
        <tr><td>Bulan ini ( a )</td><td class="c">{{ $ini }}</td><td class="m3">M&sup3;</td></tr>
        <tr><td>Bulan lalu ( b )</td><td class="c">{{ $lalu }}</td><td class="m3">M&sup3;</td></tr>
        <tr><td>Jumlah Pengambilan ( c = a - b )</td><td class="c">{{ $ini - $lalu }}</td><td class="m3">M&sup3;</td></tr>
        <tr><td>Meter Faktor ( d )</td><td class="c">{{ $tg ? number_format($faktor, 0, ',', '.') : '0' }}</td><td class="m3"></td></tr>
        <tr><td>Jumlah Pengambilan ( e = c x d )</td><td class="c">{{ $tg ? (int) round((float) $tg->pemakaian) : 0 }}</td><td class="m3">M&sup3;</td></tr>
        <tr><td>Tarif / M3</td><td class="c">Rp {{ number_format($tg->tarif ?? 0, 2, ',', '.') }}</td><td class="m3"></td></tr>
        <tr><td class="bold">Subtotal (Rp)</td><td class="c bold">Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</td><td class="m3"></td></tr>
        @if($area['kena_ppn'])
          <tr><td>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td><td class="c">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td><td class="m3"></td></tr>
          <tr><td class="bold">Total (Rp)</td><td class="c bold">Rp {{ number_format($area['total'], 0, ',', '.') }}</td><td class="m3"></td></tr>
        @endif
      </table>

      @if($tg && $fotos->count())
        <div class="foto-section">
          <div class="foto-title">Foto Meter :</div>
          <table class="foto-group">
            <tr>
              @foreach($fotos as $foto)
                <td class="foto-cell" style="width: {{ round(100 / max($fotos->count(), 1), 2) }}%;">
                  @if($foto->file_path && is_file($foto->file_path))
                    <img src="{{ $foto->file_path }}" alt="Foto meter">
                  @else
                    <em style="color: #888;">file tidak ditemukan</em>
                  @endif
                </td>
              @endforeach
            </tr>
          </table>
        </div>
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
        $sumPpn = $area['ppn'];
        $sumTotal = $area['total'];

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
          <td>NAMA : {{ $namaArea }}</td>
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
            <td class="c">{{ $col['ini'] }}</td>
          @endforeach
          <td class="c bold">{{ $sumIni }}</td>
        </tr>
        <tr>
          <td>Bulan lalu ( b )</td>
          @foreach($cols as $col)
            <td class="c">{{ $col['lalu'] }}</td>
          @endforeach
          <td class="c bold">{{ $sumLalu }}</td>
        </tr>
        <tr>
          <td>Jumlah Pengambilan ( c = a - b )</td>
          @foreach($cols as $col)
            <td class="c">{{ $col['ini'] - $col['lalu'] }}</td>
          @endforeach
          <td class="c bold">{{ $sumIni - $sumLalu }}</td>
        </tr>
        <tr>
          <td>Meter Faktor ( d )</td>
          @foreach($cols as $col)
            <td class="c">{{ number_format($col['faktor'], 0, ',', '.') }}</td>
          @endforeach
          <td class="c bold"></td>
        </tr>
        <tr>
          <td>Jumlah Pengambilan ( e = c x d )</td>
          @foreach($cols as $col)
            <td class="c">{{ $col['pemakaian'] }}</td>
          @endforeach
          <td class="c bold">{{ $sumPengambilan }}</td>
        </tr>
        <tr>
          <td>Tarif / M3</td>
          @foreach($cols as $col)
            <td class="c">Rp {{ number_format($col['tarif'], 2, ',', '.') }}</td>
          @endforeach
          <td class="c bold"></td>
        </tr>
        <tr>
          <td class="bold">Subtotal (Rp)</td>
          @foreach($cols as $col)
            <td class="c bold">Rp {{ number_format($col['jumlah'], 0, ',', '.') }}</td>
          @endforeach
          <td class="c bold">Rp {{ number_format($sumJumlah, 0, ',', '.') }}</td>
        </tr>
        @if($area['kena_ppn'])
          <tr>
            <td>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td>
            @foreach($cols as $col)
              <td class="c"></td>
            @endforeach
            <td class="c">Rp {{ number_format($sumPpn, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td class="bold">Total (Rp)</td>
            @foreach($cols as $col)
              <td class="c"></td>
            @endforeach
            <td class="c bold">Rp {{ number_format($sumTotal, 0, ',', '.') }}</td>
          </tr>
        @endif
      </table>

      <?php
        $barisFotoKol = $area['rows']->filter(fn ($r) => $r['tagihan'])->values();
        $adaFotoKol = $barisFotoKol->contains(fn ($r) => $r['tagihan']->fotos->count() > 0);
        $jmlKolomFotoKol = max($barisFotoKol->count(), 1);
        $lebarKolomFotoKol = round(100 / $jmlKolomFotoKol, 2);
      ?>
      @if($adaFotoKol)
        <div class="foto-section">
          <div class="foto-title">Foto Meter :</div>
          <table class="foto-group">
            <tr>
              @foreach($barisFotoKol as $idx => $row)
                <td class="foto-label-cell" style="width: {{ $lebarKolomFotoKol }}%;">{{ $idx + 1 }}. {{ $row['titik_meter']->nama }}</td>
              @endforeach
            </tr>
            <tr>
              @foreach($barisFotoKol as $row)
                <?php $jmlFoto = $row['tagihan']->fotos->count(); ?>
                <td class="foto-cell" style="width: {{ $lebarKolomFotoKol }}%;">
                  @if($jmlFoto)
                    @foreach($row['tagihan']->fotos as $foto)
                      @if($foto->file_path && is_file($foto->file_path))
                        <img src="{{ $foto->file_path }}" alt="Foto meter">
                      @else
                        <em style="color: #888;">file tidak ditemukan</em>
                      @endif
                    @endforeach
                  @else
                    <span class="foto-empty-row">&mdash; tidak ada foto &mdash;</span>
                  @endif
                </td>
              @endforeach
            </tr>
          </table>
        </div>
      @endif

    @else

      <table class="grid">
        <thead>
          <tr>
            <th rowspan="2" style="width: 8%;">No</th>
            <th rowspan="2" style="width: 26%;">Nama Titik Meter</th>
            <th colspan="2" style="width: 22%;">COUNTER M3</th>
            <th rowspan="2" style="width: 12%;">Jumlah Pengambilan</th>
            <th rowspan="2" style="width: 14%;">Tarif Rp/M3</th>
            <th rowspan="2" style="width: 18%;">Jumlah (Rp)</th>
          </tr>
          <tr>
            <th>Bulan Ini</th>
            <th>Bulan Lalu</th>
          </tr>
        </thead>
        <tbody>
          <?php $noUrut = 0; ?>
          @foreach($area['rows'] as $row)
            @if(($row['titik_meter']->status ?? 'aktif') !== 'aktif')
              @continue
            @endif
            <?php $tg = $row['tagihan'] ?? null; $noUrut++; ?>
            <tr>
              <td class="c">{{ $noUrut }}</td>
              <td>{{ $row['titik_meter']->nama }}</td>
              <td class="c">{{ $tg ? (int) round((float) $tg->meter_ini) : '' }}</td>
              <td class="c">{{ $tg ? (int) round((float) $tg->meter_lalu) : '' }}</td>
              <td class="c">{{ $tg ? (int) round((float) $tg->pemakaian) : '' }}</td>
              <td class="c">{{ $tg ? number_format($tg->tarif, 2, ',', '.') : number_format($row['titik_meter']->tarif_harga, 2, ',', '.') }}</td>
              <td class="c bold">{{ $tg ? number_format($tg->jumlah, 0, ',', '.') : '' }}</td>
            </tr>
          @endforeach
          <tr class="subtotal-bg bold">
            <td colspan="5">Subtotal</td>
            <td></td>
            <td class="c">Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</td>
          </tr>
          @if($area['kena_ppn'])
            <tr class="bold">
              <td colspan="5">PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td>
              <td></td>
              <td class="c">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td>
            </tr>
            <tr class="subtotal-bg bold">
              <td colspan="5">Total</td>
              <td></td>
              <td class="c">Rp {{ number_format($area['total'], 0, ',', '.') }}</td>
            </tr>
          @endif
        </tbody>
      </table>

      <?php
        $barisFoto = $area['rows']->filter(fn ($r) => $r['tagihan'])->values();
        $adaFoto = $barisFoto->contains(fn ($r) => $r['tagihan']->fotos->count() > 0);
        $fotoChunksList = $barisFoto->chunk(4)->values();
      ?>
      @if($adaFoto)
        <div class="foto-section">
          <div class="foto-title">Foto Meter :</div>
          @foreach($fotoChunksList as $chunkIdx => $chunk)
            <?php $chunkArr = $chunk->values(); ?>
            <table class="foto-group">
              <tr>
                @for($idx = 0; $idx < 4; $idx++)
                  @if(isset($chunkArr[$idx]))
                    <?php
                      $row = $chunkArr[$idx];
                      $titikIdx = $area['rows']->search(fn ($item) => ($item['titik_meter']->id ?? null) === ($row['titik_meter']->id ?? null));
                      $noLabel = $titikIdx !== false ? ($titikIdx + 1) : ($chunkIdx * 4 + $idx + 1);
                    ?>
                    <td class="foto-label-cell" style="width: 25%;">{{ $noLabel }}. {{ $row['titik_meter']->nama }}</td>
                  @else
                    <td class="foto-empty-slot" style="width: 25%;"></td>
                  @endif
                @endfor
              </tr>
              <tr>
                @for($idx = 0; $idx < 4; $idx++)
                  @if(isset($chunkArr[$idx]))
                    <?php
                      $row = $chunkArr[$idx];
                      $jmlFoto = $row['tagihan']->fotos->count();
                    ?>
                    <td class="foto-cell" style="width: 25%;">
                      @if($jmlFoto)
                        @foreach($row['tagihan']->fotos as $foto)
                          @if($foto->file_path && is_file($foto->file_path))
                            <img src="{{ $foto->file_path }}" alt="Foto meter">
                          @else
                            <em style="color: #888;">file tidak ditemukan</em>
                          @endif
                        @endforeach
                      @else
                        <span class="foto-empty-row">&mdash; tidak ada foto &mdash;</span>
                      @endif
                    </td>
                  @else
                    <td class="foto-empty-slot" style="width: 25%;"></td>
                  @endif
                @endfor
              </tr>
            </table>
          @endforeach
        </div>
      @endif

    @endif
  @endforeach

  <?php
    $ttd = collect($penandatangan)->values();
    $ttdKiri = $ttd[0] ?? null;
    $ttdKanan = $ttd[1] ?? null;
    $tempatTtd = $ttdKiri && $ttdKiri->tempat ? $ttdKiri->tempat : ($ttdKanan && $ttdKanan->tempat ? $ttdKanan->tempat : 'paiton');
    $tanggalTtd = now()->locale('id')->translatedFormat('d F Y');
    $dateLabel = ($tempatTtd ? $tempatTtd . ', ' : '') . $tanggalTtd;
  ?>
  @if($ttdKiri || $ttdKanan)
    <div class="sign">
      <table class="signature-table">
        <tr>
          <td class="sign-title">Menyetujui</td>
          <td class="sign-date">{{ $dateLabel }}</td>
        </tr>
        <tr style="height: 6px;"><td colspan="2"></td></tr>
        <tr>
          <td class="sign-jabatan" style="vertical-align: top;">
            {{ $ttdKiri ? $ttdKiri->jabatan : '' }}
          </td>
          <td class="sign-jabatan" style="vertical-align: top;">
            <div>Mengusulkan</div>
            <div style="margin-top: 2px;">{{ $ttdKanan ? $ttdKanan->jabatan : '' }}</div>
          </td>
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