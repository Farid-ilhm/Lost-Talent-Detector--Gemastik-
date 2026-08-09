<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Talent Detector</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('LOGO APK.jpg') }}">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/app_custom.css') }}">
    
    <!-- Early global confirm override to catch inline handlers -->
    <script>
        (function() {
            let pendingConfirm = null;

            window.confirm = function(message) {
                let form = null;
                if (document.activeElement) {
                    if (document.activeElement.form) {
                        form = document.activeElement.form;
                    } else {
                        form = document.activeElement.closest('form');
                    }
                }
                
                // If modal script is already loaded, show modal immediately
                if (window.showCustomConfirmFromGlobal) {
                    window.showCustomConfirmFromGlobal(message, form);
                } else {
                    // Save to run when DOM and modal script are loaded
                    pendingConfirm = { message, form };
                }
                
                return false; // Cancel native browser prompt
            };

            // Poll to check if pending confirm can be displayed
            document.addEventListener('DOMContentLoaded', function() {
                if (pendingConfirm && window.showCustomConfirmFromGlobal) {
                    window.showCustomConfirmFromGlobal(pendingConfirm.message, pendingConfirm.form);
                    pendingConfirm = null;
                }
            });
        })();
    </script>
</head>
<body>
    <div class="app-wrapper">
        <!-- 1. LEFT SIDEBAR NAVIGATION (Cleaned up, only active dashboard & logout) -->
        @auth
        <!-- MOBILE TOP BAR FOR LOGGED IN USERS -->
        <header class="mobile-dash-header">
            <a href="/dashboard" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                <img src="{{ asset('LOGO APK.jpg') }}" alt="Logo" style="width: 34px; height: 34px; border-radius: 10px; object-fit: cover;">
                <span style="font-weight: 800; font-size: 1rem; color: var(--text-dark);">Lost Talent</span>
            </a>
            <button type="button" class="mobile-menu-toggle" id="dashMobileMenuBtn" aria-label="Menu App Mobile">
                <i class="fa-solid fa-bars"></i>
            </button>
        </header>

        <div class="mobile-dash-drawer" id="dashMobileDrawer">
            <div class="mobile-drawer-inner">
                <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid var(--border-subtle);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="app-brand-icon" style="width: 36px; height: 36px; font-size: 0.85rem; background-color: var(--bg-pill-active); color: #FFF;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-dark);">{{ Auth::user()->name }}</div>
                            <span style="font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">{{ Auth::user()->role }}</span>
                        </div>
                    </div>
                </div>

                <nav class="mobile-dash-nav" style="display: flex; flex-direction: column; gap: 8px; margin-top: 14px;">
                    <a href="/dashboard" class="dash-mobile-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-border-all"></i> Dashboard Utama
                    </a>
                    @if(Auth::user()->role === 'admin')
                        <a href="/admin/institutions" class="dash-mobile-link {{ Request::is('admin/institutions*') ? 'active' : '' }}">
                            <i class="fa-solid fa-school-flag"></i> Kelola Institusi
                        </a>
                        <a href="/admin/competitions" class="dash-mobile-link {{ Request::is('admin/competitions*') ? 'active' : '' }}">
                            <i class="fa-solid fa-trophy"></i> Kelola Kompetisi
                        </a>
                        <a href="/admin/users" class="dash-mobile-link {{ Request::is('admin/users*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i> Kelola Akun Pengguna
                        </a>
                    @elseif(Auth::user()->role === 'institusi')
                        <a href="/institution/classrooms" class="dash-mobile-link {{ Request::is('institution/classrooms*') ? 'active' : '' }}">
                            <i class="fa-solid fa-door-open"></i> Kelola Kelas
                        </a>
                        <a href="/institution/teachers" class="dash-mobile-link {{ Request::is('institution/teachers*') ? 'active' : '' }}">
                            <i class="fa-solid fa-chalkboard-user"></i> Kelola Guru Pendamping
                        </a>
                        <a href="/institution/announcements" class="dash-mobile-link {{ Request::is('institution/announcements*') ? 'active' : '' }}">
                            <i class="fa-solid fa-bullhorn"></i> Pengumuman
                        </a>
                    @elseif(Auth::user()->role === 'guru')
                        <a href="/teacher/achievements" class="dash-mobile-link {{ Request::is('teacher/achievements*') ? 'active' : '' }}">
                            <i class="fa-solid fa-trophy"></i> Verifikasi Prestasi
                        </a>
                        <a href="/teacher/grades" class="dash-mobile-link {{ Request::is('teacher/grades*') ? 'active' : '' }}">
                            <i class="fa-solid fa-pen-to-square"></i> Input Rapor
                        </a>
                        <a href="/teacher/students" class="dash-mobile-link {{ Request::is('teacher/students*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users"></i> Kelola Akun Murid
                        </a>
                    @endif

                    <form action="/logout" method="POST" style="margin-top: 12px;" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
                        @csrf
                        <button type="submit" class="dash-mobile-link" style="width: 100%; color: #DC2626; background: rgba(220, 38, 38, 0.08); border: none; text-align: left; cursor: pointer;">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout (Keluar Sistem)
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <aside class="left-sidebar">
            <div class="sidebar-top">
                <a href="/dashboard" class="app-brand-icon" title="Dashboard Lost Talent Detector" style="background: transparent; overflow: hidden; padding: 0;">
                    <img src="{{ asset('LOGO APK.jpg') }}" alt="Lost Talent Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 14px;">
                </a>

                
                <nav class="nav-menu">
                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <a href="/dashboard" class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}" title="Dashboard Utama">
                            <i class="fa-solid fa-border-all"></i>
                        </a>
                        <a href="/admin/institutions" class="nav-item {{ Request::is('admin/institutions*') ? 'active' : '' }}" title="Kelola Institusi">
                            <i class="fa-solid fa-school-flag"></i>
                        </a>
                        <a href="/admin/competitions" class="nav-item {{ Request::is('admin/competitions*') ? 'active' : '' }}" title="Kelola Kompetisi">
                            <i class="fa-solid fa-trophy"></i>
                        </a>
                        <a href="/admin/users" class="nav-item {{ Request::is('admin/users*') ? 'active' : '' }}" title="Kelola Akun Pengguna">
                            <i class="fa-solid fa-users"></i>
                        </a>
                    @elseif(Auth::check() && Auth::user()->role === 'institusi')
                        <a href="/dashboard" class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}" title="Dashboard Utama">
                            <i class="fa-solid fa-border-all"></i>
                        </a>
                        <a href="/institution/classrooms" class="nav-item {{ Request::is('institution/classrooms*') ? 'active' : '' }}" title="Kelola Kelas">
                            <i class="fa-solid fa-door-open"></i>
                        </a>
                        <a href="/institution/teachers" class="nav-item {{ Request::is('institution/teachers*') ? 'active' : '' }}" title="Kelola Guru Pendamping">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </a>
                        <a href="/institution/announcements" class="nav-item {{ Request::is('institution/announcements*') ? 'active' : '' }}" title="Papan Informasi & Pengumuman Komunitas">
                            <i class="fa-solid fa-bullhorn"></i>
                        </a>
                    @elseif(Auth::check() && Auth::user()->role === 'guru')
                        <a href="/dashboard" class="nav-item {{ Request::is('dashboard') ? 'active' : '' }}" title="Dashboard Utama">
                            <i class="fa-solid fa-border-all"></i>
                        </a>
                        <a href="/teacher/achievements" class="nav-item {{ Request::is('teacher/achievements*') ? 'active' : '' }}" title="Verifikasi Prestasi Murid">
                            <i class="fa-solid fa-trophy"></i>
                        </a>
                        <a href="/teacher/grades" class="nav-item {{ Request::is('teacher/grades*') ? 'active' : '' }}" title="Input Nilai & Catatan Rapor">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <a href="/teacher/students" class="nav-item {{ Request::is('teacher/students*') ? 'active' : '' }}" title="Kelola Akun Murid">
                            <i class="fa-solid fa-users"></i>
                        </a>
                    @else
                        <a href="/dashboard" class="nav-item active" title="Dashboard Utama">
                            <i class="fa-solid fa-border-all"></i>
                        </a>
                    @endif
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
                <div style="position: relative; display: inline-block;">
                    <button class="icon-btn-circle" title="Notifikasi System" onclick="toggleNotificationDropdown(event)" style="position: relative;">
                        <i class="fa-regular fa-bell"></i>
                        <span id="notif-badge" style="display: none; position: absolute; top: -4px; right: -4px; background-color: #EF4444; color: #FFFFFF; font-size: 0.65rem; font-weight: 800; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border: 2px solid #FFFFFF;">0</span>
                    </button>
                    
                    <!-- Dropdown list -->
                    <div id="notif-dropdown" style="display: none; position: absolute; right: -55px; top: 48px; width: 250px; background-color: #FFFFFF; border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); z-index: 1000; padding: 16px; flex-direction: column; gap: 8px; text-align: left;">
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); padding-bottom: 8px; margin-bottom: 4px;">
                            <span style="font-weight: 800; font-size: 0.85rem; color: var(--text-dark);">Notifikasi Sistem</span>
                            <button onclick="markNotificationsRead()" style="background: none; border: none; color: #6366F1; font-size: 0.75rem; font-weight: 700; cursor: pointer; padding: 0;">Tandai Dibaca</button>
                        </div>
                        <div id="notif-list" style="max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;">
                            <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 12px;">Tidak ada notifikasi baru.</div>
                        </div>
                        <div style="border-top: 1px solid var(--border-subtle); padding-top: 8px; text-align: center; margin-top: 4px;">
                            <a href="{{ Auth::user()->role === 'admin' ? '/admin/institutions' : '/teacher/achievements' }}" style="color: #6366F1; font-size: 0.8rem; font-weight: 800; text-decoration: none; display: block;">Lihat Semua Notifikasi</a>
                        </div>
                    </div>
                </div>
                <button class="icon-btn-circle" title="Pengaturan" onclick="openSettingsModal()">
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
                @if(Auth::user()->role === 'guru')
                    @php
                        $teacher = Auth::user()->teacher;
                        $institutionName = $teacher && $teacher->institution && $teacher->institution->user ? $teacher->institution->user->name : null;
                    @endphp
                    @if($institutionName)
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="fa-solid fa-school" style="color: #6366F1;"></i>
                            <span>{{ $institutionName }}</span>
                        </div>
                    @endif
                @endif
            </div>

            @if(Auth::check() && Auth::user()->role === 'guru')
                @php
                    $teacher = Auth::user()->teacher;
                    $pendingAchievements = [];
                    $studentsList = [];
                    if ($teacher) {
                        $pendingAchievements = \App\Models\Achievement::whereHas('student', function ($q) use ($teacher) {
                            $q->where('institution_id', $teacher->institution_id);
                        })->where('is_verified', false)->with('student.user')->get();

                        $studentsList = \App\Models\Student::where('institution_id', $teacher->institution_id)
                            ->with(['user', 'classroom'])
                            ->get();
                    }
                @endphp
                <!-- Verifications Widget -->
                <div class="activity-widget-card" style="margin-top: 10px; background-color: #FFFFFF; border-radius: var(--radius-md); padding: 18px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 12px; box-shadow: var(--shadow-soft);">
                    <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span style="display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-clock-rotate-left" style="color: #D97706;"></i> Verifikasi Tertunda</span>
                        <span style="font-size: 0.76rem; background: #FEF3C7; color: #D97706; padding: 2px 8px; border-radius: 99px;">{{ count($pendingAchievements) }}</span>
                    </h4>
                    
                    <div style="display: flex; flex-direction: column; gap: 10px; max-height: 200px; overflow-y: auto; padding-right: 4px;">
                        @forelse($pendingAchievements as $ach)
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 6px 0; border-bottom: 1px dashed var(--border-subtle);">
                                <div style="min-width: 0; flex: 1; padding-right: 8px;">
                                    <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $ach->student->user->name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $ach->title }}</div>
                                </div>
                                <a href="/teacher/achievements" class="btn-primary-dark" style="height: 28px; padding: 0 10px; font-size: 0.72rem; border-radius: 6px; display: inline-flex; align-items: center; text-decoration: none; font-weight: 700; flex-shrink: 0; background-color: var(--accent-light); color: var(--text-dark);">
                                    Tinjau
                                </a>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 12px 0;">
                                <i class="fa-solid fa-circle-check" style="color: #10B981; font-size: 1.2rem; display: block; margin-bottom: 6px;"></i>
                                Semua sertifikat terverifikasi!
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Students Widget -->
                <div class="activity-widget-card" style="margin-top: 14px; background-color: #FFFFFF; border-radius: var(--radius-md); padding: 18px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 12px; box-shadow: var(--shadow-soft);">
                    <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span style="display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-user-graduate" style="color: #10B981;"></i> Siswa Bimbingan</span>
                        <span style="font-size: 0.76rem; background: var(--bg-pill-active); color: #047857; padding: 2px 8px; border-radius: 99px;">{{ count($studentsList) }}</span>
                    </h4>
                    
                    <div style="display: flex; flex-direction: column; gap: 10px; max-height: 200px; overflow-y: auto; padding-right: 4px;">
                        @forelse($studentsList as $s)
                            <div style="display: flex; align-items: center; gap: 10px; padding: 4px 0;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #1C1917; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; overflow: hidden; border: 1.5px solid var(--border-subtle);">
                                    @if($s->user && $s->user->avatar)
                                        <img src="{{ asset($s->user->avatar) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <span>{{ strtoupper(substr($s->user->name ?? 'S', 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $s->user->name }}</div>
                                    <div style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $s->classroom->name ?? 'Umum' }} &bull; {{ $s->nisn ?? $s->nim ?? '-' }}</div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 12px 0;">
                                Belum ada siswa terdaftar.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            @if(Auth::check() && Auth::user()->role === 'institusi')
                @php
                    $inst = \App\Models\Institution::where('user_id', Auth::user()->id)
                        ->with(['teachers.user'])
                        ->first();
                @endphp
                @if($inst)
                    <!-- Contacts Widget -->
                    <div class="activity-widget-card" style="margin-top: 10px; background-color: #FFFFFF; border-radius: var(--radius-md); padding: 18px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 12px; box-shadow: var(--shadow-soft);">
                        <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-circle-info" style="color: #4F46E5;"></i> Detail Informasi
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.82rem;">
                            <div style="display: flex; flex-direction: column; gap: 4px; padding-bottom: 8px; border-bottom: 1px dashed var(--border-subtle);">
                                <span style="color: var(--text-muted); font-weight: 700; font-size: 0.72rem; text-transform: uppercase;">No. Telepon / WhatsApp</span>
                                <strong style="color: var(--text-dark);">{{ Auth::user()->phone ?? '-' }}</strong>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <span style="color: var(--text-muted); font-weight: 700; font-size: 0.72rem; text-transform: uppercase;">Alamat Lengkap</span>
                                <span style="color: var(--text-dark); line-height: 1.4; font-weight: 500;">{{ $inst->address ?? 'Alamat belum diisi' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Teachers Widget -->
                    <div class="activity-widget-card" style="margin-top: 14px; background-color: #FFFFFF; border-radius: var(--radius-md); padding: 18px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 12px; box-shadow: var(--shadow-soft);">
                        <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; justify-content: space-between; align-items: center;">
                            <span style="display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-chalkboard-user" style="color: #6366F1;"></i> Guru Terdaftar</span>
                            <span style="font-size: 0.76rem; background: var(--bg-pill-active); color: #4338CA; padding: 2px 8px; border-radius: 99px;">{{ $inst->teachers->count() }}</span>
                        </h4>
                        
                        <div style="display: flex; flex-direction: column; gap: 10px; max-height: 220px; overflow-y: auto; padding-right: 4px;">
                            @forelse($inst->teachers as $t)
                                <div style="display: flex; align-items: center; gap: 10px; padding: 4px 0;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #1C1917; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem; overflow: hidden; border: 1.5px solid var(--border-subtle);">
                                        <img *ngIf="false" src=""> <!-- Placeholder fallback -->
                                        <span>{{ strtoupper(substr($t->user->name, 0, 1)) }}</span>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $t->user->name }}</div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $t->subject ?? 'Guru Pembina' }}</div>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 12px 0;">
                                    Belum ada guru terdaftar.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            @endif

            @if(Auth::check() && Auth::user()->role === 'admin')
                <!-- System Stats Widget -->
                <div class="activity-widget-card" style="margin-top: 10px; background-color: #FFFFFF; border-radius: var(--radius-md); padding: 18px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 12px; box-shadow: var(--shadow-soft);">
                    <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-chart-pie" style="color: #6366F1;"></i> Statistik Ringkas
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed var(--border-subtle);">
                            <span style="color: var(--text-muted); font-weight: 500;">Pengguna Aktif</span>
                            <strong style="color: var(--text-dark);">{{ \App\Models\User::where('role', '!=', 'admin')->count() }} Akun</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed var(--border-subtle);">
                            <span style="color: var(--text-muted); font-weight: 500;">Verifikasi Pending</span>
                            <strong style="color: {{ \App\Models\Institution::where('is_verified', false)->count() > 0 ? '#D97706' : 'var(--text-dark)' }};">
                                {{ \App\Models\Institution::where('is_verified', false)->count() }} Institusi
                            </strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-weight: 500;">Total Kompetisi</span>
                            <strong style="color: var(--text-dark);">{{ \App\Models\Competition::count() }} Lomba</strong>
                        </div>
                    </div>
                </div>

                <!-- Server Status Widget -->
                <div class="activity-widget-card" style="margin-top: 10px; background-color: #FFFFFF; border-radius: var(--radius-md); padding: 18px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; gap: 12px; box-shadow: var(--shadow-soft);">
                    <h4 style="font-size: 0.9rem; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-server" style="color: #10B981;"></i> Status Server
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.85rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed var(--border-subtle);">
                            <span style="color: var(--text-muted); font-weight: 500;">Status Sistem</span>
                            <span class="card-rating-badge" style="background-color: #D1F5E4; color: #065F46; font-size: 0.75rem; padding: 2px 8px; font-weight: 700; margin: 0; border-radius: 6px;">Online</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed var(--border-subtle);">
                            <span style="color: var(--text-muted); font-weight: 500;">PHP Version</span>
                            <strong style="color: var(--text-dark);">{{ PHP_VERSION }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-weight: 500;">Environment</span>
                            <strong style="color: var(--text-dark);">{{ app()->environment() }}</strong>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Tips/Notes -->
                <div class="activity-widget-card" style="margin-top: 10px; background-color: #FDFBF7; border-radius: var(--radius-md); padding: 18px; border: 1px solid #FDE68A; display: flex; flex-direction: column; gap: 8px; box-shadow: var(--shadow-soft);">
                    <h4 style="font-size: 0.9rem; font-weight: 800; color: #92400E; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-lightbulb" style="color: #F59E0B;"></i> Petunjuk Admin
                    </h4>
                    <p style="font-size: 0.8rem; color: #78716C; line-height: 1.6; margin: 0; text-align: justify;">
                        Verifikasi institusi pending agar mereka dapat mendaftarkan guru pembimbing dan siswa ke platform detektor bakat.
                    </p>
                </div>
            @endif

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
        function openSettingsModal() {
            const modal = document.getElementById('settings-modal');
            if (modal) modal.style.display = 'flex';
        }
        function closeSettingsModal() {
            const modal = document.getElementById('settings-modal');
            if (modal) modal.style.display = 'none';
        }
        function toggleNotificationDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notif-dropdown');
            if (!dropdown) return;
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'flex';
                fetchNotifications(); // Fetch immediately when opened
            } else {
                dropdown.style.display = 'none';
            }
        }

        function fetchNotifications() {
            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'guru']))
            fetch('/notifications')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notif-badge');
                    const list = document.getElementById('notif-list');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'flex';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                    if (list) {
                        if (data.notifications.length > 0) {
                            list.innerHTML = '';
                            const actionUrl = '{{ Auth::user()->role === 'admin' ? '/admin/institutions' : '/teacher/achievements' }}';
                            const iconClass = '{{ Auth::user()->role === 'admin' ? 'fa-solid fa-school-flag' : 'fa-solid fa-trophy' }}';
                            
                            data.notifications.forEach(notif => {
                                const div = document.createElement('div');
                                div.style.padding = '10px 12px';
                                div.style.borderRadius = '10px';
                                div.style.backgroundColor = '#F9FAF9';
                                div.style.border = '1px solid var(--border-subtle)';
                                div.style.fontSize = '0.8rem';
                                div.style.display = 'flex';
                                div.style.flexDirection = 'column';
                                div.style.gap = '4px';
                                div.style.transition = 'background-color 0.2s';
                                div.onmouseover = () => div.style.backgroundColor = '#F0FDF4';
                                div.onmouseout = () => div.style.backgroundColor = '#F9FAF9';
                                
                                div.innerHTML = `
                                    <div style="font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 6px;">
                                        <i class="${iconClass}" style="color: #6366F1;"></i> ${notif.title}
                                    </div>
                                    <div style="color: var(--text-muted); line-height: 1.4;">${notif.message}</div>
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                                        <span style="font-size: 0.7rem; color: #94A3B8;">${new Date(notif.created_at).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute:'2-digit'})}</span>
                                        <a href="${actionUrl}" style="color: #6366F1; font-weight: 700; text-decoration: none; font-size: 0.75rem;">Verifikasi &rarr;</a>
                                    </div>
                                `;
                                list.appendChild(div);
                            });
                        } else {
                            list.innerHTML = '<div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 12px;">Tidak ada notifikasi baru.</div>';
                        }
                    }
                })
                .catch(err => console.error('Error fetching notifications:', err));
            @endif
        }

        function markNotificationsRead() {
            @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'guru']))
            fetch('/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const badge = document.getElementById('notif-badge');
                    if (badge) badge.style.display = 'none';
                    const list = document.getElementById('notif-list');
                    if (list) {
                        list.innerHTML = '<div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; padding: 12px;">Tidak ada notifikasi baru.</div>';
                    }
                }
            })
            .catch(err => console.error('Error marking notifications as read:', err));
            @endif
        }

        // Click outside closes dropdown
        window.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notif-dropdown');
            if (dropdown && dropdown.style.display === 'flex' && !dropdown.contains(e.target) && !e.target.closest('.icon-btn-circle')) {
                dropdown.style.display = 'none';
            }
        });

        // Initialize polling if admin or guru
        @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'guru']))
        document.addEventListener('DOMContentLoaded', function() {
            fetchNotifications();
            setInterval(fetchNotifications, 10000); // Poll every 10 seconds
        });
        @endif
    </script>

    @auth
    <!-- Settings Modal (Change Password) -->
    <div id="settings-modal" style="display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 16px; backdrop-filter: blur(4px);">
        <div style="background-color: #FFFFFF; border-radius: 24px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); position: relative; border: 1px solid var(--border-subtle); animation: modalFadeIn 0.3s ease; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-gears" style="color: #4F46E5;"></i> Pengaturan Akun
                </h3>
                <button type="button" onclick="closeSettingsModal()" style="background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='var(--text-dark)'" onmouseout="this.style.color='var(--text-muted)'">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <!-- FORM 1: EDIT PROFILE -->
            <form action="/profile/update" method="POST" style="margin-bottom: 24px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 20px;">
                @csrf
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <i class="fa-solid fa-user-gear" style="color: #4F46E5; font-size: 1rem;"></i>
                    <h4 style="font-size: 1rem; font-weight: 800; color: var(--text-dark); margin: 0;">Edit Profil</h4>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <label for="profile_name" class="form-label" style="font-weight: 700; font-size: 0.85rem;">Nama / Nama Institusi:</label>
                    <input type="text" id="profile_name" name="name" class="form-control" value="{{ Auth::user()->name }}" required placeholder="Nama lengkap atau nama sekolah...">
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="profile_phone" class="form-label" style="font-weight: 700; font-size: 0.85rem;">No. Telepon / WhatsApp:</label>
                    <input type="text" id="profile_phone" name="phone" class="form-control" value="{{ Auth::user()->phone }}" placeholder="Contoh: 08123456789">
                </div>

                @if(Auth::user()->role === 'institusi')
                @php
                    $inst = \App\Models\Institution::where('user_id', Auth::user()->id)->first();
                @endphp
                <div style="margin-bottom: 16px;">
                    <label for="profile_address" class="form-label" style="font-weight: 700; font-size: 0.85rem;">Alamat Lengkap Institusi:</label>
                    <textarea id="profile_address" name="address" class="form-control" rows="2" required placeholder="Tulis alamat jalan, nomor, kecamatan, kabupaten...">{{ $inst ? $inst->address : '' }}</textarea>
                </div>
                @endif

                <div style="text-align: right;">
                    <button type="submit" class="btn-primary-dark" style="border: none; cursor: pointer; height: 38px; border-radius: 10px; padding: 0 16px; font-weight: 700; font-size: 0.82rem;">
                        Simpan Profil
                    </button>
                </div>
            </form>

            <!-- FORM 2: CHANGE PASSWORD -->
            <form action="/profile/change-password" method="POST">
                @csrf
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <i class="fa-solid fa-key" style="color: #6366F1; font-size: 1rem;"></i>
                    <h4 style="font-size: 1rem; font-weight: 800; color: var(--text-dark); margin: 0;">Ubah Kata Sandi</h4>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="current_password" class="form-label" style="font-weight: 700; font-size: 0.85rem;">Kata Sandi Sekarang:</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="Masukkan kata sandi saat ini...">
                </div>
                <div style="margin-bottom: 12px;">
                    <label for="new_password" class="form-label" style="font-weight: 700; font-size: 0.85rem;">Kata Sandi Baru (min 8 karakter):</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="8" placeholder="Masukkan kata sandi baru...">
                </div>
                <div style="margin-bottom: 20px;">
                    <label for="new_password_confirmation" class="form-label" style="font-weight: 700; font-size: 0.85rem;">Konfirmasi Kata Sandi Baru:</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required minlength="8" placeholder="Konfirmasi kata sandi baru...">
                </div>
                
                <div style="display: flex; gap: 12px; align-items: center; justify-content: flex-end;">
                    <button type="button" onclick="closeSettingsModal()" class="btn-primary-dark" style="background-color: var(--bg-pill); color: var(--text-dark); border: none; cursor: pointer; height: 38px; border-radius: 10px; padding: 0 16px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem;">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-dark" style="border: none; cursor: pointer; height: 38px; border-radius: 10px; padding: 0 16px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; font-size: 0.82rem;">
                        Simpan Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
    <style>
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    </style>
    <!-- Custom Confirmation Modal -->
    <div id="custom-confirm-modal" style="display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center; padding: 16px; backdrop-filter: blur(4px);">
        <div style="background-color: #FFFFFF; border-radius: 24px; max-width: 420px; width: 100%; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid var(--border-subtle); text-align: center; animation: modalFadeIn 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-sizing: border-box;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background-color: #FEE2E2; color: #DC2626; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin: 0 0 8px 0; letter-spacing: -0.02em;">Konfirmasi Tindakan</h3>
            <p id="custom-confirm-message" style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; margin: 0 0 28px 0; font-weight: 500;"></p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button id="custom-confirm-cancel-btn" type="button" class="btn-primary-dark" style="background-color: var(--bg-pill); color: var(--text-dark); border: none; cursor: pointer; height: 42px; border-radius: 12px; padding: 0 20px; font-weight: 800; font-size: 0.88rem; flex: 1;">
                    Batal
                </button>
                <button id="custom-confirm-ok-btn" type="button" class="btn-primary-dark" style="background-color: #DC2626; color: #FFFFFF; border: none; cursor: pointer; height: 42px; border-radius: 12px; padding: 0 20px; font-weight: 800; font-size: 0.88rem; flex: 1;">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            let confirmCallback = null;
            const modal = document.getElementById('custom-confirm-modal');
            const messageEl = document.getElementById('custom-confirm-message');
            const cancelBtn = document.getElementById('custom-confirm-cancel-btn');
            const okBtn = document.getElementById('custom-confirm-ok-btn');
            const iconEl = modal.querySelector('i');
            const iconWrapper = iconEl.parentElement;

            window.showCustomConfirm = function(message, onConfirm) {
                messageEl.textContent = message;
                confirmCallback = onConfirm;
                
                // Customize styling based on message content
                const msgLower = message.toLowerCase();
                if (msgLower.includes('keluar') || msgLower.includes('logout')) {
                    iconEl.className = 'fa-solid fa-arrow-right-from-bracket';
                    iconWrapper.style.backgroundColor = '#EFECE6';
                    iconWrapper.style.color = '#1C1917';
                    okBtn.style.backgroundColor = '#1C1917';
                    okBtn.textContent = 'Ya, Keluar';
                } else if (msgLower.includes('menolak') || msgLower.includes('reject')) {
                    iconEl.className = 'fa-solid fa-ban';
                    iconWrapper.style.backgroundColor = '#FEF3C7';
                    iconWrapper.style.color = '#D97706';
                    okBtn.style.backgroundColor = '#D97706';
                    okBtn.textContent = 'Ya, Tolak';
                } else {
                    iconEl.className = 'fa-solid fa-trash-can';
                    iconWrapper.style.backgroundColor = '#FEE2E2';
                    iconWrapper.style.color = '#DC2626';
                    okBtn.style.backgroundColor = '#DC2626';
                    okBtn.textContent = 'Ya, Hapus';
                }

                modal.style.display = 'flex';
            };

            window.showCustomConfirmFromGlobal = function(message, form) {
                window.showCustomConfirm(message, function() {
                    if (form) form.submit();
                });
            };

            function closeModal() {
                modal.style.display = 'none';
                confirmCallback = null;
            }

            cancelBtn.addEventListener('click', closeModal);
            okBtn.addEventListener('click', function() {
                if (confirmCallback) confirmCallback();
                closeModal();
            });
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const dashBtn = document.getElementById('dashMobileMenuBtn');
            const dashDrawer = document.getElementById('dashMobileDrawer');
            if (dashBtn && dashDrawer) {
                dashBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const open = dashDrawer.classList.toggle('open');
                    dashBtn.innerHTML = open ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>';
                });
                document.addEventListener('click', function(e) {
                    if (!dashDrawer.contains(e.target) && !dashBtn.contains(e.target) && dashDrawer.classList.contains('open')) {
                        dashDrawer.classList.remove('open');
                        dashBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
                    }
                });
            }
        });
    </script>
    <style>
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    </style>
    @endauth
</body>
</html>

