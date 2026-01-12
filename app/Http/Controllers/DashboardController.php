<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
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
        // Koordinat Kantor sesuai dokumen skripsi [cite: 294]
        $kantor = [
            'nama' => 'Kantor Pusat', 
            'lat' => -3.988430338950498, 
            'lng' => 119.65216109576326
        ];

        // Data perangkat dengan tambahan parameter bau (MQ-135) [cite: 160, 168]
        $devices = [
            [
                'id' => '#TR-01', 
                'lokasi' => 'Lokasi 1', 
                'persen' => 95, 
                'bau' => 200, // Normal
                'lat' => -4.006904852098234, 
                'lng' => 119.66253093102463
            ],
            [
                'id' => '#TR-02', 
                'lokasi' => 'Lokasi 2', 
                'persen' => 50, 
                'bau' => 550, // Berbau (Melebihi ambang batas simulasi 400) [cite: 321]
                'lat' => -4.010893730077395, 
                'lng' => 119.63298928262212
            ],
            [
                'id' => '#TR-03', 
                'lokasi' => 'Lokasi 3', 
                'persen' => 10, 
                'bau' => 100, // Normal
                'lat' => -3.990857044564276, 
                'lng' => 119.64606826627598
            ],
        ];

        return view('monitoring', compact('devices', 'kantor'));
    }
}