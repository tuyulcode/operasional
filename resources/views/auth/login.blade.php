<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Masuk — e-Operasional</title>

  <!-- Favicon -->
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- FontAwesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      min-height: 100vh;
      background-color: #eef2f7;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    /* ============ LOGIN CARD ============ */
    .login-card {
      display: flex;
      width: 900px;
      max-width: 100%;
      background-color: #fff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
      animation: fadeUp 0.45s ease;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(14px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* ============ LEFT VISUAL PANEL ============ */
    .login-visual {
      flex: 0 0 46%;
      background: linear-gradient(155deg, #1d4ed8 0%, #2563eb 45%, #2f8ff5 100%);
      color: #fff;
      padding: 42px 38px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }

    .login-visual::before {
      content: '';
      position: absolute;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.07);
      top: -90px;
      right: -70px;
    }

    .login-visual::after {
      content: '';
      position: absolute;
      width: 160px;
      height: 160px;
      border: 2px solid rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      bottom: -60px;
      left: -50px;
    }

    .visual-logo {
      position: relative;
      z-index: 1;
    }

    .visual-logo img {
      height: 42px;
      width: auto;
      display: block;
    }

    .visual-welcome {
      position: relative;
      z-index: 1;
      margin: 8px 0 28px;
    }

    .visual-welcome h2 {
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .visual-welcome p {
      font-size: 0.85rem;
      line-height: 1.7;
      color: rgba(255, 255, 255, 0.82);
    }

    .visual-features {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .visual-features .feature {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 0.82rem;
      color: #e7eeff;
    }

    .visual-features .feature i {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background-color: rgba(255, 255, 255, 0.16);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      flex-shrink: 0;
    }

    .visual-footer {
      position: relative;
      z-index: 1;
      font-size: 0.72rem;
      color: rgba(255, 255, 255, 0.55);
    }

    /* ============ RIGHT FORM PANEL ============ */
    .login-form {
      flex: 1;
      padding: 46px 44px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .form-heading {
      margin-bottom: 26px;
    }

    .form-heading h2 {
      font-size: 1.35rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 6px;
    }

    .form-heading p {
      font-size: 0.85rem;
      color: #64748b;
    }

    .alert-error {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background-color: #fdf2f3;
      border: 1px solid #f5c6cd;
      border-radius: 8px;
      padding: 12px 14px;
      margin-bottom: 20px;
    }

    .alert-error i {
      color: #d6334f;
      font-size: 0.95rem;
      margin-top: 1px;
    }

    .alert-error span {
      font-size: 0.82rem;
      color: #b0253d;
      line-height: 1.5;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-group label {
      display: block;
      font-size: 0.8rem;
      font-weight: 500;
      color: #475569;
      margin-bottom: 7px;
    }

    .input-wrapper {
      position: relative;
    }

    .input-wrapper input {
      width: 100%;
      padding: 12px 42px 12px 40px;
      background-color: #fff;
      border: 1px solid #d7dee8;
      border-radius: 9px;
      font-size: 0.9rem;
      color: #1e293b;
      outline: none;
      transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .input-wrapper input::placeholder {
      color: #9aa7b8;
    }

    .input-wrapper input:focus {
      border-color: #4099ff;
      box-shadow: 0 0 0 3px rgba(64, 153, 255, 0.14);
    }

    .input-wrapper > i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: 0.9rem;
      pointer-events: none;
    }

    .toggle-password {
      position: absolute;
      right: 6px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      width: 32px;
      height: 32px;
      border-radius: 6px;
      font-size: 0.85rem;
      transition: color 0.15s ease, background-color 0.15s ease;
    }

    .toggle-password:hover {
      color: #4099ff;
      background-color: #f1f5f9;
    }

    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      font-size: 0.82rem;
      color: #475569;
      user-select: none;
    }

    .remember-me input[type="checkbox"] {
      width: 15px;
      height: 15px;
      accent-color: #4099ff;
      cursor: pointer;
    }

    .btn-login {
      width: 100%;
      padding: 13px;
      background: linear-gradient(120deg, #2f8ff5 0%, #2ed8b6 100%);
      border: none;
      border-radius: 9px;
      color: #fff;
      font-size: 0.9rem;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      box-shadow: 0 8px 20px rgba(64, 153, 255, 0.3);
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .btn-login:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 24px rgba(64, 153, 255, 0.38);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .form-footer {
      margin-top: 26px;
      text-align: center;
      font-size: 0.75rem;
      color: #94a3b8;
    }

    .form-footer strong {
      color: #64748b;
      font-weight: 500;
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 860px) {
      .login-visual {
        display: none;
      }

      .login-form {
        padding: 38px 30px;
      }
    }
  </style>
</head>
<body>

  <div class="login-card">

    {{-- LEFT VISUAL PANEL --}}
    <aside class="login-visual">
      <div class="visual-logo">
        <img src="{{ asset('images/logo.png') }}" alt="e-Operasional">
      </div>

      <div>
        <div class="visual-welcome">
          <h2>Selamat Datang</h2>
          <p>
            Kelola operasional perusahaan Anda — pencatatan E-Toll, BBM, dan
            tagihan air dalam satu sistem yang terintegrasi.
          </p>
        </div>

        <div class="visual-features">
          <div class="feature">
            <i class="fa-solid fa-check"></i>
            Pencatatan pemakaian E-Toll kendaraan
          </div>
          <div class="feature">
            <i class="fa-solid fa-check"></i>
            BBM & Consumable
          </div>
          <div class="feature">
            <i class="fa-solid fa-check"></i>
            Monitoring tagihan air per titik meter
          </div>
        </div>
      </div>

      <div class="visual-footer">
        Copyright &copy; {{ date('Y') }} e-Operasional
      </div>
    </aside>

    {{-- RIGHT FORM PANEL --}}
    <section class="login-form">
      <div class="form-heading">
        <h2>Masuk</h2>
        <p>Silakan masuk dengan akun Anda untuk melanjutkan.</p>
      </div>

      @if($errors->any())
        <div class="alert-error">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span>{{ $errors->first() }}</span>
        </div>
      @endif

      <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-wrapper">
            <input type="text" id="username" name="username" placeholder="Masukkan username"
                   value="{{ old('username') }}" autocomplete="username" autofocus required>
            <i class="fa-regular fa-user"></i>
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" placeholder="Masukkan password"
                   autocomplete="current-password" required>
            <i class="fa-solid fa-lock"></i>
            <button type="button" class="toggle-password" onclick="togglePassword()">
              <i class="fa-regular fa-eye" id="toggleIcon"></i>
            </button>
          </div>
        </div>

        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" name="remember">
            <span>Ingat saya</span>
          </label>
        </div>

        <button type="submit" class="btn-login" id="btnLogin">
          Masuk
        </button>
      </form>

      <div class="form-footer">
        Copyright &copy; {{ date('Y') }} <strong>e-Operasional</strong>. Seluruh hak cipta dilindungi.
      </div>
    </section>

  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }
  </script>
</body>
</html>
