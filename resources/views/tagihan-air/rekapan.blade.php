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
          <label for="area_id">Nama Pengguna</label>
          <select id="area_id" name="area_id" class="form-control">
            <option value="">-- Semua Nama Pengguna --</option>
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
            Nama Pengguna: {{ $data->firstWhere('area.id', $areaId)['area']->nama ?? '-' }}
          @else
            Semua Nama Pengguna
          @endif
        </p>
      </div>
      <div class="card-actions">
        <a href="{{ route('rekapan.excel', ['bulan' => $bulan, 'area_id' => $areaId]) }}"
           class="btn btn-success btn-sm">
          <i class="fa-solid fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('rekapan.pdf', ['bulan' => $bulan, 'area_id' => $areaId]) }}"
           target="_blank" rel="noopener"
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
            @if($area['jml_titik'] === 1)
              @php($row1 = $area['rows']->first())
              @php($tg = $row1['tagihan'] ?? null)
              @php($ini = $tg ? (int) round((float) $tg->meter_ini) : 0)
              @php($lalu = $tg ? (int) round((float) $tg->meter_lalu) : 0)
              @php($faktor = $tg ? (float) $tg->meter_faktor : 0)
              <div class="rekapan-vertikal">
                <div class="rv-title">BIAYA PEMAKAIAN AIR</div>
                <div class="rv-meta">Bulan : {{ $periodeLabel }}</div>
                <div class="rv-meta">NAMA: {{ $area['area']->nama }}</div>
                <div class="rv-meta">ALAMAT: {{ $area['area']->alamat ?: '-' }}</div>
                <div class="rv-meta">LOKASI FLOW METER: {{ $row1['titik_meter']->nama }}</div>
                <div class="rv-section">PERHITUNGAN PEMAKAIAN</div>
                <div class="rv-row">
                  <span class="rv-label">Bulan ini</span>
                  <span class="rv-value">{{ $ini }}</span>
                </div>
                <div class="rv-row">
                  <span class="rv-label">Bulan lalu</span>
                  <span class="rv-value">{{ $lalu }}</span>
                </div>
                <div class="rv-row">
                  <span class="rv-label">Jumlah Pengambilan</span>
                  <span class="rv-value">{{ $ini - $lalu }}</span>
                </div>
                <div class="rv-row">
                  <span class="rv-label">Meter Faktor</span>
                  <span class="rv-value">{{ $tg ? number_format($faktor, 0, ',', '.') : '0' }}</span>
                </div>
                <div class="rv-row">
                  <span class="rv-label">Jumlah Pengambilan</span>
                  <span class="rv-value">{{ $tg ? (int) round((float) $tg->pemakaian) : 0 }}</span>
                </div>
                <div class="rv-row">
                  <span class="rv-label">Tarif / M3</span>
                  <span class="rv-value">Rp {{ number_format($tg->tarif ?? 0, 2, ',', '.') }}</span>
                </div>
                <div class="rv-row rv-subtotal">
                  <span class="rv-label">Jumlah (Rp)</span>
                  <span class="rv-value">Rp {{ number_format($area['subtotal'], 0, ',', '.') }}</span>
                </div>
                @if($area['kena_ppn'])
                  <div class="rv-row">
                    <span class="rv-label">PPN {{ number_format($area['persen_ppn'], 0, ',', '.') }}%</span>
                    <span class="rv-value">Rp {{ number_format($area['ppn'], 0, ',', '.') }}</span>
                  </div>
                  <div class="rv-row">
                    <span class="rv-label">Jumlah (Rp)</span>
                    <span class="rv-value">Rp {{ number_format($area['total'], 0, ',', '.') }}</span>
                  </div>
                @endif
              </div>
            @else
            <table class="app-sales-table">
              <thead>
                <tr>
                  <th>No. Urut</th>
                  <th>Nama Titik Meter</th>
                  <th>Bulan Ini</th>
                  <th>Bulan Lalu</th>
                  <th>Pengambilan</th>
                  <th>Tarif (Rp/M3)</th>
                  <th>Jumlah (Rp)</th>
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
                    <td>{{ (int) round((float) $row['tagihan']->meter_ini) }}</td>
                    <td>{{ (int) round((float) $row['tagihan']->meter_lalu) }}</td>
                    <td>{{ (int) round((float) $row['tagihan']->pemakaian) }}</td>
                    <td>Rp {{ number_format($row['tagihan']->tarif, 2, ',', '.') }}</td>
                    <td><b>Rp {{ number_format($row['tagihan']->jumlah, 0, ',', '.') }}</b></td>
                  </tr>
                @endforeach
                <tr class="rekapan-subtotal-row">
                  <td colspan="4"><b>Subtotal {{ $area['area']->nama }}</b></td>
                  <td>{{ $area['total_pemakaian'] ? (int) round($area['total_pemakaian']) : '-' }}</td>
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
            @endif
          </div>
        </div>
      @endforeach

      <div class="rekapan-grand-total">
        <span>Grand Total Semua Nama Pengguna</span>
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
          @php
            $ttdRow = $report['penandatangan']->first();
            $tempat = $ttdRow->tempat ?? '';
            $tanggalRaw = $ttdRow->tanggal_cetak ?? now()->format('Y-m-d');
            $tanggal = \Carbon\Carbon::parse($tanggalRaw)->locale('id')->translatedFormat('d F Y');
          @endphp
          {{ ($tempat ? $tempat . ', ' : '') . $tanggal }}
        </div>
      </div>
    </div>
  </div>

@endif