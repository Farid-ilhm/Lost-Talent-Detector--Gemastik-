<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Talent Detector - GEMASTIK</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
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
                <a href="/dashboard" class="app-brand-icon" title="Dashboard Lost Talent Detector" style="background: transparent; overflow: hidden; padding: 0;">
                    <img src="{{ asset('icon.png') }}" alt="Lost Talent Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 14px;">
                </a>

                
                <nav class="nav-menu">
                    <a href="/dashboard" class="nav-item active" title="Dashboard Utama">
                        <i class="fa-solid fa-border-all"></i>
                    </a>
                </nav>
            </div>

            <div class="sidebar-bottom">
                <form action="/logout" method="POST" style="margin: 0;" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
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
            <div class="header-top-bar" style="margin-bottom: 16px; display: flex; justify-content: flex-end;">
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



         </aside>
        @endauth
    </div>
    <script>
        function togglePasswordVisibility(inputId, buttonEl) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = buttonEl.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }
    </script>
</body>
</html>

