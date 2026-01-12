<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Waste IoT</title>
    
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
                <span>SmartTrash <small>Parepare</small></span>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="#"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fa-solid fa-map-location-dot"></i> Status Lokasi</a></li>
                    <li><a href="#"><i class="fa-solid fa-clock-rotate-left"></i> Histori</a></li>
                    <li class="nav-divider"></li>
                    <li><a href="#" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Ringkasan Sistem</h1>
                    <p>Selamat datang kembali, Pengelola Kebersihan.</p>
                </div>
                <div class="header-right">
                    <div class="notification-bell">
                        <i class="fa-regular fa-bell"></i>
                        <span class="dot"></span>
                    </div>
                    <div class="profile-chip">
                        <img src="{{ asset('user-icon.png') }}" alt="User">
                        <div class="profile-info">
                            <span class="name">Ari Danendra</span>
                            <span class="role">Admin</span>
                        </div>
                    </div>
                </div>
            </header>

            <div class="dashboard-body">
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-content">
                            <span class="stat-label">Total Lokasi</span>
                            <h2 class="stat-value">4 <small>Titik</small></h2>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fa-solid fa-location-arrow"></i>
                        </div>
                    </div>

                    <div class="stat-card red">
                        <div class="stat-content">
                            <span class="stat-label">Titik Penuh</span>
                            <h2 class="stat-value">1 <small>Lokasi</small></h2>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fa-solid fa-dumpster"></i>
                        </div>
                    </div>

                    <div class="stat-card yellow">
                        <div class="stat-content">
                            <span class="stat-label">ESP32 Aktif</span>
                            <h2 class="stat-value">2 <small>/ 4 Perangkat</small></h2>
                        </div>
                        <div class="stat-icon-wrapper">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                    </div>
                </div>

                <div class="data-section">
                    <div class="section-header">
                        <h3>Status Real-Time Sensor</h3>
                        <button class="btn-refresh"><i class="fa-solid fa-rotate"></i> Refresh</button>
                    </div>
                    <div class="table-card">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Titik Lokasi</th>
                                    <th>Kapasitas Sampah</th>
                                    <th>Sinyal Perangkat</th>
                                    <th>Update Terakhir</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>01</td>
                                    <td><strong>Lokasi A</strong> (Jl. Bau Massepe)</td>
                                    <td><div class="progress-container"><div class="progress-bar red" style="width: 95%"></div><span>95%</span></div></td>
                                    <td><span class="status-badge online">Online</span></td>
                                    <td>10 Detik Lalu</td>
                                </tr>
                                <tr>
                                    <td>02</td>
                                    <td><strong>Lokasi B</strong> (Soreang)</td>
                                    <td><div class="progress-container"><div class="progress-bar orange" style="width: 50%"></div><span>50%</span></div></td>
                                    <td><span class="status-badge online">Online</span></td>
                                    <td>2 Menit Lalu</td>
                                </tr>
                                <tr>
                                    <td>03</td>
                                    <td><strong>Lokasi C</strong> (Ujung)</td>
                                    <td><div class="progress-container"><div class="progress-bar grey" style="width: 5%"></div><span>5%</span></div></td>
                                    <td><span class="status-badge offline">Offline</span></td>
                                    <td>2 Jam Lalu</td>
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