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

        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Riwayat Pengangkutan</h1>
                    <p>Daftar log aktivitas pengangkutan sampah yang tercatat otomatis.</p>
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
                            @if($logs->count() > 0)
                                <form action="{{ route('hapus-semua-riwayat') }}" method="POST" id="formHapusSemua">
                                    @csrf
                                    <button type="button" class="btn-refresh" style="background: #fee2e2; color: #ef4444; border: 1px solid #fecaca;" onclick="konfirmasiHapusSemua()">
                                        <i class="fa-solid fa-trash-can"></i> Kosongkan Riwayat
                                    </button>
                                </form>
                            @endif

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
</body>
</html>