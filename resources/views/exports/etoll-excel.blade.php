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
    <td colspan="8" style="font-weight: bold; background-color: #E9ECEF; border: 1px solid #6C757D;">A. Roda Empat</td>
  </tr>
  <tr style="font-weight: bold; background-color: #343A40; color: #FFFFFF;">
    <td align="center" style="border: 1px solid #6C757D;">No.</td>
    <td align="center" style="border: 1px solid #6C757D;">Nama</td>
    <td align="center" style="border: 1px solid #6C757D;">Minggu-1</td>
    <td align="center" style="border: 1px solid #6C757D;">Minggu-2</td>
    <td align="center" style="border: 1px solid #6C757D;">Minggu-3</td>
    <td align="center" style="border: 1px solid #6C757D;">Minggu-4</td>
    <td align="center" style="border: 1px solid #6C757D;">Minggu-5</td>
    <td align="center" style="border: 1px solid #6C757D;">Jumlah (Rp)</td>
  </tr>
  @foreach($rows as $i => $row)
  <tr>
    <td align="center" style="border: 1px solid #ADB5BD;">{{ $i + 1 }}</td>
    <td style="border: 1px solid #ADB5BD;">{{ $row['nama'] }}</td>
    @foreach($row['minggu'] as $val)
      <td align="right" style="border: 1px solid #ADB5BD;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}</td>
    @endforeach
    <td align="right" style="border: 1px solid #ADB5BD;">{{ $row['jumlah'] > 0 ? number_format($row['jumlah'], 0, ',', '.') : '-' }}</td>
  </tr>
  @endforeach
  <tr style="font-weight: bold; background-color: #F1F3F5;">
    <td colspan="2" style="border: 1px solid #6C757D;">Jumlah</td>
    <td style="border: 1px solid #6C757D;"></td>
    <td style="border: 1px solid #6C757D;"></td>
    <td style="border: 1px solid #6C757D;"></td>
    <td style="border: 1px solid #6C757D;"></td>
    <td style="border: 1px solid #6C757D;"></td>
    <td align="right" style="border: 1px solid #6C757D;">{{ $totalKeseluruhan > 0 ? number_format($totalKeseluruhan, 0, ',', '.') : '-' }}</td>
  </tr>
</table>