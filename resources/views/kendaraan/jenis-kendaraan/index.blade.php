@extends('layouts.app')

@section('title', 'Data Jenis Kendaraan')

@section('content')

  <div class="page-header">
    <div class="page-title">Data Jenis Kendaraan</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Data Kendaraan</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Jenis Kendaraan</li>
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
        <h3>Data Jenis Kendaraan</h3>
        <p>Daftar merek kendaraan yang tersimpan</p>
      </div>
      <div class="card-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddJenisKendaraan()">
          <i class="fa-solid fa-plus"></i> Tambah Jenis Kendaraan
        </button>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Merek</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($jenisKendaraans as $i => $jenisKendaraan)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <div class="app-info">
                  <div>
                    <div class="app-title">{{ $jenisKendaraan->nama_merek }}</div>
                  </div>
                </div>
              </td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $jenisKendaraan->id }}"
                        data-nama-merek="{{ $jenisKendaraan->nama_merek }}"
                        onclick="openEditJenisKendaraan(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form action="{{ route('jenis-kendaraan.destroy', $jenisKendaraan->id) }}" method="POST" style="display: inline;"
                      onsubmit="return confirm('Yakin ingin menghapus jenis kendaraan ini?');">
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
              <td colspan="3" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data jenis kendaraan
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- MODAL TAMBAH / EDIT JENIS KENDARAAN --}}
  <div class="modal-overlay" id="jenisKendaraanModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="jenisKendaraanModalTitle">{{ $edit ? 'Edit Jenis Kendaraan' : 'Tambah Jenis Kendaraan' }}</h3>
        <button type="button" class="modal-close" onclick="closeJenisKendaraanModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="jenisKendaraanForm" method="POST"
            action="{{ $edit ? route('jenis-kendaraan.update', $edit->id) : route('jenis-kendaraan.store') }}">
        @csrf
        <input type="hidden" name="_method" id="jenisKendaraanMethod" value="{{ $edit ? 'PUT' : '' }}">

        <div class="modal-body">
          <div class="form-group" style="margin-bottom: 0;">
            <label for="nama_merek">Nama Merek</label>
            <input type="text" id="nama_merek" name="nama_merek" class="form-control"
                   placeholder="Contoh: Honda, Yamaha, Toyota, Suzuki"
                   value="{{ old('nama_merek', $edit->nama_merek ?? '') }}" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeJenisKendaraanModal()">
            Batal
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('jenisKendaraanModal');

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeJenisKendaraanModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeJenisKendaraanModal();
    });

    @if($edit || $errors->any())
      document.getElementById('jenisKendaraanModal').classList.add('show');
    @endif
  });

  function openAddJenisKendaraan() {
    const form = document.getElementById('jenisKendaraanForm');
    form.reset();
    form.action = '{{ route('jenis-kendaraan.store') }}';
    document.getElementById('jenisKendaraanMethod').value = '';
    document.getElementById('jenisKendaraanModalTitle').textContent = 'Tambah Jenis Kendaraan';
    document.getElementById('jenisKendaraanModal').classList.add('show');
    document.getElementById('nama_merek').focus();
  }

  function openEditJenisKendaraan(btn) {
    const form = document.getElementById('jenisKendaraanForm');
    form.reset();
    form.action = '{{ route('jenis-kendaraan.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('jenisKendaraanMethod').value = 'PUT';
    document.getElementById('nama_merek').value = btn.dataset.namaMerek;
    document.getElementById('jenisKendaraanModalTitle').textContent = 'Edit Jenis Kendaraan';
    document.getElementById('jenisKendaraanModal').classList.add('show');
  }

  function closeJenisKendaraanModal() {
    document.getElementById('jenisKendaraanModal').classList.remove('show');
  }
</script>
@endpush