@php
  $bulanRaw = $report['bulanRaw'];
  $periodeLabel = $report['periodeLabel'];
  $rows = $report['rows'];
  $totalKeseluruhan = $report['totalKeseluruhan'];
@endphp

{{-- FILTER REKAPAN --}}
<div class="card">
  <div class="card-header">
    <div class="card-header-title">
      <h3>Filter Rekapan</h3>
      <p>Bulan / tahun wajib dipilih</p>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('pemakaian-etoll.index') }}">
      <input type="hidden" name="tab" value="rekapan">
      <div class="form-grid">
        <div class="form-group">
          <label for="bulan">Bulan / Tahun <span style="color: #e11d48;">*</span></label>
          <input type="month" id="bulan" name="bulan" class="form-control"
                 value="{{ $bulanRaw }}" required>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-filter"></i> Lihat Rekapan
        </button>
      </div>
    </form>
  </div>
</div>

@if(!$report['bulan'])

  <div class="card">
    <div class="card-body" style="text-align: center; padding: 40px; color: #999;">
      <i class="fa-solid fa-calendar-days" style="font-size: 2.5rem; display: block; margin-bottom: 10px; opacity: 0.3;"></i>
      Pilih bulan / tahun di atas untuk menampilkan rekapan.
    </div>
  </div>

@else

  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Rekap E-Toll</h3>
        <p>Periode Bulan {{ $periodeLabel }}</p>
      </div>
      <div class="card-actions">
        <a href="{{ route('pemakaian-etoll.export-excel', ['bulan' => $bulanRaw]) }}"
           class="btn btn-excel btn-sm">
          <i class="fa-solid fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('pemakaian-etoll.export-pdf', ['bulan' => $bulanRaw]) }}"
           target="_blank"
           class="btn btn-pdf btn-sm">
          <i class="fa-solid fa-file-pdf"></i> Export PDF
        </a>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table" style="border: 1px solid #dee2e6;">
          <thead>
            <tr>
              <td colspan="8" style="background-color: #e9ecef; font-weight: 700; padding: 6px 10px; border: 1px solid #adb5bd; text-align: center;">
                A. Roda Empat
              </td>
            </tr>
            <tr>
              <th style="border: 1px solid #adb5bd;">No.</th>
              <th style="border: 1px solid #adb5bd;">Nama</th>
              <th style="border: 1px solid #adb5bd;">Minggu-1</th>
              <th style="border: 1px solid #adb5bd;">Minggu-2</th>
              <th style="border: 1px solid #adb5bd;">Minggu-3</th>
              <th style="border: 1px solid #adb5bd;">Minggu-4</th>
              <th style="border: 1px solid #adb5bd;">Minggu-5</th>
              <th style="border: 1px solid #adb5bd;">Jumlah (Rp)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rows as $i => $row)
            <tr>
              <td style="text-align: center; border: 1px solid #dee2e6;">{{ $i + 1 }}</td>
              <td style="border: 1px solid #dee2e6;">{{ $row['nama'] }}</td>
              @foreach($row['minggu'] as $val)
                <td style="text-align: right; border: 1px solid #dee2e6;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '-' }}</td>
              @endforeach
              <td style="text-align: right; border: 1px solid #dee2e6; font-weight: 600;">{{ $row['jumlah'] > 0 ? number_format($row['jumlah'], 0, ',', '.') : '-' }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr style="background-color: #f1f3f5; font-weight: 700;">
              <td colspan="2" style="border: 1px solid #adb5bd; padding: 6px 10px;">Jumlah</td>
              <td style="border: 1px solid #adb5bd;"></td>
              <td style="border: 1px solid #adb5bd;"></td>
              <td style="border: 1px solid #adb5bd;"></td>
              <td style="border: 1px solid #adb5bd;"></td>
              <td style="border: 1px solid #adb5bd;"></td>
              <td style="text-align: right; border: 1px solid #adb5bd;">{{ $totalKeseluruhan > 0 ? number_format($totalKeseluruhan, 0, ',', '.') : '-' }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

@endif