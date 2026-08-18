@extends('layouts.app')

@section('title', 'Data Titik Meter')

@section('content')

  <div class="page-header">
    <div class="page-title">Data Titik Meter</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Data Air</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Titik Meter</li>
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
        <h3>Data Titik Meter</h3>
        <p>Daftar titik meter yang tersimpan</p>
      </div>
      <div class="card-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddTitikMeter()">
          <i class="fa-solid fa-plus"></i> Tambah Titik Meter
        </button>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Area</th>
              <th>Nama</th>
              <th>Meter Faktor</th>
              <th>Tarif Harga</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($titikMeters as $i => $titikMeter)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ $titikMeter->area->nama ?? '-' }}</td>
              <td>
                <div class="app-info">
                  <div>
                    <div class="app-title">{{ $titikMeter->nama }}</div>
                  </div>
                </div>
              </td>
              <td>{{ $titikMeter->meter_faktor }}</td>
              <td>Rp {{ number_format($titikMeter->tarif_harga, 2, ',', '.') }}</td>
              <td>
                <span class="badge-status {{ $titikMeter->status == 'aktif' ? 'badge-aktif' : 'badge-nonaktif' }}">
                  {{ ucfirst($titikMeter->status) }}
                </span>
              </td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $titikMeter->id }}"
                        data-area-id="{{ $titikMeter->area_id }}"
                        data-nama="{{ $titikMeter->nama }}"
                        data-meter-faktor="{{ $titikMeter->meter_faktor }}"
                        data-tarif-harga="{{ $titikMeter->tarif_harga }}"
                        data-status="{{ $titikMeter->status }}"
                        onclick="openEditTitikMeter(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form action="{{ route('titik-meter.destroy', $titikMeter->id) }}" method="POST" style="display: inline;"
                      onsubmit="return confirm('Yakin ingin menghapus titik meter ini?');">
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
              <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data titik meter
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- MODAL TAMBAH / EDIT TITIK METER --}}
  <div class="modal-overlay" id="titikMeterModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="titikMeterModalTitle">{{ $edit ? 'Edit Titik Meter' : 'Tambah Titik Meter' }}</h3>
        <button type="button" class="modal-close" onclick="closeTitikMeterModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="titikMeterForm" method="POST"
            action="{{ $edit ? route('titik-meter.update', $edit->id) : route('titik-meter.store') }}">
        @csrf
        <input type="hidden" name="_method" id="titikMeterMethod" value="{{ $edit ? 'PUT' : '' }}">

        <div class="modal-body">
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
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" class="form-control"
                   placeholder="Contoh: Barak 1, Barak 2, Wisma"
                   value="{{ old('nama', $edit->nama ?? '') }}" required>
          </div>

          <div class="form-group">
            <label for="meter_faktor">Meter Faktor</label>
            <input type="number" id="meter_faktor" name="meter_faktor" class="form-control"
                   step="0.01" min="0" placeholder="Contoh: 1"
                   value="{{ old('meter_faktor', $edit->meter_faktor ?? '1') }}" required>
          </div>

          <div class="form-group">
            <label for="tarif_harga">Tarif Harga</label>
            <input type="text" id="tarif_harga" name="tarif_harga" class="form-control"
                   inputmode="numeric" placeholder="Contoh: 5.000"
                   value="{{ old('tarif_harga', $edit->tarif_harga ?? '0') }}" required>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control" required>
              <option value="aktif" {{ old('status', $edit->status ?? '') == 'aktif' ? 'selected' : '' }}>Aktif</option>
              <option value="nonaktif" {{ old('status', $edit->status ?? '') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeTitikMeterModal()">
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
    const overlay = document.getElementById('titikMeterModal');

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeTitikMeterModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeTitikMeterModal();
    });

    const hargaInput = document.getElementById('tarif_harga');
    hargaInput.addEventListener('input', function() {
      formatRupiah(hargaInput);
    });

    const form = document.getElementById('titikMeterForm');
    form.addEventListener('submit', function() {
      hargaInput.value = String(parseIdValue(hargaInput.value));
    });

    @if($edit || $errors->any())
      document.getElementById('titikMeterModal').classList.add('show');
      formatRupiah(hargaInput);
    @endif
  });

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

  function formatRupiah(input) {
    const v = parseIdValue(input.value);
    input.value = v ? v.toLocaleString('id-ID', { maximumFractionDigits: 2 }) : '';
  }

  function openAddTitikMeter() {
    const form = document.getElementById('titikMeterForm');
    form.reset();
    form.action = '{{ route('titik-meter.store') }}';
    document.getElementById('titikMeterMethod').value = '';
    document.getElementById('titikMeterModalTitle').textContent = 'Tambah Titik Meter';
    document.getElementById('titikMeterModal').classList.add('show');
    document.getElementById('area_id').focus();
  }

  function openEditTitikMeter(btn) {
    const form = document.getElementById('titikMeterForm');
    form.reset();
    form.action = '{{ route('titik-meter.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('titikMeterMethod').value = 'PUT';
    document.getElementById('area_id').value = btn.dataset.areaId;
    document.getElementById('nama').value = btn.dataset.nama;
    document.getElementById('meter_faktor').value = btn.dataset.meterFaktor;
    document.getElementById('status').value = btn.dataset.status;
    const hargaInput = document.getElementById('tarif_harga');
    hargaInput.value = btn.dataset.tarifHarga ? Number(btn.dataset.tarifHarga).toLocaleString('id-ID', { maximumFractionDigits: 2 }) : '';
    document.getElementById('titikMeterModalTitle').textContent = 'Edit Titik Meter';
    document.getElementById('titikMeterModal').classList.add('show');
  }

  function closeTitikMeterModal() {
    document.getElementById('titikMeterModal').classList.remove('show');
  }
</script>
@endpush
