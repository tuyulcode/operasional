<h3 style="text-align:center;">PEMAKAIAN BBM KENDARAAN DINAS &amp; JASA</h3>
@if(!empty($periodeLabel))
<p style="text-align:center;">Periode {{ $periodeLabel }}</p>
@endif
@include('rekapan.pemakaian-bbm._rekap-table')