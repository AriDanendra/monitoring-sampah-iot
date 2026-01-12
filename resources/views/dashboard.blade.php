<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Waste System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo-box">
                    <i class="fa-solid fa-leaf"></i>
                </div>
                <span>SmartWaste <small style="font-weight: 400; opacity: 0.7;">IoT</small></span>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Monitoring</a></li>
                    <li><a href="#"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat</a></li>
                    <li><a href="#"><i class="fa-solid fa-gear"></i> Pengaturan</a></li>
                    <li style="margin-top: 20px;"><a href="#" style="color: #ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Statistik Utama</h1>
                    <p>Pantau volume sampah dan kualitas udara secara real-time.</p>
                </div>
                <div class="header-right">
                    <div class="profile-chip">
                        <img src="{{ asset('user-icon.png') }}" alt="Admin">
                        <div class="profile-info">
                            <span class="name">Ari Danendra</span>
                            <span class="role">Administrator</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="dashboard-body">
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-content">
                            <span class="stat-label">Volume Sampah</span>
                            <h2 class="stat-value">85%</h2>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fa-solid fa-trash-can"></i>
                        </div>
                    </div>

                    <div class="stat-card orange">
                        <div class="stat-content">
                            <span class="stat-label">Kadar Gas/Bau</span>
                            <h2 class="stat-value">Normal</h2>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fa-solid fa-wind"></i>
                        </div>
                    </div>

                    <div class="stat-card pink">
                        <div class="stat-content">
                            <span class="stat-label">Total Lokasi</span>
                            <h2 class="stat-value">12</h2>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                    </div>

                    <div class="stat-card green">
                        <div class="stat-content">
                            <span class="stat-label">Perangkat Aktif</span>
                            <h2 class="stat-value">10</h2>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                    </div>
                </div>

                <div class="data-section">
                    <div class="section-header">
                        <h3>Status Detail Perangkat</h3>
                        <button class="btn-refresh"><i class="fa-solid fa-rotate"></i> Refresh Data</button>
                    </div>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Lokasi</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#TR-01</td>
                                    <td><strong>Jl. Bau Massepe</strong></td>
                                    <td>
                                        <div class="progress-container">
                                            <div class="progress-track"><div class="progress-fill red" style="width: 95%"></div></div>
                                            <span>95%</span>
                                        </div>
                                    </td>
                                    <td><span class="status-badge online">Online</span></td>
                                    <td>Sekarang</td>
                                </tr>
                                <tr>
                                    <td>#TR-02</td>
                                    <td><strong>Soreang</strong></td>
                                    <td>
                                        <div class="progress-container">
                                            <div class="progress-track"><div class="progress-fill orange" style="width: 50%"></div></div>
                                            <span>50%</span>
                                        </div>
                                    </td>
                                    <td><span class="status-badge online">Online</span></td>
                                    <td>2 Menit lalu</td>
                                </tr>
                                <tr>
                                    <td>#TR-03</td>
                                    <td><strong>Ujung</strong></td>
                                    <td>
                                        <div class="progress-container">
                                            <div class="progress-track"><div class="progress-fill emerald" style="width: 10%"></div></div>
                                            <span>10%</span>
                                        </div>
                                    </td>
                                    <td><span class="status-badge offline">Offline</span></td>
                                    <td>1 Jam lalu</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>