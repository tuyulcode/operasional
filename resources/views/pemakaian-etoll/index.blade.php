@extends('layouts.app')

@section('title', 'Pemakaian E-Toll')

@section('content')

  <div class="page-header">
    <div class="page-title">Pemakaian E-Toll</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Transaksi</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Pemakaian E-Toll</li>
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

  <div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Export Laporan</h3>
        <p>Unduh rekap pemakaian e-toll mingguan per bulan</p>
      </div>
    </div>
    <div class="card-body">
      <form id="exportForm" method="GET" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="margin-bottom: 0; min-width: 160px;">
          <label for="export_bulan">Bulan</label>
          <select id="export_bulan" name="bulan" class="form-control" required>
            <option value="" selected disabled>-- Pilih Bulan --</option>
            @foreach(['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $val => $label)
              <option value="{{ $val }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group" style="margin-bottom: 0; min-width: 120px;">
          <label for="export_tahun">Tahun</label>
          <select id="export_tahun" name="tahun" class="form-control" required>
            <option value="" selected disabled>-- Pilih Tahun --</option>
            @for($y = now()->year; $y >= now()->year - 3; $y--)
              <option value="{{ $y }}">{{ $y }}</option>
            @endfor
          </select>
        </div>
        <div style="display: flex; gap: 8px;">
          <button type="submit" formaction="{{ route('pemakaian-etoll.export-pdf') }}" formtarget="_blank" class="btn btn-pdf btn-sm">
            <i class="fa-solid fa-file-pdf"></i> Export PDF
          </button>
          <button type="submit" formaction="{{ route('pemakaian-etoll.export-excel') }}" class="btn btn-excel btn-sm">
            <i class="fa-solid fa-file-excel"></i> Export Excel
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Pemakaian E-Toll</h3>
        <p>Daftar catatan pemakaian e-toll kendaraan</p>
      </div>
      <div class="card-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddEtoll()">
          <i class="fa-solid fa-plus"></i> Tambah Pemakaian
        </button>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Pemegang Kendaraan</th>
              <th>Nominal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pemakaianEtolls as $i => $item)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
              <td>
                <div class="app-info">
                  <div>
                    <div class="app-title">{{ $item->pemegangKendaraan->nama ?? '-' }}</div>
                  </div>
                </div>
              </td>
              <td>Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $item->id }}"
                        data-pemegang-kendaraan-id="{{ $item->pemegang_kendaraan_id }}"
                        data-tanggal="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}"
                        data-nominal="{{ $item->nominal }}"
                        onclick="openEditEtoll(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button type="button" class="btn btn-icon btn-delete" title="Hapus"
                        data-id="{{ $item->id }}"
                        onclick="openDeleteEtoll(this)">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data pemakaian e-toll
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- MODAL TAMBAH / EDIT PEMAKAIAN E-TOLL --}}
  <div class="modal-overlay" id="etollModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="etollModalTitle">{{ $edit ? 'Edit Pemakaian E-Toll' : 'Tambah Pemakaian E-Toll' }}</h3>
        <button type="button" class="modal-close" onclick="closeEtollModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="etollForm" method="POST"
            action="{{ $edit ? route('pemakaian-etoll.update', $edit->id) : route('pemakaian-etoll.store') }}">
        @csrf
        <input type="hidden" name="_method" id="etollMethod" value="{{ $edit ? 'PUT' : '' }}">

        <div class="modal-body">
          <div class="form-group">
            <label for="pemegang_kendaraan_id">Pemegang Kendaraan</label>
            <select id="pemegang_kendaraan_id" name="pemegang_kendaraan_id" class="form-control" required>
              <option value="">-- Pilih Pemegang Kendaraan --</option>
              @foreach($pemegangKendaraans as $pemegang)
                <option value="{{ $pemegang->id }}" {{ old('pemegang_kendaraan_id', $edit->pemegang_kendaraan_id ?? '') == $pemegang->id ? 'selected' : '' }}>
                  {{ $pemegang->nama }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" class="form-control"
                   value="{{ old('tanggal', isset($edit) ? \Carbon\Carbon::parse($edit->tanggal)->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label for="nominal">Nominal</label>
            <input type="text" id="nominal" name="nominal" class="form-control"
                   inputmode="numeric" placeholder="Contoh: 100.000"
                   value="{{ old('nominal', $edit->nominal ?? '0') }}" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeEtollModal()">
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
  <div class="modal-overlay" id="deleteEtollModal">
    <div class="modal modal-confirm">
      <div class="modal-body modal-confirm-body">
        <div class="modal-confirm-icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-confirm-title">Hapus Pemakaian E-Toll?</h3>
        <p class="modal-confirm-text">
          Yakin ingin menghapus data pemakaian e-toll ini? Data yang dihapus tidak dapat dikembalikan.
        </p>
      </div>
      <form id="deleteEtollForm" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-footer modal-confirm-footer">
          <button type="button" class="btn btn-secondary" onclick="closeDeleteEtollModal()">
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

  .btn-pdf {
    background-color: #dc2626;
    border-color: #dc2626;
    color: #fff;
  }

  .btn-pdf:hover {
    background-color: #b91c1c;
    border-color: #b91c1c;
  }

  .btn-excel {
    background-color: #16a34a;
    border-color: #16a34a;
    color: #fff;
  }

  .btn-excel:hover {
    background-color: #15803d;
    border-color: #15803d;
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('etollModal');
    const deleteOverlay = document.getElementById('deleteEtollModal');

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeEtollModal();
    });

    deleteOverlay.addEventListener('click', function(e) {
      if (e.target === deleteOverlay) closeDeleteEtollModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeEtollModal();
        closeDeleteEtollModal();
      }
    });

    const nominalInput = document.getElementById('nominal');
    nominalInput.addEventListener('input', function() {
      formatRupiah(nominalInput);
    });

    const form = document.getElementById('etollForm');
    form.addEventListener('submit', function() {
      nominalInput.value = nominalInput.value.replace(/[^\d]/g, '');
    });

    @if($edit || $errors->any())
      document.getElementById('etollModal').classList.add('show');
      formatRupiah(nominalInput);
    @endif
  });

  function formatRupiah(input) {
    const digits = input.value.replace(/[^\d]/g, '');
    input.value = digits ? Number(digits).toLocaleString('id-ID') : '';
  }

  function todayStr() {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  }

  function openAddEtoll() {
    const form = document.getElementById('etollForm');
    form.reset();
    form.action = '{{ route('pemakaian-etoll.store') }}';
    document.getElementById('etollMethod').value = '';
    document.getElementById('etollModalTitle').textContent = 'Tambah Pemakaian E-Toll';
    document.getElementById('tanggal').value = todayStr();
    document.getElementById('etollModal').classList.add('show');
    document.getElementById('pemegang_kendaraan_id').focus();
  }

  function openEditEtoll(btn) {
    const form = document.getElementById('etollForm');
    form.reset();
    form.action = '{{ route('pemakaian-etoll.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('etollMethod').value = 'PUT';
    document.getElementById('pemegang_kendaraan_id').value = btn.dataset.pemegangKendaraanId;
    document.getElementById('tanggal').value = btn.dataset.tanggal;
    const nominalInput = document.getElementById('nominal');
    nominalInput.value = btn.dataset.nominal ? Number(btn.dataset.nominal).toLocaleString('id-ID') : '';
    document.getElementById('etollModalTitle').textContent = 'Edit Pemakaian E-Toll';
    document.getElementById('etollModal').classList.add('show');
  }

  function closeEtollModal() {
    document.getElementById('etollModal').classList.remove('show');
  }

  function openDeleteEtoll(btn) {
    const form = document.getElementById('deleteEtollForm');
    form.action = '{{ route('pemakaian-etoll.destroy', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('deleteEtollModal').classList.add('show');
  }

  function closeDeleteEtollModal() {
    document.getElementById('deleteEtollModal').classList.remove('show');
  }
</script>
@endpush