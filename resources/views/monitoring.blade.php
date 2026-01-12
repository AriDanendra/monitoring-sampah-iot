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
        body, html { height: 100%; margin: 0; overflow: hidden; } 
        .dashboard-wrapper { display: flex; height: 100vh; }
        .main-content { flex: 1; display: flex; flex-direction: column; padding: 20px 30px; overflow: hidden; }

        .monitoring-layout { 
            display: flex; 
            gap: 20px; 
            flex: 1; 
            margin-top: 15px; 
            min-height: 0; 
        }

        /* Side Panel Container */
        .side-panel { 
            width: 380px; 
            background: white; 
            border-radius: 15px; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            height: 100%;
            overflow: hidden; 
        }

        .panel-header {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            gap: 10px;
        }
        
        .panel-body {
            flex: 1;
            padding: 0 20px 20px 20px;
            overflow-y: auto;
        }
        
        .panel-body::-webkit-scrollbar { width: 5px; }
        .panel-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        #map { 
            flex: 1; 
            border-radius: 15px; 
            border: 4px solid white; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); 
            z-index: 1; 
            height: 100%; 
        }

        .btn-all-route { 
            background: #22c55e; color: white; border: none; padding: 14px; border-radius: 10px; 
            font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; 
            gap: 10px; flex: 1; transition: 0.3s;
        }
        .btn-all-route:hover { background: #16a34a; transform: translateY(-2px); }

        .btn-refresh-action {
            background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0;
            width: 48px; height: 48px; border-radius: 10px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: 0.2s;
        }
        .btn-refresh-action:hover { background: #f1f5f9; color: #1e293b; }

        .section-title { font-size: 15px; margin: 25px 0 15px; color: #1e293b; font-weight: 700; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* --- Timeline Pickup Sequence (Rapi) --- */
        .timeline-container { border-left: 2px dashed #cbd5e1; margin-left: 15px; padding-left: 25px; position: relative; }
        
        .step-card { 
            background: #ffffff; border-radius: 12px; padding: 12px 15px; margin-bottom: 15px; 
            border: 1px solid #e2e8f0; cursor: pointer; position: relative; transition: 0.2s;
            display: flex; align-items: flex-start; gap: 12px;
        }
        .step-card:hover { border-color: #6366f1; background: #f8faff; transform: translateX(5px); }

        /* Dot Penomoran */
        .step-card::before { 
            content: attr(data-step); 
            position: absolute; left: -36px; top: 12px; 
            width: 22px; height: 22px; 
            background: #6366f1; color: white; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .step-icon { color: #94a3b8; font-size: 16px; margin-top: 2px; }
        .step-info { flex: 1; }
        .step-destination { font-weight: 700; color: #1e293b; font-size: 13.5px; display: block; margin-bottom: 2px; }
        .step-details { font-size: 11px; color: #6366f1; font-weight: 500; display: flex; align-items: center; gap: 4px; }

        /* Warna Khusus Start/End */
        .step-card.start::before { background: #22c55e; }
        .step-card.end::before { background: #ef4444; }

        /* List Lokasi */
        .location-card { 
            padding: 12px; border: 1px solid #f1f5f9; border-radius: 10px; margin-bottom: 10px; 
            cursor: pointer; transition: 0.2s; display: flex; justify-content: space-between; align-items: center;
        }
        .location-card:hover { background: #f8fafc; border-color: #6366f1; }
        
        .badge-status { padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-full { background: #fee2e2; color: #ef4444; }
        .badge-smell { background: #fef3c7; color: #d97706; }

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
                    <p>Optimasi rute pengangkutan sampah berbasis real-time data.</p>
                </div>
            </header>

            <div class="monitoring-layout">
                <div class="side-panel">
                    <div class="panel-header">
                        <button class="btn-all-route" onclick="buatRuteKeliling()">
                            <i class="fa-solid fa-truck-fast"></i> Mulai Rute
                        </button>
                        <button class="btn-refresh-action" onclick="window.location.reload()" title="Refresh Data">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                    </div>

                    <div class="panel-body">
                        <div id="navigation-summary" style="display: none;">
                            <h3 class="section-title">
                                <i class="fa-solid fa-route"></i> Urutan Penjemputan
                            </h3>
                            <div id="instruction-steps" class="timeline-container"></div>
                        </div>
                        
                        <div class="list-container">
                            <h3 class="section-title">
                                <i class="fa-solid fa-hospital-user"></i> Status Titik Bak Sampah
                            </h3>
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
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const iconKantor = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/167/167707.png',
            iconSize: [40, 40]
        });
        L.marker([dataKantor.lat, dataKantor.lng], {icon: iconKantor}).addTo(map).bindPopup("TPS");

        dataDevices.forEach(d => {
            L.marker([d.lat, d.lng]).addTo(map).bindPopup(`<b>${d.id}</b><br>${d.lokasi}`);
        });

        let routingControl = null;

        function fokusKeTitik(lat, lng) {
            map.flyTo([lat, lng], 17);
        }

        function tampilkanRuteSegmen(lat1, lng1, lat2, lng2) {
            if (routingControl) map.removeControl(routingControl);

            routingControl = L.Routing.control({
                waypoints: [L.latLng(lat1, lng1), L.latLng(lat2, lng2)],
                createMarker: function() { return null; },
                lineOptions: { styles: [{ color: '#6366f1', weight: 6 }] }
            }).addTo(map);
            
            const bounds = L.latLngBounds([[lat1, lng1], [lat2, lng2]]);
            map.fitBounds(bounds.pad(0.5));
        }

        function hitungJarak(p1, p2) {
            return Math.sqrt(Math.pow(p1.lat - p2.lat, 2) + Math.pow(p1.lng - p2.lng, 2));
        }

        function urutkanDenganNearestNeighbour() {
            let unvisited = dataDevices.filter(d => d.persen >= 80 || (d.bau && d.bau >= 400)); 
            let currentPos = { lat: dataKantor.lat, lng: dataKantor.lng }; 
            let ruteTerurut = [{nama: "Depot TPS", lat: dataKantor.lat, lng: dataKantor.lng}];

            if (unvisited.length === 0) {
                alert("Semua bak sampah masih di bawah ambang batas.");
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
            ruteTerurut.push({nama: "Depot TPS (Selesai)", lat: dataKantor.lat, lng: dataKantor.lng});
            return ruteTerurut;
        }

        function buatRuteKeliling() {
            const waypointsData = urutkanDenganNearestNeighbour();
            if (!waypointsData) return;

            document.getElementById('navigation-summary').style.display = 'block';
            const instructionContainer = document.getElementById('instruction-steps');
            instructionContainer.innerHTML = '';

            waypointsData.forEach((point, i) => {
                let card = document.createElement('div');
                card.className = 'step-card';
                card.setAttribute('data-step', i + 1);
                
                let iconClass = "fa-solid fa-location-dot";
                let detailText = `<i class="fa-solid fa-up-right-from-square"></i> Lihat rute dari titik sebelumnya`;
                
                if (i === 0) {
                    card.classList.add('start');
                    iconClass = "fa-solid fa-house-flag";
                    detailText = "Titik keberangkatan armada";
                } else if (i === waypointsData.length - 1) {
                    card.classList.add('end');
                    iconClass = "fa-solid fa-flag-checkered";
                    detailText = "Titik akhir / Pembuangan";
                }

                card.innerHTML = `
                    <div class="step-icon"><i class="${iconClass}"></i></div>
                    <div class="step-info">
                        <span class="step-destination">${point.nama}</span>
                        <span class="step-details">${detailText}</span>
                    </div>
                `;

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