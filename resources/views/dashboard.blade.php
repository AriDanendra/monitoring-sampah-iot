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
        /* Indikator Bau & Kapasitas (Sesuai Threshold Skripsi) */
        .status-aman { color: #10b981; font-weight: 600; } 
        .status-bahaya { color: #ef4444; font-weight: 700; } 
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
                                        
                                        <!-- Logika Blade untuk Kapasitas (Ambang Batas 80%) -->
                                        <td>
                                            @if($item['persen'] >= 80)
                                                <span class="status-bahaya"><i class="fa-solid fa-triangle-exclamation"></i> {{ $item['persen'] }}% (Penuh)</span>
                                            @else
                                                <span class="status-aman"><i class="fa-solid fa-check"></i> {{ $item['persen'] }}% (Aman)</span>
                                            @endif
                                        </td>
                                        
                                        <!-- Logika Blade untuk Tingkat Bau (Ambang Batas 800 PPM) -->
                                        <td>
                                            @if($item['bau'] >= 800)
                                                <span class="status-bahaya"><i class="fa-solid fa-biohazard"></i> Bau Menyengat ({{ $item['bau'] }} PPM)</span>
                                            @else
                                                <span class="status-aman"><i class="fa-solid fa-leaf"></i> Aman ({{ $item['bau'] }} PPM)</span>
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
        if(closeSidebarBtn) closeSidebarBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Realtime Fetch Script
        setInterval(async () => {
            try {
                const response = await fetch('/api/realtime-data');
                const data = await response.json();

                document.getElementById('stat-total').innerText = data.totalLokasi;
                document.getElementById('stat-penuh').innerText = data.titikPenuh;
                document.getElementById('stat-aktif').innerText = data.perangkatAktif;

                let tbody = '';
                data.devices.forEach(item => {
                    
                    // Logika JS untuk Indikator Bau (Ambang Batas 800 PPM)
                    let bauHtml = item.bau >= 800
                        ? `<span class="status-bahaya"><i class="fa-solid fa-biohazard"></i> Bau Menyengat (${item.bau} PPM)</span>`
                        : `<span class="status-aman"><i class="fa-solid fa-leaf"></i> Aman (${item.bau} PPM)</span>`;

                    // Logika JS untuk Indikator Kapasitas (Ambang Batas 80%)
                    let kapasitasHtml = item.persen >= 80
                        ? `<span class="status-bahaya"><i class="fa-solid fa-triangle-exclamation"></i> ${item.persen}% (Penuh)</span>`
                        : `<span class="status-aman"><i class="fa-solid fa-check"></i> ${item.persen}% (Aman)</span>`;
                    
                    let statusCap = item.status.charAt(0).toUpperCase() + item.status.slice(1);

                    tbody += `
                    <tr>
                        <td>${item.id}</td>
                        <td><strong>${item.lokasi}</strong></td>
                        <td>${kapasitasHtml}</td>
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