@extends('layouts.app')

@section('title', 'Pemakaian BBM')

@section('content')

  <div class="page-header">
    <div class="page-title">Pemakaian BBM</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Pemakaian BBM</li>
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
        <h3>Pemakaian BBM</h3>
        <p>Input transaksi pemakaian BBM harian per kendaraan</p>
      </div>
      <div class="card-actions">
        <a href="{{ route('pemakaian-bbm.rekap') }}" class="btn btn-secondary btn-sm">
          <i class="fa-solid fa-file-lines"></i> Lihat Rekap / Export
        </a>
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
              <th>Tanggal</th>
              <th>Kendaraan</th>
              <th>Jenis BBM</th>
              <th>Liter Paiton</th>
              <th>Liter Luar Paiton</th>
              <th>Service/Oli</th>
              <th>Jasa</th>
              <th>Jumlah</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pemakaianBbms as $i => $item)
            <tr>
              <td>{{ $pemakaianBbms->firstItem() + $i }}</td>
              <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
              <td>{{ $item->kendaraan->plat_nomor ?? '-' }}</td>
              <td>{{ ucfirst($item->hargaBbm->jenis ?? '-') }}</td>
              <td>{{ $item->liter_paiton ? number_format($item->liter_paiton, 2, ',', '.') : '-' }}</td>
              <td>{{ $item->liter_luar_paiton ? number_format($item->liter_luar_paiton, 2, ',', '.') : '-' }}</td>
              <td>{{ $item->service_oli ? number_format($item->service_oli, 0, ',', '.') : '-' }}</td>
              <td>{{ $item->jasa ? number_format($item->jasa, 0, ',', '.') : '-' }}</td>
              <td>{{ number_format($item->jumlah, 0, ',', '.') }}</td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $item->id }}"
                        data-tanggal="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}"
                        data-kendaraan-id="{{ $item->kendaraan_id }}"
                        data-jenis-bbm="{{ $item->hargaBbm->jenis ?? '' }}"
                        data-liter-paiton="{{ $item->liter_paiton }}"
                        data-liter-luar-paiton="{{ $item->liter_luar_paiton }}"
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
                Belum ada data pemakaian BBM
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
          <div class="form-grid">
            <div class="form-group">
              <label for="tanggal">Tanggal</label>
              <input type="date" id="tanggal" name="tanggal" class="form-control"
                     value="{{ old('tanggal', $edit->tanggal ?? '') }}" required>
            </div>

            <div class="form-group">
              <label for="kendaraan_id">Kendaraan</label>
              <select id="kendaraan_id" name="kendaraan_id" class="form-control" required>
                <option value="">-- Pilih Kendaraan --</option>
                @foreach($kendaraans as $kendaraan)
                  <option value="{{ $kendaraan->id }}" {{ old('kendaraan_id', $edit->kendaraan_id ?? '') == $kendaraan->id ? 'selected' : '' }}>
                    {{ $kendaraan->plat_nomor }} ({{ $kendaraan->nama_jenis }}{{ $kendaraan->unit ? ' - ' . $kendaraan->unit : '' }})
                  </option>
                @endforeach
              </select>
            </div>

            <div class="form-group">
              <label for="jenis_bbm">Jenis BBM</label>
              <select id="jenis_bbm" name="jenis_bbm" class="form-control" required>
                <option value="">-- Pilih Jenis BBM --</option>
                <option value="bensin" {{ old('jenis_bbm', $edit->hargaBbm->jenis ?? '') == 'bensin' ? 'selected' : '' }}>Bensin</option>
                <option value="solar" {{ old('jenis_bbm', $edit->hargaBbm->jenis ?? '') == 'solar' ? 'selected' : '' }}>Solar</option>
              </select>
            </div>

            <div class="form-group">
              <label for="liter_paiton">Liter Paiton</label>
              <input type="number" step="0.01" min="0" id="liter_paiton" name="liter_paiton" class="form-control"
                     value="{{ old('liter_paiton', $edit->liter_paiton ?? '') }}">
            </div>

            <div class="form-group">
              <label for="liter_luar_paiton">Liter Luar Paiton</label>
              <input type="number" step="0.01" min="0" id="liter_luar_paiton" name="liter_luar_paiton" class="form-control"
                     value="{{ old('liter_luar_paiton', $edit->liter_luar_paiton ?? '') }}">
            </div>

            <div class="form-group">
              <label for="service_oli">Service, Oli, dll (Rp)</label>
              <input type="number" step="1" min="0" id="service_oli" name="service_oli" class="form-control"
                     value="{{ old('service_oli', $edit->service_oli ?? '') }}">
            </div>

            <div class="form-group">
              <label for="jasa">Jasa (Rp)</label>
              <input type="number" step="1" min="0" id="jasa" name="jasa" class="form-control"
                     value="{{ old('jasa', $edit->jasa ?? '') }}">
            </div>
          </div>
          <p style="color:#888; font-size:0.85rem; margin-top:8px;">
            Rp Paiton, Rp Luar Paiton, dan Jumlah dihitung otomatis dari harga BBM aktif.
          </p>
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
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('pemakaianModal');
    const deleteOverlay = document.getElementById('deletePemakaianModal');

    overlay.addEventListener('click', function(e) { if (e.target === overlay) closePemakaianModal(); });
    deleteOverlay.addEventListener('click', function(e) { if (e.target === deleteOverlay) closeDeletePemakaianModal(); });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') { closePemakaianModal(); closeDeletePemakaianModal(); }
    });

    @if($edit || $errors->any())
      document.getElementById('pemakaianModal').classList.add('show');
    @endif
  });

  function openAddPemakaian() {
    const form = document.getElementById('pemakaianForm');
    form.reset();
    form.action = '{{ route('pemakaian-bbm.store') }}';
    document.getElementById('pemakaianMethod').value = '';
    document.getElementById('pemakaianModalTitle').textContent = 'Tambah Pemakaian BBM';
    document.getElementById('pemakaianModal').classList.add('show');
  }

  function openEditPemakaian(btn) {
    const form = document.getElementById('pemakaianForm');
    form.reset();
    form.action = '{{ route('pemakaian-bbm.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('pemakaianMethod').value = 'PUT';
    document.getElementById('tanggal').value = btn.dataset.tanggal;
    document.getElementById('kendaraan_id').value = btn.dataset.kendaraanId;
    document.getElementById('jenis_bbm').value = btn.dataset.jenisBbm;
    document.getElementById('liter_paiton').value = btn.dataset.literPaiton;
    document.getElementById('liter_luar_paiton').value = btn.dataset.literLuarPaiton;
    document.getElementById('service_oli').value = btn.dataset.serviceOli;
    document.getElementById('jasa').value = btn.dataset.jasa;
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