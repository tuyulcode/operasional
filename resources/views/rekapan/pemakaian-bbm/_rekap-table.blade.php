@php
  // Warna aksen per grup, urut: A. Roda Empat, B. Roda Tiga, C. Roda Dua
  $groupColors = ['bdd7ee', 'd9d2e9', 'c6e0b4'];
  $grandColor  = 'ffc000';
@endphp

<table style="border-collapse: collapse; width: 100%; font-size: 12px;" border="1" cellpadding="4" cellspacing="0">
  <colgroup>
    <col style="width:5%">
    <col style="width:22%">
    <col style="width:9%">
    <col style="width:11%">
    <col style="width:28%">
    <col style="width:10%">
    <col style="width:15%">
  </colgroup>
  <thead>
    <tr style="background:#f2f2f2; font-weight:bold; text-align:center;">
      <th style="padding:10px 4px;">No.</th>
      <th style="padding:10px 4px;">Nomor Kendaraan</th>
      <th style="padding:10px 4px;">Liter</th>
      <th style="padding:10px 4px;">Rp.</th>
      <th style="padding:10px 4px;">Sparepart Consumable</th>
      <th style="padding:10px 4px;">Jasa</th>
      <th style="padding:10px 4px;">Jumlah</th>
    </tr>
    <tr style="background:#f2f2f2; font-weight:bold; text-align:center;">
      <th style="padding:6px 4px;">1</th><th style="padding:6px 4px;">2</th><th style="padding:6px 4px;">3</th><th style="padding:6px 4px;">4</th><th style="padding:6px 4px;">5</th><th style="padding:6px 4px;">6</th><th style="padding:6px 4px;">7 = 4+5+6</th>
    </tr>
  </thead>
  <tbody>
    @forelse($groups as $group)
      @php $groupColor = $groupColors[$loop->index % count($groupColors)]; @endphp

      <tr style="background:#{{ $groupColor }}; font-weight:bold;">
        <td colspan="7" style="text-align:left;">{{ $group['label'] }}</td>
      </tr>

      @foreach($group['sections'] as $section)
        @if($section['label'])
          <tr style="background:#{{ $groupColor }}; font-weight:bold;">
            <td colspan="7" style="text-align:center;">{{ $section['label'] }}</td>
          </tr>
        @endif

        @foreach($section['rows'] as $row)
          <tr style="text-align:center;">
            <td>{{ $row['no'] }}</td>
            <td>{{ $row['plat_nomor'] }}</td>
            <td>{{ $row['liter'] ? number_format($row['liter'], 2, ',', '.') : '-' }}</td>
            <td>{{ $row['rp'] ? number_format($row['rp'], 0, ',', '.') : '-' }}</td>
            <td>{{ $row['service_oli'] ? number_format($row['service_oli'], 0, ',', '.') : '-' }}</td>
            <td>{{ $row['jasa'] ? number_format($row['jasa'], 0, ',', '.') : '-' }}</td>
            <td>{{ $row['jumlah'] ? number_format($row['jumlah'], 0, ',', '.') : '-' }}</td>
          </tr>
        @endforeach
      @endforeach

      <tr style="font-weight:bold; text-align:center; background:#{{ $groupColor }};">
        <td colspan="2" style="text-align:left;">Jumlah {{ substr($group['label'], 0, 1) }}</td>
        <td>{{ number_format($group['total']['liter'], 2, ',', '.') }}</td>
        <td>{{ number_format($group['total']['rp'], 0, ',', '.') }}</td>
        <td>{{ $group['total']['service_oli'] ? number_format($group['total']['service_oli'], 0, ',', '.') : '-' }}</td>
        <td>{{ $group['total']['jasa'] ? number_format($group['total']['jasa'], 0, ',', '.') : '-' }}</td>
        <td>{{ number_format($group['total']['jumlah'], 0, ',', '.') }}</td>
      </tr>
    @empty
      <tr><td colspan="7" style="text-align:center; padding:16px;">Tidak ada data pada periode ini.</td></tr>
    @endforelse

    @if(!empty($groups))
      <tr style="font-weight:bold; text-align:center; background:#{{ $grandColor }};">
        <td colspan="2" style="text-align:left;">
          Jumlah {{ implode('+', array_map(fn($g) => substr($g['label'], 0, 1), $groups)) }}
        </td>
        <td>{{ number_format($grandTotal['liter'], 2, ',', '.') }}</td>
        <td>{{ number_format($grandTotal['rp'], 0, ',', '.') }}</td>
        <td>{{ $grandTotal['service_oli'] ? number_format($grandTotal['service_oli'], 0, ',', '.') : '-' }}</td>
        <td>{{ $grandTotal['jasa'] ? number_format($grandTotal['jasa'], 0, ',', '.') : '-' }}</td>
        <td>{{ number_format($grandTotal['jumlah'], 0, ',', '.') }}</td>
      </tr>
    @endif
  </tbody>
</table>