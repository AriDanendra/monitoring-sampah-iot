<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Waste System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <style>
        .bau-normal { color: #10b981; font-weight: 600; } 
        .bau-bahaya { color: #ef4444; font-weight: 700; } 
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
                        <h1>Statistik Utama</h1>
                        <p>Pantau status perangkat secara real-time.</p>
                    </div>
                </div>
            </header>

            <div class="dashboard-body">
                <div class="stats-grid">
                    <div class="stat-card pink">
                        <div class="stat-content">
                            <span class="stat-label">Total Lokasi Terpantau</span>
                            <h2 class="stat-value" id="stat-total">{{ $totalLokasi ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon-wrapper"><i class="fa-solid fa-location-dot"></i></div>
                    </div>

                    <div class="stat-card orange">
                        <div class="stat-content">
                            <span class="stat-label">Titik Perlu Angkut</span>
                            <h2 class="stat-value" id="stat-penuh">{{ $titikPenuh ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon-wrapper"><i class="fa-solid fa-trash-can"></i></div>
                    </div>

                    <div class="stat-card green">
                        <div class="stat-content">
                            <span class="stat-label">Perangkat Aktif</span>
                            <h2 class="stat-value" id="stat-aktif">{{ $perangkatAktif ?? 0 }}</h2>
                        </div>
                        <div class="stat-icon-wrapper"><i class="fa-solid fa-microchip"></i></div>
                    </div>
                </div>

                <div class="data-section">
                    <div class="section-header">
                        <h3>Status Detail Perangkat</h3>
                        <div style="font-size: 12px; color: #10b981;">
                            <i class="fa-solid fa-circle-check"></i> Auto-Sync Aktif
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Lokasi</th>
                                    <th>Kapasitas</th>
                                    <th>Tingkat Bau</th>
                                    <th>Status Perangkat</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                @if(isset($devices))
                                    @foreach($devices as $item)
                                    <tr>
                                        <td>{{ $item['id'] }}</td>
                                        <td><strong>{{ $item['lokasi'] }}</strong></td>
                                        <td><span style="font-weight: 600;">{{ $item['persen'] }}%</span></td>
                                        <td>
                                            @if($item['bau'] >= 800)
                                                <span class="bau-bahaya"><i class="fa-solid fa-circle-exclamation"></i> Bau Nyengat ({{ $item['bau'] }} PPM)</span>
                                            @else
                                                <span class="bau-normal"><i class="fa-solid fa-circle-check"></i> Aman ({{ $item['bau'] }} PPM)</span>
                                            @endif
                                        </td>
                                        <td><span class="status-badge {{ $item['status'] }}">{{ ucfirst($item['status']) }}</span></td>
                                        <td>{{ $item['update'] }}</td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeSidebarBtn = document.getElementById('close-sidebar-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active-mobile');
            overlay.classList.toggle('active');
        }

        mobileMenuBtn.addEventListener('click', toggleSidebar);
        closeSidebarBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Realtime Fetch Script yang sudah ada sebelumnya
        setInterval(async () => {
            try {
                const response = await fetch('/api/realtime-data');
                const data = await response.json();

                document.getElementById('stat-total').innerText = data.totalLokasi;
                document.getElementById('stat-penuh').innerText = data.titikPenuh;
                document.getElementById('stat-aktif').innerText = data.perangkatAktif;

                let tbody = '';
                data.devices.forEach(item => {
                    let bauHtml = item.bau >= 800
                        ? `<span class="bau-bahaya"><i class="fa-solid fa-circle-exclamation"></i> Bau Nyengat (${item.bau} PPM)</span>`
                        : `<span class="bau-normal"><i class="fa-solid fa-circle-check"></i> Aman (${item.bau} PPM)</span>`;
                    
                    let statusCap = item.status.charAt(0).toUpperCase() + item.status.slice(1);

                    tbody += `
                    <tr>
                        <td>${item.id}</td>
                        <td><strong>${item.lokasi}</strong></td>
                        <td><span style="font-weight: 600;">${item.persen}%</span></td>
                        <td>${bauHtml}</td>
                        <td><span class="status-badge ${item.status}">${statusCap}</span></td>
                        <td>${item.update}</td>
                    </tr>`;
                });
                
                document.getElementById('table-body').innerHTML = tbody;
            } catch (error) {
                console.error("Gagal update data real-time:", error);
            }
        }, 5000);
    </script>
</body>
</html>