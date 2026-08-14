@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

  {{-- STAT CARDS (TOP 4 CARDS) --}}
  <div class="stat-cards-grid">

    {{-- Card 1: E-Toll Bulan Ini --}}
    <div class="stat-card card-coral">
      <div class="stat-card-header">
        <div>
          <div class="stat-value">Rp {{ number_format($totalEtollBulanIni, 0, ',', '.') }}</div>
          <div class="stat-label">E-Toll Bulan Ini</div>
        </div>
        <i class="fa-solid fa-road stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-calendar"></i> {{ now()->translatedFormat('F Y') }}
      </div>
    </div>

    {{-- Card 2: BBM Bulan Ini --}}
    <div class="stat-card card-emerald">
      <div class="stat-card-header">
        <div>
          <div class="stat-value">Rp {{ number_format($totalBbmBulanIni, 0, ',', '.') }}</div>
          <div class="stat-label">BBM Bulan Ini</div>
        </div>
        <i class="fa-solid fa-gas-pump stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-calendar"></i> {{ now()->translatedFormat('F Y') }}
      </div>
    </div>

    {{-- Card 3: Tagihan Air Bulan Ini --}}
    <div class="stat-card card-info">
      <div class="stat-card-header">
        <div>
          <div class="stat-value">Rp {{ number_format($totalAirBulanIni, 0, ',', '.') }}</div>
          <div class="stat-label">Tagihan Air Bulan Ini</div>
        </div>
        <i class="fa-solid fa-droplet stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-calendar"></i> {{ now()->translatedFormat('F Y') }}
      </div>
    </div>

    {{-- Card 4: Total Kendaraan --}}
    <div class="stat-card card-cyan">
      <div class="stat-card-header">
        <div>
          <div class="stat-value">{{ $jumlahKendaraan }}</div>
          <div class="stat-label">Kendaraan Terdaftar</div>
        </div>
        <i class="fa-solid fa-car stat-icon"></i>
      </div>
      <div class="stat-card-footer">
        <i class="fa-regular fa-clock"></i> Total aktif
      </div>
    </div>

  </div>

  {{-- MIDDLE ROW: CHART --}}
  <div class="grid-row">

    {{-- Operasional Analytics Chart Card --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Rekap Operasional 6 Bulan Terakhir</h3>
          <p>Perbandingan transaksi E-Toll, BBM, dan Tagihan Air</p>
        </div>
        <div class="card-actions">
          <button class="action-icon-btn" title="Expand"><i class="fa-solid fa-up-right-and-down-left-from-center"></i></button>
        </div>
      </div>
      <div class="card-body" style="height: 320px; position: relative;">
        <canvas id="operasionalChart"></canvas>
      </div>
    </div>

    {{-- Ringkasan Card --}}
    <div class="card" style="position: relative;">
      <div class="card-body risk-card-body">
        <h4 style="font-size: 0.95rem; font-weight: 600; color: #555; margin-bottom: 10px;">Ringkasan Operasional</h4>

        <div class="gauge-container">
          <svg class="gauge-svg" viewBox="0 0 100 60">
            <path class="gauge-bg" d="M 10 50 A 40 40 0 0 1 90 50"></path>
            <path class="gauge-progress" d="M 10 50 A 40 40 0 0 1 90 50"></path>
          </svg>
          <div class="gauge-center-text">{{ $jumlahKendaraan }}</div>
        </div>

        <div class="risk-status">Kendaraan Aktif</div>

        <div class="risk-details-table">
          <div class="risk-detail-item">
            <div class="lbl">Bulan</div>
            <div class="val">{{ now()->translatedFormat('F') }}</div>
          </div>
          <div class="risk-detail-item">
            <div class="lbl">Tahun</div>
            <div class="val">{{ now()->year }}</div>
          </div>
        </div>

        <a href="#" class="btn-report" style="display: inline-block; text-decoration: none; text-align: center;">
          <i class="fa-solid fa-download"></i> Download Laporan
        </a>
      </div>
    </div>

  </div>

  {{-- BOTTOM ROW: LATEST TRANSACTIONS --}}
  <div class="grid-row">

    {{-- Latest E-Toll Transactions --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Transaksi E-Toll Terbaru</h3>
        </div>
      </div>
      <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="app-sales-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Nominal</th>
                <th>Dicatat Oleh</th>
              </tr>
            </thead>
            <tbody>
              @forelse($latestEtoll as $i => $etoll)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $etoll->tanggal->format('d/m/Y') }}</td>
                <td>
                  <div class="app-info">
                    <div>
                      <div class="app-title">{{ $etoll->pemegangKendaraan->nama ?? '-' }}</div>
                    </div>
                  </div>
                </td>
                <td class="total-val">Rp {{ number_format($etoll->nominal, 0, ',', '.') }}</td>
                <td>{{ $etoll->pencatat->username ?? '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                  <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                  Belum ada data transaksi E-Toll
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Latest BBM Transactions --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Transaksi BBM Terbaru</h3>
        </div>
      </div>
      <div class="card-body">
        <div class="activity-list">
          @forelse($latestBbm as $bbm)
          <div class="activity-item">
            <div class="activity-avatar-icon" style="background: linear-gradient(135deg, #2ed8b6, #59e0c5);">
              <i class="fa-solid fa-gas-pump"></i>
            </div>
            <div class="activity-content">
              <div class="activity-user-name">{{ $bbm->kendaraan->plat_nomor ?? '-' }}</div>
              <div class="activity-desc">
                Rp {{ number_format($bbm->jumlah, 0, ',', '.') }}
                &bull; {{ $bbm->kendaraan->nama_jenis ?? '' }}
              </div>
              <div class="activity-time">
                <i class="fa-regular fa-calendar"></i> {{ $bbm->tanggal->format('d/m/Y') }}
              </div>
            </div>
          </div>
          @empty
          <div style="text-align: center; padding: 30px; color: #999;">
            <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
            Belum ada data transaksi BBM
          </div>
          @endforelse
        </div>
      </div>
    </div>

  </div>

@endsection

@push('scripts')
<script>
  // Operasional Chart - 6 bulan terakhir
  document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('operasionalChart');
    if (!ctx) return;

    const isDark = document.body.classList.contains('dark-mode');
    const gridColor = isDark ? '#334155' : '#f0f4f8';
    const textColor = isDark ? '#94a3b8' : '#888888';

    const labels = @json($chartLabels);
    const etollData = @json($chartEtoll);
    const bbmData = @json($chartBbm);
    const airData = @json($chartAir);

    window.operasionalChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'E-Toll',
            data: etollData,
            backgroundColor: 'rgba(255, 83, 112, 0.8)',
            borderColor: '#FF5370',
            borderWidth: 1,
            borderRadius: 4,
            barPercentage: 0.7,
          },
          {
            label: 'BBM',
            data: bbmData,
            backgroundColor: 'rgba(46, 216, 182, 0.8)',
            borderColor: '#2ed8b6',
            borderWidth: 1,
            borderRadius: 4,
            barPercentage: 0.7,
          },
          {
            label: 'Tagihan Air',
            data: airData,
            backgroundColor: 'rgba(64, 153, 255, 0.8)',
            borderColor: '#4099ff',
            borderWidth: 1,
            borderRadius: 4,
            barPercentage: 0.7,
          },
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              font: { family: 'Poppins', size: 12 },
              color: textColor,
              usePointStyle: true,
              pointStyle: 'rectRounded',
              padding: 20,
            }
          },
          tooltip: {
            backgroundColor: '#353C4E',
            titleFont: { size: 12, family: 'Poppins' },
            bodyFont: { size: 12, family: 'Poppins' },
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
              }
            }
          }
        },
        scales: {
          x: {
            grid: { color: gridColor },
            ticks: { font: { family: 'Poppins', size: 11 }, color: textColor }
          },
          y: {
            grid: { color: gridColor },
            ticks: {
              font: { family: 'Poppins', size: 11 },
              color: textColor,
              callback: function(value) {
                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                return 'Rp ' + value;
              }
            }
          }
        }
      }
    });
  });
</script>
@endpush