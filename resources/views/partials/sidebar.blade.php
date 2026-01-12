<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-box">
            <i class="fa-solid fa-leaf"></i>
        </div>
        <div class="header-text-group">
            <span class="brand-name">Monitoring Sampah</span>
            <span class="brand-sub">Parepare <small class="iot-tag">IoT System</small></span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            </li>
            <li class="{{ request()->is('monitoring') ? 'active' : '' }}">
                <a href="{{ route('monitoring') }}">
                    <i class="fa-solid fa-map-location-dot"></i> Monitoring
                </a>
            </li>
            <li class="{{ request()->is('riwayat') ? 'active' : '' }}">
                <a href="{{ route('riwayat') }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
                </a>
            </li>
            <li class="{{ request()->is('pengaturan') ? 'active' : '' }}">
                <a href="{{ route('pengaturan') }}">
                    <i class="fa-solid fa-gear"></i> Pengaturan
                </a>
            </li>
            <li style="margin-top: 20px;">
                <a href="{{ route('logout') }}" style="color: #ef4444;" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </a>
            </li>
        </ul>
    </nav>
</aside>