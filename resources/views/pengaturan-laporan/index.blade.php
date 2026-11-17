@extends('layouts.app')

@section('title', 'Pengaturan Laporan')

@section('content')

  <div class="page-header">
    <div class="page-title">Pengaturan Laporan</div>
    <ul class="breadcrumb">
      <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Pengaturan</li>
      <li><i class="fa-solid fa-angle-right"></i></li>
      <li>Laporan</li>
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
        <h3><i class="fa-solid fa-image" style="color: var(--primary-color); margin-right: 8px;"></i> Ukuran Foto Meter pada Laporan</h3>
        <p>Berlaku untuk Export PDF &amp; Excel Rekapan Tagihan Air, untuk semua area.</p>
      </div>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('pengaturan-laporan.update') }}">
        @csrf
        @method('PUT')

        <div class="form-grid" style="max-width: 640px;">
          <div class="form-group">
            <label for="foto_meter_lebar_mm">
              <i class="fa-solid fa-ruler-horizontal" style="color: #999; margin-right: 5px;"></i> Lebar Maksimal (mm)
            </label>
            <input type="number" id="foto_meter_lebar_mm" name="foto_meter_lebar_mm"
                   class="form-control" step="0.1" min="5" max="120" required
                   value="{{ old('foto_meter_lebar_mm', $pengaturan->foto_meter_lebar_mm) }}">
            <small style="color: #888; display: block; margin-top: 6px;">Rentang 5–120 mm. Rasio foto tetap dijaga.</small>
          </div>

          <div class="form-group">
            <label for="foto_meter_tinggi_mm">
              <i class="fa-solid fa-ruler-vertical" style="color: #999; margin-right: 5px;"></i> Tinggi Maksimal (mm)
            </label>
            <input type="number" id="foto_meter_tinggi_mm" name="foto_meter_tinggi_mm"
                   class="form-control" step="0.1" min="5" max="120" required
                   value="{{ old('foto_meter_tinggi_mm', $pengaturan->foto_meter_tinggi_mm) }}">
            <small style="color: #888; display: block; margin-top: 6px;">Rentang 5–120 mm. Rasio foto tetap dijaga.</small>
          </div>
        </div>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color);">
          <div style="font-size: 0.85rem; font-weight: 600; color: #999; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 14px;">
            <i class="fa-solid fa-eye" style="margin-right: 6px;"></i> Preview Ukuran Foto
          </div>

          <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 10px; padding: 24px; display: flex; align-items: center; justify-content: center;">
            <div id="fotoPreviewBox" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; border: 2px solid #94a3b8; border-radius: 6px; background: #e2e8f0; color: #64748b; transition: width 0.15s ease, height 0.15s ease;">
              <i class="fa-solid fa-image" style="font-size: 1.4rem;"></i>
              <span style="font-size: 0.72rem; font-weight: 600;">Contoh foto</span>
            </div>
          </div>

          <div id="fotoPreviewInfo" style="text-align: center; color: #64748b; font-size: 0.85rem; margin-top: 10px;"></div>
        </div>

        <div class="form-actions" style="margin-top: 24px;">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </form>

      <div style="margin-top: 22px; padding: 14px 16px; background: rgba(64, 153, 255, 0.06); border-left: 4px solid var(--primary-color); border-radius: 8px; color: #475569; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-info" style="color: var(--primary-color); margin-right: 6px;"></i>
        Perubahan langsung dipakai saat mengunduh laporan berikutnya. Ukuran pada Excel otomatis dikonversi dari mm ke piksel. Jumlah foto per baris menyesuaikan otomatis agar gambar tidak terpotong saat diperbesar.
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const lebarInput = document.getElementById('foto_meter_lebar_mm');
    const tinggiInput = document.getElementById('foto_meter_tinggi_mm');
    const box = document.getElementById('fotoPreviewBox');
    const info = document.getElementById('fotoPreviewInfo');
    const MM_TO_PX = 3.7795275591;

    function updatePreview() {
      const lebarMm = Math.max(5, Math.min(120, parseFloat(lebarInput.value) || 38));
      const tinggiMm = Math.max(5, Math.min(120, parseFloat(tinggiInput.value) || 27));

      box.style.width = (lebarMm * MM_TO_PX) + 'px';
      box.style.height = (tinggiMm * MM_TO_PX) + 'px';
      info.textContent = 'Lebar ' + lebarMm + ' mm × Tinggi ' + tinggiMm + ' mm (maksimal, rasio foto dijaga)';
    }

    lebarInput.addEventListener('input', updatePreview);
    tinggiInput.addEventListener('input', updatePreview);
    updatePreview();
  });
</script>
@endpush
