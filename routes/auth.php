<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/masuk', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/masuk', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

Route::get('/konfirmasi-password', [ConfirmablePasswordController::class, 'show'])
    ->middleware('auth')
    ->name('password.confirm');

Route::post('/konfirmasi-password', [ConfirmablePasswordController::class, 'store'])
    ->middleware('auth');

Route::post('/keluar', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
