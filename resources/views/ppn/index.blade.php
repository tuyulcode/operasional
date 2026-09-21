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
            <div class="form-group">
              <label for="persentase">Persentase (%)</label>
              <input type="number" id="persentase" name="persentase" class="form-control"
                     step="0.01" min="0" max="100" placeholder="Contoh: 11"
                     value="{{ old('persentase') }}" required>
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
                <th>Persentase</th>
                <th>Status</th>
                <th class="col-aksi">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ppns as $i => $ppn)
              <tr>
                <td>{{ $ppns->firstItem() + $i }}</td>

                {{-- Persentase cukup teks biasa; warna status cukup di kolom Status --}}
                <td><span class="ppn-persentase">{{ $ppn->persentase }}%</span></td>

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
                <td colspan="4" style="text-align: center; padding: 30px; color: #999;">
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

@endsection