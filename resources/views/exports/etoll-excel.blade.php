<table>
  <tr>
    <td colspan="{{ $maxDateCount + 2 }}" align="center" style="font-weight: bold; font-size: 14px;">Rekap E-Toll</td>
  </tr>
  <tr>
    <td colspan="{{ $maxDateCount + 2 }}" align="center" style="font-size: 12px;">Periode {{ $periodeLabel }}</td>
  </tr>
  <tr>
    <td colspan="{{ $maxDateCount + 2 }}"></td>
  </tr>
  <tr>
    <td colspan="{{ $maxDateCount + 2 }}" align="center" bgcolor="#DBEAFE" style="font-weight: bold; background-color: #DBEAFE; color: #1F2937; border: 1px solid #000000;">A. Roda Empat</td>
  </tr>

  @foreach($bulanGroups as $group)
  <tr>
    <td colspan="{{ $maxDateCount + 2 }}" align="center" bgcolor="#EEF4FF" style="font-weight: bold; background-color: #EEF4FF; color: #1F2937; border: 1px solid #000000;">{{ $group['label'] }}</td>
  </tr>
  <tr>
    <td align="center" bgcolor="#E9ECEF" style="width: 120px; font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Nama</td>
    @foreach($group['rows'] as $row)
      <td align="center" bgcolor="#E9ECEF" style="width: 40px; font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">{{ $row['tanggal'] }}</td>
    @endforeach
    <td align="center" bgcolor="#E9ECEF" style="width: 80px; font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Jumlah</td>
  </tr>
  @foreach($pemegangs as $p)
  <tr>
    <td style="width: 120px; border: 1px solid #000000;">{{ $p->nama }}</td>
    @foreach($group['rows'] as $row)
      <td align="right" style="width: 40px; border: 1px solid #000000;">{{ ($row['nilai'][$p->id] ?? 0) > 0 ? $row['nilai'][$p->id] : '-' }}</td>
    @endforeach
    <td align="right" style="width: 80px; border: 1px solid #000000; font-weight: bold;">{{ ($group['totalPerPemegang'][$p->id] ?? 0) > 0 ? $group['totalPerPemegang'][$p->id] : '-' }}</td>
  </tr>
  @endforeach
  @endforeach

  <tr>
    <td colspan="{{ $maxDateCount + 1 }}" align="center" bgcolor="#F1F3F5" style="font-weight: bold; background-color: #F1F3F5; color: #1F2937; border: 1px solid #000000; text-align: center;">Total</td>
    <td align="right" bgcolor="#F1F3F5" style="width: 80px; font-weight: bold; background-color: #F1F3F5; color: #1F2937; border: 1px solid #000000;">{{ $totalKeseluruhan }}</td>
  </tr>
</table>