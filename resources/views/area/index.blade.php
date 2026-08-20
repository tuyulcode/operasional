@extends('layouts.app')

@section('title', 'Data Area')

@section('content')

  <div class="page-header">
    <div class="page-title">Data Area</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Data Air</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Area</li>
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
        <h3>Data Area</h3>
        <p>Daftar area yang tersimpan</p>
      </div>
      <div class="card-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddArea()">
          <i class="fa-solid fa-plus"></i> Tambah Area
        </button>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Pengguna</th>
              <th>Alamat</th>
              <th>PPN</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($areas as $i => $area)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <div class="app-info">
                  <div>
                    <div class="app-title">{{ $area->nama }}</div>
                  </div>
                </div>
              </td>
              <td>{{ $area->alamat ?: '-' }}</td>
              <td>
                @if($area->kena_ppn)
                  <span class="badge-status badge-aktif"><i class="fa-solid fa-percent"></i> PPN</span>
                @else
                  -
                @endif
              </td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $area->id }}"
                        data-nama="{{ $area->nama }}"
                        data-alamat="{{ $area->alamat }}"
                        data-kena-ppn="{{ $area->kena_ppn ? '1' : '0' }}"
                        data-format-rekap="{{ $area->format_rekap }}"
                        onclick="openEditArea(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form action="{{ route('area.destroy', $area->id) }}" method="POST" style="display: inline;"
                      onsubmit="return confirm('Yakin ingin menghapus area ini?');">
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
              <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data area
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- MODAL TAMBAH / EDIT AREA --}}
  <div class="modal-overlay" id="areaModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="areaModalTitle">{{ $edit ? 'Edit Area' : 'Tambah Area' }}</h3>
        <button type="button" class="modal-close" onclick="closeAreaModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="areaForm" method="POST"
            action="{{ $edit ? route('area.update', $edit->id) : route('area.store') }}">
        @csrf
        <input type="hidden" name="_method" id="areaMethod" value="{{ $edit ? 'PUT' : '' }}">

        <div class="modal-body">
          <div class="form-group">
            <label for="nama">Nama Pengguna</label>
            <input type="text" id="nama" name="nama" class="form-control"
                   placeholder="Contoh: Barak 1, Barak 2, Wisma"
                   value="{{ old('nama', $edit->nama ?? '') }}" required>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" class="form-control" rows="3"
                      placeholder="Masukkan alamat area (opsional)">{{ old('alamat', $edit->alamat ?? '') }}</textarea>
          </div>

          <div class="form-group" style="margin-bottom: 0; margin-top: 4px;">
            <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 0; cursor: pointer;">
              <input type="checkbox" id="kena_ppn" name="kena_ppn" value="1"
                     {{ old('kena_ppn', $edit->kena_ppn ?? false) ? 'checked' : '' }}>
              Kena PPN?
            </label>
          </div>

          <div class="form-group" style="margin-bottom: 0; margin-top: 8px;">
            <label for="format_rekap">Format Rekap</label>
            <select id="format_rekap" name="format_rekap" class="form-control">
              <option value="standar" {{ old('format_rekap', $edit->format_rekap ?? 'standar') == 'standar' ? 'selected' : '' }}>
                Standar (satu pelanggan per tabel)
              </option>
              <option value="list" {{ old('format_rekap', $edit->format_rekap ?? 'standar') == 'list' ? 'selected' : '' }}>
                List (banyak titik meter per tabel)
              </option>
              <option value="multikolom" {{ old('format_rekap', $edit->format_rekap ?? 'standar') == 'multikolom' ? 'selected' : '' }}>
                Multi Kolom (satu kolom per titik meter)
              </option>
            </select>
            <small style="color: #999;">Format tabel di rekap PDF / Excel.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeAreaModal()">
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
    const overlay = document.getElementById('areaModal');

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeAreaModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeAreaModal();
    });

    @if($edit || $errors->any())
      document.getElementById('areaModal').classList.add('show');
    @endif
  });

  function openAddArea() {
    const form = document.getElementById('areaForm');
    form.reset();
    form.action = '{{ route('area.store') }}';
    document.getElementById('areaMethod').value = '';
    document.getElementById('areaModalTitle').textContent = 'Tambah Area';
    document.getElementById('areaModal').classList.add('show');
    document.getElementById('nama').focus();
  }

  function openEditArea(btn) {
    const form = document.getElementById('areaForm');
    form.reset();
    form.action = '{{ route('area.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('areaMethod').value = 'PUT';
    document.getElementById('nama').value = btn.dataset.nama;
    document.getElementById('alamat').value = btn.dataset.alamat || '';
    document.getElementById('kena_ppn').checked = btn.dataset.kenaPpn === '1';
    document.getElementById('format_rekap').value = btn.dataset.formatRekap || 'standar';
    document.getElementById('areaModalTitle').textContent = 'Edit Area';
    document.getElementById('areaModal').classList.add('show');
  }

  function closeAreaModal() {
    document.getElementById('areaModal').classList.remove('show');
  }
</script>
@endpush
