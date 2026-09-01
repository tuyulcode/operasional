<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EtollController;
use App\Http\Controllers\HargaBbmController;
use App\Http\Controllers\JenisKendaraanController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\PemakaianBbmController;
use App\Http\Controllers\PemegangKendaraanController;
use App\Http\Controllers\PenandatanganController;
use App\Http\Controllers\PpnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapanController;
use App\Http\Controllers\TagihanAirController;
use App\Http\Controllers\TitikMeterController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::put('/photo', [ProfileController::class, 'updatePhoto'])->name('photo.update');
    });

    Route::prefix('ppn')->name('ppn.')->group(function () {
        Route::get('/', [PpnController::class, 'index'])->name('index');
        Route::post('/', [PpnController::class, 'store'])->name('store');
        Route::post('/{id}/aktifkan', [PpnController::class, 'activate'])->name('activate');
        Route::delete('/{id}', [PpnController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('area')->name('area.')->group(function () {
        Route::get('/', [AreaController::class, 'index'])->name('index');
        Route::post('/', [AreaController::class, 'store'])->name('store');
        Route::put('/{id}', [AreaController::class, 'update'])->name('update');
        Route::delete('/{id}', [AreaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('titik-meter')->name('titik-meter.')->group(function () {
        Route::get('/', [TitikMeterController::class, 'index'])->name('index');
        Route::post('/', [TitikMeterController::class, 'store'])->name('store');
        Route::put('/{id}', [TitikMeterController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [TitikMeterController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [TitikMeterController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tagihan-air')->name('tagihan-air.')->group(function () {
        Route::get('/', [TagihanAirController::class, 'index'])->name('index');
        Route::post('/', [TagihanAirController::class, 'store'])->name('store');
        Route::put('/{id}', [TagihanAirController::class, 'update'])->name('update');
        Route::delete('/{id}', [TagihanAirController::class, 'destroy'])->name('destroy');
        Route::delete('/foto/{foto}', [TagihanAirController::class, 'destroyFoto'])->name('foto.destroy');
    });

    Route::prefix('rekapan')->name('rekapan.')->group(function () {
        Route::get('/', [RekapanController::class, 'index'])->name('index');
        Route::get('/excel', [RekapanController::class, 'exportExcel'])->name('excel');
        Route::get('/pdf', [RekapanController::class, 'exportPdf'])->name('pdf');
    });

    Route::prefix('penandatangan')->name('penandatangan.')->group(function () {
        Route::get('/', [PenandatanganController::class, 'index'])->name('index');
        Route::put('/', [PenandatanganController::class, 'update'])->name('update');
    });

    Route::prefix('harga-bbm')->name('harga-bbm.')->group(function () {
        Route::get('/', [HargaBbmController::class, 'index'])->name('index');
        Route::post('/', [HargaBbmController::class, 'store'])->name('store');
    });

    Route::prefix('pemakaian-etoll')->name('pemakaian-etoll.')->group(function () {
        Route::get('/', [EtollController::class, 'index'])->name('index');
        Route::post('/', [EtollController::class, 'store'])->name('store');
        Route::put('/{id}', [EtollController::class, 'update'])->name('update');
        Route::delete('/{id}', [EtollController::class, 'destroy'])->name('destroy');
        Route::get('/export/pdf', [EtollController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export/excel', [EtollController::class, 'exportExcel'])->name('export-excel');
    });

    Route::prefix('pemegang-kendaraan')->name('pemegang-kendaraan.')->group(function () {
        Route::get('/', [PemegangKendaraanController::class, 'index'])->name('index');
        Route::post('/', [PemegangKendaraanController::class, 'store'])->name('store');
        Route::put('/{id}', [PemegangKendaraanController::class, 'update'])->name('update');
        Route::delete('/{id}', [PemegangKendaraanController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('kendaraan')->name('kendaraan.')->group(function () {
        Route::get('/', [KendaraanController::class, 'index'])->name('index');
        Route::post('/', [KendaraanController::class, 'store'])->name('store');
        Route::put('/{id}', [KendaraanController::class, 'update'])->name('update');
        Route::delete('/{id}', [KendaraanController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('jenis-kendaraan')->name('jenis-kendaraan.')->group(function () {
        Route::get('/', [JenisKendaraanController::class, 'index'])->name('index');
        Route::post('/', [JenisKendaraanController::class, 'store'])->name('store');
        Route::put('/{id}', [JenisKendaraanController::class, 'update'])->name('update');
        Route::delete('/{id}', [JenisKendaraanController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('pemakaian-bbm')->name('pemakaian-bbm.')->group(function () {
        Route::get('/', [PemakaianBbmController::class, 'index'])->name('index');
        Route::post('/', [PemakaianBbmController::class, 'store'])->name('store');
        Route::put('/{id}', [PemakaianBbmController::class, 'update'])->name('update');
        Route::delete('/{id}', [PemakaianBbmController::class, 'destroy'])->name('destroy');

        Route::get('/rekapan', [PemakaianBbmController::class, 'rekapan'])->name('rekapan');
        Route::get('/export/excel', [PemakaianBbmController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export/pdf', [PemakaianBbmController::class, 'exportPdf'])->name('export-pdf');

        Route::get('/pertanggungjawaban', [PemakaianBbmController::class, 'pertanggungjawaban'])->name('pertanggungjawaban');
        Route::get('/export/pertanggungjawaban/excel', [PemakaianBbmController::class, 'exportPertanggungjawabanExcel'])->name('export-pertanggungjawaban-excel');
        Route::get('/export/pertanggungjawaban/pdf', [PemakaianBbmController::class, 'exportPertanggungjawabanPdf'])->name('export-pertanggungjawaban-pdf');
    });
});

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});
