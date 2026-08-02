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
    <!-- 2. Tabel Kelas -->
    <div class="content-box" style="margin-top: 24px;">
        <div class="section-title-row" style="margin-top: 0;">
            <h3 class="section-title"><i class="fa-solid fa-door-open"></i> Daftar Kelas Terdaftar</h3>
        </div>

        @if($classrooms->isEmpty())
            <div class="alert-custom alert-warning">
                <i class="fa-solid fa-info-circle"></i>
                <span>Belum ada kelas yang didaftarkan.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
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
                                <td><strong>{{ $room->name }}</strong></td>
                                <td><span class="card-cat-badge">{{ $room->major->name ?? '-' }}</span></td>
                                <td>{{ $room->academicYear->name ?? '-' }}</td>
                                <td><strong>{{ $room->students->count() }} Murid</strong></td>
                                <td>
                                    <form action="/institution/classrooms/{{ $room->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                                        @csrf
                                        <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B;">
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

    <!-- 3. Daftar Guru Pendamping -->
    <div class="content-box" style="margin-top: 24px;">
        <div class="section-title-row" style="margin-top: 0;">
            <h3 class="section-title"><i class="fa-solid fa-users-view-finder"></i> Daftar Guru Pendamping Terdaftar</h3>
        </div>

        @if($teachers->isEmpty())
            <div class="alert-custom alert-warning">
                <i class="fa-solid fa-info-circle"></i>
                <span>Belum ada guru pendamping yang didaftarkan.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama Guru</th>
                            <th>Email Login</th>
                            <th>NIP</th>
                            <th>Mata Pelajaran Utama</th>
                            <th>Aksi Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teachers as $t)
                            <tr>
                                <td><strong>{{ $t->user->name }}</strong></td>
                                <td>{{ $t->user->email }}</td>
                                <td>{{ $t->nip ?? '-' }}</td>
                                <td><span class="card-cat-badge">{{ $t->subject ?? '-' }}</span></td>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <a href="/institution/teachers/{{ $t->id }}/edit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--bg-pill); color: var(--text-dark);">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form action="/institution/teachers/{{ $t->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus guru ini?');">
                                            @csrf
                                            <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B;">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- 4. Daftarkan Guru Baru -->
    <div class="content-box" style="margin-top: 24px;">
        <div class="section-title-row" style="margin-top: 0;">
            <h3 class="section-title"><i class="fa-solid fa-user-plus"></i> Daftarkan Guru Pendamping Baru</h3>
        </div>

        <form action="/institution/teachers" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="name" class="form-label">Nama Guru:</label>
                    <input type="text" id="name" name="name" class="form-control" required placeholder="contoh: Ibu Maria, S.Pd.">
                </div>
                <div>
                    <label for="email" class="form-label">Email Login Guru:</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="contoh: maria@school.id">
                </div>
                <div>
                    <label for="nip" class="form-label">NIP Guru:</label>
                    <input type="text" id="nip" name="nip" class="form-control" placeholder="contoh: 199208082015022001">
                </div>
                <div>
                    <label for="subject" class="form-label">Mata Pelajaran Utama:</label>
                    <input type="text" id="subject" name="subject" class="form-control" placeholder="contoh: Matematika">
                </div>
                <div>
                    <label for="password" class="form-label">Password Default:</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Minimal 8 karakter">
                </div>
            </div>
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-plus"></i> Daftarkan Guru Sekarang
            </button>
        </form>
    </div>
@endif
@endsection
