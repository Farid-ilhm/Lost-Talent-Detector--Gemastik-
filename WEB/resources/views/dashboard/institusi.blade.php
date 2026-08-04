@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Dashboard Institusi</h1>
        <p class="hero-subtitle">Kelola data kelas, pendaftaran guru pendamping, & statistik sekolah/universitas.</p>
    </div>
</div>

@if(!$institution->is_verified)
    <div class="alert-custom alert-warning" style="margin-top: 16px;">
        <i class="fa-solid fa-clock-rotate-left" style="font-size: 1.2rem;"></i>
        <div>
            <strong>Akun Institusi Menunggu Verifikasi Super Admin!</strong><br>
            <span style="font-weight: 500; font-size: 0.85rem;">Hubungi administrator atau tunggu persetujuan verifikasi untuk membuka fitur manajemen kelas dan registrasi guru.</span>
        </div>
    </div>
@endif

<!-- Overview 3 Stats Cards Grid -->
<div class="cards-grid" style="margin-top: 16px;">
    <div class="pastel-card card-pink">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-school"></i> Institusi</span>
            <span class="card-rating-badge"><i class="fa-solid fa-certificate"></i> NPSN: {{ $institution->npsn ?? '-' }}</span>
        </div>
        <h3 class="card-title">{{ $institution->user->name }}</h3>
        <div class="card-footer-row">
            <span class="card-meta-text">{{ $institution->address ?? 'Alamat Belum Diisi' }}</span>
        </div>
    </div>

    <div class="pastel-card card-sand">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-chalkboard-user"></i> Total Guru</span>
            <span class="card-rating-badge" style="background-color: #FFFFFF;">Aktif</span>
        </div>
        <h3 class="card-title" style="font-size: 2rem;">{{ $teachersCount }} <span style="font-size: 1rem; font-weight: 500;">Guru Pendamping</span></h3>
        <div class="card-footer-row">
            <span class="card-meta-text">Terdaftar di Sistem</span>
        </div>
    </div>

    <div class="pastel-card card-mint">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-user-graduate"></i> Total Siswa</span>
            <span class="card-rating-badge" style="background-color: #FFFFFF;">Aktif</span>
        </div>
        <h3 class="card-title" style="font-size: 2rem;">{{ $studentsCount }} <span style="font-size: 1rem; font-weight: 500;">Siswa / Mahasiswa</span></h3>
        <div class="card-footer-row">
            <span class="card-meta-text">{{ $classrooms->count() }} Ruang Kelas</span>
        </div>
    </div>
</div>

@if($institution->is_verified)
    <!-- Quick Actions Panel -->
    <div class="content-box" style="margin-top: 24px;">
        <div style="margin-bottom: 20px;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bolt" style="color: #6366F1;"></i> Akses Cepat Panel Institusi
            </h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Kelola data guru pendamping dan ruang kelas terdaftar institusi Anda secara efisien.</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            <!-- Card Kelola Kelas -->
            <a href="/institution/classrooms" style="text-decoration: none; color: inherit; display: block;">
                <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-md); transition: all 0.2s ease;" class="hover-scale-subtle">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background-color: #FAF0E4; color: #92400E; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px;">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <h4 style="font-weight: 700; margin-bottom: 6px; font-size: 1rem; color: var(--text-dark);">Kelola Kelas</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Lihat kelas terdaftar, jurusan, serta statistika jumlah murid.</p>
                </div>
            </a>

            <!-- Card Kelola Guru -->
            <a href="/institution/teachers" style="text-decoration: none; color: inherit; display: block;">
                <div style="background-color: #FAFAF8; border: 1px solid var(--border-subtle); padding: 24px; border-radius: var(--radius-md); transition: all 0.2s ease;" class="hover-scale-subtle">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background-color: #E8EAFF; color: #3730A3; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 16px;">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <h4 style="font-weight: 700; margin-bottom: 6px; font-size: 1rem; color: var(--text-dark);">Kelola Guru Pendamping</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">Daftarkan guru pembimbing baru, edit data guru, atau hapus akses guru.</p>
                </div>
            </a>
        </div>
    </div>
@endif
@endsection
