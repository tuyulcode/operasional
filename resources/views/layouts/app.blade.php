<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — e-Operasional</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- FontAwesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">

  @stack('styles')
</head>
<body>

<div class="app-container">

  {{-- SIDEBAR --}}
  @include('partials.sidebar')

  {{-- MAIN CONTENT WRAPPER --}}
  <div class="main-content-wrapper">

    {{-- TOP NAVBAR --}}
    @include('partials.header')

    {{-- MAIN BODY CONTENT --}}
    <main class="main-content">
      @yield('content')

      {{-- FOOTER --}}
      @include('partials.footer')
    </main>

  </div>
</div>

<!-- Custom JS -->
<script src="{{ asset('js/app.js') }}"></script>

<div id="toastContainer"></div>
@if(session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(@json(session('success')), 'success');
    });
  </script>
@endif
@if(session('error'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      showToast(@json(session('error')), 'error');
    });
  </script>
@endif

@stack('scripts')
</body>
</html>
