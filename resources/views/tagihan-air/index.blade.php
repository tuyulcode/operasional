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

  @if($errors->any())
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>
        @foreach($errors->all() as $error)
          {{ $error }}@if(!$loop->last)<br>@endif
        @endforeach
      </span>
    </div>
  @endif

  <div class="tabs">
    <a href="{{ route('tagihan-air.index', ['tab' => 'input']) }}"
       class="tab-link {{ ($tab ?? 'input') === 'input' ? 'active' : '' }}">
      <i class="fa-solid fa-table-list"></i> Input Data
    </a>
    <a href="{{ route('tagihan-air.index', ['tab' => 'data']) }}"
       class="tab-link {{ ($tab ?? 'input') === 'data' ? 'active' : '' }}">
      <i class="fa-solid fa-database"></i> Data Tagihan Air
    </a>
    <a href="{{ route('tagihan-air.index', ['tab' => 'rekapan']) }}"
       class="tab-link {{ ($tab ?? 'input') === 'rekapan' ? 'active' : '' }}">
      <i class="fa-solid fa-file-invoice"></i> Rekapan
    </a>
  </div>

  @if(($tab ?? 'input') === 'input')

  {{-- FORM INPUT --}}
  <div class="card tagihan-form">
    <div class="card-header">
      <div class="card-header-title">
        <h3>{{ $edit ? 'Edit Tagihan Air' : 'Form Tagihan Air' }}</h3>
        <p>{{ $edit ? 'Perbarui data tagihan air' : 'Input meteran bulanan tagihan air' }}</p>
      </div>
    </div>
    <div class="card-body">
      <form id="tagihanForm" class="ajax-form" method="POST" enctype="multipart/form-data"
            action="{{ $edit ? route('tagihan-air.update', $edit->id) : route('tagihan-air.store') }}">
        @csrf
        <input type="hidden" name="_method" id="tagihanMethod" value="{{ $edit ? 'PUT' : '' }}">

          <div class="form-grid">
            <div class="form-group">
              <label for="area_id">Nama Pengguna</label>
              <select id="area_id" name="area_id" class="form-control" required>
                <option value="">-- Pilih Nama Pengguna --</option>
                @foreach($areas as $area)
                  <option value="{{ $area->id }}"
                          data-kena-ppn="{{ $area->kena_ppn ? 1 : 0 }}"
                          {{ old('area_id', $edit->titikMeter->area_id ?? '') == $area->id ? 'selected' : '' }}>
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
              <label>Periode <span style="color: #e11d48;">*</span></label>
              <input type="hidden" id="periode" name="periode"
                     value="{{ old('periode', $edit ? $edit->periode->format('Y-m') : '') }}">
            </div>

            <div class="form-group">
              <label for="meter_faktor">Meter Faktor</label>
              <input type="number" id="meter_faktor" name="meter_faktor" class="form-control"
                     step="0.01" min="0" readonly
                     value="{{ old('meter_faktor', $edit->meter_faktor ?? '1') }}">
            </div>

            <div class="form-group">
              <label for="meter_lalu">Meter Bulan Lalu</label>
              <input type="number" id="meter_lalu" name="meter_lalu" class="form-control"
                     step="0.01" min="0"
                     value="{{ old('meter_lalu', $edit->meter_lalu ?? '') }}">
              <small id="meterLaluHint" style="color: #999;">Otomatis dari transaksi periode sebelumnya</small>
            </div>

            <div class="form-group">
              <label for="meter_ini">Meter Bulan Ini</label>
              <input type="number" id="meter_ini" name="meter_ini" class="form-control"
                     step="0.01" min="0" placeholder="Contoh: 120"
                     value="{{ old('meter_ini', $edit->meter_ini ?? '') }}" required>
            </div>

            <div class="form-group">
              <label for="tarif">Tarif (Rp/m³)</label>
              <input type="text" id="tarif" name="tarif" class="form-control"
                     inputmode="numeric" readonly
                     value="{{ old('tarif', $edit ? number_format($edit->tarif, 2, ',', '.') : '') }}">
            </div>

            <div class="form-group">
              <label for="foto_meter">Foto Meter (opsional, maksimal 10 foto)</label>
              <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <button type="button" id="btnAddFoto" class="btn btn-secondary btn-sm">
                  <i class="fa-solid fa-plus"></i> Tambah Foto
                </button>
                <small id="photoCapNote" style="color: #999;"></small>
              </div>
              <input type="file" id="foto_picker" name="foto_meter[]"
                     accept="image/jpeg,image/png" style="display: none;">
              <small style="color: #999;">Pilih foto (jpg/jpeg/png, maks 5 MB per file).</small>
              <div id="pendingFotoPreview" style="display: none; margin-top: 8px; flex-wrap: wrap; gap: 8px;"></div>

              <div id="fotoError" style="color: #dc3545; font-size: 12px; margin-top: 4px;"></div>

              @if($edit && $edit->fotos->count())
                <div id="oldFotoSection" style="margin-top: 8px;">
                  <small style="color: #999;">Foto tersimpan (klik hapus untuk menghapus):</small>
                  <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px;">
                    @foreach($edit->fotos as $f)
                      <div style="position: relative; display: inline-block;">
                        <img src="{{ $f->url }}" alt="Foto meter"
                             style="width: 90px; height: 70px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">
                        <button type="button" class="btn btn-icon btn-delete btn-delete-foto"
                                data-url="{{ route('tagihan-air.foto.destroy', $f->id) }}"
                                data-token="{{ csrf_token() }}"
                                title="Hapus foto ini"
                                style="position: absolute; top: 2px; right: 2px; margin: 0; padding: 2px 5px; font-size: 11px;">
                          <i class="fa-solid fa-trash-can"></i>
                        </button>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif
            </div>

            <div class="form-group">
              <label for="pemakaian">Jumlah Pengambilan</label>
              <input type="text" id="pemakaian" class="form-control" readonly
                     value="{{ $edit ? number_format($edit->pemakaian, 2, ',', '.') : '' }}">
              <small style="color: #999;">(Meter Bulan Ini - Meter Bulan Lalu) x Meter Faktor</small>
            </div>

            <div class="form-group">
              <label for="jumlah_sebelum_ppn">Jumlah Sebelum PPN (Rp)</label>
              <input type="text" id="jumlah_sebelum_ppn" class="form-control" readonly
                     value="{{ $edit ? 'Rp ' . number_format($edit->jumlah - $edit->ppn_nominal, 0, ',', '.') : '' }}">
              <small style="color: #999;">Jumlah Pengambilan x Tarif</small>
            </div>

            <div class="form-group">
              <label for="ppn_persentase">PPN (%)</label>
              <input type="text" id="ppn_persentase" class="form-control" readonly
                     value="{{ $edit ? number_format($edit->ppn_persentase, 2, ',', '.') : '' }}">
              <small style="color: #999;">Persentase PPN yang berlaku</small>
            </div>

            <div class="form-group">
              <label for="ppn_nominal">PPN (Rp)</label>
              <input type="text" id="ppn_nominal" class="form-control" readonly
                     value="{{ $edit ? 'Rp ' . number_format($edit->ppn_nominal, 0, ',', '.') : '' }}">
              <small style="color: #999;">Jumlah Sebelum PPN x PPN(%)</small>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
              <label for="jumlah">Jumlah (Rp)</label>
              <input type="text" id="jumlah" class="form-control" readonly
                     value="{{ $edit ? 'Rp ' . number_format($edit->jumlah, 0, ',', '.') : '' }}">
              <small style="color: #999;">Jumlah Sebelum PPN + PPN(Rp)</small>
            </div>
          </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> {{ $edit ? 'Simpan Perubahan' : 'Simpan' }}
          </button>
          @if($edit)
            <a href="{{ route('tagihan-air.index', ['tab' => 'input']) }}"
               class="btn btn-secondary" id="btnBatalEdit">
              <i class="fa-solid fa-ban"></i> Batal Edit
            </a>
          @endif
        </div>
      </form>

    </div>
  </div>

  @elseif(($tab ?? 'input') === 'data')

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
          <input type="text" id="quickSearch" class="form-control"
                 placeholder="Cari area, titik meter, atau periode..."
                 style="max-width: 340px; margin: 12px 12px 8px;">
          <table class="app-sales-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Periode</th>
                <th>Nama Pengguna</th>
                <th>Titik Meter</th>
                <th>Meter Bulan Lalu</th>
                <th>Meter Bulan Ini</th>
                <th>Faktor</th>
                <th>Tarif</th>
                <th>Jml Pengambilan</th>
                <th>PPN (%)</th>
                <th>PPN (Rp)</th>
                <th>Jumlah</th>
                <th>Foto</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="tbData">
              @forelse($tagihanAirs as $i => $t)
              <tr>
                <td>{{ $tagihanAirs->firstItem() + $i }}</td>
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
                <td>{{ $t->ppn_persentase > 0 ? number_format($t->ppn_persentase, 2, ',', '.') : '-' }}</td>
                <td>{{ $t->ppn_nominal > 0 ? 'Rp ' . number_format($t->ppn_nominal, 0, ',', '.') : '-' }}</td>
                <td>Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                <td>
                  @if($t->fotos->count())
                    <div style="display: flex; flex-wrap: wrap; gap: 4px; max-width: 180px;">
                      @foreach($t->fotos as $f)
                        <a href="{{ $f->url }}" target="_blank" title="Lihat foto">
                          <img src="{{ $f->url }}" alt="Foto"
                               style="width: 40px; height: 32px; object-fit: cover; border: 1px solid #ddd; border-radius: 3px;">
                        </a>
                      @endforeach
                    </div>
                  @else
                    -
                  @endif
                </td>
                <td>
                  <a href="{{ route('tagihan-air.index', ['tab' => 'input', 'edit' => $t->id]) }}"
                     class="btn btn-icon btn-edit" title="Edit">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <form action="{{ route('tagihan-air.destroy', ['id' => $t->id, 'tab' => 'data']) }}"
                        method="POST" class="delete-tagihan-form ajax-form" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-icon btn-delete btnConfirmDelete" title="Hapus">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="14" style="text-align: center; padding: 30px; color: #999;">
                  <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                  Belum ada data tagihan air
                </td>
              </tr>
              @endforelse
              <tr id="noSearchRow" style="display: none;">
                <td colspan="14" style="text-align: center; padding: 30px; color: #999;">
                  <i class="fa-solid fa-magnifying-glass" style="font-size: 1.6rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                  Tidak ada data yang cocok
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        @include('partials.pagination', ['paginator' => $tagihanAirs])
      </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div class="modal-overlay" id="hapusModal">
      <div class="modal" style="max-width: 420px;">
        <div class="modal-header">
          <h3 class="modal-title"><i class="fa-solid fa-triangle-exclamation" style="color: var(--danger-color); margin-right: 8px;"></i> Konfirmasi Hapus</h3>
          <button type="button" class="modal-close" onclick="tutupHapusModal()">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div class="modal-body">
          <p style="margin: 0; color: #475569; line-height: 1.6;">
            Yakin ingin menghapus data tagihan air ini? Tindakan ini tidak dapat dibatalkan.
          </p>
          <p id="hapusModalInfo" style="margin: 8px 0 0; padding: 8px; background: #fef2f2; border-radius: 6px; color: #991b1b; font-size: 13px; line-height: 1.6;"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="tutupHapusModal()">
            Batal
          </button>
          <button type="button" class="btn btn-danger" id="btnYaHapus">
            <i class="fa-solid fa-trash-can"></i> Ya, Hapus
          </button>
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
  let oldFotoCount = {{ $edit ? $edit->fotos->count() : 0 }};
  const MAX_FOTO = 10;

  const areaSelect = document.getElementById('area_id');
  const tmSelect = document.getElementById('titik_meter_id');
  const periodeInput = document.getElementById('periode');
  const meterLaluInput = document.getElementById('meter_lalu');
  const meterIniInput = document.getElementById('meter_ini');
  const meterFaktorInput = document.getElementById('meter_faktor');
  const tarifInput = document.getElementById('tarif');
  const pemakaianInput = document.getElementById('pemakaian');
  const jumlahSebelumPpnInput = document.getElementById('jumlah_sebelum_ppn');
  const ppnPersentaseInput = document.getElementById('ppn_persentase');
  const ppnNominalInput = document.getElementById('ppn_nominal');
  const jumlahInput = document.getElementById('jumlah');
  const defaultPpnPersen = '{{ $ppnAktif->persentase ?? 0 }}';

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

  function formatIdLive(str) {
    let s = String(str == null ? '' : str);
    let neg = s.startsWith('-') ? '-' : '';
    s = s.replace(/[^\d,]/g, '');
    const ci = s.indexOf(',');
    let intPart = ci === -1 ? s : s.slice(0, ci);
    let decPart = ci === -1 ? '' : s.slice(ci + 1).slice(0, 2);
    if (!decPart) decPart = '';
    intPart = intPart.replace(/^0+(?=\d)/, '');
    const grouped = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return neg + grouped + (ci !== -1 ? ',' + decPart : '');
  }

  function bindLiveFormat(input) {
    input.addEventListener('input', function() {
      const start = input.selectionStart || 0;
      const oldVal = input.value;
      const newVal = formatIdLive(oldVal);
      if (newVal === oldVal) return;
      const sigBefore = oldVal.slice(0, start).replace(/[^\d,]/g, '').length;
      input.value = newVal;
      let pos = 0;
      let seen = 0;
      while (pos < newVal.length && seen < sigBefore) {
        if (/[\d,]/.test(newVal[pos])) seen++;
        pos++;
      }
      input.setSelectionRange(pos, pos);
    });
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
      if (hint) hint.textContent = 'Belum ada histori periode sebelumnya di sistem. Isi Meter Bulan Lalu secara manual berdasarkan data awal (wajib).';
    }
    recalcTotals();
  }

  function fillMeterLalu() {
    updateMeterLaluState(true);
  }

  function recalcTotals() {
    // Jika Meter Bulan Ini kosong, reset semua field kalkulasi
    if (!meterIniInput.value.trim()) {
        pemakaianInput.value = '';
        jumlahSebelumPpnInput.value = '';
        ppnNominalInput.value = '';
        jumlahInput.value = '';
        return;
    }

    const ini = parseIdValue(meterIniInput.value);
    const lalu = parseIdValue(meterLaluInput.value);
    const faktor = parseIdValue(meterFaktorInput.value);
    const tarif = parseIdValue(tarifInput.value);

    if (lalu > 0 && ini < lalu) {
      showToast('Peringatan: Meter Bulan Ini (' + formatNumber(ini, 0) + ') kurang dari Meter Bulan Lalu (' + formatNumber(lalu, 0) + ')', 'error');
    }

    const pemakaian = (ini - lalu) * faktor;
    const jumlahSebelumPpn = pemakaian * tarif;
    const ppnPersen = parseIdValue(ppnPersentaseInput.value);
    const ppnNominal = Math.round(jumlahSebelumPpn * ppnPersen / 100 * 100) / 100;
    const jumlah = jumlahSebelumPpn + ppnNominal;

    pemakaianInput.value = formatNumber(pemakaian, 2);
    jumlahSebelumPpnInput.value = 'Rp ' + formatNumber(jumlahSebelumPpn, 0);
    ppnNominalInput.value = 'Rp ' + formatNumber(ppnNominal, 0);
    jumlahInput.value = 'Rp ' + formatNumber(jumlah, 0);
  }

  function autoFillMaster() {
    const opt = tmSelect.selectedOptions[0];
    if (!opt) return;
    meterFaktorInput.value = opt.dataset.faktor;
    tarifInput.value = opt.dataset.tarif ? Number(opt.dataset.tarif).toLocaleString('id-ID', { maximumFractionDigits: 2 }) : '';
    fillMeterLalu();
    recalcTotals();
  }

  // ---- Foto (tambah satu-per-satu, maksimal 10) ----
  let pendingFotos = []; // { file, url }

  function fotoSlotsLeft() {
    return MAX_FOTO - oldFotoCount - pendingFotos.length;
  }

  function updateFotoCapState() {
    const btn = document.getElementById('btnAddFoto');
    const note = document.getElementById('photoCapNote');
    const left = fotoSlotsLeft();
    btn.disabled = left <= 0;
    btn.style.opacity = left <= 0 ? '0.5' : '';
    btn.style.cursor = left <= 0 ? 'not-allowed' : '';
    note.textContent = left <= 0 ? 'Maksimal 10 foto' : '';
  }

  function setFotoError(msg) {
    const el = document.getElementById('fotoError');
    el.textContent = msg;
    if (msg) {
      setTimeout(function() {
        if (el.textContent === msg) el.textContent = '';
      }, 3000);
    }
  }

  function renderPendingFotos() {
    const preview = document.getElementById('pendingFotoPreview');
    preview.innerHTML = '';
    if (!pendingFotos.length) {
      preview.style.display = 'none';
      return;
    }
    pendingFotos.forEach(function(p, idx) {
      const wrap = document.createElement('div');
      wrap.style.cssText = 'position: relative; display: inline-block;';
      const img = document.createElement('img');
      img.src = p.url;
      img.style.cssText = 'width: 90px; height: 70px; object-fit: cover; border: 1px solid #0d6efd; border-radius: 4px;';
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.innerHTML = '&times;';
      btn.title = 'Buang foto ini dari pilihan';
      btn.style.cssText = 'position: absolute; top: 2px; right: 2px; padding: 0 5px; font-size: 12px; line-height: 18px; cursor: pointer; border-radius: 3px;';
      btn.addEventListener('click', function() {
        URL.revokeObjectURL(p.url);
        pendingFotos.splice(idx, 1);
        renderPendingFotos();
        updateFotoCapState();
      });
      wrap.appendChild(img);
      wrap.appendChild(btn);
      preview.appendChild(wrap);
    });
    preview.style.display = 'flex';
  }

  function syncFotoInput() {
    if (!pendingFotos.length) return;
    const dt = new DataTransfer();
    pendingFotos.forEach(function(p) { dt.items.add(p.file); });
    document.getElementById('foto_picker').files = dt.files;
  }

  // ---- Kompres foto baru via Canvas sebelum masuk pendingFotos ----
  const MAX_DIM = 1920;
  const MAX_FOTO_SIZE = 5 * 1024 * 1024;
  const COMPRESS_START_QUALITY = 0.75;
  const COMPRESS_MIN_QUALITY = 0.4;

  function compressFoto(file) {
    return new Promise(function(resolve, reject) {
      const url = URL.createObjectURL(file);
      const img = new Image();
      img.onload = function() {
        URL.revokeObjectURL(url);
        try {
          let w = img.naturalWidth || img.width;
          let h = img.naturalHeight || img.height;
          const scale = Math.min(1, MAX_DIM / Math.max(w, h));
          w = Math.max(1, Math.round(w * scale));
          h = Math.max(1, Math.round(h * scale));
          const canvas = document.createElement('canvas');
          canvas.width = w;
          canvas.height = h;
          const ctx = canvas.getContext('2d');
          ctx.fillStyle = '#fff';
          ctx.fillRect(0, 0, w, h);
          ctx.drawImage(img, 0, 0, w, h);
          const isPng = /\.png$/i.test(file.name) || file.type === 'image/png';
          const mime = isPng ? 'image/png' : 'image/jpeg';
          const attempt = function(quality) {
            canvas.toBlob(function(blob) {
              if (!blob) {
                if (file.size > MAX_FOTO_SIZE) { reject('Ukuran foto maksimal 5 MB.'); return; }
                resolve({ file: file, url: URL.createObjectURL(file) });
                return;
              }
              if (blob.size > MAX_FOTO_SIZE && quality > COMPRESS_MIN_QUALITY) {
                attempt(quality - 0.15);
                return;
              }
              if (blob.size > MAX_FOTO_SIZE) {
                reject('Ukuran foto melebihi 5 MB setelah dikompres.');
                return;
              }
              const newFile = new File([blob], file.name, { type: mime, lastModified: file.lastModified });
              resolve({ file: newFile, url: URL.createObjectURL(newFile) });
            }, mime, quality);
          };
          attempt(COMPRESS_START_QUALITY);
        } catch (err) {
          if (file.size > MAX_FOTO_SIZE) { reject('Ukuran foto maksimal 5 MB.'); return; }
          resolve({ file: file, url: URL.createObjectURL(file) });
        }
      };
      img.onerror = function() {
        URL.revokeObjectURL(url);
        if (file.size > MAX_FOTO_SIZE) { reject('Ukuran foto maksimal 5 MB.'); return; }
        resolve({ file: file, url: URL.createObjectURL(file) });
      };
      img.src = url;
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    filterTitikMeter();

    window.pickerPeriode = new MonthYearPicker({
      hiddenId: 'periode',
      onChange: function(val) {
        periodeInput.dispatchEvent(new Event('change'));
      }
    });

    updateMeterLaluState(false);
    bindLiveFormat(tarifInput);
    formatRupiah(tarifInput);

    areaSelect.addEventListener('change', filterTitikMeter);
    areaSelect.addEventListener('change', function() {
      const opt = areaSelect.selectedOptions[0];
      const kenaPpn = opt && opt.dataset.kenaPpn === '1';
      ppnPersentaseInput.value = kenaPpn ? defaultPpnPersen : '0';
      recalcTotals();
    });
    tmSelect.addEventListener('change', autoFillMaster);
    periodeInput.addEventListener('change', fillMeterLalu);
    meterIniInput.addEventListener('input', recalcTotals);
    meterLaluInput.addEventListener('input', recalcTotals);
    meterFaktorInput.addEventListener('input', recalcTotals);
    tarifInput.addEventListener('input', recalcTotals);
    tarifInput.addEventListener('blur', function() {
      formatRupiah(tarifInput);
    });

    const btnAddFoto = document.getElementById('btnAddFoto');
    const fotoPicker = document.getElementById('foto_picker');
    btnAddFoto.addEventListener('click', function() {
      if (btnAddFoto.disabled) return;
      fotoPicker.click();
    });
    fotoPicker.addEventListener('change', function(e) {
      const file = e.target.files[0];
      fotoPicker.value = '';
      if (!file) return;
      const okType = /\.(jpe?g|png)$/i.test(file.name) || ['image/jpeg', 'image/png'].includes(file.type);
      if (!okType) { setFotoError('Hanya file jpg / jpeg / png.'); return; }
      if (fotoSlotsLeft() <= 0) { setFotoError('Maksimal 10 foto per transaksi.'); return; }
      compressFoto(file).then(function(result) {
        if (fotoSlotsLeft() <= 0) {
          URL.revokeObjectURL(result.url);
          setFotoError('Maksimal 10 foto per transaksi.');
          return;
        }
        pendingFotos.push({ file: result.file, url: result.url });
        renderPendingFotos();
        updateFotoCapState();
      }).catch(function(errMsg) {
        setFotoError(errMsg);
      });
    });

    const form = document.getElementById('tagihanForm');

    // Custom AJAX submit handler for Tagihan Air form
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      e.stopPropagation();

      var submitBtn = form.querySelector('[type="submit"]');
      var originalHtml = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
      }

      // Sync before submit
      tarifInput.value = String(parseIdValue(tarifInput.value));
      syncFotoInput();

      var formData = new FormData(form);

      fetch(form.action, {
        method: form.method || 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(function(response) {
        return response.json().then(function(data) {
          if (!response.ok) throw data;
          return data;
        });
      })
      .then(function(data) {
        if (data.success) {
          showToast(data.message || 'Berhasil', 'success');

          // Reset form TAPI pertahankan Periode
          form.reset();
          document.getElementById('tagihanMethod').value = '';

          // Set periode dari response
          if (data.periode && window.pickerPeriode) {
            window.pickerPeriode.setValue(data.periode);
          }

          // Reset field kalkulasi
          pemakaianInput.value = '';
          jumlahSebelumPpnInput.value = '';
          ppnPersentaseInput.value = '';
          ppnNominalInput.value = '';
          jumlahInput.value = '';
          meterFaktorInput.value = '1';
          tarifInput.value = '';

          // Reset foto
          pendingFotos = [];
          renderPendingFotos();
          updateFotoCapState();

          // Re-init meter_lalu state
          updateMeterLaluState(false);
        }
      })
      .catch(function(err) {
        if (err && err.errors) {
          var msgs = [];
          Object.keys(err.errors).forEach(function(k) { msgs.push(err.errors[k][0]); });
          showToast(msgs.join('. '), 'error');
        } else if (err && err.message) {
          showToast(err.message, 'error');
        } else {
          showToast('Terjadi kesalahan jaringan', 'error');
        }
      })
      .finally(function() {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalHtml;
        }
      });
    }, true); // useCapture supaya jalan sebelum listener global

    // Dedicated foto delete handler — click-based, bukan submit (nested form invalid HTML)
    document.querySelectorAll('.btn-delete-foto').forEach(function(btn) {
      btn.addEventListener('click', function() {
        if (btn.disabled) return;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        fetch(btn.dataset.url, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': btn.dataset.token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
          if (data.success) {
            showToast(data.message || 'Foto berhasil dihapus', 'success');
            var wrapper = btn.closest('div[style]');
            if (wrapper) wrapper.remove();
            oldFotoCount--;
          } else {
            showToast(data.message || 'Gagal menghapus foto', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
          }
        })
        .catch(function() {
          showToast('Gagal menghapus foto', 'error');
          btn.disabled = false;
          btn.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
        });
      });
    });

    updateFotoCapState();
  });
</script>
@endpush
@endif

@if(($tab ?? 'input') === 'data')
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const quickSearch = document.getElementById('quickSearch');
    if (quickSearch) {
      const tbody = document.getElementById('tbData');
      const noSearchRow = document.getElementById('noSearchRow');
      let searchTimer = null;

      quickSearch.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
          const kw = quickSearch.value.trim().toLowerCase();
          let visibleCount = 0;
          const rows = tbody ? tbody.querySelectorAll('tr') : [];
          rows.forEach(function(tr) {
            if (tr.id === 'noSearchRow') return;
            if (tr.hasAttribute('colspan')) { tr.style.display = kw ? 'none' : ''; return; }
            const match = kw === '' || tr.textContent.toLowerCase().includes(kw);
            tr.style.display = match ? '' : 'none';
            if (match) visibleCount++;
          });
          if (noSearchRow) {
            noSearchRow.style.display = (kw !== '' && visibleCount === 0) ? '' : 'none';
          }
        }, 250);
      });
    }

    // ---- Modal konfirmasi hapus ----
    const hapusModal = document.getElementById('hapusModal');
    const btnYaHapus = document.getElementById('btnYaHapus');
    let hapusForm = null;

    window.tutupHapusModal = function() {
      hapusForm = null;
      hapusModal.classList.remove('show');
    };

    hapusModal.addEventListener('click', function(e) {
      if (e.target === hapusModal) tutupHapusModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && hapusModal.classList.contains('show')) {
        tutupHapusModal();
      }
    });

    document.querySelectorAll('.btnConfirmDelete').forEach(function(btn) {
      btn.addEventListener('click', function() {
        hapusForm = btn.closest('form.delete-tagihan-form');
        var tr = btn.closest('tr');
        var info = '';
        if (tr) {
          var cells = tr.querySelectorAll('td');
          if (cells.length >= 4) {
            var periode = cells[1] ? cells[1].textContent.trim() : '';
            var area = cells[2] ? cells[2].textContent.trim() : '';
            var titikMeter = cells[3] ? cells[3].textContent.trim() : '';
            info = 'Periode: ' + periode + '<br>Nama Pengguna: ' + area + '<br>Titik Meter: ' + titikMeter;
          }
        }
        document.getElementById('hapusModalInfo').innerHTML = info;
        hapusModal.classList.add('show');
      });
    });

    btnYaHapus.addEventListener('click', function() {
      if (hapusForm) hapusForm.submit();
    });
  });
</script>
@endpush
@endif