<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengangkutan - Smart Waste System</title>
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
                    <h1>Riwayat Pengangkutan</h1>
                    <p>Daftar log aktivitas pengangkutan sampah yang telah selesai.</p>
                </div>
            </header>

            <div class="dashboard-body">
                <div class="data-section">
                    <div class="section-header">
                        <h3>Log Aktivitas Terakhir</h3>
                        <div class="header-actions">
                             <button class="btn-refresh" onclick="location.reload();">
                                <i class="fa-solid fa-rotate"></i> Refresh Log
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID Perangkat</th>
                                    <th>Lokasi</th>
                                    <th>Kapasitas Saat Diangkut</th>
                                    <th>Kadar Bau (MQ-135)</th>
                                    <th>Waktu Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $index => $log)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $log->device_id }}</td>
                                    <td><strong>{{ $log->lokasi }}</strong></td>
                                    <td><span class="status-badge online">{{ $log->kapasitas_terakhir }}%</span></td>
                                    <td>{{ $log->kadar_bau_terakhir }} PPM</td>
                                    <td>{{ \Carbon\Carbon::parse($log->waktu_pengangkutan)->translatedFormat('d F Y, H:i') }} WITA</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        <i class="fa-solid fa-box-open" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                        Belum ada data riwayat pengangkutan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>