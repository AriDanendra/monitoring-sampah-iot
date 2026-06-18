<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard & Monitoring
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');

// Endpoint API untuk Real-Time Data (Dipanggil oleh JavaScript setiap 5 detik)
Route::get('/api/realtime-data', [DashboardController::class, 'getRealtimeData'])->name('api.realtime');

// Fitur Riwayat
Route::get('/riwayat', [DashboardController::class, 'riwayat'])->name('riwayat');
Route::delete('/riwayat/{id}', [DashboardController::class, 'hapusRiwayat'])->name('hapus-riwayat');
Route::post('/riwayat/hapus-semua', [DashboardController::class, 'hapusSemuaRiwayat'])->name('hapus-semua-riwayat');
Route::post('/simpan-log', [DashboardController::class, 'simpanLog'])->name('simpan-log');

Route::get('/pengaturan', function () { return "Halaman Pengaturan"; })->name('pengaturan');
Route::get('/logout', function () { return redirect('/')->with('success', 'Berhasil keluar'); })->name('logout');