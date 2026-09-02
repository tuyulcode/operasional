@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
  /* =========================================================
     ANIMASI DASAR
  ========================================================== */
  @keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
  }
  @keyframes popIn {
    0%   { opacity: 0; transform: scale(0.92); }
    70%  { opacity: 1; transform: scale(1.02); }
    100% { opacity: 1; transform: scale(1); }
  }
  @keyframes shimmer {
    0%   { background-position: -400px 0; }
    100% { background-position: 400px 0; }
  }
  @keyframes pulseDot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%      { opacity: 0.5; transform: scale(1.3); }
  }

  /* =========================================================
   FILTER FORM
========================================================== */

  .filter-card {
      animation: fadeSlideUp 0.5s ease both;
      transition: box-shadow 0.3s ease, transform 0.3s ease;
      padding: 12px 16px !important;
      border-radius: 14px;
      background: #f7f9fc;
      border: 1px solid #eef1f5;
  }

  .filter-card:hover {
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
  }

  .filter-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
  }

  .filter-group {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #fff;
      border: 1px solid #e2e6ea;
      border-radius: 10px;
      padding: 0 10px 0 12px;
      height: 42px;
      box-sizing: border-box;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .filter-group:focus-within {
      border-color: #4099ff;
      box-shadow: 0 0 0 3px rgba(64, 153, 255, 0.10);
  }

  .filter-label {
      font-size: 0.74rem;
      font-weight: 600;
      color: #9aa3ad;
      margin: 0;
      white-space: nowrap;
      text-transform: uppercase;
      letter-spacing: 0.3px;
  }

  .filter-card .form-select {
      height: 32px;
      min-width: 110px;
      padding: 2px 26px 2px 4px;
      font-size: 0.85rem;
      font-weight: 500;
      border: none;
      background-color: transparent;
      box-shadow: none !important;
  }

  .filter-card .form-select:focus {
      outline: none;
      box-shadow: none;
  }

  .btn-report {
      height: 42px;
      width: 42px;
      min-width: 42px;
      padding: 0;
      border: none;
      border-radius: 10px;
      background: linear-gradient(135deg, #4099ff 0%, #2ed8b6 100%);
      color: #fff;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      margin-left: 2px;
      margin-top: -4px;
  }

  .btn-report:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 14px rgba(64, 153, 255, 0.30);
  }

  .btn-report:active {
      transform: translateY(0);
  }

  @media (max-width: 480px) {
      .filter-row {
          flex-direction: column;
          align-items: stretch;
      }

      .filter-group,
      .btn-report {
          width: 100%;
      }
  }

  /* =========================================================
     STAT CARDS (diperkecil supaya muat 5 kartu)
  ========================================================== */
  .stat-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(195px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
  }

  .stat-card {
    border-radius: 14px;
    padding: 15px 17px;
    color: #fff;
    position: relative;
    overflow: hidden;
    opacity: 0;
    animation: fadeSlideUp 0.55s ease forwards;
    transition: transform 0.3s cubic-bezier(.22,1,.36,1), box-shadow 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }
  .stat-card:nth-child(1) { animation-delay: 0.05s; }
  .stat-card:nth-child(2) { animation-delay: 0.12s; }
  .stat-card:nth-child(3) { animation-delay: 0.19s; }
  .stat-card:nth-child(4) { animation-delay: 0.26s; }
  .stat-card:nth-child(5) { animation-delay: 0.33s; }

  .stat-card:hover {
    transform: translateY(-5px) scale(1.015);
    box-shadow: 0 12px 26px rgba(0,0,0,0.16);
  }

  /* subtle animated sheen on hover */
  .stat-card::before {
    content: "";
    position: absolute;
    top: 0; left: -75%;
    width: 50%; height: 100%;
    background: linear-gradient(120deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.25) 50%, rgba(255,255,255,0) 100%);
    transform: skewX(-20deg);
    transition: left 0.7s ease;
  }
  .stat-card:hover::before {
    left: 130%;
  }

  .card-coral   { background: linear-gradient(135deg, #ff7a90 0%, #ff5370 100%); }
  .card-emerald { background: linear-gradient(135deg, #4fe0b0 0%, #2ed8b6 100%); }
  .card-amber   { background: linear-gradient(135deg, #ffcd6b 0%, #f5a623 100%); }
  .card-info    { background: linear-gradient(135deg, #6bb6ff 0%, #4099ff 100%); }
  .card-cyan    { background: linear-gradient(135deg, #4dd0e1 0%, #26c6da 100%); }

  .stat-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }
  .stat-value {
    font-size: 1.22rem;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: 0.2px;
  }
  .stat-label {
    font-size: 0.76rem;
    opacity: 0.92;
    margin-top: 3px;
  }
  .stat-icon {
    font-size: 1.3rem;
    opacity: 0.85;
    transition: transform 0.35s ease;
  }
  .stat-card:hover .stat-icon {
    transform: rotate(-8deg) scale(1.15);
  }
  .stat-card-footer {
    margin-top: 10px;
    font-size: 0.72rem;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
  }

  /* =========================================================
     GRID / CARD GENERAL
  ========================================================== */
  .grid-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
  }

  .grid-row-3 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
  }

  .card {
    border-radius: 14px;
    background: var(--card-bg, #fff);
    opacity: 0;
    animation: fadeSlideUp 0.55s ease forwards;
    animation-delay: 0.2s;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
  }
  .card:hover {
    box-shadow: 0 10px 22px rgba(0,0,0,0.08);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px 2px;
  }
  .card-header-title h3 {
    margin: 0;
    font-size: 0.94rem;
    font-weight: 600;
  }
  .card-header-title p {
    margin: 2px 0 0;
    font-size: 0.75rem;
    color: #999;
  }

  /* =========================================================
     RISK / HARGA BBM DETAIL LIST
  ========================================================== */
  .risk-details-table {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .risk-detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 4px;
    border-bottom: 1px dashed rgba(0,0,0,0.08);
    opacity: 0;
    animation: fadeSlideUp 0.4s ease forwards;
    transition: background 0.2s ease, padding-left 0.2s ease;
    border-radius: 8px;
  }
  .risk-detail-item:nth-child(1) { animation-delay: 0.1s; }
  .risk-detail-item:nth-child(2) { animation-delay: 0.18s; }
  .risk-detail-item:nth-child(3) { animation-delay: 0.26s; }
  .risk-detail-item:nth-child(4) { animation-delay: 0.34s; }
  .risk-detail-item:hover {
    background: rgba(64,153,255,0.06);
    padding-left: 10px;
  }
  .risk-detail-item:last-child { border-bottom: none; }
  .risk-detail-item .lbl { color: #777; font-size: 0.84rem; }
  .risk-detail-item .val { font-weight: 600; font-size: 0.88rem; }

  /* =========================================================
     ALERT LIST
  ========================================================== */
  .alert-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 0.86rem;
    opacity: 0;
    animation: fadeSlideUp 0.45s ease forwards;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .alert-item:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  }
  .alert-item i {
    animation: pulseDot 1.8s ease-in-out infinite;
  }

  /* =========================================================
     TABLE
  ========================================================== */
  .table-responsive {
    overflow-x: auto;
    max-height: 340px;
    overflow-y: auto;
  }
  /* scrollbar tipis biar nggak norak */
  .table-responsive::-webkit-scrollbar { width: 6px; height: 6px; }
  .table-responsive::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 10px; }
  .table-responsive::-webkit-scrollbar-track { background: transparent; }

  .app-sales-table {
    width: 100%;
    border-collapse: collapse;
  }
  .app-sales-table thead th {
    position: sticky;
    top: 0;
    background: var(--card-bg, #fff);
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    color: #999;
    padding: 10px 16px;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    z-index: 1;
  }
  .app-sales-table tbody tr {
    opacity: 0;
    animation: fadeIn 0.4s ease forwards;
    transition: background 0.2s ease;
  }
  .app-sales-table tbody tr:hover {
    background: rgba(64,153,255,0.05);
  }
  .app-sales-table tbody td {
    padding: 10px 16px;
    font-size: 0.84rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
  }
  .app-sales-table tbody tr:nth-child(1) { animation-delay: 0.05s; }
  .app-sales-table tbody tr:nth-child(2) { animation-delay: 0.1s; }
  .app-sales-table tbody tr:nth-child(3) { animation-delay: 0.15s; }
  .app-sales-table tbody tr:nth-child(4) { animation-delay: 0.2s; }
  .app-sales-table tbody tr:nth-child(5) { animation-delay: 0.25s; }
  .app-sales-table tbody tr:nth-child(n+6) { animation-delay: 0.3s; }
  .total-val { font-weight: 600; color: #2ed8b6; }

  /* empty state */
  .empty-state {
    text-align: center;
    padding: 28px 16px;
    color: #999;
    animation: fadeIn 0.6s ease;
  }
  .empty-state i {
    font-size: 1.7rem;
    display: block;
    margin-bottom: 6px;
    opacity: 0.3;
  }

  /* chart container fade */
  .chart-wrap {
    height: 250px;
    position: relative;
    opacity: 0;
    animation: fadeIn 0.7s ease 0.3s forwards;
  }

  .chart-wrap-lg {
    height: 270px;
    position: relative;
    opacity: 0;
    animation: fadeIn 0.7s ease 0.3s forwards;
  }

  /* badge kecil untuk jumlah transaksi e-toll */
  .badge-count {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    background: rgba(245, 166, 35, 0.12);
    color: #c97e0f;
    font-size: 0.76rem;
    font-weight: 600;
  }

  @media (prefers-reduced-motion: reduce) {
    * { animation: none !important; transition: none !important; }
  }
</style>
@endpush

@section('content')

  {{-- =========================================================
       3. FILTER BULAN / TAHUN
  ========================================================== --}}
<form method="GET" action="{{ route('dashboard') }}" class="card filter-card" style="margin-bottom: 18px;">

    <div class="filter-row">

        {{-- Bulan --}}
        <div class="filter-group">
            <label class="filter-label">Bulan</label>
            <select name="bulan" class="form-select">
                @foreach($bulanList as $b)
                    <option value="{{ $b['value'] }}"
                        {{ $b['value'] == $bulan ? 'selected' : '' }}>
                        {{ $b['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Tahun --}}
        <div class="filter-group">
            <label class="filter-label">Tahun</label>
            <select name="tahun" class="form-select">
                @foreach($tahunList as $t)
                    <option value="{{ $t }}"
                        {{ $t == $tahun ? 'selected' : '' }}>
                        {{ $t }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Button --}}
        <button type="submit" class="btn-report" title="Terapkan filter">
            <i class="fa-solid fa-filter"></i>
        </button>

    </div>

</form>

  {{-- =========================================================
       1 & 2. STAT CARDS: TOTAL OPERASIONAL, BBM, E-TOLL, AIR, KENDARAAN
  ========================================================== --}}
  <div class="stat-cards-grid">

    {{-- Card 1: Total Biaya Operasional --}}
    <div class="stat-card card-coral">
      <div class="stat-card-header">
        <div>
          <div class="stat-value" data-count="{{ $totalOperasionalBulanIni }}">Rp 0</div>
          <div class="stat-label">Total Operasional</div>
        </div>
        <i class="fa-solid fa-wallet stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-calendar"></i> {{ $periodeIni->translatedFormat('F Y') }}
        @if($persenPerubahan !== null)
          &bull;
          <span style="display:inline-flex; align-items:center; gap:4px;">
            <i class="fa-solid {{ $persenPerubahan > 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
            {{ abs($persenPerubahan) }}%
          </span>
        @endif
      </div>
    </div>

    {{-- Card 2: BBM (Rp + Liter) --}}
    <div class="stat-card card-emerald">
      <div class="stat-card-header">
        <div>
          <div class="stat-value" data-count="{{ $totalBbmBulanIni }}">Rp 0</div>
          <div class="stat-label">BBM &bull; {{ number_format($totalLiterBbmBulanIni, 1, ',', '.') }} L</div>
        </div>
        <i class="fa-solid fa-gas-pump stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-calendar"></i> {{ $periodeIni->translatedFormat('F Y') }}
      </div>
    </div>

    {{-- Card 3: E-Toll (BARU) --}}
    <div class="stat-card card-amber">
      <div class="stat-card-header">
        <div>
          <div class="stat-value" data-count="{{ $totalEtollBulanIni }}">Rp 0</div>
          <div class="stat-label">E-Toll &bull; {{ $pemakaianEtollPerPemegang->sum('total_transaksi') }} transaksi</div>
        </div>
        <i class="fa-solid fa-road stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-calendar"></i> {{ $periodeIni->translatedFormat('F Y') }}
      </div>
    </div>

    {{-- Card 4: Tagihan Air --}}
    <div class="stat-card card-info">
      <div class="stat-card-header">
        <div>
          <div class="stat-value" data-count="{{ $totalAirBulanIni }}">Rp 0</div>
          <div class="stat-label">Tagihan Air</div>
        </div>
        <i class="fa-solid fa-droplet stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-calendar"></i> {{ $periodeIni->translatedFormat('F Y') }}
      </div>
    </div>

    {{-- Card 5: Total Kendaraan --}}
    <div class="stat-card card-cyan">
      <div class="stat-card-header">
        <div>
          <div class="stat-value" data-count-int="{{ $jumlahKendaraan }}">0</div>
          <div class="stat-label">Kendaraan Terdaftar</div>
        </div>
        <i class="fa-solid fa-car stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-clock"></i> Total aktif
      </div>
    </div>

  </div>

  {{-- =========================================================
       9. TREN BIAYA OPERASIONAL 6 BULAN TERAKHIR (BARU)
  ========================================================== --}}
  <div class="card" style="margin-bottom: 18px;">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Tren Biaya Operasional</h3>
        <p>Total, BBM, E-Toll, dan Air &bull; 6 bulan terakhir</p>
      </div>
      <i class="fa-solid fa-chart-line stat-icon" style="color:#4099ff;"></i>
    </div>
    <div class="card-body">
      <div class="chart-wrap-lg">
        <canvas id="trenChart"></canvas>
      </div>
    </div>
  </div>

  {{-- =========================================================
       4. KOMPOSISI BIAYA + 7. HARGA BBM TERBARU
  ========================================================== --}}
  <div class="grid-row">

    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Komposisi Biaya</h3>
          <p>E-Toll, BBM, dan Tagihan Air &bull; {{ $periodeIni->translatedFormat('F Y') }}</p>
        </div>
      </div>
      <div class="card-body">
        <div class="chart-wrap">
          <canvas id="komposisiChart"></canvas>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Harga BBM Terbaru</h3>
          @if($hargaBbmTerbaru)
            <p>Berlaku sejak {{ \Carbon\Carbon::parse($hargaBbmTerbaru->tanggal_berlaku)->translatedFormat('d F Y') }}</p>
          @endif
        </div>
        <div class="card-actions">
          <i class="fa-solid fa-gas-pump stat-icon" style="color:#2ed8b6;"></i>
        </div>
      </div>
      <div class="card-body">
        @if($hargaBbmTerbaru)
          <div class="risk-details-table">
            <div class="risk-detail-item">
              <div class="lbl">Pertamax</div>
              <div class="val">Rp {{ number_format($hargaBbmTerbaru->harga_pertamax, 0, ',', '.') }}</div>
            </div>
            <div class="risk-detail-item">
              <div class="lbl">Pertamax Turbo</div>
              <div class="val">Rp {{ number_format($hargaBbmTerbaru->harga_pertamax_turbo, 0, ',', '.') }}</div>
            </div>
            <div class="risk-detail-item">
              <div class="lbl">Pertadex</div>
              <div class="val">Rp {{ number_format($hargaBbmTerbaru->harga_pertadex, 0, ',', '.') }}</div>
            </div>
            <div class="risk-detail-item">
              <div class="lbl">Dexlite</div>
              <div class="val">Rp {{ number_format($hargaBbmTerbaru->harga_dexlite, 0, ',', '.') }}</div>
            </div>
          </div>
        @else
          <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            Belum ada data harga BBM
          </div>
        @endif
      </div>
    </div>

  </div>

  {{-- =========================================================
       8. ALERT / INFORMASI OPERASIONAL
  ========================================================== --}}
  <div class="card" style="margin-bottom: 18px;">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Alert / Informasi Operasional</h3>
      </div>
    </div>
    <div class="card-body" style="display:flex; flex-direction:column; gap:8px; padding: 10px 18px 18px;">
      @php
        $alertColor = [
          'danger'  => ['bg' => '#fdecea', 'text' => '#e04141'],
          'warning' => ['bg' => '#fff6e0', 'text' => '#b9860b'],
          'success' => ['bg' => '#e8f9f2', 'text' => '#1fa971'],
        ];
      @endphp
      @foreach($alerts as $alert)
        @php $c = $alertColor[$alert['type']]; @endphp
        <div class="alert-item" style="background:{{ $c['bg'] }}; color:{{ $c['text'] }}; animation-delay: {{ $loop->index * 0.08 }}s;">
          <i class="fa-solid {{ $alert['icon'] }}"></i>
          <span>{{ $alert['text'] }}</span>
        </div>
      @endforeach
    </div>
  </div>

  {{-- =========================================================
       5. PEMAKAIAN BBM · 6B. PEMAKAIAN E-TOLL (BARU) · 6. PEMAKAIAN AIR
  ========================================================== --}}
  <div class="grid-row-3">

    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>BBM per Kendaraan</h3>
          <p>{{ $periodeIni->translatedFormat('F Y') }}</p>
        </div>
      </div>
      <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="app-sales-table">
            <thead>
              <tr>
                <th>Kendaraan</th>
                <th>Jenis</th>
                <th>Liter</th>
                <th>Total Rp</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pemakaianBbmPerKendaraan as $row)
              <tr>
                <td>{{ $row->plat_nomor }}</td>
                <td>{{ $row->nama_jenis }}</td>
                <td>{{ number_format($row->total_liter, 1, ',', '.') }} L</td>
                <td class="total-val">Rp {{ number_format($row->total_rp, 0, ',', '.') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="4">
                  <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    Belum ada pemakaian BBM di periode ini
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- =========================================================
         6B. PEMAKAIAN E-TOLL PER PEMEGANG KENDARAAN (BARU)
    ========================================================== --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>E-Toll per Pemegang</h3>
          <p>{{ $periodeIni->translatedFormat('F Y') }}</p>
        </div>
      </div>
      <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="app-sales-table">
            <thead>
              <tr>
                <th>Pemegang</th>
                <th>Transaksi</th>
                <th>Total Rp</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pemakaianEtollPerPemegang as $row)
              <tr>
                <td>{{ $row->nama }}</td>
                <td><span class="badge-count">{{ $row->total_transaksi }}x</span></td>
                <td class="total-val">Rp {{ number_format($row->total_rp, 0, ',', '.') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="3">
                  <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    Belum ada pemakaian e-toll di periode ini
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- =========================================================
         6. PEMAKAIAN AIR PER TITIK METER
    ========================================================== --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Air per Titik Meter</h3>
          <p>{{ $periodeIni->translatedFormat('F Y') }}</p>
        </div>
      </div>
      <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="app-sales-table">
            <thead>
              <tr>
                <th>Titik Meter</th>
                <th>m3</th>
                <th>Total Rp</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pemakaianAirPerTitikMeter as $row)
              <tr>
                <td>{{ $row->nama }}</td>
                <td>{{ number_format($row->total_m3, 1, ',', '.') }}</td>
                <td class="total-val">Rp {{ number_format($row->total_rp, 0, ',', '.') }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="3">
                  <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    Belum ada tagihan air di periode ini
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.body.classList.contains('dark-mode');
    const textColor = isDark ? '#94a3b8' : '#888888';

    // ---- Animated number counters on stat cards ----
    function animateCount(el, target, isCurrency) {
      const duration = 900;
      const start = performance.now();
      function tick(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        const value = Math.floor(eased * target);
        el.textContent = isCurrency
          ? 'Rp ' + value.toLocaleString('id-ID')
          : value.toLocaleString('id-ID');
        if (progress < 1) {
          requestAnimationFrame(tick);
        } else {
          el.textContent = isCurrency
            ? 'Rp ' + target.toLocaleString('id-ID')
            : target.toLocaleString('id-ID');
        }
      }
      requestAnimationFrame(tick);
    }

    document.querySelectorAll('[data-count]').forEach(function(el) {
      const target = parseFloat(el.getAttribute('data-count')) || 0;
      animateCount(el, target, true);
    });
    document.querySelectorAll('[data-count-int]').forEach(function(el) {
      const target = parseInt(el.getAttribute('data-count-int'), 10) || 0;
      animateCount(el, target, false);
    });

    // 4. Komposisi Biaya - Donut Chart
    const komposisiCtx = document.getElementById('komposisiChart');
    if (komposisiCtx) {
      new Chart(komposisiCtx, {
        type: 'doughnut',
        data: {
          labels: @json($komposisiBiaya['labels']),
          datasets: [{
            data: @json($komposisiBiaya['data']),
            backgroundColor: ['#f5a623', '#2ed8b6', '#4099ff'],
            borderWidth: 0,
            hoverOffset: 10,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '65%',
          animation: {
            animateScale: true,
            animateRotate: true,
            duration: 900,
            easing: 'easeOutCubic',
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: { font: { family: 'Poppins', size: 11 }, color: textColor, usePointStyle: true, pointStyle: 'circle' }
            },
            tooltip: {
              callbacks: {
                label: function(ctx) {
                  return ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID');
                }
              }
            }
          }
        }
      });
    }

    // 9. Tren Biaya Operasional - Line Chart (BARU)
    const trenCtx = document.getElementById('trenChart');
    if (trenCtx) {
      new Chart(trenCtx, {
        type: 'line',
        data: {
          labels: @json($trenBiaya['labels']),
          datasets: [
            {
              label: 'Total Operasional',
              data: @json($trenBiaya['total']),
              borderColor: '#4099ff',
              backgroundColor: 'rgba(64,153,255,0.10)',
              borderWidth: 3,
              tension: 0.35,
              fill: true,
              pointRadius: 4,
              pointBackgroundColor: '#4099ff',
              pointHoverRadius: 6,
            },
            {
              label: 'BBM',
              data: @json($trenBiaya['bbm']),
              borderColor: '#2ed8b6',
              backgroundColor: 'transparent',
              borderWidth: 2,
              borderDash: [4, 3],
              tension: 0.35,
              pointRadius: 3,
            },
            {
              label: 'E-Toll',
              data: @json($trenBiaya['etoll']),
              borderColor: '#f5a623',
              backgroundColor: 'transparent',
              borderWidth: 2,
              borderDash: [4, 3],
              tension: 0.35,
              pointRadius: 3,
            },
            {
              label: 'Air',
              data: @json($trenBiaya['air']),
              borderColor: '#ff5370',
              backgroundColor: 'transparent',
              borderWidth: 2,
              borderDash: [4, 3],
              tension: 0.35,
              pointRadius: 3,
            },
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          animation: { duration: 900, easing: 'easeOutCubic' },
          scales: {
            x: {
              grid: { display: false },
              ticks: { color: textColor, font: { size: 11 } }
            },
            y: {
              grid: { color: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)' },
              ticks: {
                color: textColor,
                font: { size: 11 },
                callback: function(v) {
                  return 'Rp ' + (v / 1000000).toFixed(1) + 'jt';
                }
              }
            }
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: { font: { family: 'Poppins', size: 11 }, color: textColor, usePointStyle: true, pointStyle: 'circle' }
            },
            tooltip: {
              callbacks: {
                label: function(ctx) {
                  return ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                }
              }
            }
          }
        }
      });
    }
  });
</script>
@endpush