@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Dashboard Super Administrator</h1>
        <p class="hero-subtitle">Kelola verifikasi institusi, master data kompetisi, & statistik global sistem.</p>
    </div>
</div>

<!-- 1. Overview 4 Pastel Cards Grid -->
<div class="cards-grid" style="margin-top: 16px;">
    <div class="pastel-card card-pink">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-users"></i> Total Pengguna</span>
            <span class="card-rating-badge" style="background-color: #FFFFFF;">System Wide</span>
        </div>
        <h3 class="card-title" style="font-size: 2rem;">{{ $usersCount }} <span style="font-size: 1rem; font-weight: 500;">User Terdaftar</span></h3>
        <div class="card-footer-row">
            <span class="card-meta-text">Siswa, Mahasiswa, Guru, Institusi</span>
        </div>
    </div>

    <div class="pastel-card card-sand">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-circle-check"></i> Verified Institusi</span>
            <span class="card-rating-badge" style="background-color: #D1F5E4; color: #065F46;">Aktif</span>
        </div>
        <h3 class="card-title" style="font-size: 2rem;">{{ $verifiedInstitutionsCount }} <span style="font-size: 1rem; font-weight: 500;">Sekolah/Univ</span></h3>
        <div class="card-footer-row">
            <span class="card-meta-text">Disetujui Admin</span>
        </div>
    </div>

    <div class="pastel-card card-lavender">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-clock-rotate-left"></i> Pending Institusi</span>
            <span class="card-rating-badge" style="background-color: #FEF3C7; color: #92400E;">Perlu Verifikasi</span>
        </div>
        <h3 class="card-title" style="font-size: 2rem;">{{ $pendingInstitutionsCount }} <span style="font-size: 1rem; font-weight: 500;">Menunggu Approval</span></h3>
        <div class="card-footer-row">
            <span class="card-meta-text">Tinjau Pendaftaran</span>
        </div>
    </div>

    <div class="pastel-card card-mint">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-brain"></i> Analisis AI</span>
            <span class="card-rating-badge" style="background-color: #FFFFFF;">AI Analytics</span>
        </div>
        <h3 class="card-title" style="font-size: 2rem;">{{ $aiAnalysesCount }} <span style="font-size: 1rem; font-weight: 500;">Hasil Deteksi</span></h3>
        <div class="card-footer-row">
            <span class="card-meta-text">Deteksi Bakat Terproses</span>
        </div>
    </div>
</div>

<!-- 2. Quick Actions Panel -->
<div class="content-box" style="margin-top: 24px;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-bolt" style="color: #6366F1;"></i> Akses Cepat Panel Admin
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Kelola berbagai data master sistem secara efisien melalui navigasi di bawah ini.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <!-- Card Kelola Institusi -->
        <a href="/admin/institutions" style="text-decoration: none; color: inherit; display: block;">
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-md); transition: all 0.2s ease;" class="hover-scale-subtle">
                <div style="width: 44px; height: 44px; border-radius: 12px; background-color: var(--bg-card-sand) ?? #FAF0E4; color: var(--card-sand-accent) ?? #92400E; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px;">
                    <i class="fa-solid fa-school-flag"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 6px; font-size: 1rem; color: var(--text-dark);">Kelola Institusi</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Verifikasi & edit data sekolah atau universitas terdaftar.</p>
            </div>
        </a>

        <!-- Card Kelola Kompetisi -->
        <a href="/admin/competitions" style="text-decoration: none; color: inherit; display: block;">
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-md); transition: all 0.2s ease;" class="hover-scale-subtle">
                <div style="width: 44px; height: 44px; border-radius: 12px; background-color: var(--bg-card-lavender) ?? #E8EAFF; color: var(--card-lavender-accent) ?? #3730A3; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px;">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 6px; font-size: 1rem; color: var(--text-dark);">Kelola Kompetisi</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Atur data master perlombaan.</p>
            </div>
        </a>

        <!-- Card Kelola Pengguna -->
        <a href="/admin/users" style="text-decoration: none; color: inherit; display: block;">
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-md); transition: all 0.2s ease;" class="hover-scale-subtle">
                <div style="width: 44px; height: 44px; border-radius: 12px; background-color: var(--bg-card-mint) ?? #D8F5E8; color: var(--card-mint-accent) ?? #065F46; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 6px; font-size: 1rem; color: var(--text-dark);">Kelola Pengguna</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Pantau, tambah, edit, atau hapus akun pengguna sistem.</p>
            </div>
        </a>
    </div>
</div>

<!-- 3. Chart Section -->
<div class="content-box" style="margin-top: 24px;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-chart-bar" style="color: #10B981;"></i> Distribusi Akun Pengguna Aktif
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Visualisasi persentase penyebaran peran pengguna aktif di dalam platform.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: center; flex-wrap: wrap;">
        <!-- Left: Chart Canvas -->
        <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 20px; border-radius: var(--radius-md); height: 320px; position: relative;">
            <canvas id="userRolesChart" style="max-height: 100%; max-width: 100%;"></canvas>
        </div>
        
        <!-- Right: Summary cards or tables -->
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 16px; border-radius: var(--radius-md);">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Peran Mayoritas</span>
                @php
                    $maxRole = 'Tidak ada data';
                    $maxCount = 0;
                    foreach($roleCounts as $role => $count) {
                        if($count > $maxCount) {
                            $maxCount = $count;
                            $maxRole = ucfirst($role);
                        }
                    }
                @endphp
                <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin: 6px 0 0 0;">
                    {{ $maxRole }} ({{ $maxCount }} User)
                </h4>
            </div>
            
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 16px; border-radius: var(--radius-md);">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Rata-rata Akun per Role</span>
                <h4 style="font-size: 1.2rem; font-weight: 800; color: var(--text-dark); margin: 6px 0 0 0;">
                    {{ number_format(array_sum($roleCounts) / count($roleCounts), 1) }} Akun
                </h4>
            </div>
            
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 16px; border-radius: var(--radius-md);">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">Kepadatan Data</span>
                <h4 style="font-size: 1.2rem; font-weight: 800; color: #10B981; margin: 6px 0 0 0;">
                    Sangat Baik <i class="fa-solid fa-circle-check"></i>
                </h4>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('userRolesChart').getContext('2d');
    
    // Data from Controller
    const rawData = @json($roleCounts);
    
    const labels = Object.keys(rawData).map(function(key) {
        return key.charAt(0).toUpperCase() + key.slice(1);
    });
    const values = Object.values(rawData);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Pengguna',
                data: values,
                backgroundColor: [
                    'rgba(244, 63, 94, 0.75)',  // Siswa
                    'rgba(59, 130, 246, 0.75)',  // Mahasiswa
                    'rgba(139, 92, 246, 0.75)',  // Guru
                    'rgba(245, 158, 11, 0.75)',  // Institusi
                    'rgba(16, 185, 129, 0.75)'   // Umum
                ],
                borderColor: [
                    '#F43F5E',
                    '#3B82F6',
                    '#8B5CF6',
                    '#F59E0B',
                    '#10B981'
                ],
                borderWidth: 1.5,
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF',
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#64748B',
                        font: {
                            weight: '600'
                        }
                    },
                    grid: {
                        color: '#E2E8F0'
                    }
                },
                x: {
                    ticks: {
                        color: '#64748B',
                        font: {
                            weight: '600'
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endsection
