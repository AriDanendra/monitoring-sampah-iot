<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Data simulasi ini nantinya bisa Anda ganti dengan: $devices = Device::all();
        $devices = [
            ['id' => '#TR-01', 'lokasi' => 'Jl. Bau Massepe', 'persen' => 95, 'status' => 'online', 'update' => 'Sekarang'],
            ['id' => '#TR-02', 'lokasi' => 'Soreang', 'persen' => 50, 'status' => 'online', 'update' => '2 Menit lalu'],
            ['id' => '#TR-03', 'lokasi' => 'Ujung', 'persen' => 10, 'status' => 'offline', 'update' => '1 Jam lalu'],
        ];

        // Logika pengolahan data dilakukan di sini
        $totalLokasi = count($devices);
        $titikPenuh = collect($devices)->where('persen', '>=', 80)->count();
        $perangkatAktif = collect($devices)->where('status', 'online')->count();

        // Mengirimkan data ke view dashboard.blade.php
        return view('dashboard', compact('devices', 'totalLokasi', 'titikPenuh', 'perangkatAktif'));
    }
}