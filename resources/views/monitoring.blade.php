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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body, html { height: 100%; margin: 0; overflow: hidden; } 
        .dashboard-wrapper { display: flex; height: 100vh; }
        .main-content { flex: 1; display: flex; flex-direction: column; padding: 20px 30px; overflow: hidden; }
        .monitoring-layout { display: flex; gap: 20px; flex: 1; margin-top: 15px; min-height: 0; }
        .side-panel { width: 380px; background: white; border-radius: 15px; display: flex; flex-direction: column; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: 100%; overflow: hidden; }
        .panel-header { padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; gap: 10px; }
        .panel-body { flex: 1; padding: 0 20px 20px 20px; overflow-y: auto; }
        .panel-body::-webkit-scrollbar { width: 5px; }
        .panel-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        #map { flex: 1; border-radius: 15px; border: 4px solid white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 1; height: 100%; }
        .btn-all-route { background: #22c55e; color: white; border: none; padding: 14px; border-radius: 10px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; flex: 1; transition: 0.3s; }
        .btn-all-route:hover { background: #16a34a; transform: translateY(-2px); }
        .section-title { font-size: 15px; margin: 25px 0 15px; color: #1e293b; font-weight: 700; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .timeline-container { border-left: 2px dashed #cbd5e1; margin-left: 15px; padding-left: 25px; position: relative; }
        .step-card { background: #ffffff; border-radius: 12px; padding: 12px 15px; margin-bottom: 15px; border: 1px solid #e2e8f0; cursor: pointer; position: relative; transition: 0.2s; display: flex; align-items: flex-start; gap: 12px; }
        .step-card:hover { border-color: #6366f1; background: #f8faff; transform: translateX(5px); }
        .step-card::before { content: attr(data-step); position: absolute; left: -36px; top: 12px; width: 22px; height: 22px; background: #6366f1; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .step-icon { color: #94a3b8; font-size: 16px; margin-top: 2px; }
        .step-info { flex: 1; }
        .step-destination { font-weight: 700; color: #1e293b; font-size: 13.5px; display: block; margin-bottom: 2px; }
        .step-details { font-size: 11px; color: #6366f1; font-weight: 500; display: flex; align-items: center; gap: 4px; }
        .step-card.start::before { background: #22c55e; }
        .step-card.end::before { background: #ef4444; }
        .location-card { padding: 12px; border: 1px solid #f1f5f9; border-radius: 10px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; }
        .location-card:hover { background: #f8fafc; border-color: #6366f1; }
        .badge-status { padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .badge-full { background: #fee2e2; color: #ef4444; }
        .badge-extreme { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .leaflet-routing-container { display: none; }
        .custom-route-marker { background: none; border: none; }

        @media screen and (max-width: 768px) {
            body, html { height: auto; overflow: auto; } 
            .main-content { overflow: visible; padding: 15px; height: auto; }
            .monitoring-layout { flex-direction: column; height: auto; overflow: visible; gap: 15px; }
            .side-panel { width: 100%; height: 450px; flex: none; }
            #map { width: 100%; height: 400px !important; flex: none; min-height: 400px; z-index: 1; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        @include('partials.sidebar')
        <div id="sidebar-overlay" class="sidebar-overlay"></div>
        <main class="main-content">
            <header class="top-header">
                <div class="header-left" style="display: flex; align-items: center; gap: 15px;">
                    <button id="mobile-menu-btn" class="mobile-menu-btn">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div>
                        <h1>Monitoring & Rute Optimal</h1>
                        <p>Optimasi rute pengangkutan sampah berbasis real-time data.</p>
                    </div>
                </div>
            </header>

            <div class="monitoring-layout">
                <div class="side-panel">
                    <div class="panel-header">
                        <button class="btn-all-route" onclick="buatRuteKeliling()">
                            <i class="fa-solid fa-truck-fast"></i> Mulai Rute
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
                            <div id="location-list"></div>
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
        let dataDevices = {!! json_encode($devices) !!};
        
        // Menerima Array Rute Optimal yang sudah dihitung oleh Laravel Backend
        let dataRuteOptimal = {!! json_encode($ruteOptimal ?? []) !!};

        const map = L.map('map').setView([dataKantor.lat, dataKantor.lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        setTimeout(function () {
            map.invalidateSize();
        }, 500);

        const iconKantor = L.icon({
            iconUrl: 'https://cdn-icons-png.flaticon.com/512/3299/3299935.png',
            iconSize: [40, 40]
        });
        L.marker([dataKantor.lat, dataKantor.lng], {icon: iconKantor}).addTo(map).bindPopup("TPS");

        let deviceLayerGroup = L.layerGroup().addTo(map);
        let routingControl = null;
        let ruteElements = [];

        function renderMarkers(devices) {
            deviceLayerGroup.clearLayers(); 
            devices.forEach(d => {
                const popupContent = `
                    <div style="font-family: 'Inter', sans-serif; min-width: 150px;">
                        <b style="font-size: 14px; color: #1e293b;">${d.id}</b><br>
                        <span style="color: #64748b; font-size: 12px;">${d.lokasi}</span>
                        <hr style="margin: 8px 0; border: 0; border-top: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-size: 12px;">Kapasitas:</span>
                            <b style="font-size: 12px; color: ${d.persen >= 80 ? '#ef4444' : '#22c55e'};">${d.persen}%</b>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 12px;">Kondisi Bau:</span>
                            <b style="font-size: 12px; color: ${d.bau >= 800 ? '#b91c1c' : (d.bau >= 400 ? '#f59e0b' : '#64748b')};">
                                ${d.status_bau}
                            </b>
                        </div>
                    </div>
                `;
                L.marker([d.lat, d.lng]).addTo(deviceLayerGroup).bindPopup(popupContent);
            });
        }

        function renderList(devices) {
            let listHtml = '';
            devices.forEach(item => {
                let badges = '';
                if(item.persen >= 80) {
                    badges += `<span class="badge-status badge-full">Penuh</span> `;
                }
                if(item.bau >= 800) {
                    badges += `<span class="badge-status badge-extreme"><i class="fa-solid fa-triangle-exclamation"></i> Bau Nyengat</span>`;
                } else if(item.persen < 80) {
                    badges += `<span class="badge-status" style="background: #f1f5f9; color: #64748b;">Aman</span>`;
                }

                listHtml += `
                <div class="location-card">
                    <div onclick="fokusKeTitik(${item.lat}, ${item.lng})" style="flex: 1; cursor: pointer;">
                        <span style="font-weight: 700; color: #1e293b;">${item.id}</span><br>
                        <small style="color: #64748b; display: block; margin-bottom: 4px;">${item.lokasi}</small>
                        <div style="font-size: 11px; color: #475569; display: flex; gap: 8px;">
                            <span><i class="fa-solid fa-fill-drip"></i> <strong>${item.persen}%</strong></span>
                            <span>|</span>
                            <span><i class="fa-solid fa-wind"></i> <strong>${item.status_bau}</strong></span>
                        </div>
                    </div>
                    <div style="text-align: right; display: flex; flex-direction: column; gap: 5px; align-items: flex-end;">
                        ${badges}
                        <small style="font-size: 10px; color: #94a3b8;">${item.update}</small>
                    </div>
                </div>`;
            });
            document.getElementById('location-list').innerHTML = listHtml;
        }

        renderMarkers(dataDevices);
        renderList(dataDevices);

        setInterval(async () => {
            try {
                const response = await fetch('/api/realtime-data');
                const data = await response.json();
                
                dataDevices = data.devices;
                dataRuteOptimal = data.rute_optimal; // Update rute jika ada perubahan kondisi sampah

                renderMarkers(dataDevices);
                renderList(dataDevices);
            } catch (error) {
                console.error("Gagal sinkronisasi data:", error);
            }
        }, 5000);

        function bersihkanRuteManual() {
            if (ruteElements.length > 0) {
                ruteElements.forEach(el => map.removeLayer(el));
                ruteElements = [];
            }
        }

        function fokusKeTitik(lat, lng) {
            if(window.innerWidth <= 768) {
                document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
            }
            map.flyTo([lat, lng], 17);
        }

        function tampilkanRuteSegmen(lat1, lng1, lat2, lng2) {
            if (routingControl) map.removeControl(routingControl);
            bersihkanRuteManual();

            routingControl = L.Routing.control({
                waypoints: [L.latLng(lat1, lng1), L.latLng(lat2, lng2)],
                createMarker: function() { return null; },
                show: false, 
                lineOptions: { styles: [{ color: '#1a73e8', weight: 6, opacity: 0.9 }] } 
            }).addTo(map);
            
            const bounds = L.latLngBounds([[lat1, lng1], [lat2, lng2]]);
            map.fitBounds(bounds.pad(0.5));

            if(window.innerWidth <= 768) {
                document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
            }
        }

        function buatRuteKeliling() {
            // Menggunakan data rute yang di-generate dari Laravel Controller!
            const waypointsData = dataRuteOptimal;

            if (!waypointsData || waypointsData.length <= 1) {
                Swal.fire({ title: 'Status Aman', text: 'Semua bak sampah masih di bawah ambang batas.', icon: 'info', confirmButtonColor: '#6366f1' });
                return;
            }

            document.getElementById('navigation-summary').style.display = 'block';
            const instructionContainer = document.getElementById('instruction-steps');
            instructionContainer.innerHTML = '';

            waypointsData.forEach((point, i) => {
                let card = document.createElement('div');
                card.className = 'step-card';
                card.setAttribute('data-step', i + 1);
                let iconClass = i === 0 ? "fa-house-flag" : (i === waypointsData.length - 1 ? "fa-flag-checkered" : "fa-location-dot");
                
                card.innerHTML = `
                    <div class="step-icon"><i class="fa-solid ${iconClass}"></i></div>
                    <div class="step-info">
                        <span class="step-destination">${point.nama}</span>
                        <span class="step-details">Lihat rute pengangkutan</span>
                    </div>
                `;

                card.onclick = i > 0 
                    ? () => tampilkanRuteSegmen(waypointsData[i-1].lat, waypointsData[i-1].lng, point.lat, point.lng)
                    : () => fokusKeTitik(point.lat, point.lng);
                
                instructionContainer.appendChild(card);
            });

            if (routingControl) map.removeControl(routingControl);
            bersihkanRuteManual();

            const warnaRute = '#1a73e8';

            routingControl = L.Routing.control({
                waypoints: waypointsData.map(p => L.latLng(p.lat, p.lng)),
                createMarker: function() { return null; },
                show: false, 
                lineOptions: { 
                    addWaypoints: false, 
                    styles: [{ color: 'transparent', opacity: 0, weight: 0 }] 
                }
            }).addTo(map);

            routingControl.on('routesfound', function(e) {
                const route = e.routes[0];
                const coords = route.coordinates;
                
                if (route.waypointIndices) {
                    for (let i = 0; i < route.waypointIndices.length - 1; i++) {
                        let startIndex = route.waypointIndices[i];
                        let endIndex = route.waypointIndices[i+1];
                        let segmentCoords = coords.slice(startIndex, endIndex + 1);

                        let polyline = L.polyline(segmentCoords, { color: warnaRute, weight: 6, opacity: 0.9 }).addTo(map);
                        
                        let titikTujuan = waypointsData[i + 1];
                        
                        let marker = L.marker([titikTujuan.lat, titikTujuan.lng], {
                            icon: L.divIcon({
                                className: 'custom-route-marker',
                                html: `<div style="background-color: ${warnaRute}; color: white; width: 26px; height: 26px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.4);">${i + 1}</div>`,
                                iconSize: [26, 26],
                                iconAnchor: [13, 42] 
                            }),
                            zIndexOffset: 1000 - i 
                        }).addTo(map);
                        
                        marker.bindTooltip(`Urutan ${i + 1}: ${titikTujuan.nama}`, { direction: 'top', offset: [0, -10] });

                        ruteElements.push(polyline, marker);
                    }
                }
            });
            const bounds = L.latLngBounds(waypointsData.map(p => [p.lat, p.lng]));
            map.fitBounds(bounds.pad(0.3));

            if(window.innerWidth <= 768) {
                document.getElementById('map').scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>
    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeSidebarBtn = document.getElementById('close-sidebar-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active-mobile');
            overlay.classList.toggle('active');
        }

        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleSidebar);
        if(closeSidebarBtn) closeSidebarBtn.addEventListener('click', toggleSidebar);
        if(overlay) overlay.addEventListener('click', toggleSidebar);
    </script>
</body>
</html>