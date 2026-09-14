<table>
  @php
    $totalDateCols = 0;
    foreach ($bulanGroups as $group) {
      $totalDateCols += count($group['rows']);
    }

    $grandTotalPerPemegang = [];
    foreach ($pemegangs as $p) {
      $grandTotalPerPemegang[$p->id] = 0;
      foreach ($bulanGroups as $group) {
        $grandTotalPerPemegang[$p->id] += $group['totalPerPemegang'][$p->id] ?? 0;
      }
    }

    $namaPct = 12;
    $jumlahPct = 12;
    $sisaPct = 100 - $namaPct - $jumlahPct;
    $tanggalPct = $totalDateCols > 0 ? round($sisaPct / $totalDateCols, 2) : $sisaPct;
  @endphp

  <tr>
    <td colspan="{{ $totalDateCols + 2 }}" align="center" style="font-weight: bold; font-size: 14px;">Rekap E-Toll</td>
  </tr>
  <tr>
    <td colspan="{{ $totalDateCols + 2 }}" align="center" style="font-size: 12px;">Periode {{ $periodeLabel }}</td>
  </tr>
  <tr>
    <td colspan="{{ $totalDateCols + 2 }}"></td>
  </tr>
  <tr>
    <td colspan="{{ $totalDateCols + 2 }}" align="center" bgcolor="#DBEAFE" style="font-weight: bold; background-color: #DBEAFE; color: #1F2937; border: 1px solid #000000;">A. Roda Empat</td>
  </tr>

  <tr>
    <td rowspan="3" align="center" bgcolor="#E9ECEF" style="width: {{ $namaPct }}%; font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Nama</td>
    @foreach($bulanGroups as $group)
      <td colspan="{{ count($group['rows']) }}" align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">{{ $group['label'] }}</td>
    @endforeach
    <td rowspan="3" align="center" bgcolor="#E9ECEF" style="width: {{ $jumlahPct }}%; font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Jumlah</td>
  </tr>

  <tr>
    @foreach($bulanGroups as $group)
      <td colspan="{{ count($group['rows']) }}" align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Tanggal</td>
    @endforeach
  </tr>

  <tr>
    @foreach($bulanGroups as $group)
      @foreach($group['rows'] as $row)
        <td align="center" bgcolor="#E9ECEF" style="width: {{ $tanggalPct }}%; font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">{{ $row['tanggal'] }}</td>
      @endforeach
    @endforeach
  </tr>

  @foreach($pemegangs as $p)
  <tr>
    <td style="width: {{ $namaPct }}%; border: 1px solid #000000;">{{ $p->nama }}</td>
    @foreach($bulanGroups as $group)
      @foreach($group['rows'] as $row)
        <td align="right" style="width: {{ $tanggalPct }}%; border: 1px solid #000000;">{{ ($row['nilai'][$p->id] ?? 0) > 0 ? $row['nilai'][$p->id] : '-' }}</td>
      @endforeach
    @endforeach
    <td align="right" style="width: {{ $jumlahPct }}%; border: 1px solid #000000; font-weight: bold;">{{ $grandTotalPerPemegang[$p->id] > 0 ? $grandTotalPerPemegang[$p->id] : '-' }}</td>
  </tr>
  @endforeach

  <tr>
    <td colspan="{{ $totalDateCols + 1 }}" align="center" bgcolor="#F1F3F5" style="font-weight: bold; background-color: #F1F3F5; color: #1F2937; border: 1px solid #000000; text-align: center;">Total</td>
    <td align="right" bgcolor="#F1F3F5" style="width: {{ $jumlahPct }}%; font-weight: bold; background-color: #F1F3F5; color: #1F2937; border: 1px solid #000000;">{{ $totalKeseluruhan }}</td>
  </tr>
</table>
