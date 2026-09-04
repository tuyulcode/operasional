@extends('layouts.app')

@section('title', 'Data Area')

@section('content')

@push('styles')
<style>
  .format-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
  }
  .format-card {
    position: relative;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    background: #fff;
  }
  .format-card:hover {
    border-color: #93c5fd;
    background: #f0f7ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  }
  .format-card.selected {
    border-color: #3b82f6;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
  }
  .format-card-icon {
    font-size: 1.8rem;
    color: #6b7280;
    margin-bottom: 8px;
    transition: color 0.2s;
  }
  .format-card.selected .format-card-icon {
    color: #3b82f6;
  }
  .format-card-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
  }
  .format-card-desc {
    font-size: 0.72rem;
    color: #6b7280;
    line-height: 1.4;
  }
  .format-card-example {
    font-size: 0.68rem;
    color: #9ca3af;
    margin-top: 4px;
    font-style: italic;
  }
  .format-card-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #fef3c7;
    color: #92400e;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
    border: 1px solid #fcd34d;
    white-space: nowrap;
  }
  @media (max-width: 600px) {
    .format-cards-grid { grid-template-columns: 1fr; }
  }
</style>
@endpush

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
              <td>{{ $areas->firstItem() + $i }}</td>
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
                        data-jml-titik="{{ $area->titikMeter()->count() }}"
                        onclick="openEditArea(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form action="{{ route('area.destroy', $area->id) }}" method="POST" style="display: inline;"
                      class="ajax-form">
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
        @include('partials.pagination', ['paginator' => $areas])
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
      <form id="areaForm" class="ajax-form" method="POST"
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

          <div class="form-group">
            <label>Format Rekap</label>
            <input type="hidden" name="format_rekap" id="format_rekap"
                   value="{{ old('format_rekap', $edit->format_rekap ?? 'standar') }}">
            @php
              $currentFormat = old('format_rekap', $edit->format_rekap ?? 'standar');
              $jmlTitik = $edit ? $edit->titikMeter()->count() : 0;
              $suggestedFormat = null;
              if ($jmlTitik === 1) $suggestedFormat = 'standar';
              elseif ($jmlTitik >= 2 && $jmlTitik <= 3) $suggestedFormat = 'multikolom';
              elseif ($jmlTitik >= 4) $suggestedFormat = 'list';
            @endphp
            <div class="format-cards-grid">
              <div class="format-card {{ $currentFormat === 'standar' ? 'selected' : '' }}" data-value="standar" onclick="selectFormatCard(this)">
                <span class="format-card-badge" data-badge="standar" style="{{ $suggestedFormat !== 'standar' ? 'display:none' : '' }}">Mungkin cocok:</span>
                <div class="format-card-icon"><i class="fa-solid fa-user"></i></div>
                <div class="format-card-title">1 Pelanggan</div>
                <div class="format-card-desc">1 pelanggan per tabel rekap</div>
                <div class="format-card-example">Contoh: perusahaan, instansi</div>
              </div>
              <div class="format-card {{ $currentFormat === 'multikolom' ? 'selected' : '' }}" data-value="multikolom" onclick="selectFormatCard(this)">
                <span class="format-card-badge" data-badge="multikolom" style="{{ $suggestedFormat !== 'multikolom' ? 'display:none' : '' }}">Mungkin cocok:</span>
                <div class="format-card-icon"><i class="fa-solid fa-table-columns"></i></div>
                <div class="format-card-title">1 Pelanggan, Banyak Titik Ukur</div>
                <div class="format-card-desc">Beberapa titik meter berdampingan sebagai kolom</div>
                <div class="format-card-example">Contoh: hotel dengan beberapa titik meter</div>
              </div>
              <div class="format-card {{ $currentFormat === 'list' ? 'selected' : '' }}" data-value="list" onclick="selectFormatCard(this)">
                <span class="format-card-badge" data-badge="list" style="{{ $suggestedFormat !== 'list' ? 'display:none' : '' }}">Mungkin cocok:</span>
                <div class="format-card-icon"><i class="fa-solid fa-users"></i></div>
                <div class="format-card-title">Banyak Pelanggan Sejenis</div>
                <div class="format-card-desc">Daftar pelanggan dalam 1 tabel rekap</div>
                <div class="format-card-example">Contoh: warung, toilet, kios</div>
              </div>
            </div>
            @if($jmlTitik > 0)
              <small id="formatBadgeInfo" style="color: #999; margin-top: 6px; display: block;">
                <i class="fa-solid fa-circle-info" style="font-size: 0.7rem;"></i>
                Area ini memiliki <span class="jml-titik-count">{{ $jmlTitik }}</span> titik meter. Badge "Mungkin cocok:" hanyalah saran, Anda bebas memilih format lain.
              </small>
            @else
              <small id="formatBadgeInfo" style="color: #999; margin-top: 6px; display: none;">
                <i class="fa-solid fa-circle-info" style="font-size: 0.7rem;"></i>
                Area ini memiliki <span class="jml-titik-count">0</span> titik meter. Badge "Mungkin cocok:" hanyalah saran, Anda bebas memilih format lain.
              </small>
            @endif
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

  function selectFormatCard(card) {
    document.querySelectorAll('.format-card').forEach(function(c) {
      c.classList.remove('selected');
    });
    card.classList.add('selected');
    document.getElementById('format_rekap').value = card.dataset.value;
  }

  function selectFormatCardByValue(value) {
    document.querySelectorAll('.format-card').forEach(function(c) {
      c.classList.toggle('selected', c.dataset.value === value);
    });
    document.getElementById('format_rekap').value = value;
  }

  function updateFormatBadge(jmlTitik) {
    var suggested = null;
    if (jmlTitik === 1) suggested = 'standar';
    else if (jmlTitik >= 2 && jmlTitik <= 3) suggested = 'multikolom';
    else if (jmlTitik >= 4) suggested = 'list';

    document.querySelectorAll('.format-card-badge').forEach(function(badge) {
      badge.style.display = badge.dataset.badge === suggested ? '' : 'none';
    });

    var infoEl = document.getElementById('formatBadgeInfo');
    if (infoEl) {
      if (jmlTitik > 0) {
        infoEl.style.display = 'block';
        infoEl.querySelector('.jml-titik-count').textContent = jmlTitik;
      } else {
        infoEl.style.display = 'none';
      }
    }
  }

  function openAddArea() {
    var form = document.getElementById('areaForm');
    form.reset();
    form.action = '{{ route('area.store') }}';
    document.getElementById('areaMethod').value = '';
    document.getElementById('areaModalTitle').textContent = 'Tambah Area';
    selectFormatCardByValue('standar');
    updateFormatBadge(0);
    document.getElementById('areaModal').classList.add('show');
    document.getElementById('nama').focus();
  }

  function openEditArea(btn) {
    var form = document.getElementById('areaForm');
    form.reset();
    form.action = '{{ route('area.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('areaMethod').value = 'PUT';
    document.getElementById('nama').value = btn.dataset.nama;
    document.getElementById('alamat').value = btn.dataset.alamat || '';
    document.getElementById('kena_ppn').checked = btn.dataset.kenaPpn === '1';
    selectFormatCardByValue(btn.dataset.formatRekap || 'standar');
    updateFormatBadge(parseInt(btn.dataset.jmlTitik || '0'));
    document.getElementById('areaModalTitle').textContent = 'Edit Area';
    document.getElementById('areaModal').classList.add('show');
  }

  function closeAreaModal() {
    document.getElementById('areaModal').classList.remove('show');
  }
</script>
@endpush
