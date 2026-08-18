@extends('layouts.app')

@section('title', 'Users')

@section('content')

  <div class="page-header">
    <div class="page-title">Users</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Pengaturan</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Users</li>
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
        <h3>Users</h3>
        <p>Daftar pengguna yang dapat login ke sistem</p>
      </div>
      <div class="card-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddUser()">
          <i class="fa-solid fa-plus"></i> Tambah User
        </button>
      </div>
    </div>
    <div class="card-body" style="padding: 0;">
      <div class="table-responsive">
        <table class="app-sales-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Username</th>
              <th>Role</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $i => $u)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td>
                <div class="app-info">
                  <div>
                    <div class="app-title">{{ $u->username }}</div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge-status {{ $u->role === 'admin' ? 'badge-aktif' : 'badge-nonaktif' }}">
                  {{ ucfirst($u->role) }}
                </span>
              </td>
              <td>
                <button type="button" class="btn btn-icon btn-edit" title="Edit"
                        data-id="{{ $u->id }}"
                        data-username="{{ $u->username }}"
                        data-role="{{ $u->role }}"
                        onclick="openEditUser(this)">
                  <i class="fa-solid fa-pen"></i>
                </button>
                @if($u->id !== auth()->id())
                <button type="button" class="btn btn-icon btn-delete" title="Hapus"
                        data-id="{{ $u->id }}"
                        data-username="{{ $u->username }}"
                        onclick="openDeleteUser(this)">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" style="text-align: center; padding: 30px; color: #999;">
                <i class="fa-solid fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.3;"></i>
                Belum ada data user
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- MODAL TAMBAH / EDIT USER --}}
  <div class="modal-overlay" id="userModal">
    <div class="modal">
      <div class="modal-header">
        <h3 class="modal-title" id="userModalTitle">{{ $edit ? 'Edit User' : 'Tambah User' }}</h3>
        <button type="button" class="modal-close" onclick="closeUserModal()">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="userForm" method="POST"
            action="{{ $edit ? route('users.update', $edit->id) : route('users.store') }}">
        @csrf
        <input type="hidden" name="_method" id="userMethod" value="{{ $edit ? 'PUT' : '' }}">

        <div class="modal-body">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" maxlength="100"
                   placeholder="Contoh: budi.santoso"
                   value="{{ old('username', $edit->username ?? '') }}" required>
          </div>

          <div class="form-group">
            <label for="password">
              Password
              <span id="passwordHint" style="font-weight: normal; color: #999;">{{ $edit ? '(kosongkan jika tidak diubah)' : '' }}</span>
            </label>
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="Minimal 6 karakter" autocomplete="new-password"
                   {{ $edit ? '' : 'required' }}>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label for="role">Role</label>
            <select id="role" name="role" class="form-control" required>
              <option value="">-- Pilih Role --</option>
              <option value="admin" {{ old('role', $edit->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
              <option value="petugas" {{ old('role', $edit->role ?? '') == 'petugas' ? 'selected' : '' }}>Petugas</option>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeUserModal()">
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
  <div class="modal-overlay" id="deleteUserModal">
    <div class="modal modal-confirm">
      <div class="modal-body modal-confirm-body">
        <div class="modal-confirm-icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="modal-confirm-title">Hapus User?</h3>
        <p class="modal-confirm-text">
          Yakin ingin menghapus user
          <strong id="deleteUserUsername">-</strong>
          ? Data yang dihapus tidak dapat dikembalikan.
        </p>
      </div>
      <form id="deleteUserForm" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="modal-footer modal-confirm-footer">
          <button type="button" class="btn btn-secondary" onclick="closeDeleteUserModal()">
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
    const overlay = document.getElementById('userModal');
    const deleteOverlay = document.getElementById('deleteUserModal');

    overlay.addEventListener('click', function(e) {
      if (e.target === overlay) closeUserModal();
    });

    deleteOverlay.addEventListener('click', function(e) {
      if (e.target === deleteOverlay) closeDeleteUserModal();
    });

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeUserModal();
        closeDeleteUserModal();
      }
    });

    @if($edit || $errors->any())
      document.getElementById('userModal').classList.add('show');
    @endif
  });

  function openAddUser() {
    const form = document.getElementById('userForm');
    form.reset();
    form.action = '{{ route('users.store') }}';
    document.getElementById('userMethod').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordHint').textContent = '';
    document.getElementById('userModalTitle').textContent = 'Tambah User';
    document.getElementById('userModal').classList.add('show');
    document.getElementById('username').focus();
  }

  function openEditUser(btn) {
    const form = document.getElementById('userForm');
    form.reset();
    form.action = '{{ route('users.update', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('userMethod').value = 'PUT';
    document.getElementById('username').value = btn.dataset.username;
    document.getElementById('role').value = btn.dataset.role;
    document.getElementById('password').required = false;
    document.getElementById('passwordHint').textContent = '(kosongkan jika tidak diubah)';
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userModal').classList.add('show');
  }

  function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
  }

  function openDeleteUser(btn) {
    const form = document.getElementById('deleteUserForm');
    form.action = '{{ route('users.destroy', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('deleteUserUsername').textContent = btn.dataset.username;
    document.getElementById('deleteUserModal').classList.add('show');
  }

  function closeDeleteUserModal() {
    document.getElementById('deleteUserModal').classList.remove('show');
  }
</script>
@endpush