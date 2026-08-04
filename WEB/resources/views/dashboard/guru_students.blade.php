@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Kelola Akun Murid</h1>
        <p class="hero-subtitle">Kelola dan edit data profil (NISN, NIM, Semester, dan Kelas) siswa/mahasiswa Anda.</p>
    </div>
</div>

<!-- Daftar Murid Terdaftar -->
<div class="content-box" style="margin-top: 24px;">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-users"></i> Daftar Murid Terdaftar</h3>
    </div>

    @if($students->isEmpty())
        <div class="alert-custom alert-warning">
            <i class="fa-solid fa-info-circle"></i>
            <span>Belum ada murid yang terdaftar.</span>
        </div>
    @else
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">No.</th>
                        <th>Nama Murid</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>NISN / NIM</th>
                        <th>Kelas / Semester</th>
                        <th>Aksi Management</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $st)
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
                            <td><strong>{{ $st->user->name }}</strong></td>
                            <td>{{ $st->user->email }}</td>
                            <td><span class="card-cat-badge">{{ ucfirst($st->user->role) }}</span></td>
                            <td>
                                @if($st->user->role === 'siswa')
                                    {{ $st->nisn ?? '-' }}
                                @elseif($st->user->role === 'mahasiswa')
                                    {{ $st->nim ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($st->user->role === 'siswa')
                                    {{ $st->classroom->name ?? '-' }} ({{ $st->classroom->major->name ?? '-' }})
                                @elseif($st->user->role === 'mahasiswa')
                                    Semester {{ $st->semester ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <a href="/teacher/students/{{ $st->id }}/edit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--bg-pill); color: var(--text-dark); text-decoration: none;">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <form action="/teacher/students/{{ $st->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun murid ini secara permanen?');">
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
@endsection
