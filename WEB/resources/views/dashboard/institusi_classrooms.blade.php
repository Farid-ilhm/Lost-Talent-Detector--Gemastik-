@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Kelola Ruang Kelas</h1>
        <p class="hero-subtitle">Lihat daftar kelas yang terdaftar pada institusi Anda serta jumlah siswa di masing-masing kelas.</p>
    </div>
</div>

@if(!$institution->is_verified)
    <div class="alert-custom alert-warning" style="margin-top: 16px;">
        <i class="fa-solid fa-clock-rotate-left" style="font-size: 1.2rem;"></i>
        <div>
            <strong>Akun Institusi Menunggu Verifikasi Super Admin!</strong><br>
            <span style="font-weight: 500; font-size: 0.85rem;">Hubungi administrator atau tunggu persetujuan verifikasi untuk membuka fitur manajemen kelas.</span>
        </div>
    </div>
@else
    <!-- Tabel Kelas -->
    <div class="content-box" style="margin-top: 24px;">
        <div class="section-title-row" style="margin-top: 0;">
            <h3 class="section-title"><i class="fa-solid fa-door-open"></i> Daftar Kelas Terdaftar</h3>
        </div>

        @if($classrooms->isEmpty())
            <div class="alert-custom alert-warning">
                <i class="fa-solid fa-info-circle"></i>
                <span>Belum ada kelas yang didaftarkan. Kelas akan otomatis terdaftar ketika guru pembimbing menginput data siswa baru.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No.</th>
                            <th>Nama Kelas</th>
                            <th>Jurusan</th>
                            <th>Tahun Akademik</th>
                            <th>Jumlah Siswa</th>
                            <th>Aksi Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classrooms as $room)
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
                                <td><strong>{{ $room->name }}</strong></td>
                                <td><span class="card-cat-badge">{{ $room->major->name ?? '-' }}</span></td>
                                <td>{{ $room->academicYear->name ?? '-' }}</td>
                                <td><strong>{{ $room->students->count() }} Murid</strong></td>
                                <td>
                                    <form action="/institution/classrooms/{{ $room->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                                        @csrf
                                        <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B; border: none; cursor: pointer;">
                                            <i class="fa-solid fa-trash"></i> Hapus Kelas
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
@endsection
