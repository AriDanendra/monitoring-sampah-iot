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
        @include('partials.sidebar')

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Statistik Utama</h1>
                    <p>Pantau status perangkat secara real-time.</p>
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
                    <div class="stat-card pink">
                        <div class="stat-content">
                            <span class="stat-label">Total Lokasi Terpantau</span>
                            <h2 class="stat-value">{{ $totalLokasi }}</h2>
                        </div>
                        <div class="stat-icon-wrapper"><i class="fa-solid fa-location-dot"></i></div>
                    </div>

                    <div class="stat-card orange">
                        <div class="stat-content">
                            <span class="stat-label">Titik Penuh (Siap Angkut)</span>
                            <h2 class="stat-value">{{ $titikPenuh }}</h2>
                        </div>
                        <div class="stat-icon-wrapper"><i class="fa-solid fa-trash-can"></i></div>
                    </div>

                    <div class="stat-card green">
                        <div class="stat-content">
                            <span class="stat-label">Perangkat Aktif</span>
                            <h2 class="stat-value">{{ $perangkatAktif }}</h2>
                        </div>
                        <div class="stat-icon-wrapper"><i class="fa-solid fa-microchip"></i></div>
                    </div>
                </div>

                <div class="data-section">
                    <div class="section-header">
                        <h3>Status Detail Perangkat</h3>
                        <button class="btn-refresh" onclick="location.reload();">
                            <i class="fa-solid fa-rotate"></i> Refresh Data
                        </button>
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
                                @foreach($devices as $item)
                                <tr>
                                    <td>{{ $item['id'] }}</td>
                                    <td><strong>{{ $item['lokasi'] }}</strong></td>
                                    <td>
                                        <span style="font-weight: 600;">{{ $item['persen'] }}%</span>
                                    </td>
                                    <td><span class="status-badge {{ $item['status'] }}">{{ ucfirst($item['status']) }}</span></td>
                                    <td>{{ $item['update'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>