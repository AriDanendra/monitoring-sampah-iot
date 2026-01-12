<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\History; // 1. TAMBAHKAN INI

class DashboardController extends Controller
{
    // --- KONFIGURASI LANGSUNG (HARDCODED) ---
    private $baseUrl = 'https://thingsboard.cloud';
    private $username = 'adanendra20@gmail.com'; 
    private $password = '13012004';

    // Device IDs dari ThingsBoard
    private $deviceIdTR01 = '7d7292d0-ef9d-11f0-931e-d77481df73d2'; 
    private $deviceIdTR02 = 'd9e974b0-efda-11f0-a6fc-1dffa956f056'; 

    private function getThingsBoardToken()
    {
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

        $response = Http::withToken($token)
            ->get($this->baseUrl . "/api/plugins/telemetry/DEVICE/{$deviceId}/values/timeseries", [
                'keys' => 'persen,bau'
            ]);

        return $response->json();
    }

    private function formatDeviceData($deviceId, $idTag, $lokasi, $lat, $lng)
    {
        $telemetry = $this->getDeviceTelemetry($deviceId);
        
        $lastTs = isset($telemetry['persen']) ? $telemetry['persen'][0]['ts'] : null;
        $status = 'offline';
        $update = 'Tidak ada data';

        if ($lastTs) {
            $lastActivity = $lastTs / 1000;
            $diffInSeconds = time() - $lastActivity;

            if ($diffInSeconds < 300) {
                $status = 'online';
                $update = 'Baru saja';
            } else {
                $status = 'offline';
                $totalMenit = round($diffInSeconds / 60);
                if ($totalMenit >= 60) {
                    $jam = round($totalMenit / 60);
                    $update = $jam . ' jam lalu';
                } else {
                    $update = $totalMenit . ' menit lalu';
                }
            }
        }

        return [
            'id' => $idTag,
            'lokasi' => $lokasi,
            'persen' => isset($telemetry['persen']) ? (int)$telemetry['persen'][0]['value'] : 0,
            'bau' => isset($telemetry['bau']) ? (int)$telemetry['bau'][0]['value'] : 0,
            'status' => $status,
            'update' => $update,
            'lat' => $lat,
            'lng' => $lng
        ];
    }

    private function getDeviceData()
    {
        return [
            $this->formatDeviceData($this->deviceIdTR01, '#TR-01', 'Grand Sulawesi Parepare', -4.006904852098234, 119.66253093102463),
            $this->formatDeviceData($this->deviceIdTR02, '#TR-02', 'Perumahan Pare Town House', -4.010893730077395, 119.63298928262212),
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

    // --- 2. FITUR BARU: HALAMAN RIWAYAT ---
    public function riwayat()
    {
        $logs = History::orderBy('waktu_pengangkutan', 'desc')->get();
        return view('riwayat', compact('logs'));
    }

    // --- 3. FITUR BARU: SIMPAN LOG KE DATABASE ---
    public function simpanLog(Request $request)
    {
        History::create([
            'device_id' => $request->id,
            'lokasi' => $request->lokasi,
            'kapasitas_terakhir' => $request->persen,
            'kadar_bau_terakhir' => $request->bau,
            'waktu_pengangkutan' => now(),
        ]);

        return back()->with('success', 'Riwayat pengangkutan berhasil dicatat.');
    }
}