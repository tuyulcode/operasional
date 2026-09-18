<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekapan Tagihan Air</title>
  <style>
    @page { margin: 22px 20px; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #111; }

    .doc-header table { width: 100%; border-collapse: collapse; }
    .doc-header .logo-cell { width: 22%; text-align: left; vertical-align: middle; }
    .doc-header .logo-cell img { width: 135px; height: auto; display: block; }
    .doc-header .org-cell { text-align: left; vertical-align: middle; }
    .doc-header .org-name { font-size: 15px; font-weight: bold; }
    .doc-header .org-sub { font-size: 11px; margin-top: 2px; }

    .area-title { text-align: center; font-weight: bold; font-size: 14px; margin: 12px 0 2px; }
    .area-lokasi { text-align: center; font-size: 11px; font-weight: bold; margin-bottom: 1px; }
    .area-bulan { text-align: center; font-weight: bold; font-size: 11px; margin-bottom: 8px; }

    table.grid { width: 100%; border-collapse: collapse; }
    table.grid th, table.grid td { border: 1px solid #333; padding: 3px 5px; font-size: 10px; }
    table.grid thead th { background: #d9e2f3; text-align: center; font-weight: bold; }
    .r { text-align: right; }
    .c { text-align: center; }
    .bold { font-weight: bold; }
    .row-total { background: #e2efda; font-weight: bold; }
    .row-ppn { background: #fff2cc; }

    .foto-section { margin-top: 10px; page-break-inside: avoid; }
    .foto-title { font-weight: bold; font-size: 10px; margin-bottom: 4px; }
    table.foto-group { border-collapse: collapse; margin: 0 auto 4px; table-layout: fixed; page-break-inside: avoid; }
    table.foto-group td { border: 1px solid #333; padding: 3px 4px; font-size: 9px; word-wrap: break-word; overflow-wrap: break-word; }
    table.foto-group td.foto-label-cell { background: #d9e2f3; font-weight: bold; text-align: center; }
    table.foto-group td.foto-cell { text-align: center; vertical-align: middle; height: 30mm; }
    table.foto-group td.foto-cell img { max-width: 38mm; max-height: 27mm; width: auto; height: auto; display: block; margin: 0 auto; border: 1px solid #888; }
    .foto-empty-row { color: #888; text-align: center; font-style: italic; }

    .sign { margin-top: 40px; page-break-inside: avoid; }
    .signature-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .signature-table td { width: 50%; vertical-align: top; text-align: center; padding: 0; }
    .sign-title { font-weight: bold; text-align: center; font-size: 10px; }
    .sign-date { text-align: center; font-size: 10px; }
    .sign-jabatan { font-weight: bold; text-align: center; font-size: 10px; line-height: 1.3; }
    .sign-nama { font-weight: bold; text-align: center; font-size: 10px; text-decoration: underline; }
    .signature-space { height: 74px; }
  </style>
</head>
<body>

  @foreach($data as $i => $area)
    <?php
      $namaArea = str_replace('PT.', 'PT', $area['area']->nama);
      $alamatArea = $area['area']->alamat ?: null;

      // Semua area memakai format list yang sama (mengikuti sheet PUNCAK 1).
      $rows = $area['rows']->filter(fn ($r) => ($r['titik_meter']->status ?? 'aktif') === 'aktif')->values();

      // Total hanya menghitung baris AKTIF saja, konsisten dengan baris
      // yang ditampilkan di tabel (baris nonaktif tidak tampil & tidak dihitung).
      $subtotal = $rows->sum(fn ($r) => max(0, (float) (($r['tagihan']->jumlah ?? 0) - ($r['tagihan']->ppn_nominal ?? 0))));
      $ppnValue = $rows->sum(fn ($r) => (float) ($r['tagihan']->ppn_nominal ?? 0));
      $total = $subtotal + $ppnValue;
      $firstPpn = $rows->first(fn ($r) => $r['tagihan'] && (float) $r['tagihan']->ppn_persentase > 0);
      $ppnPersen = $firstPpn ? (float) $firstPpn['tagihan']->ppn_persentase : 0;
    ?>

    @if($i > 0)
      <div style="page-break-before: always;"></div>
    @endif

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

    <div class="area-title">Rekap Biaya Pemakaian Air</div>
    <div class="area-lokasi"><b>Nama Pengguna</b> : {{ $namaArea }}</div>
    @if($alamatArea)
      <div class="area-lokasi"><b>Lokasi Flow Meter</b> : {{ $alamatArea }}</div>
    @endif
    <div class="area-bulan">Bulan : {{ $periodeLabel }}</div>

    <table class="grid">
      <thead>
        <tr>
          <th rowspan="2" style="width: 7%;">No.<br>Urut</th>
          <th rowspan="2" style="width: 27%;">Nama Titik Meter</th>
          <th colspan="2" style="width: 24%;">COUNTER&nbsp;&nbsp;M<sup>3</sup></th>
          <th rowspan="2" style="width: 13%;">Jumlah<br>Pengambilan</th>
          <th rowspan="2" style="width: 12%;">TARIF<br>Rp / M<sup>3</sup></th>
          <th rowspan="2" style="width: 17%;">JUMLAH<br>Rp</th>
        </tr>
        <tr>
          <th>Bulan Ini</th>
          <th>Bulan Lalu</th>
        </tr>
      </thead>
      <tbody>

        <?php $noUrut = 0; ?>
        @foreach($rows as $row)
          <?php
            $tg = $row['tagihan'] ?? null;
            $noUrut++;
            $jumlah = $tg ? (float) $tg->jumlah : 0;
          ?>
          <tr>
            <td class="c">{{ $noUrut }}</td>
            <td>{{ $row['titik_meter']->nama }}</td>
            <td class="c">{{ $tg ? (int) round((float) $tg->meter_ini) : '-' }}</td>
            <td class="c">{{ $tg ? (int) round((float) $tg->meter_lalu) : '-' }}</td>
            <td class="c">{{ $tg ? (int) round((float) $tg->pemakaian) : '-' }}</td>
            <td class="c">{{ number_format($tg ? (float) $tg->tarif : (float) ($row['titik_meter']->tarif_harga ?? 0), 0, ',', '.') }}</td>
            <td class="r">{{ $jumlah > 0 ? number_format($jumlah, 0, ',', '.') : '-' }}</td>
          </tr>
        @endforeach

        <tr class="row-total">
          <td colspan="6" class="c">Jumlah Total</td>
          <td class="r">{{ number_format($subtotal, 0, ',', '.') }}</td>
        </tr>

        @if($area['kena_ppn'])
          <tr class="row-ppn">
            <td colspan="6" class="c">PPN {{ number_format($ppnPersen, 0, ',', '.') }}%</td>
            <td class="r">{{ number_format($ppnValue, 0, ',', '.') }}</td>
          </tr>
          <tr class="row-total">
            <td colspan="6" class="c">Total</td>
            <td class="r">{{ number_format($total, 0, ',', '.') }}</td>
          </tr>
        @endif
      </tbody>
    </table>

    <?php
      $barisFoto = $rows->filter(fn ($r) => $r['tagihan'] && $r['tagihan']->fotos->count() > 0)->values();
      $fotoChunksList = $barisFoto->chunk(4)->values();
    ?>
    @if($barisFoto->count())
      <div class="foto-section">
        <div class="foto-title">Foto Meter :</div>
        @foreach($fotoChunksList as $chunkIdx => $chunk)
          <?php
            $chunkArr = $chunk->values();
            $jmlSlot = $chunkArr->count();
            // 1-3 foto: tabel dipersempit lalu dipusatkan. 4 foto: penuh selebar tabel.
            $lebarTabel = [1 => 30, 2 => 55, 3 => 78][$jmlSlot] ?? 100;
            $lebarSlot = round(100 / $jmlSlot, 2);
          ?>
          <table class="foto-group" style="width: {{ $lebarTabel }}%;">
            <tr>
              @foreach($chunkArr as $idx => $row)
                <?php
                  $titikIdx = $rows->search(fn ($item) => ($item['titik_meter']->id ?? null) === ($row['titik_meter']->id ?? null));
                  $noLabel = $titikIdx !== false ? ($titikIdx + 1) : ($chunkIdx * 4 + $idx + 1);
                ?>
                <td class="foto-label-cell" style="width: {{ $lebarSlot }}%;">{{ $noLabel }}. {{ $row['titik_meter']->nama }}</td>
              @endforeach
            </tr>
            <tr>
              @foreach($chunkArr as $row)
                <td class="foto-cell" style="width: {{ $lebarSlot }}%;">
                  @foreach($row['tagihan']->fotos as $foto)
                    @if($foto->file_path && is_file($foto->file_path))
                      <img src="{{ $foto->file_path }}" alt="Foto meter">
                    @else
                      <em style="color: #888;">file tidak ditemukan</em>
                    @endif
                  @endforeach
                </td>
              @endforeach
            </tr>
          </table>
        @endforeach
      </div>
    @endif
  @endforeach

  <?php
    $ttd = collect($penandatangan)->values();
    $ttdKiri = $ttd[0] ?? null;
    $ttdKanan = $ttd[1] ?? null;
    $tempatTtd = $ttdKiri && $ttdKiri->tempat ? $ttdKiri->tempat : ($ttdKanan && $ttdKanan->tempat ? $ttdKanan->tempat : 'Paiton');
    $tanggalTtd = now()->locale('id')->translatedFormat('d F Y');
    $dateLabel = ($tempatTtd ? $tempatTtd . ', ' : '') . $tanggalTtd;
  ?>
  @if($ttdKiri || $ttdKanan)
    <div class="sign">
      <table class="signature-table">
        <tr>
          <td></td>
          <td class="sign-date">{{ $dateLabel }}</td>
        </tr>
        <tr style="height: 8px;"><td colspan="2"></td></tr>
        <tr>
          <td class="sign-title">Menyetujui,</td>
          <td class="sign-title">{{ $ttdKanan ? 'Mengusulkan,' : '' }}</td>
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