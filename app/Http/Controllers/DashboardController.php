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

            // Diubah menjadi 60 detik (1 menit)
            if ($diffInSeconds < 120) {
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
            'status' => 'offline',            // Diubah menjadi offline
            'update' => 'Tidak ada data',     // Teks disesuaikan
            'lat' => $lat,
            'lng' => $lng
        ];
    }

    private function getDeviceData()
    {
        // Titik Asli dari ThingsBoard
        $devices = [
            $this->formatDeviceData($this->deviceIdTR01, '#TR-01', 'Kantor Kelurahan Galung Maloang', -4.017858, 119.662751),
            $this->formatDeviceData($this->deviceIdTR02, '#TR-02', 'Kampus 2 ITH Parepare', -4.027225, 119.630519),
        ];

        // 20 Titik Dummy - Disebar 5 titik di 4 Kecamatan Berbeda
        $dummyList = [
            // KECAMATAN UJUNG
            ['Pasar Senggol', -4.007292, 119.621973, 45, 300],          // TR-03
            ['Monumen Habibie Ainun', -4.012640, 119.622021, 50, 250],  // TR-04
            ['Pelabuhan Nusantara', -4.012841, 119.620385, 55, 400],    // TR-05
            ['Dinas Pendidikan Kota Parepare', -4.008670, 119.625532, 35, 200], // TR-06
            ['Masjid Raya Parepare', -4.009462, 119.621925, 90, 850],   // TR-07 ✅

            // KECAMATAN SOREANG
            ['Pasar Lakessi', -4.004092, 119.627335, 55, 350],          // TR-08
            ['Polsek Soreang', -3.990735, 119.651813, 40, 150],         // TR-09
            ['MAN 1 Parepare', -3.983912, 119.640791, 35, 180],         // TR-10
            ['Kebun Raya Jompie Parepare', -3.99705, 119.64098, 20, 100], // TR-11
            ['UPTD SD Negeri 23 Parepare', -4.003799, 119.633485, 92, 880], // TR-12 ✅

            // KECAMATAN BACUKIKI BARAT
            ['RS dr. Hasri Ainun Habibie', -4.048255, 119.621842, 55, 300], // TR-13
            ['Islamic Center', -4.015792, 119.623808, 40, 180],             // TR-14
            ['RSUD Andi Makassau', -4.03525, 119.63425, 45, 250],           // TR-15
            ['Kantor Kecamatan Bacukiki Barat', -4.042680, 119.627396, 50, 400], // TR-16
            ['SMAN 2 Parepare', -4.038579, 119.627541, 55, 350],            // TR-17

            // KECAMATAN BACUKIKI
            ['Puskesmas Lompoe', -4.015252, 119.657346, 95, 900],      // TR-18 ✅
            ['Perumahan Grand Sulawesi', -4.00746, 119.66094, 35, 150], // TR-19
            ['UPTD SMP Negeri 8 Parepare', -4.015911, 119.647229, 45, 300], // TR-20
            ['Kantor Kelurahan Lompoe', -4.017151, 119.651037, 50, 400], // TR-21
            ['Lapas Kelas IIA Parepare', -4.003751, 119.666948, 60, 500], // TR-22
        ];

        $idCounter = 3;
        foreach ($dummyList as $d) {
            $idTag = sprintf('#TR-%02d', $idCounter);
            $devices[] = $this->createDummyData($idTag, $d[0], $d[1], $d[2], $d[3], $d[4]);
            $idCounter++;
        }

        return $devices;
    }

    // =========================================================================
    // ALGORITMA NEAREST NEIGHBOUR (UNTUK SKRIPSI)
    // =========================================================================

    // Rumus Haversine: Mengubah derajat Lat/Lng menjadi jarak dalam Kilometer (Meningkatkan Akurasi Ilmiah)
    private function hitungJarakHaversine($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        return $earthRadius * $c; // Jarak dalam KM
    }

    // Logika Pengurutan
    private function hitungRuteNearestNeighbour($devices, $kantor) {
        // Filter titik yang melampaui batas ambang penuh/bau
        $unvisited = collect($devices)->filter(function ($d) {
            return $d['persen'] >= 80 || $d['bau'] >= 800;
        })->values()->toArray();

        // Jika tidak ada titik yang perlu diangkut
        if (count($unvisited) == 0) {
            return []; 
        }

        $currentPos = ['lat' => $kantor['lat'], 'lng' => $kantor['lng'], 'nama' => 'Depot TPS'];
        $rute = [$currentPos];

        // Eksekusi Nearest Neighbour
        while (count($unvisited) > 0) {
            $nearestIndex = -1;
            $jarakTerkecil = PHP_INT_MAX;

            foreach ($unvisited as $index => $titik) {
                $jarak = $this->hitungJarakHaversine($currentPos['lat'], $currentPos['lng'], $titik['lat'], $titik['lng']);
                if ($jarak < $jarakTerkecil) {
                    $jarakTerkecil = $jarak;
                    $nearestIndex = $index;
                }
            }

            $titikTerdekat = $unvisited[$nearestIndex];
            
            $rute[] = [
                'id' => $titikTerdekat['id'],
                'nama' => $titikTerdekat['lokasi'],
                'lat' => $titikTerdekat['lat'],
                'lng' => $titikTerdekat['lng'],
                'jarak_ke_titik_km' => round($jarakTerkecil, 2) 
            ];

            // Pindah posisi ke titik yang baru saja dipilih
            $currentPos = ['lat' => $titikTerdekat['lat'], 'lng' => $titikTerdekat['lng'], 'nama' => $titikTerdekat['lokasi']];
            
            // Hapus titik dari daftar yang belum dikunjungi
            array_splice($unvisited, $nearestIndex, 1);
        }

        // Kembali ke titik awal (Depot TPS)
        $rute[] = ['nama' => 'Depot TPS (Selesai)', 'lat' => $kantor['lat'], 'lng' => $kantor['lng']];

        return $rute;
    }
    // =========================================================================

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

        // Hitung rute otomatis saat halaman dibuka
        $ruteOptimal = $this->hitungRuteNearestNeighbour($devices, $kantor);

        return view('monitoring', compact('devices', 'kantor', 'ruteOptimal'));
    }

    public function riwayat(Request $request)
    {
        $query = History::query();

        if ($request->filled('search')) {
            $query->where('lokasi', 'like', '%' . $request->search . '%')
                 ->orWhere('device_id', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('waktu_pengangkutan', $request->tanggal);
        }

        $logs = $query->orderBy('waktu_pengangkutan', 'desc')->get();
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

        return response()->json(['success' => true, 'message' => 'Riwayat pengangkutan berhasil dicatat.']);
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

        // Hitung rute secara real-time via API
        $ruteOptimal = $this->hitungRuteNearestNeighbour($devices, $kantor);

        return response()->json([
            'devices' => $devices,
            'kantor' => $kantor,
            'rute_optimal' => $ruteOptimal, 
            'totalLokasi' => $totalLokasi,
            'titikPenuh' => $titikPenuh,
            'perangkatAktif' => $perangkatAktif
        ]);
    }
}