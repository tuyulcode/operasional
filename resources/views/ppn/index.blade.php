@extends('layouts.app')

@section('title', 'Input PPN')

@section('content')

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

  <div class="grid-row">

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
          <table class="app-sales-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Persentase</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ppns as $i => $ppn)
              <tr>
                <td>{{ $ppns->firstItem() + $i }}</td>
                <td>
                  <span class="badge-status {{ $ppn->status == 'aktif' ? 'badge-aktif' : 'badge-nonaktif' }}">
                    {{ $ppn->persentase }}%
                  </span>
                </td>
                <td>
                  <span class="badge-status {{ $ppn->status == 'aktif' ? 'badge-aktif' : 'badge-nonaktif' }}">
                    {{ ucfirst($ppn->status) }}
                  </span>
                </td>
                <td>
                  @if($ppn->status == 'aktif')
                    <span class="badge-status badge-aktif"><i class="fa-solid fa-check"></i> Aktif</span>
                  @else
                    <form action="{{ route('ppn.activate', $ppn->id) }}" method="POST" style="display: inline;">
                      @csrf
                      <button type="submit" class="btn btn-primary btn-sm" title="Ubah status menjadi aktif">
                        <i class="fa-solid fa-toggle-on"></i> Ubah Status
                      </button>
                    </form>
                  @endif
                  <form action="{{ route('ppn.destroy', $ppn->id) }}" method="POST" style="display: inline;"
                        onsubmit="return confirm('Yakin ingin menghapus data PPN ini?');">
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
