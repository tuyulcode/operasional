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

  @if($errors->any())
    <div class="alert-custom alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <span>{{ $errors->first() }}</span>
    </div>
  @endif

  <div class="pf-layout">

    {{-- KARTU IDENTITAS --}}
    <div class="pf-card pf-identity">

      <div class="pf-identity-banner"></div>

      <div class="pf-identity-body">
        <form id="photoForm" method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <label for="photo" class="pf-avatar-wrapper" title="Klik untuk pilih foto">
            @if($user->photo ?? false)
              <img src="{{ asset('storage/' . $user->photo) }}" alt="Foto Profil" class="pf-avatar-img" id="pfAvatarImg">
            @else
              <div class="pf-avatar-fallback" id="pfAvatarFallback">{{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}</div>
              <img src="" alt="Foto Profil" class="pf-avatar-img" id="pfAvatarImg" style="display: none;">
            @endif
            <span class="pf-avatar-edit"><i class="fa-solid fa-camera"></i></span>
          </label>

          <input type="file" name="photo" id="photo" accept="image/png, image/jpeg, image/jpg" class="pf-hidden-input"
                 onchange="pfPreviewPhoto(this)">

          <h2 class="pf-username">{{ $user->username ?? '-' }}</h2>
          <span class="pf-role-badge">{{ ucfirst($user->role ?? '-') }}</span>

          <div class="pf-photo-controls">
            <label for="photo" class="pf-change-photo-btn" id="pfChangeBtn">
              <i class="fa-solid fa-arrows-rotate"></i> Ganti Foto
            </label>

            <div id="pfPhotoActions" class="pf-photo-actions" style="display: none;">
              <button type="submit" class="pf-save-photo-btn">
                <i class="fa-solid fa-check"></i> Simpan
              </button>
              <button type="button" class="pf-cancel-photo-btn" onclick="pfCancelPhoto()">
                Batal
              </button>
            </div>
          </div>
        </form>

        <p class="pf-hint">JPG atau PNG, maksimal 2MB</p>

        <div class="pf-identity-footer">
          <i class="fa-solid fa-circle-info"></i>
          <span>Terhubung sebagai {{ ucfirst($user->role ?? '-') }}</span>
        </div>
      </div>
    </div>

    {{-- KOLOM KANAN --}}
    <div class="pf-main">

      {{-- INFO AKUN --}}
      <div class="pf-card">
        <div class="pf-card-head">
          <div class="pf-card-head-icon"><i class="fa-solid fa-id-card"></i></div>
          <div>
            <h3>Info Akun</h3>
            <p>Data identitas kamu di sistem</p>
          </div>
        </div>

        <div class="pf-info-row">
          <div class="pf-info-col">
            <span class="pf-info-label"><i class="fa-solid fa-user"></i> Username</span>
            <span class="pf-info-value">{{ $user->username ?? '-' }}</span>
          </div>
          <div class="pf-info-col">
            <span class="pf-info-label"><i class="fa-solid fa-briefcase"></i> Role / Jabatan</span>
            <span class="pf-info-value">{{ ucfirst($user->role ?? '-') }}</span>
          </div>
        </div>
      </div>

      {{-- UBAH PASSWORD --}}
      <div class="pf-card">
        <div class="pf-card-head">
          <div class="pf-card-head-icon"><i class="fa-solid fa-key"></i></div>
          <div>
            <h3>Ubah Password</h3>
            <p>Gunakan kombinasi yang kuat dan mudah kamu ingat</p>
          </div>
        </div>

        <form method="POST" action="{{ route('profile.password.update') }}" class="pf-form">
          @csrf
          @method('PUT')

          <div class="pf-field">
            <label for="current_password">Password Lama</label>
            <div class="pf-input-icon">
              <i class="fa-solid fa-lock"></i>
              <input type="password" id="current_password" name="current_password"
                     placeholder="Masukkan password lama" required>
            </div>
          </div>

          <div class="pf-field-row">
            <div class="pf-field">
              <label for="new_password">Password Baru</label>
              <div class="pf-input-icon">
                <i class="fa-solid fa-lock-open"></i>
                <input type="password" id="new_password" name="new_password"
                       placeholder="Minimal 8 karakter" minlength="8" required>
              </div>
            </div>

            <div class="pf-field">
              <label for="new_password_confirmation">Konfirmasi Password</label>
              <div class="pf-input-icon">
                <i class="fa-solid fa-lock-open"></i>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                       placeholder="Ulangi password baru" minlength="8" required>
              </div>
            </div>
          </div>

          <div class="pf-form-footer">
            <button type="submit" class="pf-submit-btn">
              <i class="fa-solid fa-check"></i> Simpan Password Baru
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>

@endsection

@push('styles')
<style>
  .pf-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 20px;
    align-items: stretch; /* kolom kiri & kanan tingginya disamakan lagi */
  }

  @media (max-width: 800px) {
    .pf-layout {
      grid-template-columns: 1fr;
    }
  }

  .pf-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(16, 24, 40, 0.06), 0 1px 2px rgba(16, 24, 40, 0.04);
    border: 1px solid #eef0f3;
  }

  /* ===== Kartu Identitas ===== */
  .pf-identity {
    text-align: center;
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  .pf-identity-banner {
    height: 88px;
    width: 100%;
    flex-shrink: 0;
    background: linear-gradient(135deg, #1e2749, #4f46e5 65%, #6366f1);
  }

  .pf-identity-body {
    flex: 1;
    padding: 0 24px 26px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center; /* konten ditengahkan di sisa tinggi kartu, bukan numpuk di atas */
  }

  .pf-identity-footer {
    margin-top: 22px;
    padding-top: 16px;
    border-top: 1px dashed #eef0f3;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.75rem;
    color: #98a2b3;
  }

  .pf-identity-footer i {
    color: #c7cbd4;
  }

  .pf-identity-body form {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
  }

  .pf-avatar-wrapper {
    position: relative;
    display: block;
    width: 104px;
    height: 104px;
    border-radius: 50%;
    cursor: pointer;
    margin-top: -52px; /* avatar menumpuk di atas banner, tidak ada celah kosong */
    margin-bottom: 14px;
    flex-shrink: 0;
  }

  .pf-avatar-img {
    width: 104px;
    height: 104px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    box-shadow: 0 0 0 4px #fff;
  }

  .pf-avatar-fallback {
    width: 104px;
    height: 104px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.25rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    box-shadow: 0 0 0 4px #fff;
  }

  .pf-avatar-edit {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(17, 24, 39, 0.55);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    opacity: 0;
    transition: opacity 0.15s ease;
  }

  .pf-avatar-wrapper:hover .pf-avatar-edit {
    opacity: 1;
  }

  .pf-hidden-input {
    display: none;
  }

  .pf-username {
    margin: 0 0 6px;
    font-size: 1.1rem;
    font-weight: 700;
    color: #101828;
  }

  .pf-role-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 999px;
    background: #ecfdf5;
    color: #059669;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 18px;
  }

  .pf-photo-controls {
    min-height: 34px; /* sama tinggi utk state "Ganti Foto" maupun "Simpan/Batal" -> tidak ada elemen yg loncat */
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .pf-change-photo-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 16px;
    border-radius: 8px;
    background: #f4f5f7;
    color: #344054;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
    border: 1px solid #e4e6ea;
  }

  .pf-change-photo-btn:hover {
    background: #eceef1;
  }

  .pf-hint {
    margin: 14px 0 0;
    font-size: 0.72rem;
    color: #98a2b3;
  }

  /* ===== Kolom kanan ===== */
  .pf-main {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .pf-main .pf-card {
    padding: 28px 24px;
  }

  .pf-card-head {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 22px;
  }

  .pf-card-head-icon {
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: #eef2ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
  }

  .pf-card-head h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #101828;
  }

  .pf-card-head p {
    margin: 2px 0 0;
    font-size: 0.82rem;
    color: #98a2b3;
  }

  .pf-info-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  @media (max-width: 560px) {
    .pf-info-row {
      grid-template-columns: 1fr;
    }
  }

  .pf-info-col {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 16px 18px;
    background: #f9fafb;
    border-radius: 10px;
  }

  .pf-info-label {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 0.72rem;
    font-weight: 600;
    color: #98a2b3;
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  .pf-info-label i {
    font-size: 0.7rem;
    color: #b0b6c1;
  }

  .pf-info-value {
    font-size: 0.98rem;
    font-weight: 600;
    color: #1d2939;
  }

  /* ===== Form password ===== */
  .pf-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
    margin-bottom: 18px;
  }

  .pf-field-row {
    display: flex;
    gap: 16px;
  }

  @media (max-width: 560px) {
    .pf-field-row {
      flex-direction: column;
      gap: 0;
    }
  }

  .pf-field label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #344054;
  }

  .pf-input-icon {
    position: relative;
    display: flex;
    align-items: center;
  }

  .pf-input-icon i {
    position: absolute;
    left: 14px;
    font-size: 0.85rem;
    color: #98a2b3;
  }

  .pf-input-icon input {
    width: 100%;
    padding: 11px 14px 11px 38px;
    border-radius: 9px;
    border: 1px solid #e4e6ea;
    font-size: 0.9rem;
    font-family: inherit;
    background: #fcfcfd;
    transition: border-color 0.15s ease, background 0.15s ease;
  }

  .pf-input-icon input:focus {
    outline: none;
    border-color: #6366f1;
    background: #fff;
  }

  /* Matikan warna kuning bawaan browser saat autofill (mis. password manager) */
  .pf-input-icon input:-webkit-autofill,
  .pf-input-icon input:-webkit-autofill:hover,
  .pf-input-icon input:-webkit-autofill:focus {
    -webkit-text-fill-color: #1d2939;
    -webkit-box-shadow: 0 0 0 1000px #fcfcfd inset;
    box-shadow: 0 0 0 1000px #fcfcfd inset;
    transition: background-color 9999s ease-in-out 0s;
  }

  .pf-form-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 4px;
  }

  .pf-submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 22px;
    border-radius: 9px;
    border: none;
    background: #4f46e5;
    color: #fff;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
  }

  .pf-submit-btn:hover {
    background: #4338ca;
  }

  .pf-photo-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
  }

  .pf-save-photo-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid #059669;
    background: #ecfdf5;
    color: #059669;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
  }

  .pf-save-photo-btn:hover {
    background: #d1fae5;
  }

  .pf-cancel-photo-btn {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid #e4e6ea;
    background: #fff;
    color: #667085;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease;
  }

  .pf-cancel-photo-btn:hover {
    background: #f9fafb;
  }
</style>
@endpush

@push('scripts')
<script>
  let pfOriginalAvatarHtml = null;

  function pfPreviewPhoto(input) {
    const file = input.files[0];
    if (!file) return;

    if (pfOriginalAvatarHtml === null) {
      pfOriginalAvatarHtml = document.querySelector('.pf-avatar-wrapper').innerHTML;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      const img = document.getElementById('pfAvatarImg');
      const fallback = document.getElementById('pfAvatarFallback');

      img.src = e.target.result;
      img.style.display = 'block';
      if (fallback) fallback.style.display = 'none';

      document.getElementById('pfPhotoActions').style.display = 'flex';
      document.getElementById('pfChangeBtn').style.display = 'none';
    };
    reader.readAsDataURL(file);
  }

  function pfCancelPhoto() {
    document.getElementById('photo').value = '';
    if (pfOriginalAvatarHtml !== null) {
      document.querySelector('.pf-avatar-wrapper').innerHTML = pfOriginalAvatarHtml;
    }
    document.getElementById('pfPhotoActions').style.display = 'none';
    document.getElementById('pfChangeBtn').style.display = 'inline-flex';
  }
</script>
@endpush