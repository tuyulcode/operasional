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

      <div class="harga-bbm-columns">

        {{-- BENSIN --}}
        <div class="harga-section">
          <h4 class="harga-section-title">
            <i class="fa-solid fa-gas-pump"></i> Bensin
          </h4>
          <form method="POST" action="{{ route('harga-bbm.store') }}" class="harga-form">
            @csrf
            <input type="hidden" name="jenis" value="bensin">

            <div class="form-group">
              <label for="bensin_harga_paiton">Harga Paiton</label>
              <input type="text" id="bensin_harga_paiton" name="harga_paiton" class="form-control rupiah-input"
                     inputmode="numeric" placeholder="Harga BBM per liter di Paiton"
                     value="{{ old('harga_paiton', $bensin ? number_format($bensin->harga_paiton, 0, ',', '.') : '') }}" required>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Bensin
              </button>
            </div>
          </form>
        </div>

        {{-- SOLAR --}}
        <div class="harga-section">
          <h4 class="harga-section-title">
            <i class="fa-solid fa-oil-can"></i> Solar
          </h4>
          <form method="POST" action="{{ route('harga-bbm.store') }}" class="harga-form">
            @csrf
            <input type="hidden" name="jenis" value="solar">

            <div class="form-group">
              <label for="solar_harga_paiton">Harga Paiton</label>
              <input type="text" id="solar_harga_paiton" name="harga_paiton" class="form-control rupiah-input"
                     inputmode="numeric" placeholder="Harga BBM per liter di Paiton"
                     value="{{ old('harga_paiton', $solar ? number_format($solar->harga_paiton, 0, ',', '.') : '') }}" required>
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
  </div>

@endsection

@push('styles')
<style>
  .harga-bbm-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }

  .harga-section {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 20px;
  }

  .harga-section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    font-size: 16px;
    font-weight: 600;
  }

  @media (max-width: 768px) {
    .harga-bbm-columns {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.rupiah-input').forEach(function(input) {
      input.addEventListener('input', function() {
        formatRupiahLive(input);
      });
    });

    document.querySelectorAll('.harga-form').forEach(function(form) {
      form.addEventListener('submit', function() {
        form.querySelectorAll('.rupiah-input').forEach(function(input) {
          input.value = String(parseIdValue(input.value));
        });
      });
    });
  });

  function parseIdValue(str) {
    if (!str) return 0;
    let s = String(str).trim();
    if (!s || !/^-?\d[\d.,]*$/.test(s)) return 0;
    if (s.includes(',')) {
      s = s.replace(/\./g, '').replace(',', '.');
    } else if (/^-?\d{1,3}(\.\d{3})+$/.test(s)) {
      s = s.replace(/\./g, '');
    }
    const v = parseFloat(s);
    return isNaN(v) ? 0 : v;
  }

  function formatRupiahLive(input) {
    const cursorFromEnd = input.value.length - input.selectionStart;

    // ambil hanya digit, biarkan koma untuk desimal
    let raw = input.value.replace(/[^\d,]/g, '');

    let [intPart, decPart] = raw.split(',');
    intPart = intPart ? intPart.replace(/^0+(?=\d)/, '') : '';

    let formatted = intPart ? Number(intPart).toLocaleString('id-ID') : '';
    if (decPart !== undefined) {
      formatted += ',' + decPart;
    }

    input.value = formatted;

    const newPos = Math.max(formatted.length - cursorFromEnd, 0);
    input.setSelectionRange(newPos, newPos);
  }
</script>
@endpush