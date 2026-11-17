<?php

namespace Tests\Feature;

use App\Http\Controllers\RekapanController;
use App\Models\Area;
use App\Models\PengaturanLaporan;
use App\Models\Ppn;
use App\Models\TagihanAir;
use App\Models\TagihanAirFoto;
use App\Models\TitikMeter;
use App\Models\User;
use App\Services\RekapanExcel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaturanLaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Storage::disk('public')->delete('test-foto-meter.png');

        parent::tearDown();
    }

    private function seedReport(): User
    {
        $user = User::create(['username' => 'admin', 'password_hash' => bcrypt('secret'), 'role' => 'admin']);
        Ppn::create(['persentase' => 11, 'status' => 'aktif']);

        $area = Area::create(['nama' => 'Hotel', 'alamat' => null, 'kena_ppn' => false]);
        $titikMeter = TitikMeter::create([
            'area_id' => $area->id,
            'nama' => 'Barak 1',
            'meter_faktor' => 1,
            'tarif_harga' => 5000,
            'status' => 'aktif',
        ]);

        $tagihan = TagihanAir::create([
            'titik_meter_id' => $titikMeter->id,
            'periode' => '2026-08-01',
            'meter_lalu' => 0,
            'meter_ini' => 100,
            'meter_faktor' => 1,
            'tarif' => 5000,
            'pemakaian' => 100,
            'jumlah' => 500000,
        ]);

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Storage::disk('public')->put('test-foto-meter.png', $png);

        TagihanAirFoto::create([
            'tagihan_air_id' => $tagihan->id,
            'path_foto' => 'test-foto-meter.png',
        ]);

        return $user;
    }

    private function seedPhotoRows(int $count, int $imageWidth = 400, int $imageHeight = 300): User
    {
        $user = $this->seedReport();
        $area = Area::where('nama', 'Hotel')->firstOrFail();

        for ($i = 2; $i <= $count; $i++) {
            $titikMeter = TitikMeter::create([
                'area_id' => $area->id,
                'nama' => 'Barak '.$i,
                'meter_faktor' => 1,
                'tarif_harga' => 5000,
                'status' => 'aktif',
            ]);

            $tagihan = TagihanAir::create([
                'titik_meter_id' => $titikMeter->id,
                'periode' => '2026-08-01',
                'meter_lalu' => 0,
                'meter_ini' => 100,
                'meter_faktor' => 1,
                'tarif' => 5000,
                'pemakaian' => 100,
                'jumlah' => 500000,
            ]);

            TagihanAirFoto::create([
                'tagihan_air_id' => $tagihan->id,
                'path_foto' => 'test-foto-meter.png',
            ]);
        }

        $image = imagecreatetruecolor($imageWidth, $imageHeight);
        imagefill($image, 0, 0, imagecolorallocate($image, 210, 225, 240));
        imagepng($image, Storage::disk('public')->path('test-foto-meter.png'));
        imagedestroy($image);

        return $user;
    }

    private function excelPhotoDrawings(User $user, float $lebar, float $tinggi)
    {
        $this->actingAs($user)->put('/pengaturan-laporan', [
            'foto_meter_lebar_mm' => $lebar,
            'foto_meter_tinggi_mm' => $tinggi,
        ])->assertSessionHas('success');

        $report = (new RekapanController)
            ->buildReport(Request::create('/rekapan', 'GET', ['bulan' => '2026-08']));

        return collect(RekapanExcel::generate($report)->getSheet(0)->getDrawingCollection())
            ->filter(fn ($drawing) => str_starts_with($drawing->getName(), 'foto-'))
            ->values();
    }

    public function test_settings_page_can_be_opened_and_updated(): void
    {
        $user = $this->seedReport();

        $this->actingAs($user)->get('/pengaturan-laporan')
            ->assertOk()
            ->assertSee('Ukuran Foto Meter pada Laporan')
            ->assertSee('38');

        $this->actingAs($user)->put('/pengaturan-laporan', [
            'foto_meter_lebar_mm' => 50,
            'foto_meter_tinggi_mm' => 40,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('pengaturan_laporan', [
            'foto_meter_lebar_mm' => 50,
            'foto_meter_tinggi_mm' => 40,
        ]);
    }

    public function test_setting_is_used_by_rekapan_exports(): void
    {
        $user = $this->seedReport();

        $this->actingAs($user)->put('/pengaturan-laporan', [
            'foto_meter_lebar_mm' => 45.5,
            'foto_meter_tinggi_mm' => 32.5,
        ])->assertSessionHas('success');

        $report = (new RekapanController)
            ->buildReport(Request::create('/rekapan', 'GET', ['bulan' => '2026-08']));

        $this->assertSame(45.5, $report['fotoMeter']['lebar_mm']);
        $this->assertSame(32.5, $report['fotoMeter']['tinggi_mm']);

        $this->actingAs($user)->get('/rekapan/pdf?bulan=2026-08')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($user)->get('/rekapan/excel?bulan=2026-08')
            ->assertOk();
    }

    public function test_setting_uses_default_when_row_is_missing(): void
    {
        PengaturanLaporan::query()->delete();

        $pengaturan = PengaturanLaporan::current();

        $this->assertSame(PengaturanLaporan::DEFAULT_FOTO_LEBAR_MM, $pengaturan->foto_meter_lebar_mm);
        $this->assertSame(PengaturanLaporan::DEFAULT_FOTO_TINGGI_MM, $pengaturan->foto_meter_tinggi_mm);
    }

    public function test_large_photo_setting_is_exported_without_errors(): void
    {
        $user = $this->seedReport();

        $this->actingAs($user)->put('/pengaturan-laporan', [
            'foto_meter_lebar_mm' => 110,
            'foto_meter_tinggi_mm' => 90,
        ])->assertSessionHas('success');

        $this->actingAs($user)->get('/rekapan/pdf?bulan=2026-08')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($user)->get('/rekapan/excel?bulan=2026-08')
            ->assertOk();
    }

    public function test_excel_large_photos_are_not_scaled_down_and_use_one_per_row(): void
    {
        $user = $this->seedPhotoRows(4);

        $drawings = $this->excelPhotoDrawings($user, 110, 90);

        $this->assertCount(4, $drawings);
        foreach ($drawings as $drawing) {
            $this->assertSame(416, $drawing->getWidth());
            $this->assertSame(312, $drawing->getHeight());
        }

        $photoRows = $drawings
            ->map(fn ($drawing) => preg_replace('/^[A-Z]+/', '', $drawing->getCoordinates()))
            ->unique();
        $this->assertCount(4, $photoRows);
    }

    public function test_excel_three_photos_fall_back_to_two_per_row_when_needed(): void
    {
        $user = $this->seedPhotoRows(3);

        $drawings = $this->excelPhotoDrawings($user, 50, 40);

        $this->assertCount(3, $drawings);
        foreach ($drawings as $drawing) {
            $this->assertSame(189, $drawing->getWidth());
            $this->assertSame(142, $drawing->getHeight());
        }

        $photoRows = $drawings
            ->map(fn ($drawing) => preg_replace('/^[A-Z]+/', '', $drawing->getCoordinates()))
            ->unique();
        $this->assertCount(2, $photoRows);
    }

    public function test_excel_default_size_puts_four_photos_in_one_row_without_scaling(): void
    {
        $user = $this->seedPhotoRows(4);

        $drawings = $this->excelPhotoDrawings($user, 38, 27);

        $this->assertCount(4, $drawings);
        foreach ($drawings as $drawing) {
            $this->assertSame(136, $drawing->getWidth());
            $this->assertSame(102, $drawing->getHeight());
        }

        $photoRows = $drawings
            ->map(fn ($drawing) => preg_replace('/^[A-Z]+/', '', $drawing->getCoordinates()))
            ->unique();
        $this->assertCount(1, $photoRows);
    }
}
