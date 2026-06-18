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
    private $username = 'zetsukami213@gmail.com'; 
    private $password = '13012004';

    // Device IDs dari ThingsBoard
    private $deviceIdTR01 = 'bd7c1110-6a3d-11f1-8797-0936777895d2'; 
    private $deviceIdTR02 = 'e6e20f00-6a3d-11f1-ad38-35390c349091'; 

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

        $statusBau = 'Aman';
        if ($currentBau >= 800) {
            $statusBau = 'Bau Nyengat';
        } elseif ($currentBau >= 400) {
            $statusBau = 'Bau';
        }

        $cacheKey = "status_penuh_" . str_replace('#', '', $idTag);
        $wasFullOrSmelly = Cache::get($cacheKey, false);

        if ($currentPersen >= 80 || $currentBau >= 800) {
            Cache::put($cacheKey, true, now()->addDays(7));
        } 

        if ($wasFullOrSmelly && $currentPersen < 10) {
            History::create([
                'device_id' => $idTag,
                'lokasi' => $lokasi,
                'kapasitas_terakhir' => 100, 
                'kadar_bau_terakhir' => $currentBau,
                'waktu_pengangkutan' => now(),
            ]);
            Cache::forget($cacheKey);
        }

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
            'status_bau' => $statusBau, 
            'status' => $status,
            'update' => $update,
            'lat' => $lat,
            'lng' => $lng
        ];
    }

    // --- FUNGSI HELPER UNTUK MEMBUAT DATA DUMMY ---
    private function createDummyData($idTag, $lokasi, $lat, $lng, $persen, $bau)
    {
        $statusBau = 'Aman';
        if ($bau >= 800) {
            $statusBau = 'Bau Nyengat';
        } elseif ($bau >= 400) {
            $statusBau = 'Bau';
        }

        return [
            'id' => $idTag,
            'lokasi' => $lokasi,
            'persen' => $persen,
            'bau' => $bau,
            'status_bau' => $statusBau,
            'status' => 'online', 
            'update' => 'Baru saja',
            'lat' => $lat,
            'lng' => $lng
        ];
    }

    private function getDeviceData()
    {
        // 1. Data Asli IoT
        $devices = [
            $this->formatDeviceData($this->deviceIdTR01, '#TR-01', 'Grand Sulawesi Parepare', -4.006904852098234, 119.66253093102463),
            $this->formatDeviceData($this->deviceIdTR02, '#TR-02', 'Perumahan Pare Town House', -4.010893730077395, 119.63298928262212),
        ];

        // 2. Data Dummy (2 Lokasi x 4 Kecamatan = 8 Titik)
        $dummyList = [
            // --- KECAMATAN UJUNG (Pusat Kota/Barat) ---
            ['Pasar Senggol', -4.007292, 119.621973, 95, 900],
            ['Monumen Habibie', -4.012640, 119.6220213, 45, 300],

            // --- KECAMATAN SOREANG (Utara) ---
            ['Pasar Lakessi', -4.004092, 119.627335, 85, 450],
            ['Polsek Soreang', -3.990735, 119.651813, 20, 100],

            // --- KECAMATAN BACUKIKI BARAT (Selatan) ---
            ['RS dr. Hasri Ainun Habibie', -4.048255, 119.621842, 90, 850],
            ['Islamic Center', -4.015800, 119.623808, 30, 150],

            // --- KECAMATAN BACUKIKI (Timur/Pedalaman) ---
            ['Puskesmas Lompoe', -4.015252, 119.657346, 40, 850],
            ['Kantor Camat', -4.022163, 119.656651, 80, 200],
        ];

        // 3. Gabungkan Data Dummy ke Daftar Devices
        $idCounter = 3;
        foreach ($dummyList as $d) {
            $idTag = sprintf('#TR-%02d', $idCounter);
            
            $devices[] = $this->createDummyData(
                $idTag, 
                $d[0], $d[1], $d[2], $d[3], $d[4]            
            );
            $idCounter++;
        }

        return $devices;
    }

    public function index()
    {
        $devices = $this->getDeviceData();
        $totalLokasi = count($devices);
        
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

    // --- FUNGSI BARU UNTUK FETCH API (REAL-TIME UPDATE) ---
    public function getRealtimeData()
    {
        $devices = $this->getDeviceData();
        $kantor = [
            'nama' => 'TPS', 
            'lat' => -3.988430338950498, 
            'lng' => 119.65216109576326
        ];
        
        $totalLokasi = count($devices);
        
        $titikPenuh = collect($devices)->filter(function ($item) {
            return $item['persen'] >= 80 || $item['bau'] >= 800;
        })->count();

        $perangkatAktif = collect($devices)->where('status', 'online')->count();

        return response()->json([
            'devices' => $devices,
            'kantor' => $kantor,
            'totalLokasi' => $totalLokasi,
            'titikPenuh' => $titikPenuh,
            'perangkatAktif' => $perangkatAktif
        ]);
    }
}