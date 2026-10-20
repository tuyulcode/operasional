@extends('layouts.app')

@section('title', 'Input PPN')

@section('content')

  <style>
    /* Form lebih ramping, tabel dapat ruang lebih lebar */
    .grid-row.ppn-grid {
      display: grid;
      grid-template-columns: minmax(260px, 1.2fr) minmax(0, 1.5fr);
      gap: 24px;
      align-items: stretch; /* tinggi kedua kartu sama, sisi bawah lurus */
    }

    .grid-row.ppn-grid > .card {
      height: 100%;
      margin-bottom: 0;
    }

    /* Tombol aksi selalu satu baris, rata kanan */
    .ppn-actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 8px;
      flex-wrap: nowrap;
      white-space: nowrap;
    }

    .ppn-table th.col-aksi {
      text-align: right;
    }

    .ppn-persentase {
      font-weight: 600;
    }

    /* [BARU] teks bantuan di bawah input persentase */
    .ppn-hint {
      display: block;
      margin-top: 4px;
      font-size: 0.8rem;
      color: #8a94a6;
    }

    /* [BARU] tombol edit */
    .btn-edit {
      background: #e8f1ff;
      color: #2f7df6;
    }
    .btn-edit:hover {
      background: #d5e6ff;
    }

    /* [BARU] modal edit PPN */
    .ppn-modal {
      position: fixed;
      inset: 0;
      z-index: 9999;
      background: rgba(0, 0, 0, 0.45);
      align-items: center;
      justify-content: center;
      padding: 16px;
    }
    .ppn-modal-box {
      width: min(440px, 100%);
      background: var(--card-bg, #fff);
      color: inherit;
      border-radius: 12px;
      padding: 22px 24px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
    }
    .ppn-modal-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
    }
    .ppn-modal-head h3 {
      margin: 0;
      font-size: 1.1rem;
    }
    .ppn-modal-x {
      background: none;
      border: 0;
      font-size: 1.6rem;
      line-height: 1;
      cursor: pointer;
      color: inherit;
      opacity: 0.6;
    }
    .ppn-modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      margin-top: 16px;
    }

    @media (max-width: 900px) {
      .grid-row.ppn-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <div class="page-header">
    <div class="page-title">Input PPN</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Data Air</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>PPN</li>
    </ul>
  </div>

  @if($errors->any())
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <div class="grid-row ppn-grid">

    {{-- FORM INPUT PPN --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Tambah Data PPN</h3>
          <p>Isi persentase PPN. Status aktif hanya boleh satu, ubah melalui tombol di tabel.</p>
        </div>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('ppn.store') }}">
          @csrf

          <div class="form-grid">
            {{-- [DIUBAH] label "Persentase (%)" menjadi "Persentase Laporan (%)" --}}
            <div class="form-group" style="grid-column: 1 / -1;">
              <label for="persentase">Persentase Laporan (%)</label>
              <input type="number" id="persentase" name="persentase" class="form-control"
                     step="0.01" min="0" max="100" placeholder="Contoh: 12"
                     value="{{ old('persentase') }}" required>
              <small class="ppn-hint">Angka yang ditampilkan di laporan/rekapan (PDF &amp; Excel).</small>
            </div>

            {{-- [BARU] Persentase Perhitungan --}}
            <div class="form-group" style="grid-column: 1 / -1;">
              <label for="persentase_realisasi">Persentase Perhitungan (%)</label>
              <input type="number" id="persentase_realisasi" name="persentase_realisasi" class="form-control"
                     step="0.01" min="0" max="100" placeholder="Contoh: 11"
                     value="{{ old('persentase_realisasi') }}" required>
              <small class="ppn-hint">Angka yang dipakai untuk menghitung nominal PPN sebenarnya.</small>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    {{-- TABEL DATA PPN --}}
    <div class="card">
      <div class="card-header">
        <div class="card-header-title">
          <h3>Data PPN</h3>
          <p>Daftar persentase PPN yang tersimpan</p>
        </div>
      </div>
      <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="app-sales-table ppn-table">
            <thead>
              <tr>
                <th>No</th>
                {{-- [DIUBAH] 1 kolom "Persentase" menjadi 2 kolom --}}
                <th>Persentase Laporan</th>
                <th>Persentase Perhitungan</th>
                <th>Status</th>
                <th class="col-aksi">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ppns as $i => $ppn)
              <tr>
                <td>{{ $ppns->firstItem() + $i }}</td>

                {{-- Persentase Laporan (tampil di rekapan) --}}
                <td><span class="ppn-persentase">{{ $ppn->persentase }}%</span></td>

                {{-- [BARU] Persentase Perhitungan (kalau kosong, otomatis sama dengan laporan) --}}
                <td><span class="ppn-persentase">{{ $ppn->persentase_realisasi ?? $ppn->persentase }}%</span></td>

                <td>
                  <span class="badge-status {{ $ppn->status == 'aktif' ? 'badge-aktif' : 'badge-nonaktif' }}">
                    {{ ucfirst($ppn->status) }}
                  </span>
                </td>

                <td>
                  <div class="ppn-actions">
                    {{-- Data yang sudah aktif tidak perlu tombol/badge lagi (sudah ada di kolom Status) --}}
                    @if($ppn->status != 'aktif')
                      <form action="{{ route('ppn.activate', $ppn->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" title="Ubah status menjadi aktif">
                          <i class="fa-solid fa-toggle-on"></i> Aktifkan
                        </button>
                      </form>
                    @endif

                    {{-- [BARU] Tombol Edit (tidak tampil untuk petugas, sama seperti hapus) --}}
                    @if(Auth::user()->role !== 'petugas')
                      <button type="button" class="btn btn-icon btn-edit" title="Edit"
                              data-action="{{ route('ppn.update', $ppn->id) }}"
                              data-persentase="{{ $ppn->persentase }}"
                              data-realisasi="{{ $ppn->persentase_realisasi ?? $ppn->persentase }}"
                              onclick="bukaEditPpn(this)">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                    @endif

                    @if(Auth::user()->role === 'petugas')
                      <button type="button" class="btn btn-icon" disabled title="Anda tidak memiliki akses untuk menghapus data">
                        <i class="fa-solid fa-lock"></i>
                      </button>
                    @else
                      <form action="{{ route('ppn.destroy', $ppn->id) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus data PPN ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon btn-delete" title="Hapus">
                          <i class="fa-solid fa-trash-can"></i>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                {{-- [DIUBAH] colspan 4 menjadi 5 karena kolom bertambah --}}
                <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                  <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                  Belum ada data PPN
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @include('partials.pagination', ['paginator' => $ppns])
      </div>
    </div>

  </div>

  {{-- [BARU] MODAL EDIT PPN --}}
  <div id="modalEditPpn" class="ppn-modal" style="display: none;"
       onclick="if (event.target === this) tutupEditPpn()">
    <div class="ppn-modal-box">
      <div class="ppn-modal-head">
        <h3>Edit Data PPN</h3>
        <button type="button" class="ppn-modal-x" onclick="tutupEditPpn()" title="Tutup">&times;</button>
      </div>

      <form method="POST" id="formEditPpn" action="">
        @csrf
        @method('PUT')

        <div class="form-group">
          <label for="edit_persentase">Persentase Laporan (%)</label>
          <input type="number" id="edit_persentase" name="persentase" class="form-control"
                 step="0.01" min="0" max="100" required>
          <small class="ppn-hint">Angka yang ditampilkan di laporan/rekapan (PDF &amp; Excel).</small>
        </div>

        <div class="form-group" style="margin-top: 14px;">
          <label for="edit_persentase_realisasi">Persentase Perhitungan (%)</label>
          <input type="number" id="edit_persentase_realisasi" name="persentase_realisasi" class="form-control"
                 step="0.01" min="0" max="100" required>
          <small class="ppn-hint">Angka yang dipakai untuk menghitung nominal PPN sebenarnya.</small>
        </div>

        <small class="ppn-hint" style="margin-top: 12px;">
          Catatan: kalau data ini sedang berstatus Aktif, perubahan langsung berlaku di seluruh rekapan.
        </small>

        <div class="ppn-modal-actions">
          <button type="button" class="btn btn-sm" onclick="tutupEditPpn()">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function bukaEditPpn(btn) {
      document.getElementById('formEditPpn').action = btn.dataset.action;
      document.getElementById('edit_persentase').value = btn.dataset.persentase;
      document.getElementById('edit_persentase_realisasi').value = btn.dataset.realisasi;
      document.getElementById('modalEditPpn').style.display = 'flex';
    }

    function tutupEditPpn() {
      document.getElementById('modalEditPpn').style.display = 'none';
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') tutupEditPpn();
    });
  </script>

@endsection