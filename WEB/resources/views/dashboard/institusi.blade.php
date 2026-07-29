@extends('layouts.app')

@section('content')
<h2>Dashboard Institusi (Sekolah / Universitas)</h2>
<hr>

@if(!$institution->is_verified)
    <div style="background-color: #fff3cd; color: #856404; padding: 15px; border: 1px solid #ffeeba; margin-bottom: 20px;">
        <strong>Akun Institusi Menunggu Verifikasi!</strong><br>
        Akun sekolah/universitas Anda belum disetujui oleh Super Administrator. Hubungi administrator atau tunggu sampai verifikasi selesai untuk dapat mengakses fitur manajemen kelas dan pendaftaran guru pendamping.
    </div>
@endif

<!-- Profil Institusi -->
<h3>Profil Institusi</h3>
<p>Nama Institusi: <strong>{{ $institution->user->name }}</strong></p>
<p>NPSN / Identitas: {{ $institution->npsn ?? '-' }}</p>
<p>Alamat: {{ $institution->address ?? '-' }}</p>
<p>Website: {{ $institution->website ?? '-' }}</p>

<hr>

<!-- Statistik Singkat -->
<h3>1. Ringkasan Statistik</h3>
<ul>
    <li>Total Guru Aktif: {{ $teachersCount }}</li>
    <li>Total Siswa Terdaftar: {{ $studentsCount }}</li>
    <li>Total Kelas: {{ $classrooms->count() }}</li>
</ul>

<hr>

@if($institution->is_verified)
    <!-- Tabel Kelas -->
    <h3>2. Daftar Kelas Terdaftar</h3>
    @if($classrooms->isEmpty())
        <p>Belum ada kelas yang didaftarkan.</p>
    @else
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Nama Kelas</th>
                    <th>Jurusan</th>
                    <th>Tahun Akademik</th>
                    <th>Jumlah Siswa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classrooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->major->name ?? '-' }}</td>
                        <td>{{ $room->academicYear->name ?? '-' }}</td>
                        <td>{{ $room->students->count() }} Murid</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <hr>

    <!-- Daftarkan Guru Baru -->
    <h3>3. Daftarkan Guru Pendamping Baru</h3>
    <form action="/institution/teachers" method="POST">
        @csrf
        <div>
            <label for="name">Nama Guru:</label><br>
            <input type="text" id="name" name="name" required placeholder="contoh: Ibu Maria, S.Pd.">
        </div>
        <br>
        <div>
            <label for="email">Email Login Guru:</label><br>
            <input type="email" id="email" name="email" required placeholder="contoh: maria@school.id">
        </div>
        <br>
        <div>
            <label for="nip">NIP Guru:</label><br>
            <input type="text" id="nip" name="nip" placeholder="contoh: 199208082015022001">
        </div>
        <br>
        <div>
            <label for="subject">Mata Pelajaran Utama:</label><br>
            <input type="text" id="subject" name="subject" placeholder="contoh: Matematika">
        </div>
        <br>
        <div>
            <label for="password">Password Default (Minimal 8 karakter):</label><br>
            <input type="password" id="password" name="password" required>
        </div>
        <br>
        <button type="submit">Daftarkan Guru</button>
    </form>
@else
    <p><em>*Menu pengelolaan kelas dan pendaftaran guru dinonaktifkan sementara hingga verifikasi akun disetujui.</em></p>
@endif
@endsection
