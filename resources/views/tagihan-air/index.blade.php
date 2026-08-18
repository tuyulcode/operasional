@extends('layouts.app')

@section('title', 'Tagihan Air')

@section('content')

  <div class="page-header">
    <div class="page-title">Tagihan Air</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Transaksi</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Tagihan Air</li>
    </ul>
  </div>

  @if(session('success'))
    <div class="alert-custom alert-success">
      <i class="fa-solid fa-circle-check"></i>
      <span>{{ session('success') }}</span>
    </div>
  @endif

  @if(session('error'))
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ session('error') }}</span>
    </div>
  @endif

  @if($errors->any())
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <div class="tabs">
    <a href="{{ route('tagihan-air.index', ['tab' => 'input']) }}"
       class="tab-link {{ ($tab ?? 'input') === 'input' ? 'active' : '' }}">
      <i class="fa-solid fa-table-list"></i> Input Data
    </a>
    <a href="{{ route('tagihan-air.index', ['tab' => 'rekapan']) }}"
       class="tab-link {{ ($tab ?? 'input') === 'rekapan' ? 'active' : '' }}">
      <i class="fa-solid fa-file-invoice"></i> Rekapan
    </a>
  </div>

  @if(($tab ?? 'input') === 'input')

  {{-- FILTER --}}
  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Filter</h3>
        <p>Saring daftar tagihan berdasarkan area dan periode</p>
      </div>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('tagihan-air.index') }}">
        <div class="form-grid">
          <div class="form-group">
            <label for="filter_area_id">Area</label>
            <select id="filter_area_id" name="area_id" class="form-control">
              <option value="">-- Semua Area --</option>
              @foreach($areas as $area)
                <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                  {{ $area->nama }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="filter_bulan">Bulan / Tahun</label>
            <input type="month" id="filter_bulan" name="bulan" class="form-control"
                   value="{{ request('bulan') }}">
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter"></i> Terapkan Filter
          </button>
          <a href="{{ route('tagihan-air.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-rotate-left"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- FORM INPUT --}}
  <div class="card tagihan-form">
    <div class="card-header">
      <div class="card-header-title">
        <h3>{{ $edit ? 'Edit Tagihan Air' : 'Form Tagihan Air' }}</h3>
          <p>{{ $edit ? 'Perbarui data tagihan air' : 'Input meteran bulanan tagihan air' }}</p>
        </div>
      </div>
      <div class="card-body">
        <form id="tagihanForm" method="POST" enctype="multipart/form-data"
              action="{{ $edit ? route('tagihan-air.update', $edit->id) : route('tagihan-air.store') }}">
          @csrf
          <input type="hidden" name="_method" id="tagihanMethod" value="{{ $edit ? 'PUT' : '' }}">

          <div class="form-grid">
            <div class="form-group">
              <label for="area_id">Area</label>
              <select id="area_id" name="area_id" class="form-control" required>
                <option value="">-- Pilih Area --</option>
                @foreach($areas as $area)
                  <option value="{{ $area->id }}" {{ old('area_id', $edit->area_id ?? '') == $area->id ? 'selected' : '' }}>
                    {{ $area->nama }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="titik_meter_id">Titik Meter</label>
              <select id="titik_meter_id" name="titik_meter_id" class="form-control" required>
                <option value="">-- Pilih Titik Meter --</option>
                @foreach($titikMeters as $tm)
                  <option value="{{ $tm->id }}"
                          data-area="{{ $tm->area_id }}"
                          data-faktor="{{ $tm->meter_faktor }}"
                          data-tarif="{{ $tm->tarif_harga }}"
                          {{ old('titik_meter_id', $edit->titik_meter_id ?? '') == $tm->id ? 'selected' : '' }}>
                    {{ $tm->nama }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="periode">Periode</label>
              <input type="month" id="periode" name="periode" class="form-control"
                     value="{{ old('periode', $edit ? $edit->periode->format('Y-m') : '') }}" required>
            </div>

            <div class="form-group">
              <label for="meter_lalu">Meter Lalu</label>
              <input type="number" id="meter_lalu" name="meter_lalu" class="form-control"
                     step="0.01" min="0"
                     value="{{ old('meter_lalu', $edit->meter_lalu ?? '') }}">
              <small id="meterLaluHint" style="color: #999;">Otomatis dari transaksi periode sebelumnya</small>
            </div>

            <div class="form-group">
              <label for="meter_ini">Meter Ini</label>
              <input type="number" id="meter_ini" name="meter_ini" class="form-control"
                     step="0.01" min="0" placeholder="Contoh: 120"
                     value="{{ old('meter_ini', $edit->meter_ini ?? '') }}" required>
            </div>

            <div class="form-group">
              <label for="meter_faktor">Meter Faktor</label>
              <input type="number" id="meter_faktor" name="meter_faktor" class="form-control"
                     step="0.01" min="0" placeholder="Contoh: 1"
                     value="{{ old('meter_faktor', $edit->meter_faktor ?? '1') }}" required>
            </div>

            <div class="form-group">
              <label for="tarif">Tarif (Rp/m3)</label>
              <input type="text" id="tarif" name="tarif" class="form-control"
                     inputmode="numeric" placeholder="Contoh: 5.000"
                     value="{{ old('tarif', $edit ? number_format($edit->tarif, 2, ',', '.') : '') }}" required>
            </div>

            <div class="form-group">
              <label for="foto">Foto Meter (opsional)</label>
              <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
              @if($edit && $edit->foto)
                <small style="color: #999;">Foto saat ini:
                  <a href="{{ asset($edit->foto) }}" target="_blank">{{ basename($edit->foto) }}</a>
                  — pilih file baru untuk mengganti.
                </small>
              @endif
            </div>

            <div class="form-group">
              <label for="pemakaian">Pemakaian Terkoreksi (m3)</label>
              <input type="text" id="pemakaian" class="form-control" readonly
                     value="{{ $edit ? number_format($edit->pemakaian, 2, ',', '.') : '' }}">
              <small style="color: #999;">(Meter Ini − Meter Lalu) × Meter Faktor — otomatis</small>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
              <label for="jumlah">Jumlah (Rp)</label>
              <input type="text" id="jumlah" class="form-control" readonly
                     value="{{ $edit ? 'Rp ' . number_format($edit->jumlah, 0, ',', '.') : '' }}">
              <small style="color: #999;">Pemakaian × Tarif — otomatis</small>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> {{ $edit ? 'Simpan Perubahan' : 'Simpan' }}
            </button>
            @if($edit)
              <a href="{{ route('tagihan-air.index', request()->only(['area_id', 'bulan'])) }}" class="btn btn-secondary">
                <i class="fa-solid fa-ban"></i> Batal Edit
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>

    {{-- TABEL --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Data Tagihan Air</h3>
          <p>Daftar tagihan air yang tersimpan</p>
        </div>
      </div>
      <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="app-sales-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Periode</th>
                <th>Area</th>
                <th>Titik Meter</th>
                <th>Meter Lalu</th>
                <th>Meter Ini</th>
                <th>Faktor</th>
                <th>Tarif</th>
                <th>Pemakaian</th>
                <th>Jumlah</th>
                <th>Foto</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($tagihanAirs as $i => $t)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->periode->format('m-Y') }}</td>
                <td>{{ $t->titikMeter->area->nama ?? '-' }}</td>
                <td>
                  <div class="app-info">
                    <div>
                      <div class="app-title">{{ $t->titikMeter->nama ?? '-' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ number_format($t->meter_lalu, 2, ',', '.') }}</td>
                <td>{{ number_format($t->meter_ini, 2, ',', '.') }}</td>
                <td>{{ $t->meter_faktor }}</td>
                <td>Rp {{ number_format($t->tarif, 2, ',', '.') }}</td>
                <td>{{ number_format($t->pemakaian, 2, ',', '.') }}</td>
                <td>Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                <td>
                  @if($t->foto)
                    <a href="{{ asset($t->foto) }}" target="_blank" title="Lihat foto">
                      <i class="fa-solid fa-image" style="color: #4361ee;"></i>
                    </a>
                  @else
                    -
                  @endif
                </td>
                <td>
                  <a href="{{ route('tagihan-air.index', array_merge(request()->query(), ['edit' => $t->id])) }}"
                     class="btn btn-icon btn-edit" title="Edit">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <form action="{{ route('tagihan-air.destroy', ['id' => $t->id, 'area_id' => request('area_id'), 'bulan' => request('bulan')]) }}"
                        method="POST" style="display: inline;"
                        onsubmit="return confirm('Yakin ingin menghapus tagihan air ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-icon btn-delete" title="Hapus">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="12" style="text-align: center; padding: 30px; color: #999;">
                  <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                  Belum ada data tagihan air
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  @else

    @include('tagihan-air.rekapan', ['report' => $report])

  @endif

@endsection

@if(($tab ?? 'input') === 'input')
@push('scripts')
<script>
  const meterMap = @json($meterMap);

  const areaSelect = document.getElementById('area_id');
  const tmSelect = document.getElementById('titik_meter_id');
  const periodeInput = document.getElementById('periode');
  const meterLaluInput = document.getElementById('meter_lalu');
  const meterIniInput = document.getElementById('meter_ini');
  const meterFaktorInput = document.getElementById('meter_faktor');
  const tarifInput = document.getElementById('tarif');
  const pemakaianInput = document.getElementById('pemakaian');
  const jumlahInput = document.getElementById('jumlah');

  function formatNumber(value, decimals) {
    if (isNaN(value)) return '0';
    const opts = decimals !== undefined
      ? { minimumFractionDigits: decimals, maximumFractionDigits: decimals }
      : { maximumFractionDigits: 0 };
    return value.toLocaleString('id-ID', opts);
  }

  function formatRupiah(input) {
    const v = parseIdValue(input.value);
    input.value = v ? v.toLocaleString('id-ID', { maximumFractionDigits: 2 }) : '';
  }

  function parseIdValue(str) {
    if (!str) return 0;
    let s = String(str).trim();
    if (!s || !/^-?\d[\d.,]*$/.test(s)) return 0;
    if (s.includes(',')) {
      s = s.replace(/\./g, '').replace(',', '.');
    } else if (/^-?\d{1,3}(\.\d{3})+$/.test(s)) {
      s = s.replace(/\./g, '');
    }
    const v = parseFloat(s);
    return isNaN(v) ? 0 : v;
  }

  function filterTitikMeter() {
    const area = areaSelect.value;
    for (const opt of tmSelect.options) {
      if (opt.value === '') continue;
      opt.style.display = (!area || opt.dataset.area === area) ? '' : 'none';
    }
    if (tmSelect.value) {
      const selected = tmSelect.selectedOptions[0];
      if (selected && selected.dataset.area !== area) {
        tmSelect.value = '';
      }
    }
  }

  function updateMeterLaluState(overwrite) {
    const tm = tmSelect.value;
    const per = periodeInput.value;
    const hint = document.getElementById('meterLaluHint');
    if (!tm || !per) {
      meterLaluInput.readOnly = true;
      if (hint) hint.textContent = 'Pilih titik meter & periode terlebih dahulu.';
      return;
    }
    const hist = meterMap[tm] || {};
    let latest = null;
    for (const p in hist) {
      if (p < per && (!latest || p > latest)) latest = p;
    }
    if (latest) {
      if (overwrite) meterLaluInput.value = hist[latest];
      meterLaluInput.readOnly = true;
      if (hint) hint.textContent = 'Otomatis dari transaksi periode sebelumnya.';
    } else {
      if (overwrite) meterLaluInput.value = '';
      meterLaluInput.readOnly = false;
      if (hint) hint.textContent = 'Belum ada histori periode sebelumnya di sistem. Isi Meter Lalu secara manual berdasarkan data awal (wajib).';
    }
    recalcTotals();
  }

  function fillMeterLalu() {
    updateMeterLaluState(true);
  }

  function recalcTotals() {
    const ini = parseIdValue(meterIniInput.value);
    const lalu = parseIdValue(meterLaluInput.value);
    const faktor = parseIdValue(meterFaktorInput.value);
    const tarif = parseIdValue(tarifInput.value);
    const pemakaian = (ini - lalu) * faktor;
    pemakaianInput.value = formatNumber(pemakaian, 2);
    jumlahInput.value = 'Rp ' + formatNumber(pemakaian * tarif);
  }

  function autoFillMaster() {
    const opt = tmSelect.selectedOptions[0];
    if (!opt) return;
    meterFaktorInput.value = opt.dataset.faktor;
    tarifInput.value = opt.dataset.tarif ? Number(opt.dataset.tarif).toLocaleString('id-ID', { maximumFractionDigits: 2 }) : '';
    fillMeterLalu();
    recalcTotals();
  }

  document.addEventListener('DOMContentLoaded', function() {
    filterTitikMeter();
    updateMeterLaluState(false);

    areaSelect.addEventListener('change', filterTitikMeter);
    tmSelect.addEventListener('change', autoFillMaster);
    periodeInput.addEventListener('change', fillMeterLalu);
    meterIniInput.addEventListener('input', recalcTotals);
    meterLaluInput.addEventListener('input', recalcTotals);
    meterFaktorInput.addEventListener('input', recalcTotals);
    tarifInput.addEventListener('input', function() {
      formatRupiah(tarifInput);
      recalcTotals();
    });

    const form = document.getElementById('tagihanForm');
    form.addEventListener('submit', function() {
      tarifInput.value = String(parseIdValue(tarifInput.value));
    });
  });
</script>
@endpush
@endif