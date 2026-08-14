@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')

  <div class="page-header">
    <div class="page-title">Profil Saya</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Profil Saya</li>
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
        <h3>Info Akun</h3>
        <p>Informasi akun kamu di sistem</p>
      </div>
    </div>
    <div class="card-body">
      <div class="profile-info-grid">
        <div class="profile-info-item">
          <span class="profile-info-label">Username</span>
          <span class="profile-info-value">{{ $user->username ?? '-' }}</span>
        </div>
        <div class="profile-info-item">
          <span class="profile-info-label">Role / Jabatan</span>
          <span class="profile-info-value">
            <span class="badge-status badge-aktif">{{ ucfirst($user->role ?? '-') }}</span>
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="card" style="margin-top: 20px;">
    <div class="card-header">
      <div class="card-header-title">
        <h3>Ubah Password</h3>
        <p>Pastikan gunakan password yang kuat dan mudah kamu ingat</p>
      </div>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('profile.password.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
          <label for="current_password">Password Lama</label>
          <input type="password" id="current_password" name="current_password" class="form-control"
                 placeholder="Masukkan password lama" required>
        </div>

        <div class="form-group">
          <label for="new_password">Password Baru</label>
          <input type="password" id="new_password" name="new_password" class="form-control"
                 placeholder="Minimal 8 karakter" minlength="8" required>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
          <label for="new_password_confirmation">Konfirmasi Password Baru</label>
          <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control"
                 placeholder="Ulangi password baru" minlength="8" required>
        </div>

        <div style="margin-top: 20px; text-align: right;">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-key"></i> Simpan Password Baru
          </button>
        </div>
      </form>
    </div>
  </div>

@endsection

@push('styles')
<style>
  .profile-info-grid {
    display: flex;
    flex-direction: column;
    gap: 18px;
  }

  .profile-info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding-bottom: 14px;
    border-bottom: 1px solid #eee;
  }

  .profile-info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .profile-info-label {
    font-size: 0.8rem;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  .profile-info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
  }
</style>
@endpush