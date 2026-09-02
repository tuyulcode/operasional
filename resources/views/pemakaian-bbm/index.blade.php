@extends('layouts.app')

@section('title', 'BBM & Consumable')

@section('content')

  @php
    $jenisBbmLabels = [
      'pertamax'       => 'Pertamax',
      'pertadex'       => 'Pertadex',
      'dexlite'        => 'Dexlite',
      'pertamax_turbo' => 'Pertamax Turbo',
    ];
  @endphp

  <div class="page-header">
    <div class="page-title">BBM & Consumable</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Transaksi</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>BBM & Consumable</li>
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
    <a href="{{ route('pemakaian-bbm.index') }}"
       class="tab-link {{ request()->routeIs('pemakaian-bbm.index') ? 'active' : '' }}">
      <i class="fa-solid fa-table-list"></i> Input Data
    </a>
    <a href="{{ route('pemakaian-bbm.rekapan') }}"
       class="tab-link {{ request()->routeIs('pemakaian-bbm.rekapan') ? 'active' : '' }}">
      <i class="fa-solid fa-file-invoice"></i> Rekapan
    </a>
    <a href="{{ route('pemakaian-bbm.pertanggungjawaban') }}"
       class="tab-link {{ request()->routeIs('pemakaian-bbm.pertanggungjawaban') ? 'active' : '' }}">
      <i class="fa-solid fa-file-signature"></i> Pertanggungjawaban
    </a>
    <a href="{{ route('pemakaian-bbm.riwayat') }}"
       class="tab-link {{ request()->routeIs('pemakaian-bbm.riwayat') ? 'active' : '' }}">
      <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
    </a>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3>BBM & Consumable</h3>
        <p>Input transaksi pemakaian BBM dan Consumable harian per kendaraan</p>
      </div>
      <div class="card-actions">
        <form id="refreshHargaForm" method="POST" action="{{ route('pemakaian-bbm.refresh-harga') }}" style="display:inline-block;">
          @csrf
          <button type="submit" class="btn btn-outline-primary btn-sm" id="btnRefreshHarga" title="Hitung ulang Jumlah kalau ada perubahan Harga BBM">
            <i class="fa-solid fa-rotate"></i> Refresh
          </button>
        </form>
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddPemakaian()">
          <i class="fa-solid fa-plus"></i> Tambah Data
        </button>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal Pengisian</th>
              <th>Kendaraan</th>
              <th>Jenis BBM</th>
              <th>Lokasi</th>
              <th>Liter</th>
              <th style="text-align:center;">Sparepart</th>
              <th>Jasa</th>
              <th>Jumlah</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pemakaianBbms as $i => $item)
            @php
              // Tampilkan liter sesuai jumlah desimal yang diinput (maks 3), tanpa nol berlebih di belakang
              $literDisplay = '-';
              if ($item->liter) {
                $literDisplay = rtrim(rtrim(number_format($item->liter, 3, ',', '.'), '0'), ',');
              }
            @endphp
            <tr>
              <td>{{ $pemakaianBbms->firstItem() + $i }}</td>
              <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
              <td>{{ $item->kendaraan->plat_nomor ?? '-' }}</td>
              <td>{{ $jenisBbmLabels[$item->jenis_bbm] ?? '-' }}</td>
              <td>{{ $item->lokasi_pembelian === 'luar_paiton' ? 'Luar Paiton' : 'Paiton' }}</td>
              <td>{{ $literDisplay }}</td>
              <td style="text-align:center;">{{ $item->service_oli ? number_format($item->service_oli, 0, ',', '.') : '-' }}</td>
              <td>{{ $item->jasa ? number_format($item->jasa, 0, ',', '.') : '-' }}</td>
              <td>{{ number_format($item->jumlah, 0, ',', '.') }}</td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $item->id }}"
                        data-tanggal="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}"
                        data-kendaraan-id="{{ $item->kendaraan_id }}"
                        data-jenis-bbm="{{ $item->jenis_bbm }}"
                        data-lokasi-pembelian="{{ $item->lokasi_pembelian }}"
                        data-liter="{{ $item->liter }}"
                        data-service-oli="{{ $item->service_oli }}"
                        data-jasa="{{ $item->jasa }}"
                        onclick="openEditPemakaian(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button type="button" class="btn btn-icon btn-delete" title="Hapus"
                        data-id="{{ $item->id }}"
                        onclick="openDeletePemakaian(this)">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="10" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data pemakaian BBM & Consumable
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div style="padding: 16px;">
        {{ $pemakaianBbms->links() }}
      </div>
    </div>
  </div>

  {{-- MODAL TAMBAH / EDIT --}}
  <div class="modal-overlay" id="pemakaianModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="pemakaianModalTitle">{{ $edit ? 'Edit Pemakaian BBM' : 'Tambah Pemakaian BBM' }}</h3>
        <button type="button" class="modal-close" onclick="closePemakaianModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="pemakaianForm" method="POST"
            action="{{ $edit ? route('pemakaian-bbm.update', $edit->id) : route('pemakaian-bbm.store') }}">
        @csrf
        <input type="hidden" name="_method" id="pemakaianMethod" value="{{ $edit ? 'PUT' : '' }}">

        <div class="modal-body">

          <h4 class="form-section-title">Pemakaian BBM</h4>
          <div class="form-grid">
            <div class="form-group">
              <label for="tanggal">Tanggal Pengisian</label>
              <input type="date" id="tanggal" name="tanggal" class="form-control"
                     value="{{ old('tanggal', $edit->tanggal ?? '') }}" required>
              <small class="form-hint form-hint-spacer">&nbsp;</small>
            </div>

            <div class="form-group searchable-select" id="kendaraanSelectWrap">
              <label for="kendaraan_search">Kendaraan</label>
              <input type="text" id="kendaraan_search" class="form-control" placeholder="Cari kendaraan..." autocomplete="off">
              <input type="hidden" id="kendaraan_id" name="kendaraan_id" value="{{ old('kendaraan_id', $edit->kendaraan_id ?? '') }}" required>
              <div class="searchable-dropdown" id="kendaraanDropdown"></div>
              <small class="form-hint form-hint-spacer">&nbsp;</small>
            </div>

            <div class="form-group">
              <label for="jenis_bbm">Jenis BBM</label>
              <select id="jenis_bbm" name="jenis_bbm" class="form-control" required>
                <option value="">-- Pilih Jenis BBM --</option>
                @foreach($jenisBbmLabels as $value => $label)
                  <option value="{{ $value }}" {{ old('jenis_bbm', $edit->jenis_bbm ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              <small class="form-hint form-hint-spacer">&nbsp;</small>
            </div>

            <div class="form-group">
              <label for="harga_per_liter_display">Harga per Liter</label>
              <input type="text" id="harga_per_liter_display" class="form-control" readonly
                     placeholder="Isi tanggal & jenis BBM dulu" style="background:#f3f4f6; cursor:not-allowed;">
              <small class="form-hint form-hint-spacer">&nbsp;</small>
            </div>

            <div class="form-group">
              <label>Lokasi Pembelian</label>
              <div class="radio-group">
                <label class="radio-option">
                  <input type="radio" name="lokasi_pembelian" value="paiton"
                         {{ old('lokasi_pembelian', $edit->lokasi_pembelian ?? '') == 'paiton' ? 'checked' : '' }} required>
                  <span>Paiton</span>
                </label>
                <label class="radio-option">
                  <input type="radio" name="lokasi_pembelian" value="luar_paiton"
                         {{ old('lokasi_pembelian', $edit->lokasi_pembelian ?? '') == 'luar_paiton' ? 'checked' : '' }}>
                  <span>Luar Paiton</span>
                </label>
              </div>
              <small class="form-hint form-hint-spacer">&nbsp;</small>
            </div>

            <div class="form-group">
              <label for="liter">Liter</label>
              <input type="text" inputmode="decimal" id="liter" name="liter" class="form-control"
                     placeholder="0,000" value="{{ old('liter', isset($edit->liter) ? str_replace('.', ',', (string) $edit->liter) : '') }}">
              <small class="form-hint">Input 0, jika tidak ada data (maks. 3 angka di belakang koma)</small>
            </div>
          </div>

          <hr class="form-section-divider">

          <h4 class="form-section-title">Consumable Kendaraan</h4>
          <div class="form-grid">
            <div class="form-group">
              <label for="service_oli">Sparepart Consumable (Rp)</label>
              <input type="text" inputmode="numeric" id="service_oli" name="service_oli" class="form-control"
                     value="{{ old('service_oli', $edit->service_oli ?? '') }}">
              <small class="form-hint">Input 0, jika tidak ada data</small>
            </div>

            <div class="form-group">
              <label for="jasa">Jasa (Rp)</label>
              <input type="text" inputmode="numeric" id="jasa" name="jasa" class="form-control"
                     value="{{ old('jasa', $edit->jasa ?? '') }}">
              <small class="form-hint">Input 0, jika tidak ada data</small>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closePemakaianModal()">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- MODAL KONFIRMASI HAPUS --}}
  <div class="modal-overlay" id="deletePemakaianModal">
    <div class="modal modal-confirm">
      <div class="modal-body modal-confirm-body">
        <div class="modal-confirm-icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-confirm-title">Hapus Data Pemakaian?</h3>
        <p class="modal-confirm-text">Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <form id="deletePemakaianForm" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-footer modal-confirm-footer">
          <button type="button" class="btn btn-secondary" onclick="closeDeletePemakaianModal()">Batal</button>
          <button type="submit" class="btn btn-danger">
            <i class="fa-solid fa-trash-can"></i> Ya, Hapus
          </button>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('styles')
<style>
  #pemakaianModal .modal { max-width: 640px; }
  .modal-confirm { max-width: 380px; }
  .modal-confirm-body { text-align: center; padding: 32px 24px 8px; }
  .modal-confirm-icon {
    width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%;
    background: #fef2f2; color: #dc2626; display: flex; align-items: center;
    justify-content: center; font-size: 1.5rem;
  }
  .modal-confirm-title { margin: 0 0 8px; font-size: 1.1rem; font-weight: 700; color: #1f2937; }
  .modal-confirm-text { margin: 0; color: #6b7280; font-size: 0.9rem; line-height: 1.5; }
  .modal-confirm-footer { justify-content: center; padding-top: 20px; }
  .btn-danger { background-color: #dc2626; border-color: #dc2626; color: #fff; }
  .btn-danger:hover { background-color: #b91c1c; border-color: #b91c1c; }
  .form-hint {
    display: block;
    margin-top: 6px;
    font-size: 0.75rem;
    line-height: 1;
    color: #9ca3af;
  }
  .form-hint-spacer {
    color: transparent;
  }
  #pemakaianForm .form-grid {
    row-gap: 14px;
  }
  #pemakaianForm .form-group {
    margin-bottom: 0;
  }

  .form-section-title {
    margin: 0 0 12px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6b7280;
  }
  .form-section-divider {
    border: none;
    border-top: 1px solid #e5e7eb;
    margin: 20px 0 16px;
  }

  .radio-group {
    display: flex;
    gap: 16px;
    align-items: center;
    height: 38px;
  }
  .radio-option {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    color: #374151;
    cursor: pointer;
    font-weight: 400;
  }
  .radio-option input[type="radio"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
  }

  .searchable-select {
    position: relative;
  }
  .searchable-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 2px);
    left: 0;
    right: 0;
    max-height: 220px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    z-index: 50;
  }
  .searchable-dropdown.show {
    display: block;
  }
  .searchable-dropdown-item {
    padding: 8px 12px;
    font-size: 0.9rem;
    cursor: pointer;
  }
  .searchable-dropdown-item:hover {
    background: #f3f4f6;
  }
  .searchable-dropdown-empty {
    padding: 8px 12px;
    font-size: 0.85rem;
    color: #9ca3af;
  }
</style>
@endpush

@push('scripts')
<script>
  // Riwayat harga BBM (urut tanggal_berlaku terbaru dulu). Harga sekarang tergantung
  // TANGGAL TRANSAKSI + JENIS BBM (bukan cuma jenis doang seperti dulu).
  const hargaBbmList = @json($hargaBbmList);

  // Daftar kendaraan buat combobox pencarian - label cuma plat + jenis, tanpa "Unit ..."
  const kendaraanList = @json($kendaraans->map(fn($k) => [
    'id'    => $k->id,
    'label' => $k->plat_nomor . ' (' . $k->nama_jenis . ')',
  ]));

  function cariHargaBbm(tanggal, jenis) {
    if (!tanggal || !jenis) return null;
    const row = hargaBbmList.find(function(h) { return h.tanggal_berlaku <= tanggal; });
    if (!row) return null;
    const nilai = row['harga_' + jenis];
    return (nilai === undefined || nilai === null) ? null : nilai;
  }

  function renderKendaraanDropdown(filter) {
    const dropdown = document.getElementById('kendaraanDropdown');
    const term = filter.toLowerCase();
    const filtered = kendaraanList.filter(function(k) { return k.label.toLowerCase().includes(term); });

    dropdown.innerHTML = '';
    if (!filtered.length) {
      dropdown.innerHTML = '<div class="searchable-dropdown-empty">Kendaraan tidak ditemukan</div>';
    } else {
      filtered.forEach(function(k) {
        const item = document.createElement('div');
        item.className = 'searchable-dropdown-item';
        item.textContent = k.label;
        item.addEventListener('mousedown', function(e) {
          e.preventDefault();
          document.getElementById('kendaraan_id').value = k.id;
          document.getElementById('kendaraan_search').value = k.label;
          dropdown.classList.remove('show');
        });
        dropdown.appendChild(item);
      });
    }
    dropdown.classList.add('show');
  }

  function setKendaraanById(id) {
    const found = kendaraanList.find(function(k) { return String(k.id) === String(id); });
    document.getElementById('kendaraan_id').value = id || '';
    document.getElementById('kendaraan_search').value = found ? found.label : '';
  }

  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('pemakaianModal');
    const deleteOverlay = document.getElementById('deletePemakaianModal');

    overlay.addEventListener('click', function(e) { if (e.target === overlay) closePemakaianModal(); });
    deleteOverlay.addEventListener('click', function(e) { if (e.target === deleteOverlay) closeDeletePemakaianModal(); });

    // Tombol Refresh: hitung ulang Jumlah semua baris berdasarkan Harga BBM yang
    // berlaku sekarang. Server yang nentuin baris mana yang benar-benar berubah
    // (lihat PemakaianBbmController::refreshHarga), di sini cuma kasih feedback
    // loading biar user nggak klik berkali-kali sambil nunggu.
    const refreshHargaForm = document.getElementById('refreshHargaForm');
    refreshHargaForm.addEventListener('submit', function() {
      const btn = document.getElementById('btnRefreshHarga');
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghitung ulang...';
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { closePemakaianModal(); closeDeletePemakaianModal(); }
    });

    const serviceOliInput = document.getElementById('service_oli');
    const jasaInput = document.getElementById('jasa');

    serviceOliInput.addEventListener('input', function() { formatRupiah(serviceOliInput); });
    jasaInput.addEventListener('input', function() { formatRupiah(jasaInput); });

    document.getElementById('jenis_bbm').addEventListener('change', updateHargaPerLiterDisplay);
    document.getElementById('tanggal').addEventListener('change', updateHargaPerLiterDisplay);

    // Liter: pakai KOMA sebagai pemisah desimal, maksimal 3 angka di belakang koma
    document.getElementById('liter').addEventListener('input', function() {
      let v = this.value.replace(/[^0-9,]/g, '');
      const firstComma = v.indexOf(',');
      if (firstComma !== -1) {
        v = v.slice(0, firstComma + 1) + v.slice(firstComma + 1).replace(/,/g, '');
        const decimals = v.slice(firstComma + 1);
        if (decimals.length > 3) {
          v = v.slice(0, firstComma + 1) + decimals.slice(0, 3);
        }
      }
      this.value = v;
    });

    const kendaraanSearch = document.getElementById('kendaraan_search');
    kendaraanSearch.addEventListener('focus', function() { renderKendaraanDropdown(this.value); });
    kendaraanSearch.addEventListener('input', function() {
      document.getElementById('kendaraan_id').value = '';
      renderKendaraanDropdown(this.value);
    });
    document.addEventListener('click', function(e) {
      if (!document.getElementById('kendaraanSelectWrap').contains(e.target)) {
        document.getElementById('kendaraanDropdown').classList.remove('show');
      }
    });

    const pemakaianForm = document.getElementById('pemakaianForm');
    pemakaianForm.addEventListener('submit', function() {
      serviceOliInput.value = serviceOliInput.value.replace(/[^\d]/g, '');
      jasaInput.value = jasaInput.value.replace(/[^\d]/g, '');

      // Kirim ke server pakai titik (format numerik standar), tampilan tetap koma
      const literInput = document.getElementById('liter');
      literInput.value = literInput.value.replace(',', '.');
    });

    @if($edit || $errors->any())
      document.getElementById('pemakaianModal').classList.add('show');
      formatRupiah(serviceOliInput);
      formatRupiah(jasaInput);
      updateHargaPerLiterDisplay();
      setKendaraanById(document.getElementById('kendaraan_id').value);
    @endif
  });

  function updateHargaPerLiterDisplay() {
    const tanggal = document.getElementById('tanggal').value;
    const jenis = document.getElementById('jenis_bbm').value;
    const display = document.getElementById('harga_per_liter_display');

    const harga = cariHargaBbm(tanggal, jenis);
    if (harga !== null) {
      display.value = `Rp ${Number(harga).toLocaleString('id-ID')}`;
    } else if (tanggal && jenis) {
      display.value = 'Belum ada harga untuk tanggal ini';
    } else {
      display.value = '';
    }
  }

  function formatRupiah(input) {
    const digits = input.value.replace(/[^\d]/g, '');
    input.value = digits ? Number(digits).toLocaleString('id-ID') : '';
  }

  function pfTodayDateString() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  function openAddPemakaian() {
    const form = document.getElementById('pemakaianForm');
    form.reset();
    form.action = '{{ route('pemakaian-bbm.store') }}';
    document.getElementById('pemakaianMethod').value = '';
    document.getElementById('pemakaianModalTitle').textContent = 'Tambah Pemakaian BBM';

    document.getElementById('tanggal').value = pfTodayDateString();
    setKendaraanById('');
    updateHargaPerLiterDisplay();

    document.getElementById('service_oli').value = '0';
    document.getElementById('jasa').value = '0';
    formatRupiah(document.getElementById('service_oli'));
    formatRupiah(document.getElementById('jasa'));

    document.getElementById('pemakaianModal').classList.add('show');
  }

  function openEditPemakaian(btn) {
    const form = document.getElementById('pemakaianForm');
    form.reset();
    form.action = '{{ route('pemakaian-bbm.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('pemakaianMethod').value = 'PUT';
    document.getElementById('tanggal').value = btn.dataset.tanggal;
    setKendaraanById(btn.dataset.kendaraanId);
    document.getElementById('jenis_bbm').value = btn.dataset.jenisBbm;

    const lokasiRadio = document.querySelector('input[name="lokasi_pembelian"][value="' + btn.dataset.lokasiPembelian + '"]');
    if (lokasiRadio) lokasiRadio.checked = true;

    // dataset.liter datang dari DB pakai titik (mis. "12.500") - tampilkan pakai koma
    document.getElementById('liter').value = String(btn.dataset.liter || '').replace('.', ',');
    document.getElementById('service_oli').value = btn.dataset.serviceOli;
    document.getElementById('jasa').value = btn.dataset.jasa;
    formatRupiah(document.getElementById('service_oli'));
    formatRupiah(document.getElementById('jasa'));
    updateHargaPerLiterDisplay();
    document.getElementById('pemakaianModalTitle').textContent = 'Edit Pemakaian BBM';
    document.getElementById('pemakaianModal').classList.add('show');
  }

  function closePemakaianModal() {
    document.getElementById('pemakaianModal').classList.remove('show');
  }

  function openDeletePemakaian(btn) {
    const form = document.getElementById('deletePemakaianForm');
    form.action = '{{ route('pemakaian-bbm.destroy', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('deletePemakaianModal').classList.add('show');
  }

  function closeDeletePemakaianModal() {
    document.getElementById('deletePemakaianModal').classList.remove('show');
  }
</script>
@endpush