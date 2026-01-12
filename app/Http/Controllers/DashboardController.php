<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    
    private function getDeviceData()
    {
        return [
            [
                'id' => '#TR-01', 
                'lokasi' => 'Grand Sulawesi Parepare', 
                'persen' => 95, 
                'bau' => 200, 
                'status' => 'offline', 
                'update' => 'Sekarang',
                'lat' => -4.006904852098234, 
                'lng' => 119.66253093102463
            ],
            [
                'id' => '#TR-02', 
                'lokasi' => 'Perumahan Pare Town House', 
                'persen' => 50, 
                'bau' => 550, 
                'status' => 'online', 
                'update' => '2 Menit lalu',
                'lat' => -4.010893730077395, 
                'lng' => 119.63298928262212
            ],
            [
                'id' => '#TR-03', 
                'lokasi' => 'Perumahan Bukit Harapan Indah', 
                'persen' => 10, 
                'bau' => 100, 
                'status' => 'offline', 
                'update' => '1 Jam lalu',
                'lat' => -3.990857044564276, 
                'lng' => 119.64606826627598
            ],
        ];
    }

    public function index()
    {
        $devices = $this->getDeviceData();
        $totalLokasi = count($devices);
        $titikPenuh = collect($devices)->where('persen', '>=', 80)->count();
        $perangkatAktif = collect($devices)->where('status', 'online')->count();

        return view('dashboard', compact('devices', 'totalLokasi', 'titikPenuh', 'perangkatAktif'));
    }

    public function monitoring()
    {
        $devices = $this->getDeviceData();
        
        $kantor = [
            'nama' => 'TPS', 
            'lat' => -3.988430338950498, 
            'lng' => 119.65216109576326
        ];

        return view('monitoring', compact('devices', 'kantor'));
    }
}