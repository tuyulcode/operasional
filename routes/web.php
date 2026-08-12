<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PpnController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\TitikMeterController;
use App\Http\Controllers\HargaBbmController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
        Route::delete('/{id}', [TitikMeterController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('harga-bbm')->name('harga-bbm.')->group(function () {
        Route::get('/', [HargaBbmController::class, 'index'])->name('index');
        Route::post('/', [HargaBbmController::class, 'store'])->name('store');
    });
});

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});
