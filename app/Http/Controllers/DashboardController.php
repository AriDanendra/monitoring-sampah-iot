<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Data simulasi untuk halaman dashboard
        $devices = [
            ['id' => '#TR-01', 'lokasi' => 'Jl. Bau Massepe', 'persen' => 95, 'status' => 'offline', 'update' => 'Sekarang'],
            ['id' => '#TR-02', 'lokasi' => 'Soreang', 'persen' => 50, 'status' => 'online', 'update' => '2 Menit lalu'],
            ['id' => '#TR-03', 'lokasi' => 'Ujung', 'persen' => 10, 'status' => 'offline', 'update' => '1 Jam lalu'],
        ];

        $totalLokasi = count($devices);
        $titikPenuh = collect($devices)->where('persen', '>=', 80)->count();
        $perangkatAktif = collect($devices)->where('status', 'online')->count();

        return view('dashboard', compact('devices', 'totalLokasi', 'titikPenuh', 'perangkatAktif'));
    }

    public function monitoring()
    {
        // Koordinat Kantor (Titik Awal & Akhir) sesuai input Anda
        $kantor = [
            'nama' => 'Kantor Pusat', 
            'lat' => -3.988430338950498, 
            'lng' => 119.65216109576326
        ];

        // Data perangkat dengan koordinat Lokasi 1, 2, dan 3 sesuai input Anda
        $devices = [
            [
                'id' => '#TR-01', 
                'lokasi' => 'Lokasi 1', 
                'persen' => 95, 
                'lat' => -4.006904852098234, 
                'lng' => 119.66253093102463
            ],
            [
                'id' => '#TR-02', 
                'lokasi' => 'Lokasi 2', 
                'persen' => 50, 
                'lat' => -4.010893730077395, 
                'lng' => 119.63298928262212
            ],
            [
                'id' => '#TR-03', 
                'lokasi' => 'Lokasi 3', 
                'persen' => 10, 
                'lat' => -3.990857044564276, 
                'lng' => 119.64606826627598
            ],
        ];

        return view('monitoring', compact('devices', 'kantor'));
    }
}