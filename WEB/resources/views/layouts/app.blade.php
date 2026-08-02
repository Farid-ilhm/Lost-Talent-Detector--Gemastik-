<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Talent Detector - GEMASTIK</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/app_custom.css') }}">
</head>
<body>
    <div class="app-wrapper">
        <!-- 1. LEFT SIDEBAR NAVIGATION (Cleaned up, only active dashboard & logout) -->
        @auth
        <aside class="left-sidebar">
            <div class="sidebar-top">
                <a href="/dashboard" class="app-brand-icon" title="Dashboard Lost Talent Detector">
                    <i class="fa-solid fa-brain"></i>
                </a>
                
                <nav class="nav-menu">
                    <a href="/dashboard" class="nav-item active" title="Dashboard Utama">
                        <i class="fa-solid fa-border-all"></i>
                    </a>
                </nav>
            </div>

            <div class="sidebar-bottom">
                <form action="/logout" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="nav-item" title="Logout (Keluar Sistem)" style="color: #DC2626; cursor: pointer; border: none; background: transparent;">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
                <div class="app-brand-icon" style="width: 40px; height: 40px; font-size: 0.9rem; background-color: var(--bg-pill-active);" title="{{ Auth::user()->name }}">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
            </div>
        </aside>
        @endauth

        <!-- 2. MAIN CONTENT AREA -->
        <main class="main-layout" style="{{ !Auth::check() ? 'max-width: 800px; margin: 0 auto; width: 100%;' : '' }}">
            <!-- Domain URL Header Bar -->
            <div class="header-top-bar" style="margin-bottom: 16px;">
                <div class="url-bar-mock">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>lost-talent-detector.gemastik.id</span>
                </div>
                <div>
                    @auth
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">
                            Role: <strong style="color: var(--text-dark);">{{ ucfirst(Auth::user()->role) }}</strong>
                        </span>
                    @else
                        <a href="/" style="font-size: 0.88rem; font-weight: 700; color: var(--text-dark); text-decoration: none;">
                            <i class="fa-solid fa-arrow-left"></i> Kembali ke Landing Page
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="alert-custom alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-custom alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-custom alert-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <ul style="margin-left: 10px; list-style-type: none;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- 3. RIGHT DASHBOARD WIDGET PANEL (Show only when logged in) -->
        @auth
        <aside class="right-panel">
            <div class="right-panel-header">
                <button class="icon-btn-circle" title="Notifikasi System">
                    <i class="fa-regular fa-bell"></i>
                </button>
                <button class="icon-btn-circle" title="Pengaturan">
                    <i class="fa-solid fa-gear"></i>
                </button>
            </div>

            <!-- User Profile Widget -->
            <div class="profile-card-widget">
                <div class="app-brand-icon" style="width: 72px; height: 72px; font-size: 1.8rem; border-radius: 50%; background: linear-gradient(135deg, #1C1917, #44403C);">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <h3 class="profile-name">{{ Auth::user()->name }}</h3>
                <div class="friends-pill">
                    <i class="fa-solid fa-award" style="font-size: 0.8rem;"></i>
                    <span>{{ ucfirst(Auth::user()->role) }}</span>
                </div>
            </div>

            <!-- Activity Chart Card -->
            <div class="activity-widget-card">
                <div class="activity-header">
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Aktivitas Analisis</div>
                        <div style="font-size: 1.3rem; font-weight: 800; color: var(--text-dark);">Skor Bakat</div>
                    </div>
                    <div class="activity-val-tag">
                        ✨ Terverifikasi AI
                    </div>
                </div>

                <div class="activity-chart-bars">
                    <div class="bar-col">
                        <div class="bar-segmented" style="height: 55%;">
                            <div class="bar-seg-pink" style="height: 40%;"></div>
                            <div class="bar-seg-sand" style="height: 60%;"></div>
                        </div>
                        <span class="bar-label">Rapor</span>
                    </div>
                    <div class="bar-col">
                        <div class="bar-segmented" style="height: 70%;">
                            <div class="bar-seg-lavender" style="height: 50%;"></div>
                            <div class="bar-seg-mint" style="height: 50%;"></div>
                        </div>
                        <span class="bar-label">Prestasi</span>
                    </div>
                    <div class="bar-col">
                        <div class="bar-segmented" style="height: 85%;">
                            <div class="bar-seg-mint" style="height: 70%;"></div>
                            <div class="bar-seg-sand" style="height: 30%;"></div>
                        </div>
                        <span class="bar-label">RIASEC</span>
                    </div>
                    <div class="bar-col active">
                        <div class="bar-segmented" style="height: 95%;">
                            <div class="bar-seg-sand" style="height: 30%;"></div>
                            <div class="bar-seg-mint" style="height: 40%;"></div>
                            <div class="bar-seg-lavender" style="height: 30%;"></div>
                        </div>
                        <span class="bar-label">AI</span>
                    </div>
                </div>
            </div>

            <!-- Mini Courses / Recommendations Widget -->
            <div class="mini-courses-list">
                <div class="section-title" style="font-size: 0.95rem;">Focus Area & Lomba</div>

                <div class="mini-course-card card-pink">
                    <div class="card-header-row" style="margin-bottom: 8px;">
                        <span class="card-cat-badge"><i class="fa-solid fa-laptop"></i> Teknologi & IT</span>
                        <span class="card-rating-badge"><i class="fa-solid fa-star" style="color: #F59E0B;"></i> 4.9</span>
                    </div>
                    <div style="font-weight: 700; font-size: 0.95rem;">Pengembangan Perangkat Lunak & AI</div>
                    <div class="card-footer-row" style="margin-top: 8px;">
                        <span class="card-meta-text">GEMASTIK Divisi IT</span>
                    </div>
                </div>

                <div class="mini-course-card card-sand">
                    <div class="card-header-row" style="margin-bottom: 8px;">
                        <span class="card-cat-badge"><i class="fa-solid fa-shield-halved"></i> Cyber Security</span>
                        <span class="card-rating-badge"><i class="fa-solid fa-star" style="color: #F59E0B;"></i> 5.0</span>
                    </div>
                    <div style="font-weight: 700; font-size: 0.95rem;">Keamanan Informasi & Data Science</div>
                </div>
            </div>
        </aside>
        @endauth
    </div>
</body>
</html>
