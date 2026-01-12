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
        /* Layout Dasar Dashboard */
        .monitoring-layout { display: flex; gap: 20px; height: calc(100vh - 140px); margin-top: 20px; }
        .side-panel { width: 350px; background: white; border-radius: 15px; padding: 20px; display: flex; flex-direction: column; gap: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        #map { flex-grow: 1; border-radius: 15px; border: 4px solid white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 1; }
        
        .list-container { overflow-y: auto; flex-grow: 1; margin-top: 10px; }
        .location-card { 
            padding: 12px; border: 1px solid #f1f5f9; border-radius: 10px; margin-bottom: 10px; 
            cursor: pointer; transition: 0.2s; display: flex; justify-content: space-between; align-items: center;
        }
        .location-card:hover { background: #f8fafc; border-color: #6366f1; }
        
        /* Tombol Aksi */
        .btn-all-route { 
            background: #22c55e; color: white; border: none; padding: 12px; border-radius: 10px; 
            font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
        }
        .btn-all-route:hover { background: #16a34a; }

        /* Badge Status */
        .badge-status { padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 5px; }
        .badge-full { background: #fee2e2; color: #ef4444; }
        .badge-smell { background: #fef3c7; color: #d97706; }

        /* Tampilan Timeline Navigasi */
        .timeline-container { border-left: 3px solid #6366f1; margin-left: 10px; padding-left: 20px; position: relative; margin-top: 10px; }
        .step-card { 
            background: #f8fafc; border-radius: 8px; padding: 10px; margin-bottom: 15px; 
            position: relative; border: 1px solid #e2e8f0; cursor: pointer; transition: 0.2s;
        }
        .step-card:hover { background: #eef2ff; border-color: #6366f1; }
        .step-card::before { content: ''; position: absolute; left: -27px; top: 12px; width: 12px; height: 12px; background: #6366f1; border-radius: 50%; border: 3px solid white; }
        .step-destination { font-weight: 700; color: #1e293b; display: block; font-size: 14px; }
        .step-details { font-size: 11px; color: #6366f1; font-weight: 500; }

        /* Sembunyikan panel bawaan Leaflet agar rapi */
        .leaflet-routing-container { display: none; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        @include('partials.sidebar')

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Monitoring & Rute Optimal</h1>
                    <p>Klik urutan lokasi untuk melihat rute per segmen.</p>
                </div>
            </header>

            <div class="monitoring-layout">
                <div class="side-panel">
                    <button class="btn-all-route" onclick="buatRuteKeliling()">
                        <i class="fa-solid fa-truck-fast"></i> Mulai Rute Pengangkutan
                    </button>

                    <div id="navigation-summary" style="margin-top: 15px; display: none;">
                        <h3 style="font-size: 16px; margin-bottom: 10px; color: #1e293b;">
                            <i class="fa-solid fa-map-location-dot"></i> Urutan Penjemputan
                        </h3>
                        <div id="instruction-steps" class="timeline-container">
                        </div>
                    </div>
                    
                    <div class="list-container">
                        <h3 style="margin-bottom: 15px; font-size: 16px;">Titik Lokasi Terdeteksi</h3>
                        @foreach($devices as $item)
                        <div class="location-card" onclick="fokusKeTitik({{ $item['lat'] }}, {{ $item['lng'] }})">
                            <div>
                                <span style="font-weight: 700; color: #1e293b;">{{ $item['id'] }}</span><br>
                                <small style="color: #64748b;">{{ $item['lokasi'] }}</small>
                            </div>
                            <div style="text-align: right;">
                                @if($item['persen'] >= 80)
                                    <span class="badge-status badge-full">Penuh</span>
                                @endif
                                @if(isset($item['bau']) && $item['bau'] >= 400)
                                    <span class="badge-status badge-smell">Berbau</span>
                                @endif
                            </div>
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
        // Data dari Controller
        const dataKantor = {!! json_encode($kantor) !!};
        const dataDevices = {!! json_encode($devices) !!};

        // Inisialisasi Map
        const map = L.map('map').setView([dataKantor.lat, dataKantor.lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Marker Kantor
        const iconKantor = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/167/167707.png',
            iconSize: [40, 40]
        });
        L.marker([dataKantor.lat, dataKantor.lng], {icon: iconKantor}).addTo(map).bindPopup("Kantor Pusat");

        // Marker Lokasi Sampah
        dataDevices.forEach(d => {
            L.marker([d.lat, d.lng]).addTo(map).bindPopup(`<b>${d.id}</b><br>${d.lokasi}`);
        });

        let routingControl = null;

        // Fungsi untuk fokus ke titik tertentu
        function fokusKeTitik(lat, lng) {
            map.flyTo([lat, lng], 17);
        }

        // Fungsi untuk menampilkan satu rute segmen (A ke B)
        function tampilkanRuteSegmen(lat1, lng1, lat2, lng2) {
            if (routingControl) map.removeControl(routingControl);

            routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(lat1, lng1),
                    L.latLng(lat2, lng2)
                ],
                createMarker: function() { return null; },
                lineOptions: { styles: [{ color: '#6366f1', weight: 6 }] }
            }).addTo(map);
            
            const bounds = L.latLngBounds([ [lat1, lng1], [lat2, lng2] ]);
            map.fitBounds(bounds.pad(0.5));
        }

        // Hitung Jarak sederhana
        function hitungJarak(p1, p2) {
            return Math.sqrt(Math.pow(p1.lat - p2.lat, 2) + Math.pow(p1.lng - p2.lng, 2));
        }

        // Algoritma Urutan
        function urutkanDenganNearestNeighbour() {
            let unvisited = dataDevices.filter(d => d.persen >= 80 || (d.bau && d.bau >= 400)); 
            let currentPos = { lat: dataKantor.lat, lng: dataKantor.lng }; 
            let ruteTerurut = [ {nama: "Kantor Pusat (Mulai)", lat: dataKantor.lat, lng: dataKantor.lng} ];

            if (unvisited.length === 0) {
                alert("Tidak ada lokasi yang perlu diangkut.");
                return null;
            }

            while (unvisited.length > 0) {
                let indexTerdekat = -1;
                let jarakTerkecil = Infinity;

                for (let i = 0; i < unvisited.length; i++) {
                    let jarak = hitungJarak(currentPos, unvisited[i]);
                    if (jarak < jarakTerkecil) {
                        jarakTerkecil = jarak;
                        indexTerdekat = i;
                    }
                }

                let titik = unvisited.splice(indexTerdekat, 1)[0];
                ruteTerurut.push({nama: titik.lokasi, lat: titik.lat, lng: titik.lng});
                currentPos = { lat: titik.lat, lng: titik.lng }; 
            }

            ruteTerurut.push({nama: "Kantor Pusat (Selesai)", lat: dataKantor.lat, lng: dataKantor.lng});
            return ruteTerurut;
        }

        // Tampilkan urutan di panel samping
        function buatRuteKeliling() {
            const waypointsData = urutkanDenganNearestNeighbour();
            if (!waypointsData) return;

            document.getElementById('navigation-summary').style.display = 'block';
            const instructionContainer = document.getElementById('instruction-steps');
            instructionContainer.innerHTML = '';

            // Render Timeline
            waypointsData.forEach((point, i) => {
                let card = document.createElement('div');
                card.className = 'step-card';
                
                let detailText = (i === 0) ? "Titik Keberangkatan" : "Klik untuk lihat rute ke sini";
                if (i === waypointsData.length - 1) detailText = "Titik Akhir (Depot)";

                card.innerHTML = `
                    <span class="step-destination">${point.nama}</span>
                    <span class="step-details">${detailText}</span>
                `;

                // Event klik untuk rute segmen
                if (i > 0) {
                    card.onclick = () => {
                        const prev = waypointsData[i-1];
                        tampilkanRuteSegmen(prev.lat, prev.lng, point.lat, point.lng);
                    };
                } else {
                    card.onclick = () => fokusKeTitik(point.lat, point.lng);
                }

                instructionContainer.appendChild(card);
            });

            // Tampilkan rute penuh di awal
            if (routingControl) map.removeControl(routingControl);
            routingControl = L.Routing.control({
                waypoints: waypointsData.map(p => L.latLng(p.lat, p.lng)),
                createMarker: function() { return null; }
            }).addTo(map);

            const bounds = L.latLngBounds(waypointsData.map(p => [p.lat, p.lng]));
            map.fitBounds(bounds.pad(0.3));
        }
    </script>
</body>
</html>