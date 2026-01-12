<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController; // Pastikan ini di-import

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Halaman Dashboard menggunakan Controller
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Halaman lainnya (masih statis)
Route::get('/monitoring', function () {
    return view('monitoring');
})->name('monitoring');

Route::get('/riwayat', function () {
    return "Halaman Riwayat - Sedang dalam pengembangan";
})->name('riwayat');

Route::get('/pengaturan', function () {
    return "Halaman Pengaturan - Sedang dalam pengembangan";
})->name('pengaturan');

Route::get('/logout', function () {
    return redirect('/')->with('success', 'Berhasil keluar');
})->name('logout');