<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monitoring Sampah Parepare</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="brand">Sistem Monitoring Tempat Sampah Parepare</div>
            <nav>
                <ul>
                    <li class="active"><a href="#">Dashboard</a></li>
                    <li><a href="#">Status Lokasi</a></li>
                    <li><a href="#">Histori</a></li>
                    <li><a href="#">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <header class="top-bar">
                <div class="user-profile">
                    <img src="user-icon.png" alt="User">
                    <span>User</span>
                </div>
            </header>

            <section class="dashboard-section">
                <h2>Dashboard</h2>
                <hr>

                <div class="cards-container">
                    <div class="card blue">
                        <div class="card-info">
                            <h3>Total lokasi Terpantau</h3>
                            <p class="count">4 Lokasi</p>
                        </div>
                        <div class="card-footer">More Info &rarr;</div>
                    </div>

                    <div class="card red">
                        <div class="card-info">
                            <h3>Titik Penuh (Siap angkut)</h3>
                            <p class="count">1 Lokasi</p>
                        </div>
                        <div class="card-footer">More Info &rarr;</div>
                    </div>

                    <div class="card yellow">
                        <div class="card-info">
                            <h3>Perangkat Aktif</h3>
                            <p class="count">2/4 Aktif</p>
                        </div>
                        <div class="card-footer">More Info &rarr;</div>
                    </div>
                </div>

                <div class="table-container">
                    <h3>Status Lokasi Real Time</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Lokasi</th>
                                <th>Keterisian (Volume)</th>
                                <th>Status</th>
                                <th>Sinkronisasi Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Lokasi A</td>
                                <td>95% (Penuh)</td>
                                <td><span class="badge active">Aktif</span></td>
                                <td>Baru Saja</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Lokasi B</td>
                                <td>50% (Aman)</td>
                                <td><span class="badge active">Aktif</span></td>
                                <td>2 menit yang lalu</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>