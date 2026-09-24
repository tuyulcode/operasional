@php
  // Warna aksen per grup, urut: A. Roda Empat, B. Roda Tiga, C. Roda Dua
  // (samain dengan tabel Rekapan)
  $groupColors = [
    'A. Roda Empat' => 'bdd7ee', // biru muda
    'B. Roda Tiga'  => 'd9d2e9', // ungu muda
    'C. Roda Dua'   => 'c6e0b4', // hijau muda
  ];
  $grandColor = 'ffc000'; // oranye - baris "Jumlah Total"

  // Semua jenis kendaraan SELALU ditampilkan, walaupun tidak ada datanya di
  // periode ini - yang kosong tetap muncul dengan isi strip.
  $byLabel = collect($groups)->keyBy('label');

  $displayGroups = collect(array_keys($groupColors))->map(function ($label) use ($byLabel) {
    return $byLabel->get($label) ?? [
      'label'    => $label,
      'sections' => [],
      'total'    => ['liter' => 0, 'rp' => 0],
    ];
  })->values();

  $displayGrandTotal = ['liter' => 0, 'rp' => 0];
  foreach ($displayGroups as $g) {
    $displayGrandTotal['liter'] += $g['total']['liter'];
    $displayGrandTotal['rp'] += $g['total']['rp'];
  }
@endphp

<div class="ptj-report-block" style="width:75%; margin:15px auto 10px auto;">
  <table style="border-collapse: collapse; width: 100%; font-size: 12px; margin-bottom:10px;" border="1" cellpadding="4" cellspacing="0">
    <colgroup>
      <col style="width:8%">
      <col style="width:42%">
      <col style="width:20%">
      <col style="width:30%">
    </colgroup>
    <tbody>
      @foreach($displayGroups as $index => $group)
        @php
          $groupColor = $groupColors[$group['label']] ?? 'ffffff';
          // "A. Roda Empat" -> "A", dipakai buat label "Subtotal A"
          $hurufGroup = substr($group['label'], 0, 1);
        @endphp

        {{-- Header kolom (No. / Nomor Kendaraan / Liter / Rp.) cuma muncul
             sekali, DI ATAS banner grup pertama (Roda Empat). --}}
        @if($index === 0)
          <tr style="font-weight:bold; text-align:center;">
            <td style="padding:8px 4px;">No.</td>
            <td style="padding:8px 4px;">Plat Nomor Kendaraan</td>
            <td style="padding:8px 4px;">Liter</td>
            <td style="padding:8px 4px;">Rp.</td>
          </tr>
        @endif

        {{-- Banner jenis kendaraan, di-highlight sesuai warna grupnya --}}
        <tr style="background:#{{ $groupColor }}; font-weight:bold;">
          <td colspan="4" style="text-align:left; padding:8px 12px;">{{ $group['label'] }}</td>
        </tr>

        @forelse($group['sections'] as $section)
          @if($section['label'])
            <tr style="font-weight:bold;">
              <td colspan="4" style="text-align:center;">{{ $section['label'] }}</td>
            </tr>
          @endif

          @foreach($section['rows'] as $row)
            <tr style="text-align:center;">
              <td>{{ $row['no'] }}</td>
              <td>{{ $row['plat_nomor'] }}</td>
              <td>{{ $row['liter'] ? number_format($row['liter'], 2, ',', '.') : '-' }}</td>
              <td>{{ $row['rp'] ? number_format($row['rp'], 0, ',', '.') : '-' }}</td>
            </tr>
          @endforeach
        @empty
          {{-- Jenis kendaraan ini tidak punya data di periode ini - tetap
               ditampilkan, isinya strip semua. --}}
          <tr style="text-align:center;">
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
          </tr>
        @endforelse

        <tr style="background:#{{ $groupColor }}; font-weight:bold; text-align:center;">
          <td colspan="2" style="text-align:left; padding:8px 12px;">Subtotal {{ $hurufGroup }}</td>
          <td>{{ number_format($group['total']['liter'], 2, ',', '.') }}</td>
          <td>{{ number_format($group['total']['rp'], 0, ',', '.') }}</td>
        </tr>
      @endforeach

      <tr style="background:#{{ $grandColor }}; font-weight:bold; text-align:center;">
        <td colspan="2" style="text-align:left; padding:8px 12px;">Jumlah Total</td>
        <td>{{ number_format($displayGrandTotal['liter'], 2, ',', '.') }}</td>
        <td>{{ number_format($displayGrandTotal['rp'], 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
</div>