<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Login — e-Operasional</title>

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
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
      position: relative;
      overflow: hidden;
    }

    /* Animated background circles */
    body::before,
    body::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.3;
      animation: float 8s ease-in-out infinite;
    }
    body::before {
      width: 400px;
      height: 400px;
      background: linear-gradient(135deg, #4099ff, #2ed8b6);
      top: -100px;
      right: -100px;
    }
    body::after {
      width: 350px;
      height: 350px;
      background: linear-gradient(135deg, #FF5370, #FFB64D);
      bottom: -80px;
      left: -80px;
      animation-delay: -4s;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px) scale(1); }
      50% { transform: translateY(-30px) scale(1.05); }
    }

    .login-container {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 440px;
      padding: 20px;
    }

    .login-card {
      background: rgba(30, 41, 59, 0.85);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 20px;
      padding: 48px 40px;
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
      animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .login-brand {
      text-align: center;
      margin-bottom: 36px;
    }

    .login-brand .brand-icon {
      width: 64px;
      height: 64px;
      background: linear-gradient(135deg, #4099ff, #2ed8b6);
      border-radius: 16px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      box-shadow: 0 8px 25px rgba(64, 153, 255, 0.3);
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { box-shadow: 0 8px 25px rgba(64, 153, 255, 0.3); }
      50% { box-shadow: 0 8px 35px rgba(64, 153, 255, 0.5); }
    }

    .login-brand .brand-icon i {
      font-size: 1.8rem;
      color: #fff;
    }

    .login-brand h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #f1f5f9;
      letter-spacing: -0.5px;
    }

    .login-brand p {
      font-size: 0.85rem;
      color: #94a3b8;
      margin-top: 4px;
    }

    .form-group {
      margin-bottom: 22px;
      position: relative;
    }

    .form-group label {
      display: block;
      font-size: 0.8rem;
      font-weight: 500;
      color: #94a3b8;
      margin-bottom: 8px;
      letter-spacing: 0.3px;
    }

    .input-wrapper {
      position: relative;
    }

    .input-wrapper i {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
      font-size: 0.95rem;
      transition: color 0.3s;
    }

    .input-wrapper input {
      width: 100%;
      padding: 14px 16px 14px 46px;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid rgba(100, 116, 139, 0.3);
      border-radius: 12px;
      font-size: 0.9rem;
      color: #f1f5f9;
      outline: none;
      transition: all 0.3s ease;
    }

    .input-wrapper input::placeholder {
      color: #475569;
    }

    .input-wrapper input:focus {
      border-color: #4099ff;
      background: rgba(15, 23, 42, 0.8);
      box-shadow: 0 0 0 3px rgba(64, 153, 255, 0.15);
    }

    .input-wrapper input:focus + i,
    .input-wrapper input:focus ~ i {
      color: #4099ff;
    }

    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 28px;
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }

    .remember-me input[type="checkbox"] {
      width: 16px;
      height: 16px;
      accent-color: #4099ff;
      cursor: pointer;
    }

    .remember-me span {
      font-size: 0.8rem;
      color: #94a3b8;
    }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #4099ff 0%, #2ed8b6 100%);
      border: none;
      border-radius: 12px;
      color: #fff;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      box-shadow: 0 6px 20px rgba(64, 153, 255, 0.3);
      position: relative;
      overflow: hidden;
    }

    .btn-login::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(64, 153, 255, 0.4);
    }

    .btn-login:hover::before {
      left: 100%;
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .alert-error {
      background: rgba(255, 83, 112, 0.15);
      border: 1px solid rgba(255, 83, 112, 0.3);
      border-radius: 10px;
      padding: 12px 16px;
      margin-bottom: 22px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: shake 0.4s ease-in-out;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .alert-error i {
      color: #FF5370;
      font-size: 1.1rem;
    }

    .alert-error span {
      font-size: 0.82rem;
      color: #fca5a5;
    }

    .login-footer {
      text-align: center;
      margin-top: 28px;
      font-size: 0.75rem;
      color: #475569;
    }

    .login-footer a {
      color: #4099ff;
      text-decoration: none;
    }

    /* Particles / decorative dots */
    .particle {
      position: absolute;
      width: 4px;
      height: 4px;
      background: rgba(64, 153, 255, 0.3);
      border-radius: 50%;
      animation: particle-float 6s ease-in-out infinite;
    }

    .particle:nth-child(1) { top: 20%; left: 15%; animation-delay: 0s; }
    .particle:nth-child(2) { top: 60%; left: 80%; animation-delay: -2s; }
    .particle:nth-child(3) { top: 80%; left: 30%; animation-delay: -4s; }
    .particle:nth-child(4) { top: 10%; left: 70%; animation-delay: -1s; }
    .particle:nth-child(5) { top: 40%; left: 90%; animation-delay: -3s; }

    @keyframes particle-float {
      0%, 100% { transform: translateY(0px) scale(1); opacity: 0.3; }
      50% { transform: translateY(-20px) scale(1.5); opacity: 0.8; }
    }

    /* Toggle password visibility */
    .toggle-password {
      position: absolute;
      right: 16px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #64748b;
      cursor: pointer;
      font-size: 0.95rem;
      transition: color 0.3s;
    }
    .toggle-password:hover {
      color: #4099ff;
    }
  </style>
</head>
<body>

  <!-- Decorative particles -->
  <div class="particle"></div>
  <div class="particle"></div>
  <div class="particle"></div>
  <div class="particle"></div>
  <div class="particle"></div>

  <div class="login-container">
    <div class="login-card">

      <div class="login-brand">
        <div class="brand-icon">
          <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 48px; height: auto;">
        </div>
        <h1>e-Operasional</h1>
        <p>Sistem Manajemen Operasional</p>
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
          <i class="fa-solid fa-right-to-bracket"></i>&nbsp; Masuk
        </button>
      </form>

      <div class="login-footer">
        Copyright &copy; {{ date('Y') }} <strong>e-Operasional</strong>. All rights reserved.
      </div>

    </div>
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
