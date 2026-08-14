@php
  $bulan = $report['bulan'];
  $areaId = $report['areaId'];
  $periodeLabel = $report['periodeLabel'];
  $data = $report['data'];
  $grandTotal = $report['grandTotal'];
@endphp

{{-- FILTER REKAPAN --}}
<div class="card">
  <div class="card-header">
    <div class="card-header-title">
      <h3>Filter Rekapan</h3>
      <p>Bulan / tahun wajib dipilih, area opsional</p>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('tagihan-air.index') }}">
      <input type="hidden" name="tab" value="rekapan">
      <div class="form-grid">
        <div class="form-group">
          <label for="bulan">Bulan / Tahun <span style="color: #e11d48;">*</span></label>
          <input type="month" id="bulan" name="bulan" class="form-control"
                 value="{{ $bulan }}" required>
        </div>
        <div class="form-group">
          <label for="area_id">Area</label>
          <select id="area_id" name="area_id" class="form-control">
            <option value="">-- Semua Area --</option>
            @foreach($report['areas'] as $area)
              <option value="{{ $area->id }}" {{ $areaId == $area->id ? 'selected' : '' }}>
                {{ $area->nama }}
              </option>
            @endforeach
          </select>
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

@if(!$bulan)

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
        <h3>Rekapan {{ $periodeLabel }}</h3>
        <p>
          @if($areaId)
            Area: {{ $data->firstWhere('area.id', $areaId)['area']->nama ?? '-' }}
          @else
            Semua Area
          @endif
        </p>
      </div>
      <div class="card-actions">
        <a href="{{ route('rekapan.excel', ['bulan' => $bulan, 'area_id' => $areaId]) }}"
           class="btn btn-success btn-sm">
          <i class="fa-solid fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('rekapan.pdf', ['bulan' => $bulan, 'area_id' => $areaId]) }}"
           class="btn btn-danger btn-sm">
          <i class="fa-solid fa-file-pdf"></i> Export PDF
        </a>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      @foreach($data as $area)
        <div class="rekapan-area">
          <div class="rekapan-area-header">
            <div class="rekapan-area-title">
              <i class="fa-solid fa-location-dot"></i> {{ $area['area']->nama }}
            </div>
            <div class="rekapan-area-total">
              Subtotal: <b>Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</b>
            </div>
          </div>
          <div class="table-responsive">
            <table class="app-sales-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Titik Meter</th>
                  <th>Meter Lalu</th>
                  <th>Meter Ini</th>
                  <th>Pemakaian (m3)</th>
                  <th>Tarif</th>
                  <th>Jumlah</th>
                </tr>
              </thead>
              <tbody>
                @foreach($area['rows'] as $i => $row)
                  @continue(!$row['tagihan'])
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                      <div class="app-info">
                        <div>
                          <div class="app-title">{{ $row['titik_meter']->nama }}</div>
                          <div class="app-desc">{{ $row['titik_meter']->status == 'aktif' ? 'Aktif' : 'Nonaktif' }}</div>
                        </div>
                      </div>
                    </td>
                    <td>{{ number_format($row['tagihan']->meter_lalu, 2, ',', '.') }}</td>
                    <td>{{ number_format($row['tagihan']->meter_ini, 2, ',', '.') }}</td>
                    <td>{{ number_format($row['tagihan']->pemakaian, 2, ',', '.') }}</td>
                    <td>Rp {{ number_format($row['tagihan']->tarif, 0, ',', '.') }}</td>
                    <td><b>Rp {{ number_format($row['tagihan']->jumlah, 0, ',', '.') }}</b></td>
                  </tr>
                @endforeach
                <tr class="rekapan-subtotal-row">
                  <td colspan="4"><b>Subtotal {{ $area['area']->nama }}</b></td>
                  <td>{{ $area['total_pemakaian'] ? number_format($area['total_pemakaian'], 2, ',', '.') . ' m3' : '-' }}</td>
                  <td></td>
                  <td><b>Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</b></td>
                </tr>
                @if($area['kena_ppn'])
                  <tr class="rekapan-ppn-row">
                    <td colspan="4"><b>PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</b></td>
                    <td></td>
                    <td></td>
                    <td>Rp {{ number_format($area['ppn'], 0, ',', '.') }}</td>
                  </tr>
                  <tr class="rekapan-total-row">
                    <td colspan="4"><b>Total {{ $area['area']->nama }}</b></td>
                    <td></td>
                    <td></td>
                    <td><b>Rp {{ number_format($area['total'], 0, ',', '.') }}</b></td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      @endforeach

      <div class="rekapan-grand-total">
        <span>Grand Total Semua Area</span>
        <span><b>Rp {{ number_format($grandTotal, 0, ',', '.') }}</b></span>
      </div>

      <div class="rekapan-ttd">
        <div class="rekapan-ttd-title">Mengetahui / Menyetujui</div>
        <div class="rekapan-ttd-row">
          @foreach($report['penandatangan'] as $row)
            <div class="rekapan-ttd-col">
              <div class="ttd-jabatan">{{ $row->jabatan }}</div>
              <div class="ttd-space"></div>
              <div class="ttd-nama">{{ $row->nama ?: '...................................' }}</div>
            </div>
          @endforeach
        </div>
        <div class="rekapan-ttd-tanggal">
          @php($tempat = $report['penandatangan']->first()->tempat ?? '')
          {{ ($tempat ? $tempat . ', ' : '') . now()->locale('id')->translatedFormat('d F Y') }}
        </div>
      </div>
    </div>
  </div>

@endif