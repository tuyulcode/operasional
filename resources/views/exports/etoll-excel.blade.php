<table>
  @php $totalKolom = $pemegangs->count() + 2; @endphp
  <tr>
    <td colspan="{{ $totalKolom }}" align="center" style="font-weight: bold; font-size: 14px;">Rekap E-Toll</td>
  </tr>
  <tr>
    <td colspan="{{ $totalKolom }}" align="center" style="font-size: 12px;">Periode Bulan {{ $bulanNama }} {{ $tahun }}</td>
  </tr>
  <tr>
    <td colspan="{{ $totalKolom }}"></td>
  </tr>
  <tr>
    <td colspan="{{ $totalKolom }}" align="center" bgcolor="#DBEAFE" style="font-weight: bold; background-color: #DBEAFE; color: #1F2937; border: 1px solid #000000;">A. Roda Empat</td>
  </tr>
  <tr>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Tanggal</td>
    @foreach($pemegangs as $p)
      <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">{{ $p->nama }}</td>
    @endforeach
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Jumlah</td>
  </tr>
  @foreach($rows as $row)
  <tr>
    <td align="center" style="border: 1px solid #000000;">{{ $row['tanggal'] }}</td>
    @foreach($pemegangs as $p)
      <td align="right" style="border: 1px solid #000000;">{{ ($row['nilai'][$p->id] ?? 0) > 0 ? number_format($row['nilai'][$p->id], 0, ',', '.') : '-' }}</td>
    @endforeach
    <td align="right" style="border: 1px solid #000000;">{{ $row['total'] > 0 ? number_format($row['total'], 0, ',', '.') : '-' }}</td>
  </tr>
  @endforeach
  <tr>
    <td colspan="{{ $pemegangs->count() + 1 }}" align="center" bgcolor="#F1F3F5" style="font-weight: bold; background-color: #F1F3F5; color: #1F2937; border: 1px solid #000000; text-align: center;">Total</td>
    <td align="right" bgcolor="#F1F3F5" style="font-weight: bold; background-color: #F1F3F5; color: #1F2937; border: 1px solid #000000;">{{ number_format($totalKeseluruhan, 0, ',', '.') }}</td>
  </tr>
</table>