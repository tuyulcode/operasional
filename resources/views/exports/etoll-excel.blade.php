<table>
  <tr>
    <td colspan="8" align="center" style="font-weight: bold; font-size: 14px;">Rekap E-Toll</td>
  </tr>
  <tr>
    <td colspan="8" align="center" style="font-size: 12px;">Periode Bulan {{ $bulanNama }} {{ $tahun }}</td>
  </tr>
  <tr>
    <td colspan="8"></td>
  </tr>
  <tr>
    <td colspan="8" align="center" bgcolor="#DBEAFE" style="font-weight: bold; background-color: #DBEAFE; color: #1F2937; border: 1px solid #000000;">A. Roda Empat</td>
  </tr>
  <tr>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">No.</td>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Nama</td>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Minggu-1</td>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Minggu-2</td>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Minggu-3</td>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Minggu-4</td>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Minggu-5</td>
    <td align="center" bgcolor="#E9ECEF" style="font-weight: bold; background-color: #E9ECEF; color: #1F2937; border: 1px solid #000000;">Jumlah (Rp)</td>
  </tr>
  @foreach($rows as $i => $row)
  <tr>
    <td align="center" style="border: 1px solid #000000;">{{ $i + 1 }}</td>
    <td style="border: 1px solid #000000;">{{ $row['nama'] }}</td>
    @foreach($row['minggu'] as $val)
      <td align="right" style="border: 1px solid #000000;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}</td>
    @endforeach
    <td align="right" style="border: 1px solid #000000;">{{ $row['jumlah'] > 0 ? number_format($row['jumlah'], 0, ',', '.') : '-' }}</td>
  </tr>
  @endforeach
  <tr>
    <td colspan="2" bgcolor="#F1F3F5" style="font-weight: bold; background-color: #F1F3F5; color: #1F2937; border: 1px solid #000000;">Jumlah</td>
    <td bgcolor="#F1F3F5" style="background-color: #F1F3F5; border: 1px solid #000000;"></td>
    <td bgcolor="#F1F3F5" style="background-color: #F1F3F5; border: 1px solid #000000;"></td>
    <td bgcolor="#F1F3F5" style="background-color: #F1F3F5; border: 1px solid #000000;"></td>
    <td bgcolor="#F1F3F5" style="background-color: #F1F3F5; border: 1px solid #000000;"></td>
    <td bgcolor="#F1F3F5" style="background-color: #F1F3F5; border: 1px solid #000000;"></td>
    <td align="right" bgcolor="#F1F3F5" style="font-weight: bold; background-color: #F1F3F5; color: #1F2937; border: 1px solid #000000;">{{ $totalKeseluruhan > 0 ? number_format($totalKeseluruhan, 0, ',', '.') : '-' }}</td>
  </tr>
</table>