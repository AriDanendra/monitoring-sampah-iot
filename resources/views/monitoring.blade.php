<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring - Smart Waste System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <style>
        .monitoring-layout { display: flex; gap: 20px; height: calc(100vh - 140px); margin-top: 20px; }
        .side-panel { width: 350px; background: white; border-radius: 15px; padding: 20px; display: flex; flex-direction: column; gap: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        #map { flex-grow: 1; border-radius: 15px; border: 4px solid white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 1; }
        
        .list-container { overflow-y: auto; flex-grow: 1; }
        .location-card { 
            padding: 12px; border: 1px solid #f1f5f9; border-radius: 10px; margin-bottom: 10px; 
            cursor: pointer; transition: 0.2s; display: flex; justify-content: space-between; align-items: center;
        }
        .location-card:hover { background: #f8fafc; border-color: #6366f1; }
        .btn-all-route { 
            background: #22c55e; color: white; border: none; padding: 12px; border-radius: 10px; 
            font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-all-route:hover { background: #16a34a; }
        .badge-full { background: #fee2e2; color: #ef4444; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        @include('partials.sidebar')

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Monitoring & Rute Optimal</h1>
                    <p>Navigasi efisien dari kantor pusat ke titik penjemputan.</p>
                </div>
            </header>

            <div class="monitoring-layout">
                <div class="side-panel">
                    <button class="btn-all-route" onclick="buatRuteKeliling()">
                        <i class="fa-solid fa-truck-fast"></i> Mulai Rute Pengangkutan
                    </button>
                    
                    <div class="list-container">
                        <h3 style="margin-bottom: 15px; font-size: 16px;">Titik Lokasi Terdeteksi</h3>
                        @foreach($devices as $item)
                        <div class="location-card" onclick="fokusKeTitik({{ $item['lat'] }}, {{ $item['lng'] }})">
                            <div>
                                <span style="font-weight: 700; color: #1e293b;">{{ $item['id'] }}</span><br>
                                <small style="color: #64748b;">{{ $item['lokasi'] }}</small>
                            </div>
                            @if($item['persen'] >= 80)
                                <span class="badge-full">Penuh</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <div id="map"></div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <script>
        const dataKantor = {!! json_encode($kantor) !!};
        const dataDevices = {!! json_encode($devices) !!};

        const map = L.map('map').setView([dataKantor.lat, dataKantor.lng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Marker Kantor
        const iconKantor = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/167/167707.png',
            iconSize: [40, 40]
        });
        L.marker([dataKantor.lat, dataKantor.lng], {icon: iconKantor})
            .addTo(map)
            .bindPopup("<b>Kantor Pusat (Mulai/Selesai)</b>");

        // Marker Tempat Sampah
        dataDevices.forEach(d => {
            L.marker([d.lat, d.lng])
                .addTo(map)
                .bindPopup(`<b>${d.id}</b><br>${d.lokasi}<br>Kapasitas: ${d.persen}%`);
        });

        let routingControl = null;

        // Fungsi menghitung jarak antara dua titik (Euclidean Distance sederhana)
        function hitungJarak(p1, p2) {
            return Math.sqrt(Math.pow(p1.lat - p2.lat, 2) + Math.pow(p1.lng - p2.lng, 2));
        }

        // FUNGSI UTAMA: Algoritma Nearest Neighbour
        function urutkanDenganNearestNeighbour() {
            let unvisited = [...dataDevices]; // Salinan daftar lokasi yang belum dikunjungi
            let currentPos = { lat: dataKantor.lat, lng: dataKantor.lng }; // Posisi awal di Kantor
            let ruteTerurut = [L.latLng(dataKantor.lat, dataKantor.lng)];

            while (unvisited.length > 0) {
                let indexTerdekat = -1;
                let jarakTerkecil = Infinity;

                // Cari tetangga terdekat dari posisi saat ini
                for (let i = 0; i < unvisited.length; i++) {
                    let jarak = hitungJarak(currentPos, unvisited[i]);
                    if (jarak < jarakTerkecil) {
                        jarakTerkecil = jarak;
                        indexTerdekat = i;
                    }
                }

                // Pindah ke titik terdekat yang ditemukan
                let titikTerdekat = unvisited.splice(indexTerdekat, 1)[0];
                ruteTerurut.push(L.latLng(titikTerdekat.lat, titikTerdekat.lng));
                currentPos = { lat: titikTerdekat.lat, lng: titikTerdekat.lng }; // Update posisi saat ini
            }

            // Kembali ke Kantor sebagai titik akhir
            ruteTerurut.push(L.latLng(dataKantor.lat, dataKantor.lng));
            return ruteTerurut;
        }

        function buatRuteKeliling() {
            if (routingControl) map.removeControl(routingControl);

            // Dapatkan rute yang sudah dioptimasi dengan Nearest Neighbour
            let waypoints = urutkanDenganNearestNeighbour();

            routingControl = L.Routing.control({
                waypoints: waypoints,
                routeWhileDragging: false,
                addWaypoints: false,
                draggableWaypoints: false,
                lineOptions: {
                    styles: [{ color: '#2563eb', weight: 6, opacity: 0.8 }] // Warna biru untuk rute optimasi
                },
                createMarker: function() { return null; }
            }).addTo(map);

            // Zoom otomatis ke rute
            const group = new L.featureGroup(dataDevices.map(d => L.marker([d.lat, d.lng])));
            map.fitBounds(group.getBounds().pad(0.3));
        }

        function fokusKeTitik(lat, lng) {
            map.flyTo([lat, lng], 17);
        }
    </script>
</body>
</html>