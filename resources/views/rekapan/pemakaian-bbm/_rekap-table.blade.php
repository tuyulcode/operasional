@php
  // Warna aksen per grup, urut: A. Roda Empat, B. Roda Tiga, C. Roda Dua
  $groupColors = ['bdd7ee', 'd9d2e9', 'c6e0b4'];
  $grandColor  = 'ffc000';
@endphp

<table style="border-collapse: collapse; width: 100%; font-size: 12px;" border="1" cellpadding="4" cellspacing="0">
  <thead>
    <tr style="background:#f2f2f2; font-weight:bold; text-align:center;">
      <th rowspan="3">No.</th>
      <th rowspan="3">No.<br>Kendaraan</th>
      <th colspan="2">Pengisian Di Paiton</th>
      <th colspan="2">Pengisian Di Luar Paiton</th>
      <th rowspan="3">Service, Oli, dll</th>
      <th rowspan="3">Jasa</th>
      <th rowspan="3">Jumlah</th>
    </tr>
    <tr style="background:#f2f2f2; font-weight:bold; text-align:center;">
      <th colspan="2">PREMIUM/SOLAR</th>
      <th colspan="2">PREMIUM/SOLAR</th>
    </tr>
    <tr style="background:#f2f2f2; font-weight:bold; text-align:center;">
      <th>Liter</th><th>Rp.</th>
      <th>Liter</th><th>Rp.</th>
    </tr>
    <tr style="background:#f2f2f2; font-weight:bold; text-align:center;">
      <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9 = 4+6+7+8</th>
    </tr>
  </thead>
  <tbody>
    @forelse($groups as $group)
      @php $groupColor = $groupColors[$loop->index % count($groupColors)]; @endphp

      <tr style="background:#{{ $groupColor }}; font-weight:bold;">
        <td colspan="9" style="text-align:left;">{{ $group['label'] }}</td>
      </tr>

      @foreach($group['sections'] as $section)
        @if($section['label'])
          <tr style="background:#{{ $groupColor }}; font-weight:bold;">
            <td colspan="9" style="text-align:center;">{{ $section['label'] }}</td>
          </tr>
        @endif

        @foreach($section['rows'] as $row)
          <tr style="text-align:center;">
            <td>{{ $row['no'] }}</td>
            <td>{{ $row['plat_nomor'] }}</td>
            <td>{{ $row['liter_paiton'] ? number_format($row['liter_paiton'], 2, ',', '.') : '-' }}</td>
            <td>{{ $row['rp_paiton'] ? number_format($row['rp_paiton'], 0, ',', '.') : '-' }}</td>
            <td>{{ $row['liter_luar_paiton'] ? number_format($row['liter_luar_paiton'], 2, ',', '.') : '-' }}</td>
            <td>{{ $row['rp_luar_paiton'] ? number_format($row['rp_luar_paiton'], 0, ',', '.') : '-' }}</td>
            <td>{{ $row['service_oli'] ? number_format($row['service_oli'], 0, ',', '.') : '-' }}</td>
            <td>{{ $row['jasa'] ? number_format($row['jasa'], 0, ',', '.') : '-' }}</td>
            <td>{{ $row['jumlah'] ? number_format($row['jumlah'], 0, ',', '.') : '-' }}</td>
          </tr>
        @endforeach
      @endforeach

      <tr style="font-weight:bold; text-align:center; background:#{{ $groupColor }};">
        <td colspan="2" style="text-align:left;">Jumlah {{ substr($group['label'], 0, 1) }}</td>
        <td>{{ number_format($group['total']['liter_paiton'], 2, ',', '.') }}</td>
        <td>{{ number_format($group['total']['rp_paiton'], 0, ',', '.') }}</td>
        <td>{{ number_format($group['total']['liter_luar_paiton'], 2, ',', '.') }}</td>
        <td>{{ number_format($group['total']['rp_luar_paiton'], 0, ',', '.') }}</td>
        <td>{{ $group['total']['service_oli'] ? number_format($group['total']['service_oli'], 0, ',', '.') : '-' }}</td>
        <td>{{ $group['total']['jasa'] ? number_format($group['total']['jasa'], 0, ',', '.') : '-' }}</td>
        <td>{{ number_format($group['total']['jumlah'], 0, ',', '.') }}</td>
      </tr>
    @empty
      <tr><td colspan="9" style="text-align:center; padding:16px;">Tidak ada data pada periode ini.</td></tr>
    @endforelse

    @if(!empty($groups))
      <tr style="font-weight:bold; text-align:center; background:#{{ $grandColor }};">
        <td colspan="2" style="text-align:left;">
          Jumlah {{ implode('+', array_map(fn($g) => substr($g['label'], 0, 1), $groups)) }}
        </td>
        <td>{{ number_format($grandTotal['liter_paiton'], 2, ',', '.') }}</td>
        <td>{{ number_format($grandTotal['rp_paiton'], 0, ',', '.') }}</td>
        <td>{{ number_format($grandTotal['liter_luar_paiton'], 2, ',', '.') }}</td>
        <td>{{ number_format($grandTotal['rp_luar_paiton'], 0, ',', '.') }}</td>
        <td>{{ $grandTotal['service_oli'] ? number_format($grandTotal['service_oli'], 0, ',', '.') : '-' }}</td>
        <td>{{ $grandTotal['jasa'] ? number_format($grandTotal['jasa'], 0, ',', '.') : '-' }}</td>
        <td>{{ number_format($grandTotal['jumlah'], 0, ',', '.') }}</td>
      </tr>
    @endif
  </tbody>
</table>