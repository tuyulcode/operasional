<h4 style="text-align:center; margin-bottom:16px;">PEMAKAIAN BBM KENDARAAN DINAS</h4>

@foreach($weeks as $week)
  <p style="text-align:center; font-weight:bold; margin:18px 0 6px;">
    {{ $week['no'] }}. Periode {{ $week['periodeLabel'] }}
  </p>
  <div class="table-responsive" style="margin-bottom:20px;">
    @include('rekapan.pemakaian-bbm._pertanggungjawaban-table', [
      'groups'     => $week['groups'],
      'grandTotal' => $week['grandTotal'],
    ])
  </div>
@endforeach

<table style="width:100%; border-collapse:collapse; margin-top:28px; font-size:13px; page-break-inside: avoid;">
  <tr>
    <td style="width:60%; vertical-align:top; padding-right:24px; border:none;">
      <strong>Keterangan :</strong><br>
      Laporan Pengeluaran BBM bulan {{ $bulanLabel }}
      <table style="margin-top:10px; font-size:13px; border-collapse:collapse;">
        <tr>
          <td style="padding:2px 0; border:none;">- Pemakaian BBM untuk di Paiton</td>
          <td style="padding:2px 8px; border:none;">Rp</td>
          <td style="padding:2px 0; text-align:right; min-width:110px; border:none;">{{ number_format($keterangan['paiton'], 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td style="padding:2px 0; border:none;">- Pemakaian BBM ke Luar Paiton</td>
          <td style="padding:2px 8px; border:none;">Rp</td>
          <td style="padding:2px 0; text-align:right; border:none;">{{ number_format($keterangan['luar_paiton'], 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td style="padding:2px 0; border:none;">- Pemb. Sparepart Consumable</td>
          <td style="padding:2px 8px; border:none;">Rp</td>
          <td style="padding:2px 0; text-align:right; border:none;">{{ number_format($keterangan['service_oli'], 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td style="padding:2px 0; border:none;">- Penggunaan Jasa</td>
          <td style="padding:2px 8px; border:none;">Rp</td>
          <td style="padding:2px 0; text-align:right; border:none;">{{ number_format($keterangan['jasa'], 0, ',', '.') }}</td>
        </tr>
        <tr style="font-weight:bold; border-top:1px solid #333;">
          <td style="padding:4px 0; border:none;">Jumlah</td>
          <td style="padding:4px 8px; border:none;">Rp</td>
          <td style="padding:4px 0; text-align:right; border:none;">{{ number_format($keterangan['jumlah'], 0, ',', '.') }}</td>
        </tr>
      </table>
    </td>

    <td style="width:40%; vertical-align:top; text-align:center; border:none;">
      @php
        $tempat = $penandatangan->tempat ?? '';
        $tanggalCetakLabel = now()->locale('id')->translatedFormat('d F Y');
      @endphp
      {{ ($tempat ? $tempat . ', ' : '') . $tanggalCetakLabel }}<br>
      <strong>{{ strtoupper($penandatangan->jabatan ?? 'ASMAN SDM UMUM & CSR') }}</strong>
      <div style="height:70px;"></div>
      <strong style="text-decoration:underline;">{{ $penandatangan->nama ?? '...................................' }}</strong>
    </td>
  </tr>
</table>