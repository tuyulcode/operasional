@extends('layouts.app')

@section('title', 'Riwayat Harga BBM')

@section('content')

  <div class="page-header">
    <div class="page-title">Riwayat Harga BBM</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Master Data</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Harga BBM</li>
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

  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Riwayat Harga BBM</h3>
        <p>Daftar harga BBM per tanggal berlaku</p>
      </div>
      <div class="card-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddHargaBbm()">
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
              <th>Tanggal Berlaku</th>
              <th>Pertamax</th>
              <th>Pertamax Turbo</th>
              <th>Dexlite</th>
              <th>Pertadex</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($riwayat as $i => $item)
            <tr>
              <td>{{ $riwayat->firstItem() + $i }}</td>
              <td>{{ $item->tanggal_berlaku->format('d-m-Y') }}</td>
              <td>Rp {{ number_format($item->harga_pertamax, 0, ',', '.') }}</td>
              <td>Rp {{ number_format($item->harga_pertamax_turbo, 0, ',', '.') }}</td>
              <td>Rp {{ number_format($item->harga_dexlite, 0, ',', '.') }}</td>
              <td>Rp {{ number_format($item->harga_pertadex, 0, ',', '.') }}</td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $item->id }}"
                        data-tanggal-berlaku="{{ $item->tanggal_berlaku->format('Y-m-d') }}"
                        data-harga-pertamax="{{ $item->harga_pertamax }}"
                        data-harga-pertamax-turbo="{{ $item->harga_pertamax_turbo }}"
                        data-harga-pertadex="{{ $item->harga_pertadex }}"
                        data-harga-dexlite="{{ $item->harga_dexlite }}"
                        onclick="openEditHargaBbm(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>

                @if($item->isDipakai())
                  <button type="button" class="btn btn-icon" disabled
                          title="Tidak bisa dihapus, data sudah dipakai">
                    <i class="fa-solid fa-lock"></i>
                  </button>
                @else
                  <button type="button" class="btn btn-icon btn-delete" title="Hapus"
                          data-id="{{ $item->id }}"
                          data-tanggal-berlaku="{{ $item->tanggal_berlaku->format('d-m-Y') }}"
                          onclick="openDeleteHargaBbm(this)">
                    <i class="fa-solid fa-trash-can"></i>
                  </button>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data harga BBM
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($riwayat->hasPages())
        <div style="padding: 16px;">
          {{ $riwayat->links() }}
        </div>
      @endif
    </div>
  </div>

  {{-- MODAL TAMBAH / EDIT HARGA BBM --}}
  <div class="modal-overlay" id="hargaBbmModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="hargaBbmModalTitle">Tambah Data Harga BBM</h3>
        <button type="button" class="modal-close" onclick="closeHargaBbmModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      @if($terakhir)
        <div class="data-terakhir-box" id="dataTerakhirBox">
          <div class="data-terakhir-header">
            <span><i class="fa-solid fa-clock-rotate-left"></i> Data Terakhir ({{ $terakhir->tanggal_berlaku->format('d-m-Y') }})</span>
          </div>
          <div class="data-terakhir-grid">
            <div><span>Pertamax</span><strong>Rp {{ number_format($terakhir->harga_pertamax, 0, ',', '.') }}</strong></div>
            <div><span>Pertamax Turbo</span><strong>Rp {{ number_format($terakhir->harga_pertamax_turbo, 0, ',', '.') }}</strong></div>
            <div><span>Dexlite</span><strong>Rp {{ number_format($terakhir->harga_dexlite, 0, ',', '.') }}</strong></div>
            <div><span>Pertadex</span><strong>Rp {{ number_format($terakhir->harga_pertadex, 0, ',', '.') }}</strong></div>
          </div>
        </div>
      @endif

      <form id="hargaBbmForm" class="ajax-form harga-form" method="POST" action="{{ route('harga-bbm.store') }}" novalidate>
        @csrf
        <input type="hidden" name="_method" id="hargaBbmMethod" value="">

        <div class="modal-body">
          <div class="form-group">
            <label for="tanggal_berlaku">Tanggal Berlaku</label>
            <input type="date" id="tanggal_berlaku" name="tanggal_berlaku" class="form-control"
                   value="{{ old('tanggal_berlaku') }}" required>
            <small class="form-hint" id="tanggalBerlakuHint"></small>
            <small class="field-error" id="err_tanggal_berlaku"></small>
          </div>

          <div class="form-group">
            <label for="harga_pertamax">Harga Pertamax</label>
            <input type="text" id="harga_pertamax" name="harga_pertamax" class="form-control rupiah-input"
                   inputmode="numeric" placeholder="Harga Pertamax per liter"
                   value="{{ old('harga_pertamax') }}" required>
            <small class="field-error" id="err_harga_pertamax"></small>
          </div>

          <div class="form-group">
            <label for="harga_pertamax_turbo">Harga Pertamax Turbo</label>
            <input type="text" id="harga_pertamax_turbo" name="harga_pertamax_turbo" class="form-control rupiah-input"
                   inputmode="numeric" placeholder="Harga Pertamax Turbo per liter"
                   value="{{ old('harga_pertamax_turbo') }}" required>
            <small class="field-error" id="err_harga_pertamax_turbo"></small>
          </div>

          <div class="form-group">
            <label for="harga_dexlite">Harga Dexlite</label>
            <input type="text" id="harga_dexlite" name="harga_dexlite" class="form-control rupiah-input"
                   inputmode="numeric" placeholder="Harga Dexlite per liter"
                   value="{{ old('harga_dexlite') }}" required>
            <small class="field-error" id="err_harga_dexlite"></small>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label for="harga_pertadex">Harga Pertadex</label>
            <input type="text" id="harga_pertadex" name="harga_pertadex" class="form-control rupiah-input"
                   inputmode="numeric" placeholder="Harga Pertadex per liter"
                   value="{{ old('harga_pertadex') }}" required>
            <small class="field-error" id="err_harga_pertadex"></small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeHargaBbmModal()">
            Batal
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- MODAL KONFIRMASI HAPUS --}}
  <div class="modal-overlay" id="deleteHargaBbmModal">
    <div class="modal modal-confirm">
      <div class="modal-body modal-confirm-body">
        <div class="modal-confirm-icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-confirm-title">Hapus Data Harga BBM?</h3>
        <p class="modal-confirm-text">
          Anda akan menghapus data harga BBM tanggal
          <strong id="deleteHargaBbmTanggal">-</strong>.
          Tindakan ini tidak dapat dibatalkan.
        </p>
      </div>
      <form id="deleteHargaBbmForm" class="ajax-form" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-footer modal-confirm-footer">
          <button type="button" class="btn btn-secondary" onclick="closeDeleteHargaBbmModal()">
            Batal
          </button>
          <button type="submit" class="btn btn-danger">
            <i class="fa-solid fa-trash-can"></i> Ya, Hapus
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- MODAL PERINGATAN HARGA SAMA DENGAN DATA TERAKHIR --}}
  <div class="modal-overlay" id="samaHargaModal">
    <div class="modal modal-confirm">
      <div class="modal-body modal-confirm-body">
        <div class="modal-confirm-icon modal-confirm-icon-warning">
          <i class="fa-solid fa-circle-info"></i>
        </div>
        <h3 class="modal-confirm-title">Harga Belum Berubah</h3>
        <p class="modal-confirm-text">
          Harga yang Anda masukkan sama persis dengan data terakhir
          (<strong id="samaHargaTanggalTerakhir">-</strong>).
          Tetap simpan sebagai data baru dengan tanggal ini?
        </p>
      </div>
      <div class="modal-footer modal-confirm-footer">
        <button type="button" class="btn btn-secondary" onclick="closeSamaHargaModal()">
          Cek Lagi
        </button>
        <button type="button" class="btn btn-primary" onclick="confirmSamaHarga()">
          <i class="fa-solid fa-floppy-disk"></i> Tetap Simpan
        </button>
      </div>
    </div>
  </div>

@endsection

@push('styles')
<style>
  .btn-danger {
    background-color: #dc2626;
    border-color: #dc2626;
    color: #fff;
  }

  .btn-danger:hover {
    background-color: #b91c1c;
    border-color: #b91c1c;
  }

  .modal-confirm {
    max-width: 380px;
  }

  .modal-confirm-body {
    text-align: center;
    padding: 32px 24px 8px;
  }

  .modal-confirm-icon {
    width: 56px;
    height: 56px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: #fef2f2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }

  .modal-confirm-icon-warning {
    background: #fffbeb;
    color: #d97706;
  }

  .modal-confirm-title {
    margin: 0 0 8px;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1f2937;
  }

  .modal-confirm-text {
    margin: 0;
    color: #6b7280;
    font-size: 0.9rem;
    line-height: 1.5;
  }

  .modal-confirm-footer {
    justify-content: center;
    padding-top: 20px;
  }

  .alert-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
  }

  .form-hint {
    display: block;
    margin-top: 4px;
    font-size: 0.78rem;
    color: #9ca3af;
  }

  /* Pesan error per-kolom */
  .field-error {
    display: none;
    margin-top: 4px;
    font-size: 0.78rem;
    color: #dc2626;
  }

  .form-control.is-invalid {
    border-color: #dc2626 !important;
  }

  .form-control.is-invalid:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
  }

  /* Panel "Data Terakhir" */
  .data-terakhir-box {
    margin: 16px 24px 0;
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
  }

  .data-terakhir-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 10px;
  }

  .data-terakhir-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }

  .data-terakhir-grid div {
    display: flex;
    flex-direction: column;
    font-size: 0.78rem;
  }

  .data-terakhir-grid span {
    color: #94a3b8;
  }

  .data-terakhir-grid strong {
    color: #1f2937;
    font-size: 0.85rem;
  }

  .btn-xs {
    padding: 4px 10px;
    font-size: 0.75rem;
  }
</style>
@endpush

@php
  $terakhirJson = $terakhir ? [
      'tanggal_berlaku' => $terakhir->tanggal_berlaku->format('Y-m-d'),
      'harga_pertamax' => (string) $terakhir->harga_pertamax,
      'harga_pertamax_turbo' => (string) $terakhir->harga_pertamax_turbo,
      'harga_pertadex' => (string) $terakhir->harga_pertadex,
      'harga_dexlite' => (string) $terakhir->harga_dexlite,
  ] : null;
@endphp

@push('scripts')
<script>
  const TERAKHIR = @json($terakhirJson);

  let isEditMode = false;
  let pendingSubmit = false;

  // Definisi field harga: id input -> label untuk pesan error
  const HARGA_FIELDS = [
    { id: 'harga_pertamax', label: 'Harga Pertamax' },
    { id: 'harga_pertamax_turbo', label: 'Harga Pertamax Turbo' },
    { id: 'harga_dexlite', label: 'Harga Dexlite' },
    { id: 'harga_pertadex', label: 'Harga Pertadex' },
  ];

  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('hargaBbmModal');
    const deleteOverlay = document.getElementById('deleteHargaBbmModal');
    const samaHargaOverlay = document.getElementById('samaHargaModal');

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeHargaBbmModal();
    });

    deleteOverlay.addEventListener('click', function(e) {
      if (e.target === deleteOverlay) closeDeleteHargaBbmModal();
    });

    samaHargaOverlay.addEventListener('click', function(e) {
      if (e.target === samaHargaOverlay) closeSamaHargaModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeHargaBbmModal();
        closeDeleteHargaBbmModal();
        closeSamaHargaModal();
      }
    });

    document.querySelectorAll('.rupiah-input').forEach(function(input) {
      input.addEventListener('input', function() {
        formatRupiahLive(input);
        clearFieldError(input.id);
      });
    });

    document.getElementById('tanggal_berlaku').addEventListener('change', function() {
      clearFieldError('tanggal_berlaku');
    });

    document.getElementById('hargaBbmForm').addEventListener('submit', function(e) {
      // Kalau ini submit lanjutan setelah user konfirmasi "Tetap Simpan", lolos langsung
      if (pendingSubmit) {
        formatFieldsForSubmit();
        return;
      }

      e.preventDefault();

      clearAllFieldErrors();

      if (!validateHargaForm()) {
        return;
      }

      const pertamax = parseIdValue(document.getElementById('harga_pertamax').value);
      const turbo    = parseIdValue(document.getElementById('harga_pertamax_turbo').value);
      const dexlite  = parseIdValue(document.getElementById('harga_dexlite').value);
      const pertadex = parseIdValue(document.getElementById('harga_pertadex').value);

      const isSamaDenganTerakhir = TERAKHIR && !isEditMode &&
        pertamax === Number(TERAKHIR.harga_pertamax) &&
        turbo    === Number(TERAKHIR.harga_pertamax_turbo) &&
        dexlite  === Number(TERAKHIR.harga_dexlite) &&
        pertadex === Number(TERAKHIR.harga_pertadex);

      if (isSamaDenganTerakhir) {
        document.getElementById('samaHargaTanggalTerakhir').textContent =
          TERAKHIR.tanggal_berlaku.split('-').reverse().join('-');
        document.getElementById('samaHargaModal').classList.add('show');
        return;
      }

      pendingSubmit = true;
      formatFieldsForSubmit();
      this.submit();
    });

    @if($errors->any())
      document.getElementById('hargaBbmModal').classList.add('show');
    @endif
  });

  /**
   * Validasi: tanggal & semua harga wajib diisi dan harga tidak boleh 0.
   * Return true kalau lolos, false kalau ada error (sekaligus menampilkan pesannya).
   */
  function validateHargaForm() {
    let isValid = true;

    const tanggalInput = document.getElementById('tanggal_berlaku');
    if (!tanggalInput.value) {
      showFieldError('tanggal_berlaku', 'Tanggal berlaku wajib diisi.');
      isValid = false;
    }

    HARGA_FIELDS.forEach(function(field) {
      const input = document.getElementById(field.id);
      const raw = input.value.trim();

      if (!raw) {
        showFieldError(field.id, field.label + ' wajib diisi.');
        isValid = false;
        return;
      }

      const value = parseIdValue(raw);
      if (value <= 0) {
        showFieldError(field.id, field.label + ' tidak boleh 0.');
        isValid = false;
      }
    });

    if (!isValid) {
      const firstInvalid = document.querySelector('.form-control.is-invalid');
      if (firstInvalid) firstInvalid.focus();
    }

    return isValid;
  }

  function showFieldError(fieldId, message) {
    const input = document.getElementById(fieldId);
    const errorEl = document.getElementById('err_' + fieldId);
    if (input) input.classList.add('is-invalid');
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.style.display = 'block';
    }
  }

  function clearFieldError(fieldId) {
    const input = document.getElementById(fieldId);
    const errorEl = document.getElementById('err_' + fieldId);
    if (input) input.classList.remove('is-invalid');
    if (errorEl) {
      errorEl.textContent = '';
      errorEl.style.display = 'none';
    }
  }

  function clearAllFieldErrors() {
    clearFieldError('tanggal_berlaku');
    HARGA_FIELDS.forEach(function(field) {
      clearFieldError(field.id);
    });
  }

  function formatFieldsForSubmit() {
    document.querySelectorAll('.rupiah-input').forEach(function(input) {
      input.value = String(parseIdValue(input.value));
    });
  }

  function closeSamaHargaModal() {
    document.getElementById('samaHargaModal').classList.remove('show');
  }

  function confirmSamaHarga() {
    closeSamaHargaModal();
    pendingSubmit = true;
    formatFieldsForSubmit();
    document.getElementById('hargaBbmForm').submit();
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

  function formatRupiahLive(input) {
    const cursorFromEnd = input.value.length - input.selectionStart;
    let raw = input.value.replace(/[^\d,]/g, '');
    let [intPart, decPart] = raw.split(',');
    intPart = intPart ? intPart.replace(/^0+(?=\d)/, '') : '';
    let formatted = intPart ? Number(intPart).toLocaleString('id-ID') : '';
    if (decPart !== undefined) {
      formatted += ',' + decPart;
    }
    input.value = formatted;
    const newPos = Math.max(formatted.length - cursorFromEnd, 0);
    input.setSelectionRange(newPos, newPos);
  }

  function formatRupiahValue(value) {
    const num = Number(value);
    if (isNaN(num)) return '';
    return num.toLocaleString('id-ID');
  }

  function resetHargaFields() {
    ['harga_pertamax', 'harga_pertamax_turbo', 'harga_dexlite', 'harga_pertadex'].forEach(function(id) {
      document.getElementById(id).value = '';
    });
  }

  function openAddHargaBbm() {
    isEditMode = false;
    pendingSubmit = false;

    const form = document.getElementById('hargaBbmForm');
    form.reset();
    resetHargaFields();
    clearAllFieldErrors();
    form.action = '{{ route('harga-bbm.store') }}';
    document.getElementById('hargaBbmMethod').value = '';
    document.getElementById('hargaBbmModalTitle').textContent = 'Tambah Data Harga BBM';

    const dateInput = document.getElementById('tanggal_berlaku');
    const hint = document.getElementById('tanggalBerlakuHint');

    if (TERAKHIR) {
      // Tanggal baru tidak boleh sama/mundur dari tanggal terakhir
      const minDate = new Date(TERAKHIR.tanggal_berlaku);
      minDate.setDate(minDate.getDate() + 1);
      const minStr = minDate.toISOString().slice(0, 10);
      dateInput.min = minStr;
      dateInput.value = minStr;
      hint.textContent = 'Tidak boleh sama atau sebelum ' + TERAKHIR.tanggal_berlaku.split('-').reverse().join('-');
    } else {
      dateInput.removeAttribute('min');
      hint.textContent = '';
    }

    const box = document.getElementById('dataTerakhirBox');
    if (box) box.style.display = '';

    document.getElementById('hargaBbmModal').classList.add('show');
  }

  function openEditHargaBbm(btn) {
    isEditMode = true;
    pendingSubmit = false;

    const form = document.getElementById('hargaBbmForm');
    form.reset();
    clearAllFieldErrors();
    form.action = '{{ route('harga-bbm.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('hargaBbmMethod').value = 'PUT';
    document.getElementById('hargaBbmModalTitle').textContent = 'Edit Data Harga BBM';

    const dateInput = document.getElementById('tanggal_berlaku');
    dateInput.removeAttribute('min'); // edit boleh betulkan tanggal lama
    dateInput.value = btn.dataset.tanggalBerlaku;
    document.getElementById('tanggalBerlakuHint').textContent = '';

    document.getElementById('harga_pertamax').value = formatRupiahValue(btn.dataset.hargaPertamax);
    document.getElementById('harga_pertamax_turbo').value = formatRupiahValue(btn.dataset.hargaPertamaxTurbo);
    document.getElementById('harga_pertadex').value = formatRupiahValue(btn.dataset.hargaPertadex);
    document.getElementById('harga_dexlite').value = formatRupiahValue(btn.dataset.hargaDexlite);

    // Sembunyikan panel "data terakhir" saat mode edit biar tidak membingungkan
    const box = document.getElementById('dataTerakhirBox');
    if (box) box.style.display = 'none';

    document.getElementById('hargaBbmModal').classList.add('show');
  }

  function closeHargaBbmModal() {
    document.getElementById('hargaBbmModal').classList.remove('show');
  }

  function openDeleteHargaBbm(btn) {
    const form = document.getElementById('deleteHargaBbmForm');
    form.action = '{{ route('harga-bbm.destroy', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('deleteHargaBbmTanggal').textContent = btn.dataset.tanggalBerlaku;
    document.getElementById('deleteHargaBbmModal').classList.add('show');
  }

  function closeDeleteHargaBbmModal() {
    document.getElementById('deleteHargaBbmModal').classList.remove('show');
  }
</script>
@endpush