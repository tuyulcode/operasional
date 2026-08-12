@extends('layouts.app')

@section('title', 'Input Harga BBM')

@section('content')

  <div class="page-header">
    <div class="page-title">Input Harga BBM</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Master Data</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Harga BBM</li>
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
        <h3>Input Harga BBM</h3>
        <p>Setiap jenis BBM hanya memiliki satu data harga.</p>
      </div>
    </div>
    <div class="card-body">

      <div class="tabs-nav">
        <button type="button" class="tab-btn active" data-tab="bensin" onclick="switchTab('bensin')">
          <i class="fa-solid fa-gas-pump"></i> Bensin
        </button>
        <button type="button" class="tab-btn" data-tab="solar" onclick="switchTab('solar')">
          <i class="fa-solid fa-oil-can"></i> Solar
        </button>
      </div>

      {{-- TAB BENSIN --}}
      <div class="tab-pane active" id="tab-bensin">
        <form method="POST" action="{{ route('harga-bbm.store') }}" class="harga-form">
          @csrf
          <input type="hidden" name="jenis" value="bensin">

          <div class="form-grid">
            <div class="form-group">
              <label for="bensin_harga_paiton">Harga Paiton</label>
              <input type="text" id="bensin_harga_paiton" name="harga_paiton" class="form-control rupiah-input"
                     inputmode="numeric" placeholder="Harga BBM per liter di Paiton"
                     value="{{ old('harga_paiton', $bensin ? number_format($bensin->harga_paiton, 0, ',', '.') : '') }}" required>
            </div>

            <div class="form-group">
              <label for="bensin_harga_luar_paiton">Harga Luar Paiton</label>
              <input type="text" id="bensin_harga_luar_paiton" name="harga_luar_paiton" class="form-control rupiah-input"
                     inputmode="numeric" placeholder="Harga BBM per liter di luar Paiton"
                     value="{{ old('harga_luar_paiton', $bensin ? number_format($bensin->harga_luar_paiton, 0, ',', '.') : '') }}" required>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Simpan Bensin
            </button>
          </div>
        </form>
      </div>

      {{-- TAB SOLAR --}}
      <div class="tab-pane" id="tab-solar">
        <form method="POST" action="{{ route('harga-bbm.store') }}" class="harga-form">
          @csrf
          <input type="hidden" name="jenis" value="solar">

          <div class="form-grid">
            <div class="form-group">
              <label for="solar_harga_paiton">Harga Paiton</label>
              <input type="text" id="solar_harga_paiton" name="harga_paiton" class="form-control rupiah-input"
                     inputmode="numeric" placeholder="Harga BBM per liter di Paiton"
                     value="{{ old('harga_paiton', $solar ? number_format($solar->harga_paiton, 0, ',', '.') : '') }}" required>
            </div>

            <div class="form-group">
              <label for="solar_harga_luar_paiton">Harga Luar Paiton</label>
              <input type="text" id="solar_harga_luar_paiton" name="harga_luar_paiton" class="form-control rupiah-input"
                     inputmode="numeric" placeholder="Harga BBM per liter di luar Paiton"
                     value="{{ old('harga_luar_paiton', $solar ? number_format($solar->harga_luar_paiton, 0, ',', '.') : '') }}" required>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-floppy-disk"></i> Simpan Solar
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.rupiah-input').forEach(function(input) {
      input.addEventListener('input', function() {
        formatRupiah(input);
      });
    });

    document.querySelectorAll('.harga-form').forEach(function(form) {
      form.addEventListener('submit', function() {
        form.querySelectorAll('.rupiah-input').forEach(function(input) {
          input.value = input.value.replace(/[^\d]/g, '');
        });
      });
    });

    @if($errors->any() && old('jenis'))
      switchTab('{{ old('jenis') }}');
    @endif
  });

  function formatRupiah(input) {
    const digits = input.value.replace(/[^\d]/g, '');
    input.value = digits ? Number(digits).toLocaleString('id-ID') : '';
  }

  function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(function(btn) {
      btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    document.querySelectorAll('.tab-pane').forEach(function(pane) {
      pane.classList.toggle('active', pane.id === 'tab-' + tab);
    });
  }
</script>
@endpush
