@extends('layouts.app')

@section('title', 'Pemegang Kendaraan')

@section('content')

  <div class="page-header">
    <div class="page-title">Pemegang Kendaraan</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Master Data</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Pemegang Kendaraan</li>
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
        <h3>Pemegang Kendaraan</h3>
        <p>Daftar nama pemegang / pengguna kendaraan</p>
      </div>
      <div class="card-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddPemegang()">
          <i class="fa-solid fa-plus"></i> Tambah Pemegang
        </button>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pemegangKendaraans as $i => $item)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $item->nama }}</td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $item->id }}"
                        data-nama="{{ $item->nama }}"
                        onclick="openEditPemegang(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button type="button" class="btn btn-icon btn-delete" title="Hapus"
                        data-id="{{ $item->id }}"
                        data-nama="{{ $item->nama }}"
                        onclick="openDeletePemegang(this)">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="3" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data pemegang kendaraan
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- MODAL TAMBAH / EDIT PEMEGANG KENDARAAN --}}
  <div class="modal-overlay" id="pemegangModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="pemegangModalTitle">{{ $edit ? 'Edit Pemegang Kendaraan' : 'Tambah Pemegang Kendaraan' }}</h3>
        <button type="button" class="modal-close" onclick="closePemegangModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="pemegangForm" class="ajax-form" method="POST"
            action="{{ $edit ? route('pemegang-kendaraan.update', $edit->id) : route('pemegang-kendaraan.store') }}">
        @csrf
        <input type="hidden" name="_method" id="pemegangMethod" value="{{ $edit ? 'PUT' : '' }}">

        <div class="modal-body">
          <div class="form-group" style="margin-bottom: 0;">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" class="form-control" maxlength="100"
                   placeholder="Contoh: Budi Santoso"
                   value="{{ old('nama', $edit->nama ?? '') }}" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closePemegangModal()">
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
  <div class="modal-overlay" id="deletePemegangModal">
    <div class="modal modal-confirm">
      <div class="modal-body modal-confirm-body">
        <div class="modal-confirm-icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-confirm-title">Hapus Pemegang Kendaraan?</h3>
        <p class="modal-confirm-text">
          Yakin ingin menghapus pemegang kendaraan
          <strong id="deletePemegangNama">-</strong>
          ? Data yang dihapus tidak dapat dikembalikan.
        </p>
      </div>
      <form id="deletePemegangForm" class="ajax-form" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-footer modal-confirm-footer">
          <button type="button" class="btn btn-secondary" onclick="closeDeletePemegangModal()">
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
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('pemegangModal');
    const deleteOverlay = document.getElementById('deletePemegangModal');

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closePemegangModal();
    });

    deleteOverlay.addEventListener('click', function(e) {
      if (e.target === deleteOverlay) closeDeletePemegangModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closePemegangModal();
        closeDeletePemegangModal();
      }
    });

    @if($edit || $errors->any())
      document.getElementById('pemegangModal').classList.add('show');
    @endif
  });

  function openAddPemegang() {
    const form = document.getElementById('pemegangForm');
    form.reset();
    form.action = '{{ route('pemegang-kendaraan.store') }}';
    document.getElementById('pemegangMethod').value = '';
    document.getElementById('pemegangModalTitle').textContent = 'Tambah Pemegang Kendaraan';
    document.getElementById('pemegangModal').classList.add('show');
    document.getElementById('nama').focus();
  }

  function openEditPemegang(btn) {
    const form = document.getElementById('pemegangForm');
    form.reset();
    form.action = '{{ route('pemegang-kendaraan.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('pemegangMethod').value = 'PUT';
    document.getElementById('nama').value = btn.dataset.nama;
    document.getElementById('pemegangModalTitle').textContent = 'Edit Pemegang Kendaraan';
    document.getElementById('pemegangModal').classList.add('show');
  }

  function closePemegangModal() {
    document.getElementById('pemegangModal').classList.remove('show');
  }

  function openDeletePemegang(btn) {
    const form = document.getElementById('deletePemegangForm');
    form.action = '{{ route('pemegang-kendaraan.destroy', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('deletePemegangNama').textContent = btn.dataset.nama;
    document.getElementById('deletePemegangModal').classList.add('show');
  }

  function closeDeletePemegangModal() {
    document.getElementById('deletePemegangModal').classList.remove('show');
  }
</script>
@endpush