<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: sans-serif; }
    h3, p { text-align: center; margin: 2px 0; }
    table { width: 100%; }
  </style>
</head>
<body>
  <h3>PEMAKAIAN BBM KENDARAAN DINAS &amp; JASA</h3>
  <p>Periode {{ $periodeLabel }}</p>
  @include('rekapan.pemakaian-bbm._rekap-table')
</body>
</html>