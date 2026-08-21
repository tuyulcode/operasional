@php
  $filteredGroups = collect($groups)->reject(fn ($g) => str_contains($g['label'], 'Roda Tiga'))->values();

  $displayGrandTotal = ['liter' => 0, 'rp' => 0];
  foreach ($filteredGroups as $g) {
    $displayGrandTotal['liter'] += $g['total']['liter'];
    $displayGrandTotal['rp'] += $g['total']['rp'];
  }
@endphp

@foreach($filteredGroups as $group)
  @php
    $noUrut = $loop->index + 1;
    $namaGroup = preg_replace('/^[A-Za-z]\.\s*/', '', $group['label']);
  @endphp

  <div style="width:65%; margin:0 auto;">
    <p style="display:inline-block; font-weight:bold; margin:14px 0 6px; padding:4px 10px; font-size:13px; background:#fbdce6; border-radius:4px;">{{ $noUrut }}. {{ $namaGroup }}</p>

    <table style="border-collapse: collapse; width: 100%; font-size: 12px; margin-bottom:10px;" border="1" cellpadding="4" cellspacing="0">
      <colgroup>
        <col style="width:8%">
        <col style="width:42%">
        <col style="width:20%">
        <col style="width:30%">
      </colgroup>
      <thead>
        <tr style="font-weight:bold; text-align:center;">
          <th style="padding:10px 4px;">No.</th>
          <th style="padding:10px 4px;">Nomor Kendaraan</th>
          <th style="padding:10px 4px;">Liter</th>
          <th style="padding:10px 4px;">Rp.</th>
        </tr>
        <tr style="font-weight:bold; text-align:center;">
          <th style="padding:6px 4px;">1</th>
          <th style="padding:6px 4px;">2</th>
          <th style="padding:6px 4px;">3</th>
          <th style="padding:6px 4px;">4</th>
        </tr>
      </thead>
      <tbody>
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
          <tr><td colspan="4" style="text-align:center; padding:16px;">Tidak ada data pada periode ini.</td></tr>
        @endforelse

        <tr style="font-weight:bold; text-align:center;">
          <td colspan="2" style="text-align:left;">Jumlah {{ $noUrut }}</td>
          <td>{{ number_format($group['total']['liter'], 2, ',', '.') }}</td>
          <td>{{ number_format($group['total']['rp'], 0, ',', '.') }}</td>
        </tr>

        @if($loop->last)
          <tr style="font-weight:bold; text-align:center;">
            <td colspan="2" style="text-align:left;">
              Jumlah {{ implode(' + ', range(1, count($filteredGroups))) }}
            </td>
            <td>{{ number_format($displayGrandTotal['liter'], 2, ',', '.') }}</td>
            <td>{{ number_format($displayGrandTotal['rp'], 0, ',', '.') }}</td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>
@endforeach