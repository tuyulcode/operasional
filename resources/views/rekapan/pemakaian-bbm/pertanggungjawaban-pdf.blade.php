<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { size: portrait; margin: 20px; }
    body { font-family: sans-serif; }

    .logo-row {
      text-align: left;
      margin-bottom: 8px;
    }
    .logo-row img {
      height: 45px;
    }
  </style>
</head>
<body>

  <div class="logo-row">
    <img src="{{ public_path('images/logo-pln2.png') }}" alt="Logo PLN">
  </div>

  @include('rekapan.pemakaian-bbm._pertanggungjawaban-report', [
    'weeks'         => $weeks,
    'bulanLabel'    => $bulanLabel,
    'keterangan'    => $keterangan,
    'penandatangan' => $penandatangan,
  ])

</body>
</html>