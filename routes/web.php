<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () { return view('welcome'); });

// Dashboard & Monitoring
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');

// Fitur Riwayat
Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');
Route::post('/simpan-log', [DashboardController::class, 'simpanLog'])->name('simpan-log');

Route::get('/pengaturan', function () { return "Halaman Pengaturan"; })->name('pengaturan');
Route::get('/logout', function () { return redirect('/')->with('success', 'Berhasil keluar'); })->name('logout');