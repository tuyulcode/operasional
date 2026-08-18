<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Rekapan Tagihan Air</title>
  <style>
    @page { margin: 24px 24px; }
    body { font-family: Arial, sans-serif; font-size: 10px; color: #111; }

    .doc-header table { width: 100%; border-collapse: collapse; }
    .doc-header .logo-cell { width: 18%; text-align: left; vertical-align: middle; }
    .doc-header .logo-cell img { width: 58px; height: auto; }
    .doc-header .title-cell { text-align: center; vertical-align: middle; }
    .doc-header h1 { margin: 0; font-size: 15px; }
    .doc-header .sub { font-size: 11px; margin-top: 2px; }

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
    .foto-meter { margin-top: 10px; }
    .foto-meter img { width: 70mm; height: auto; border: 1px solid #888; }
    .foto-empty { margin-top: 10px; color: #555; }

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
        <td class="title-cell">
          <h1>TAGIHAN AIR BULANAN</h1>
          <div class="sub">{{ $periodeLabel }}</div>
        </td>
        <td class="logo-cell"></td>
      </tr>
    </table>
  </div>

  @foreach($data as $i => $area)
    @if($i > 0)
      <div style="page-break-before: always;"></div>
    @endif

    <?php $isVertikal = ($area['jml_titik'] ?? $area['rows']->count()) === 1; ?>

    @if($isVertikal)
      <?php
        $row1 = $area['rows']->first();
        $tg = $row1['tagihan'] ?? null;
        $ini = $tg ? (int) round((float) $tg->meter_ini) : 0;
        $lalu = $tg ? (int) round((float) $tg->meter_lalu) : 0;
        $faktor = $tg ? (float) $tg->meter_faktor : 0;
        $fotoTg = $tg && $tg->foto ? public_path($tg->foto) : null;
      ?>

      <table class="vbox">
        <tr><td colspan="2" class="v-title">BIAYA PEMAKAIAN AIR</td></tr>
        <tr>
          <td>Bulan : {{ $periodeLabel }}</td>
          <td class="r bold">NAMA: {{ $area['area']->nama }}</td>
        </tr>
        <tr>
          <td>ALAMAT : {{ $area['area']->alamat ?: '-' }}</td>
          <td class="r">LOKASI FLOW METER : {{ $row1['titik_meter']->nama }}</td>
        </tr>
        <tr><td colspan="2" class="v-title">PERHITUNGAN PEMAKAIAN</td></tr>
        <tr><td>Bulan ini</td><td class="r">{{ $ini }}</td></tr>
        <tr><td>Bulan lalu</td><td class="r">{{ $lalu }}</td></tr>
        <tr><td>Jumlah Pengambilan</td><td class="r">{{ $ini - $lalu }}</td></tr>
        <tr><td>Meter Faktor</td><td class="r">{{ $tg ? number_format($faktor, 0, ',', '.') : '0' }}</td></tr>
        <tr><td>Jumlah Pengambilan</td><td class="r">{{ $tg ? (int) round((float) $tg->pemakaian) : 0 }}</td></tr>
        <tr><td>Tarif / M3</td><td class="r">Rp {{ number_format($tg->tarif ?? 0, 2, ',', '.') }}</td></tr>
        <tr><td class="bold">Jumlah (Rp)</td><td class="r bold">Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</td></tr>
        @if($area['kena_ppn'])
          <tr><td>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td><td class="r">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td></tr>
          <tr><td class="bold">Jumlah (Rp)</td><td class="r bold">Rp {{ number_format($area['total'], 0, ',', '.') }}</td></tr>
        @endif
      </table>

      @if($fotoTg && is_file($fotoTg))
        <div class="foto-meter">
          <strong>FOTO METER :</strong><br>
          <img src="{{ $fotoTg }}" alt="Foto meter">
        </div>
      @elseif($tg && $tg->foto)
        <div class="foto-empty">Foto meter ada di database tetapi file tidak ditemukan.</div>
      @endif

    @else

      <div class="area-name">{{ $area['area']->nama }}</div>
      <table class="grid">
        <thead>
          <tr>
            <th style="width: 8%;">No. Urut</th>
            <th style="width: 26%;">Nama Titik Meter</th>
            <th colspan="2" style="width: 22%;">COUNTER M3</th>
            <th style="width: 12%;">Pengambilan</th>
            <th style="width: 14%;">Tarif (Rp/M3)</th>
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
            <?php $fotoPath = $row['tagihan']->foto ? public_path($row['tagihan']->foto) : null; ?>
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
            <td colspan="4">Subtotal {{ $area['area']->nama }}</td>
            <td class="r">{{ $area['total_pemakaian'] ? (int) round($area['total_pemakaian']) : '-' }}</td>
            <td></td>
            <td class="r">{{ number_format($area['subtotal'], 0, ',', '.') }}</td>
          </tr>
          @if($area['kena_ppn'])
            <tr class="bold">
              <td colspan="4">PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</td>
              <td></td>
              <td></td>
              <td class="r">{{ number_format($area['ppn'], 0, ',', '.') }}</td>
            </tr>
            <tr class="subtotal-bg bold">
              <td colspan="4">Total {{ $area['area']->nama }}</td>
              <td></td>
              <td></td>
              <td class="r">{{ number_format($area['total'], 0, ',', '.') }}</td>
            </tr>
          @endif
        </tbody>
      </table>

      <?php $adaFoto = $area['rows']->contains(function ($r) { return $r['tagihan'] && $r['tagihan']->foto; }); ?>
      @if($adaFoto)
        <p style="margin-top: 14px; font-weight: bold;">Foto Meter :</p>
        <table class="grid">
          <thead>
            <tr><th>No</th><th>Nama Titik Meter</th><th style="width: 30%;">Foto</th></tr>
          </thead>
          <tbody>
            @foreach($area['rows'] as $i => $row)
              @continue(!$row['tagihan'])
              <?php $fotoPath = $row['tagihan']->foto ? public_path($row['tagihan']->foto) : null; ?>
              <tr>
                <td class="c">{{ $i + 1 }}</td>
                <td>{{ $row['titik_meter']->nama }}</td>
                <td class="foto">
                  @if($fotoPath && is_file($fotoPath))
                    <img src="{{ $fotoPath }}" alt="Foto meter">
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
    $tanggalTtd = now()->locale('id')->translatedFormat('d F Y');
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