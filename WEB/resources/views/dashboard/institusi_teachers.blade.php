@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Kelola Guru Pendamping</h1>
        <p class="hero-subtitle">Daftarkan guru pendamping baru atau atur guru pendamping yang sudah terdaftar.</p>
    </div>
</div>

@if(!$institution->is_verified)
    <div class="alert-custom alert-warning" style="margin-top: 16px;">
        <i class="fa-solid fa-clock-rotate-left" style="font-size: 1.2rem;"></i>
        <div>
            <strong>Akun Institusi Menunggu Verifikasi Super Admin!</strong><br>
            <span style="font-weight: 500; font-size: 0.85rem;">Hubungi administrator atau tunggu persetujuan verifikasi untuk membuka fitur registrasi guru.</span>
        </div>
    </div>
@else
    <!-- Daftar Guru Pendamping -->
    <div class="content-box" style="margin-top: 24px;">
        <div class="section-title-row" style="margin-top: 0;">
            <h3 class="section-title"><i class="fa-solid fa-chalkboard-user"></i> Daftar Guru Pendamping Terdaftar</h3>
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
                            <th style="width: 50px; text-align: center;">No.</th>
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
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
                                <td><strong>{{ $t->user->name }}</strong></td>
                                <td>{{ $t->user->email }}</td>
                                <td>{{ $t->nip ?? '-' }}</td>
                                <td><span class="card-cat-badge">{{ $t->subject ?? '-' }}</span></td>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <a href="/institution/teachers/{{ $t->id }}/edit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--bg-pill); color: var(--text-dark);">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form action="/institution/teachers/{{ $t->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus guru ini? Akun login guru bersangkutan juga akan ikut dihapus.');">
                                            @csrf
                                            <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B; border: none; cursor: pointer;">
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

    <!-- Daftarkan Guru Baru -->
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
                    <div class="password-toggle-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required placeholder="Minimal 8 karakter">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" title="Lihat Password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-plus"></i> Daftarkan Guru Sekarang
            </button>
        </form>
    </div>
@endif
@endsection
