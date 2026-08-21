<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { size: portrait; margin: 20px; }
    body { font-family: sans-serif; }

    .header-table {
      width: 100%;
      table-layout: fixed;
      border-collapse: collapse;
      margin-bottom: 16px;
    }
    .header-table td {
      border: none;
      vertical-align: top;
    }
    .logo-cell {
      width: 15%;
      text-align: left;
    }
    .logo-cell img {
      height: 45px;
    }
    .title-cell {
      width: 70%;
      text-align: center;
    }

    h3 { margin: 0; font-size: 15px; line-height: 1.2; }
  </style>
</head>
<body>

  <table class="header-table">
    <tr>
      <td class="logo-cell">
        <img src="{{ public_path('images/logo-pln2.png') }}" alt="Logo PLN">
      </td>
      <td class="title-cell">
        <h3>PERTANGGUNGJAWABAN PEMAKAIAN BBM</h3>
      </td>
      <td class="logo-cell"></td>
    </tr>
  </table>

  @include('rekapan.pemakaian-bbm._pertanggungjawaban-report', [
    'weeks'         => $weeks,
    'bulanLabel'    => $bulanLabel,
    'keterangan'    => $keterangan,
    'penandatangan' => $penandatangan,
  ])

</body>
</html>