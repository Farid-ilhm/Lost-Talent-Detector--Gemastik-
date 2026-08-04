@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Dashboard Guru & Pembina</h1>
        <p class="hero-subtitle">Kelola verifikasi prestasi, nilai rapor, & catatan pengembangan murid.</p>
    </div>
</div>

<!-- Teacher Profile Card -->
<div class="pastel-card card-sand" style="margin-top: 16px; min-height: auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <span class="card-cat-badge"><i class="fa-solid fa-chalkboard-user"></i> Profil Pengajar</span>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-top: 4px;">{{ $teacher->user->name }}</h2>
            <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 2px;">
                NIP: <strong>{{ $teacher->nip ?? '-' }}</strong> | Mapel Diampu: <strong>{{ $teacher->subject ?? '-' }}</strong>
            </div>
        </div>
        <div class="card-rating-badge" style="background-color: #FFFFFF; font-size: 0.9rem; padding: 8px 16px;">
            <i class="fa-solid fa-graduation-cap"></i> {{ $students->count() }} Murid Terdaftar
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="content-box" style="margin-top: 24px;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-bolt" style="color: #6366F1;"></i> Akses Cepat Panel Pengajar
        </h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Kelola seluruh verifikasi dan evaluasi minat bakat murid di kelas Anda.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <!-- Card Verifikasi Prestasi -->
        <a href="/teacher/achievements" style="text-decoration: none; color: inherit; display: block;">
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-md); transition: all 0.2s ease;" class="hover-scale-subtle">
                <div style="width: 44px; height: 44px; border-radius: 12px; background-color: #E8EAFF; color: #3730A3; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px;">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 6px; font-size: 1rem; color: var(--text-dark);">Verifikasi Prestasi</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Periksa, setujui, atau tolak unggahan sertifikat prestasi siswa.</p>
            </div>
        </a>

        <!-- Card Input Nilai -->
        <a href="/teacher/grades" style="text-decoration: none; color: inherit; display: block;">
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-md); transition: all 0.2s ease;" class="hover-scale-subtle">
                <div style="width: 44px; height: 44px; border-radius: 12px; background-color: #FAF0E4; color: #92400E; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px;">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 6px; font-size: 1rem; color: var(--text-dark);">Input Nilai & Catatan</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Input nilai rapor dan tulis catatan minat bakat perkembangan murid.</p>
            </div>
        </a>

        <!-- Card Kelola Murid -->
        <a href="/teacher/students" style="text-decoration: none; color: inherit; display: block;">
            <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-md); transition: all 0.2s ease;" class="hover-scale-subtle">
                <div style="width: 44px; height: 44px; border-radius: 12px; background-color: #E2FBF0; color: #065F46; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px;">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h4 style="font-weight: 700; margin-bottom: 6px; font-size: 1rem; color: var(--text-dark);">Kelola Akun Murid</h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Kelola profil murid, edit info akademik, atau hapus akun murid.</p>
            </div>
        </a>
    </div>
</div>
@endsection
