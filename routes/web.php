<?php

use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Halaman Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Halaman Monitoring
Route::get('/monitoring', function () {
    return view('monitoring');
})->name('monitoring');

// Halaman Riwayat
Route::get('/riwayat', function () {
    return "Halaman Riwayat - Sedang dalam pengembangan"; // Nantinya ganti dengan view('riwayat')
})->name('riwayat');

// Halaman Pengaturan
Route::get('/pengaturan', function () {
    return "Halaman Pengaturan - Sedang dalam pengembangan"; // Nantinya ganti dengan view('pengaturan')
})->name('pengaturan');

// Fungsi Logout
Route::get('/logout', function () {
    // Tambahkan logika logout di sini jika sudah menggunakan sistem login
    return redirect('/')->with('success', 'Berhasil keluar');
})->name('logout');