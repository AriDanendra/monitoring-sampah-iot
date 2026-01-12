<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    // --- KONFIGURASI LANGSUNG (HARDCODED) ---
    private $baseUrl = 'https://thingsboard.cloud';
    private $username = 'adanendra20@gmail.com'; // Ganti dengan email ThingsBoard Anda
    private $password = '13012004';         // Ganti dengan password ThingsBoard Anda
    private $deviceIdTR01 = '7d7292d0-ef9d-11f0-931e-d77481df73d2'; // Ganti dengan Device ID dari ThingsBoard

    private function getThingsBoardToken()
    {
        // Menyimpan token di cache selama 1 jam agar tidak login setiap saat
        return Cache::remember('tb_token', 3600, function () {
            $response = Http::post($this->baseUrl . '/api/auth/login', [
                'username' => $this->username,
                'password' => $this->password
            ]);

            return $response->json()['token'] ?? null;
        });
    }

    private function getDeviceTelemetry($deviceId)
    {
        $token = $this->getThingsBoardToken();
        if (!$token) return null;

        // Mengambil data telemetri terakhir (persen dan bau)
        $response = Http::withToken($token)
            ->get($this->baseUrl . "/api/plugins/telemetry/DEVICE/{$deviceId}/values/timeseries", [
                'keys' => 'persen,bau'
            ]);

        return $response->json();
    }

    private function getDeviceData()
    {
        // Ambil data asli dari ThingsBoard untuk perangkat TR-01
        $telemetry = $this->getDeviceTelemetry($this->deviceIdTR01);

        return [
            [
                'id' => '#TR-01', 
                'lokasi' => 'Grand Sulawesi Parepare', 
                // Mengambil nilai 'value' dari respon JSON, jika tidak ada default ke 0
                'persen' => isset($telemetry['persen']) ? (int)$telemetry['persen'][0]['value'] : 0, 
                'bau' => isset($telemetry['bau']) ? (int)$telemetry['bau'][0]['value'] : 0, 
                'status' => 'online', 
                'update' => 'Baru saja',
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