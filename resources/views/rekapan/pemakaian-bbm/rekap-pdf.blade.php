<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
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
    p.periode { margin: 4px 0 0; text-align: center; }
    table.rekap { width: 100%; }
  </style>
</head>
<body>

  <table class="header-table">
    <tr>
      <td class="logo-cell">
        <img src="{{ public_path('images/logo-pln2.png') }}" alt="Logo PLN">
      </td>
      <td class="title-cell">
        <h3>PEMAKAIAN BBM KENDARAAN DINAS &amp; JASA</h3>
        <p class="periode">Periode {{ $periodeLabel }}</p>
      </td>
      <td class="logo-cell"></td>
    </tr>
  </table>

  @include('rekapan.pemakaian-bbm._rekap-table')

</body>
</html>