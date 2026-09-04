@extends('layouts.app')

@section('title', 'Data Kendaraan')

@section('content')

  <div class="page-header">
    <div class="page-title">Data Kendaraan</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Data Kendaraan</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Kendaraan</li>
    </ul>
  </div>

  @if($errors->any())
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Data Kendaraan</h3>
        <p>Daftar kendaraan yang tersimpan</p>
      </div>
      <div class="card-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddKendaraan()">
          <i class="fa-solid fa-plus"></i> Tambah Kendaraan
        </button>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Merek</th>
              <th>Plat Nomor</th>
              <th>Jenis</th>
              <th>Unit</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kendaraans as $i => $kendaraan)
            <tr>
              <td>{{ $kendaraans->firstItem() + $i }}</td>
              <td>{{ $kendaraan->jenisKendaraan->nama_merek ?? '-' }}</td>
              <td>
                <div class="app-info">
                  <div>
                    <div class="app-title">{{ $kendaraan->plat_nomor }}</div>
                  </div>
                </div>
              </td>
              <td>{{ $kendaraan->nama_jenis }}</td>
              <td>{{ $kendaraan->unit ?? '-' }}</td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $kendaraan->id }}"
                        data-jenis-kendaraan-id="{{ $kendaraan->jenis_kendaraan_id }}"
                        data-plat-nomor="{{ $kendaraan->plat_nomor }}"
                        data-nama-jenis="{{ $kendaraan->nama_jenis }}"
                        data-unit="{{ $kendaraan->unit }}"
                        onclick="openEditKendaraan(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button type="button" class="btn btn-icon btn-delete" title="Hapus"
                        data-id="{{ $kendaraan->id }}"
                        data-plat-nomor="{{ $kendaraan->plat_nomor }}"
                        onclick="openDeleteKendaraan(this)">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data kendaraan
              </td>
            </tr>
            @endforelse
          </tbody>
          </table>
        </div>
        @include('partials.pagination', ['paginator' => $kendaraans])
      </div>
    </div>

    {{-- MODAL TAMBAH / EDIT KENDARAAN --}}
  <div class="modal-overlay" id="kendaraanModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="kendaraanModalTitle">{{ $edit ? 'Edit Kendaraan' : 'Tambah Kendaraan' }}</h3>
        <button type="button" class="modal-close" onclick="closeKendaraanModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="kendaraanForm" class="ajax-form" method="POST"
            action="{{ $edit ? route('kendaraan.update', $edit->id) : route('kendaraan.store') }}">
        @csrf
        <input type="hidden" name="_method" id="kendaraanMethod" value="{{ $edit ? 'PUT' : '' }}">

        <div class="modal-body">
          <div class="form-group">
            <label for="jenis_kendaraan_id">Merek</label>
            <select id="jenis_kendaraan_id" name="jenis_kendaraan_id" class="form-control" required>
              <option value="">-- Pilih Merek --</option>
              @foreach($jenisKendaraans as $jenisKendaraan)
                <option value="{{ $jenisKendaraan->id }}" {{ old('jenis_kendaraan_id', $edit->jenis_kendaraan_id ?? '') == $jenisKendaraan->id ? 'selected' : '' }}>
                  {{ $jenisKendaraan->nama_merek }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- PLAT NOMOR: 3 FIELD TERPISAH (DEPAN - TENGAH - BELAKANG) --}}
          <div class="form-group">
            <label for="plat_depan">Plat Nomor</label>
            @php
              $platParts = explode(' ', old('plat_nomor_raw', $edit->plat_nomor ?? ''));
            @endphp
            <div class="plat-nomor-group">
              <input type="text" id="plat_depan" name="plat_depan" class="form-control plat-depan"
                     placeholder="AG" maxlength="2" autocomplete="off"
                     oninput="formatPlatDepan(this)"
                     value="{{ old('plat_depan', $platParts[0] ?? '') }}" required>
              <span class="plat-sep">-</span>
              <input type="text" id="plat_tengah" name="plat_tengah" class="form-control plat-tengah"
                     placeholder="1234" maxlength="4" autocomplete="off" inputmode="numeric"
                     oninput="formatPlatTengah(this)"
                     value="{{ old('plat_tengah', $platParts[1] ?? '') }}" required>
              <span class="plat-sep">-</span>
              <input type="text" id="plat_belakang" name="plat_belakang" class="form-control plat-belakang"
                     placeholder="ABC" maxlength="3" autocomplete="off"
                     oninput="formatPlatBelakang(this)"
                     value="{{ old('plat_belakang', $platParts[2] ?? '') }}">
            </div>
            {{-- field gabungan yang benar-benar dikirim ke server --}}
            <input type="hidden" id="plat_nomor" name="plat_nomor" value="{{ $edit->plat_nomor ?? '' }}">
          </div>

          <div class="form-group">
            <label for="nama_jenis">Jenis Kendaraan</label>
            <select id="nama_jenis" name="nama_jenis" class="form-control" required>
              <option value="">-- Pilih Jenis --</option>
              <option value="Roda 2" {{ old('nama_jenis', $edit->nama_jenis ?? '') == 'Roda 2' ? 'selected' : '' }}>Roda 2</option>
              <option value="Roda 3" {{ old('nama_jenis', $edit->nama_jenis ?? '') == 'Roda 3' ? 'selected' : '' }}>Roda 3</option>
              <option value="Roda 4" {{ old('nama_jenis', $edit->nama_jenis ?? '') == 'Roda 4' ? 'selected' : '' }}>Roda 4</option>
            </select>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label for="unit">Unit</label>
            <select id="unit" name="unit" class="form-control">
              <option value="">-- Pilih Unit --</option>
              <option value="Unit 1 & 2" {{ old('unit', $edit->unit ?? '') == 'Unit 1 & 2' ? 'selected' : '' }}>Unit 1 & 2</option>
              <option value="Unit 9" {{ old('unit', $edit->unit ?? '') == 'Unit 9' ? 'selected' : '' }}>Unit 9</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeKendaraanModal()">
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
  <div class="modal-overlay" id="deleteKendaraanModal">
    <div class="modal modal-confirm">
      <div class="modal-body modal-confirm-body">
        <div class="modal-confirm-icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-confirm-title">Hapus Kendaraan?</h3>
        <p class="modal-confirm-text">
          Anda akan menghapus kendaraan dengan plat nomor
          <strong id="deleteKendaraanPlatNomor">-</strong>.
          Tindakan ini tidak dapat dibatalkan.
        </p>
      </div>
      <form id="deleteKendaraanForm" class="ajax-form" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-footer modal-confirm-footer">
          <button type="button" class="btn btn-secondary" onclick="closeDeleteKendaraanModal()">
            Batal
          </button>
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

  .btn-danger {
    background-color: #dc2626;
    border-color: #dc2626;
    color: #fff;
  }

  .btn-danger:hover {
    background-color: #b91c1c;
    border-color: #b91c1c;
  }

  /* PLAT NOMOR 3 FIELD */
  .plat-nomor-group {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .plat-nomor-group .form-control {
    text-align: center;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 1px;
  }

  .plat-depan {
    flex: 0 0 60px;
  }

  .plat-tengah {
    flex: 0 0 90px;
  }

  .plat-belakang {
    flex: 0 0 70px;
  }

  .plat-sep {
    color: #9ca3af;
    font-weight: 700;
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('kendaraanModal');
    const deleteOverlay = document.getElementById('deleteKendaraanModal');
    const kendaraanForm = document.getElementById('kendaraanForm');

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeKendaraanModal();
    });

    deleteOverlay.addEventListener('click', function(e) {
      if (e.target === deleteOverlay) closeDeleteKendaraanModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeKendaraanModal();
        closeDeleteKendaraanModal();
      }
    });

    // Gabungkan 3 field plat nomor jadi satu sebelum form dikirim
    kendaraanForm.addEventListener('submit', function() {
      combinePlatNomor();
    });

    @if($edit || $errors->any())
      document.getElementById('kendaraanModal').classList.add('show');
    @endif
  });

  // ===== Format & batasi input plat nomor =====
  function formatPlatDepan(el) {
    el.value = el.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 2);
  }

  function formatPlatTengah(el) {
    el.value = el.value.replace(/[^0-9]/g, '').slice(0, 4);
  }

  function formatPlatBelakang(el) {
    el.value = el.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 3);
  }

  function combinePlatNomor() {
    const depan = document.getElementById('plat_depan').value.trim();
    const tengah = document.getElementById('plat_tengah').value.trim();
    const belakang = document.getElementById('plat_belakang').value.trim();
    document.getElementById('plat_nomor').value = [depan, tengah, belakang]
      .filter(Boolean)
      .join(' ');
  }

  function openAddKendaraan() {
    const form = document.getElementById('kendaraanForm');
    form.reset();
    form.action = '{{ route('kendaraan.store') }}';
    document.getElementById('kendaraanMethod').value = '';
    document.getElementById('kendaraanModalTitle').textContent = 'Tambah Kendaraan';
    document.getElementById('kendaraanModal').classList.add('show');
    document.getElementById('jenis_kendaraan_id').focus();
  }

  function openEditKendaraan(btn) {
    const form = document.getElementById('kendaraanForm');
    form.reset();
    form.action = '{{ route('kendaraan.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('kendaraanMethod').value = 'PUT';
    document.getElementById('jenis_kendaraan_id').value = btn.dataset.jenisKendaraanId;

    // Pecah plat_nomor lama (contoh: "AG 1234 XX") jadi 3 field
    const platParts = (btn.dataset.platNomor || '').trim().split(/\s+/);
    document.getElementById('plat_depan').value = platParts[0] || '';
    document.getElementById('plat_tengah').value = platParts[1] || '';
    document.getElementById('plat_belakang').value = platParts[2] || '';

    document.getElementById('nama_jenis').value = btn.dataset.namaJenis;
    document.getElementById('unit').value = btn.dataset.unit;
    document.getElementById('kendaraanModalTitle').textContent = 'Edit Kendaraan';
    document.getElementById('kendaraanModal').classList.add('show');
  }

  function closeKendaraanModal() {
    document.getElementById('kendaraanModal').classList.remove('show');
  }

  function openDeleteKendaraan(btn) {
    const form = document.getElementById('deleteKendaraanForm');
    form.action = '{{ route('kendaraan.destroy', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('deleteKendaraanPlatNomor').textContent = btn.dataset.platNomor;
    document.getElementById('deleteKendaraanModal').classList.add('show');
  }

  function closeDeleteKendaraanModal() {
    document.getElementById('deleteKendaraanModal').classList.remove('show');
  }
</script>
@endpush