<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TagihanAirController;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'apiLogin']);

Route::get('/tagihan-air', [TagihanAirController::class, 'index']);
