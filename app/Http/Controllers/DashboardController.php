<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\History;

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
        
        $currentPersen = isset($telemetry['persen']) ? (int)$telemetry['persen'][0]['value'] : 0;
        $currentBau = isset($telemetry['bau']) ? (int)$telemetry['bau'][0]['value'] : 0;
        $lastTs = isset($telemetry['persen']) ? $telemetry['persen'][0]['ts'] : null;

        // --- LOGIKA KLASIFIKASI KADAR BAU ---
        $statusBau = 'Aman';
        if ($currentBau >= 800) {
            $statusBau = 'Bau Nyengat';
        } elseif ($currentBau >= 400) {
            $statusBau = 'Bau';
        }
        // ------------------------------------

        // --- LOGIKA OTOMATISASI PENYIMPANAN RIWAYAT ---
        $cacheKey = "status_penuh_" . str_replace('#', '', $idTag);
        $wasFullOrSmelly = Cache::get($cacheKey, false);

        // 1. Jika sampah terdeteksi Penuh (>= 80%) ATAU Bau Nyengat (>= 800), tandai di sistem
        if ($currentPersen >= 80 || $currentBau >= 800) {
            Cache::put($cacheKey, true, now()->addDays(7));
        } 

        // 2. Jika sebelumnya ditandai perlu diangkut DAN sekarang sudah kosong (< 10%)
        if ($wasFullOrSmelly && $currentPersen < 10) {
            History::create([
                'device_id' => $idTag,
                'lokasi' => $lokasi,
                'kapasitas_terakhir' => 100, // Asumsi diangkut saat mencapai batas
                'kadar_bau_terakhir' => $currentBau,
                'waktu_pengangkutan' => now(),
            ]);
            
            // Hapus tanda di cache karena sudah selesai diangkut
            Cache::forget($cacheKey);
        }
        // ----------------------------------------------

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
            'persen' => $currentPersen,
            'bau' => $currentBau,
            'status_bau' => $statusBau, // Menambahkan label status bau
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
                'status_bau' => 'Aman',
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
        
        // Menghitung titik yang perlu diangkut (Penuh >= 80 atau Bau Nyengat >= 800)
        $titikPenuh = collect($devices)->filter(function ($item) {
            return $item['persen'] >= 80 || $item['bau'] >= 800;
        })->count();

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

    public function riwayat()
    {
        $logs = History::orderBy('waktu_pengangkutan', 'desc')->get();
        return view('riwayat', compact('logs'));
    }

    public function simpanLog(Request $request)
    {
        History::create([
            'device_id' => $request->id,
            'lokasi' => $request->lokasi,
            'kapasitas_terakhir' => $request->persen,
            'kadar_bau_terakhir' => $request->bau,
            'waktu_pengangkutan' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pengangkutan berhasil dicatat.'
        ]);
    }

    public function hapusRiwayat($id)
    {
        History::destroy($id);
        return redirect()->back()->with('success', 'Data riwayat berhasil dihapus.');
    }

    public function hapusSemuaRiwayat()
    {
        History::truncate();
        return redirect()->back()->with('success', 'Semua data riwayat telah dikosongkan.');
    }
}