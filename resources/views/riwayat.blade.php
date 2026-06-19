<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengangkutan - Smart Waste System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <h1>Riwayat Pengangkutan</h1>
                        <p>Daftar log aktivitas pengangkutan sampah yang tercatat otomatis.</p>
                    </div>
                </div>
            </header>

            <div class="dashboard-body">
                @if(session('success'))
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: "{{ session('success') }}",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    </script>
                @endif

                <div class="data-section">
                    <div class="section-header">
                        <h3>Log Aktivitas Terakhir</h3>
                        <div class="header-actions" style="display: flex; gap: 10px;">
                            @if($logs->count() > 0 && !request('search') && !request('tanggal'))
                                <form action="{{ route('hapus-semua-riwayat') }}" method="POST" id="formHapusSemua">
                                    @csrf
                                    <button type="button" class="btn-refresh" style="background: #fee2e2; color: #ef4444; border: 1px solid #fecaca;" onclick="konfirmasiHapusSemua()">
                                        <i class="fa-solid fa-trash-can"></i> Kosongkan Riwayat
                                    </button>
                                </form>
                            @endif

                            <button class="btn-refresh" onclick="window.location.href='{{ url()->current() }}'">
                                <i class="fa-solid fa-rotate"></i> Refresh Log
                            </button>
                        </div>
                    </div>

                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0;">
                        <form action="{{ url()->current() }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                            
                            <div style="flex: 1; min-width: 200px;">
                                <label for="search" style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 8px;">Cari Lokasi / ID Perangkat</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik lokasi atau ID..." 
                                       style="width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; font-family: inherit; box-sizing: border-box;">
                            </div>

                            <div style="min-width: 150px;">
                                <label for="tanggal" style="display: block; font-size: 14px; font-weight: 500; color: #475569; margin-bottom: 8px;">Tanggal Pengangkutan</label>
                                <input type="date" name="tanggal" id="tanggal" value="{{ request('tanggal') }}" 
                                       style="width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; font-family: inherit; box-sizing: border-box;">
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <button type="submit" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 500; font-family: inherit; transition: background 0.2s;">
                                    <i class="fa-solid fa-filter" style="margin-right: 5px;"></i> Terapkan
                                </button>
                                @if(request('search') || request('tanggal'))
                                    <a href="{{ url()->current() }}" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 500; font-family: inherit; transition: background 0.2s; display: inline-flex; align-items: center;">
                                        <i class="fa-solid fa-xmark" style="margin-right: 5px;"></i> Reset Filter
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID Perangkat</th>
                                    <th>Lokasi</th>
                                    <th>Kapasitas Terakhir</th>
                                    <th>Kadar Bau</th>
                                    <th>Waktu Selesai</th>
                                    <th style="text-align: center;">Aksi</th>
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
                                    <td style="text-align: center;">
                                        <form action="{{ route('hapus-riwayat', $log->id) }}" method="POST" style="display:inline;" id="delete-form-{{ $log->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 16px;" 
                                                    onclick="konfirmasiHapus('{{ $log->id }}')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                        <i class="fa-solid fa-box-open" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                                        @if(request('search') || request('tanggal'))
                                            Data riwayat dengan filter tersebut tidak ditemukan.
                                        @else
                                            Belum ada data riwayat pengangkutan.
                                        @endif
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

    <script>
        // Konfirmasi Hapus Satuan
        function konfirmasiHapus(id) {
            Swal.fire({
                title: 'Hapus Riwayat?',
                text: "Data ini akan dihapus secara permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }

        // Konfirmasi Hapus Semua
        function konfirmasiHapusSemua() {
            Swal.fire({
                title: 'Kosongkan Semua Riwayat?',
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'danger',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Kosongkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formHapusSemua').submit();
                }
            })
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