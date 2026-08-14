<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Penandatangan;
use App\Models\Ppn;
use App\Models\TagihanAir;
use App\Models\TitikMeter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenandatanganTest extends TestCase
{
    use RefreshDatabase;

    private function seedReport(): User
    {
        $user = User::create(['username' => 'admin', 'password_hash' => bcrypt('secret'), 'role' => 'admin']);
        $ppn = Ppn::create(['persentase' => 11, 'status' => 'aktif']);
        $hotel = Area::create(['nama' => 'Hotel', 'alamat' => null, 'kena_ppn' => true]);
        $puncak = Area::create(['nama' => 'Puncak', 'alamat' => null, 'kena_ppn' => false]);

        $tm1 = TitikMeter::create(['area_id' => $hotel->id, 'nama' => 'Barak 1', 'meter_faktor' => 1, 'tarif_harga' => 5000, 'status' => 'aktif']);
        $tm2 = TitikMeter::create(['area_id' => $puncak->id, 'nama' => 'Wisma', 'meter_faktor' => 1, 'tarif_harga' => 6000, 'status' => 'aktif']);

        TagihanAir::create([
            'titik_meter_id' => $tm1->id, 'periode' => '2026-08-01',
            'meter_lalu' => 0, 'meter_ini' => 100, 'meter_faktor' => 1,
            'tarif' => 5000, 'pemakaian' => 100, 'jumlah' => 500000,
        ]);
        TagihanAir::create([
            'titik_meter_id' => $tm2->id, 'periode' => '2026-08-01',
            'meter_lalu' => 0, 'meter_ini' => 50, 'meter_faktor' => 1,
            'tarif' => 6000, 'pemakaian' => 50, 'jumlah' => 300000,
        ]);

        return $user;
    }

    public function test_settings_update_and_view()
    {
        $user = $this->seedReport();

        $rows = Penandatangan::orderBy('id')->get();

        $this->actingAs($user)->put('/penandatangan', [
            'nama' => [$rows[0]->id => 'Budi Santoso', $rows[1]->id => 'Siti Rahayu'],
            'tempat' => 'Bandung',
        ])->assertSessionHas('success');

        $this->assertSame('Budi Santoso', Penandatangan::find($rows[0]->id)->nama);
        $this->assertSame('Siti Rahayu', Penandatangan::find($rows[1]->id)->nama);
        $this->assertSame('Bandung', Penandatangan::find($rows[0]->id)->tempat);

        // settings page render
        $this->actingAs($user)->get('/penandatangan')
            ->assertOk()
            ->assertSee('Manajer Bisnis Support')
            ->assertSee('Asman SDM Umum', false);

        // rekapan view memuat nama penandatangan
        $this->actingAs($user)->get('/tagihan-air?tab=rekapan&bulan=2026-08')
            ->assertOk()
            ->assertSee('Mengetahui / Menyetujui')
            ->assertSee('Budi Santoso')
            ->assertSee('Siti Rahayu')
            ->assertSee('Bandung');

        // PDF memuat nama & jabatan
        $pdf = $this->actingAs($user)->get('/rekapan/pdf?bulan=2026-08');
        $pdf->assertOk();
        $content = $pdf->getContent();
        $this->assertStringStartsWith('%PDF', $content);
        $this->assertStringContainsString('Mengetahui / Menyetujui', $content);
        $this->assertStringContainsString('Manajer Bisnis Support', $content);
        $this->assertStringContainsString('Budi Santoso', $content);
        $this->assertStringContainsString('Siti Rahayu', $content);
        $this->assertStringContainsString('Bandung', $content);
    }
}