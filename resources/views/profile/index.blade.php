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