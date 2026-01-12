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
        .step-card { background: #f8fafc; border-radius: 8px; padding: 10px; margin-bottom: 15px; position: relative; border: 1px solid #e2e8f0; }
        .step-card::before { content: ''; position: absolute; left: -27px; top: 12px; width: 12px; height: 12px; background: #6366f1; border-radius: 50%; border: 3px solid white; }
        .step-destination { font-weight: 700; color: #1e293b; display: block; margin-bottom: 5px; font-size: 14px; }
        .step-details { font-size: 12px; color: #64748b; line-height: 1.5; }

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
                    <p>Navigasi efisien dari kantor pusat ke titik penjemputan[cite: 156].</p>
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
        // Registrasi Bahasa Indonesia untuk Navigasi [cite: 162]
        L.Routing.Localization['id'] = {
            directions: { North: 'Utara', Northeast: 'Timur Laut', East: 'Timur', Southeast: 'Tenggara', South: 'Selatan', Southwest: 'Barat Daya', West: 'Barat', Northwest: 'Barat Laut' },
            instructions: {
                Head: ['Kepala {dir}', 'di {road}'], SlightRight: ['Serong kanan', 'di {road}'], Right: ['Belok kanan', 'di {road}'], SharpRight: ['Belok tajam kanan', 'di {road}'],
                SlightLeft: ['Serong kiri', 'di {road}'], Left: ['Belok kiri', 'di {road}'], SharpLeft: ['Belok tajam kiri', 'di {road}'], Straight: ['Lurus', 'di {road}'],
                DestinationReached: 'Sampai di tujuan', WayPointReached: 'Sampai di titik transit', Roundabout: ['Masuk bundaran', 'di {road}'], TurnAround: 'Putar balik'
            },
            formatOrder: function(n) { return n; },
            ui: { startPlaceholder: 'Awal', viaPlaceholder: 'Via', endPlaceholder: 'Tujuan' }
        };

        const dataKantor = {!! json_encode($kantor) !!};
        const dataDevices = {!! json_encode($devices) !!};

        const map = L.map('map').setView([dataKantor.lat, dataKantor.lng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Marker Kantor Pusat (Depot) [cite: 294, 299]
        const iconKantor = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/167/167707.png',
            iconSize: [40, 40]
        });
        L.marker([dataKantor.lat, dataKantor.lng], {icon: iconKantor})
            .addTo(map)
            .bindPopup("<b>Kantor Pusat (Mulai/Selesai)</b>");

        // Marker Lokasi Sampah [cite: 280]
        dataDevices.forEach(d => {
            let statusText = (d.bau && d.bau >= 400) ? "<br><span style='color:orange'>Status: Berbau</span>" : "";
            L.marker([d.lat, d.lng])
                .addTo(map)
                .bindPopup(`<b>${d.id}</b><br>${d.lokasi}<br>Kapasitas: ${d.persen}%${statusText}`);
        });

        let routingControl = null;

        function hitungJarak(p1, p2) {
            return Math.sqrt(Math.pow(p1.lat - p2.lat, 2) + Math.pow(p1.lng - p2.lng, 2));
        }

        // Algoritma Nearest Neighbour Sesuai Metodologi [cite: 292-299]
        function urutkanDenganNearestNeighbour() {
            // Filter: Hanya lokasi penuh (>=80%) atau berbau (>=400ppm) [cite: 161, 321]
            let unvisited = dataDevices.filter(d => d.persen >= 80 || (d.bau && d.bau >= 400)); 
            let currentPos = { lat: dataKantor.lat, lng: dataKantor.lng }; 
            let ruteTerurut = [L.latLng(dataKantor.lat, dataKantor.lng)];

            if (unvisited.length === 0) {
                alert("Tidak ada lokasi yang perlu diangkut saat ini.");
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

                let titikTerdekat = unvisited.splice(indexTerdekat, 1)[0];
                ruteTerurut.push(L.latLng(titikTerdekat.lat, titikTerdekat.lng));
                currentPos = { lat: titikTerdekat.lat, lng: titikTerdekat.lng }; 
            }

            ruteTerurut.push(L.latLng(dataKantor.lat, dataKantor.lng)); // Kembali ke Depot [cite: 299]
            return ruteTerurut;
        }

        function buatRuteKeliling() {
            if (routingControl) map.removeControl(routingControl);

            let waypoints = urutkanDenganNearestNeighbour();
            if (!waypoints) return;

            document.getElementById('navigation-summary').style.display = 'block';
            const instructionContainer = document.getElementById('instruction-steps');
            instructionContainer.innerHTML = '<p style="font-size:12px;">Menghitung rute optimal...</p>';

            routingControl = L.Routing.control({
                waypoints: waypoints,
                language: 'id', // Gunakan lokalisasi yang didaftarkan
                routeWhileDragging: false,
                addWaypoints: false,
                createMarker: function() { return null; }
            }).addTo(map);

            // Olah instruksi menjadi tampilan Timeline yang mudah dibaca [cite: 282, 283]
            routingControl.on('routesfound', function(e) {
                const routes = e.routes[0];
                const instructions = routes.instructions;
                instructionContainer.innerHTML = '';
                let waypointCount = 0;

                instructions.forEach((instr, i) => {
                    // Hanya tampilkan langkah saat mencapai titik lokasi
                    if (instr.type === 'WaypointReached' || i === 0 || i === instructions.length - 1) {
                        let latLng = waypoints[waypointCount];
                        let locationName = "Titik Penjemputan";

                        if (waypointCount === 0) locationName = "Kantor Pusat (Mulai)";
                        else if (waypointCount === waypoints.length - 1) locationName = "Kantor Pusat (Selesai)";
                        else {
                            let device = dataDevices.find(d => Math.abs(d.lat - latLng.lat) < 0.0001);
                            locationName = device ? device.lokasi : "Lokasi Sampah";
                        }

                        let card = document.createElement('div');
                        card.className = 'step-card';
                        card.innerHTML = `
                            <span class="step-destination">${locationName}</span>
                            <div class="step-details">
                                Jarak: ${(instr.distance / 1000).toFixed(2)} km <br>
                                Estimasi waktu tiba: ${Math.round(instr.time / 60)} menit
                            </div>
                        `;
                        instructionContainer.appendChild(card);
                        waypointCount++;
                    }
                });
            });

            const bounds = L.latLngBounds(waypoints);
            map.fitBounds(bounds.pad(0.3));
        }

        function fokusKeTitik(lat, lng) {
            map.flyTo([lat, lng], 17);
        }
    </script>
</body>
</html>