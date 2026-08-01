@extends('layouts.app')

@section('content')
<h2>Dashboard Super Administrator</h2>
<hr>

<!-- 1. Statistik Global -->
<h3>1. Ringkasan Statistik Sistem</h3>
<ul>
    <li>Total Pengguna Terdaftar: {{ $usersCount }}</li>
    <li>Total Institusi Terverifikasi: {{ $verifiedInstitutionsCount }}</li>
    <li>Total Institusi Menunggu Verifikasi: {{ $pendingInstitutionsCount }}</li>
    <li>Total Riwayat Analisis Bakat AI: {{ $aiAnalysesCount }}</li>
</ul>

<hr>

<!-- 2. Kelola Verifikasi Institusi -->
<h3>2. Kelola Verifikasi Institusi (Sekolah/Universitas)</h3>
@if($institutions->isEmpty())
    <p>Belum ada institusi terdaftar dalam database.</p>
@else
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Nama Institusi</th>
                <th>Email Admin</th>
                <th>NPSN</th>
                <th>Tipe</th>
                <th>No. Telp</th>
                <th>Status Verifikasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($institutions as $inst)
                <tr>
                    <td>{{ $inst->user->name }}</td>
                    <td>{{ $inst->user->email }}</td>
                    <td>{{ $inst->npsn ?? '-' }}</td>
                    <td>{{ $inst->type }}</td>
                    <td>{{ $inst->user->phone ?? '-' }}</td>
                    <td>
                        @if($inst->is_verified)
                            <span style="color: green;">Aktif (Terverifikasi)</span>
                        @else
                            <strong style="color: orange;">Pending</strong>
                        @endif
                    </td>
                    <td>
                        @if(!$inst->is_verified)
                            <div style="display: flex; gap: 5px;">
                                <form action="/admin/institutions/{{ $inst->id }}/verify" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit">Setujui & Verifikasi</button>
                                </form>
                                <form action="/admin/institutions/{{ $inst->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Yakin ingin menolak pendaftaran institusi ini?');">
                                    @csrf
                                    <button type="submit">Tolak</button>
                                </form>
                            </div>
                        @else
                            <div style="display: flex; gap: 5px;">
                                <a href="/admin/institutions/{{ $inst->id }}/edit"><button type="button">Edit</button></a>
                                <form action="/admin/institutions/{{ $inst->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus institusi ini secara permanen dari database?');">
                                    @csrf
                                    <button type="submit">Hapus</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<hr>

<!-- 3. Kelola Master Data Lomba -->
<h3>3. Kelola Master Kompetisi Nasional</h3>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>Judul Kompetisi</th>
            <th>Kategori</th>
            <th>Penyelenggara</th>
            <th>Batas Pendaftaran</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($competitions as $comp)
            <tr>
                <td>{{ $comp->title }}</td>
                <td>{{ $comp->category }}</td>
                <td>{{ $comp->organizer ?? '-' }}</td>
                <td>{{ $comp->registration_deadline ? $comp->registration_deadline->format('d-m-Y') : '-' }}</td>
                <td>{{ $comp->description ?? '-' }}</td>
                <td>
                    <div style="display: flex; gap: 5px;">
                        <a href="/admin/competitions/{{ $comp->id }}/edit"><button type="button">Edit</button></a>
                        <form action="/admin/competitions/{{ $comp->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Yakin ingin menghapus kompetisi ini?');">
                            @csrf
                            <button type="submit">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>
<form action="/admin/competitions" method="POST">
    @csrf
    <h4>Tambah Master Kompetisi Baru</h4>
    <div>
        <label for="title">Judul Kompetisi:</label><br>
        <input type="text" id="title" name="title" required placeholder="contoh: GEMASTIK - Keamanan Siber">
    </div>
    <br>
    <div>
        <label for="category">Kategori Lomba:</label><br>
        <select id="category" name="category" required>
            <option value="teknologi">Teknologi / IT</option>
            <option value="sains">Sains / Matematika</option>
            <option value="seni">Seni & Desain</option>
            <option value="lainnya">Lainnya</option>
        </select>
    </div>
    <br>
    <div>
        <label for="organizer">Penyelenggara / Institusi:</label><br>
        <input type="text" id="organizer" name="organizer" placeholder="contoh: Puspresnas">
    </div>
    <br>
    <div>
        <label for="registration_deadline">Batas Pendaftaran:</label><br>
        <input type="date" id="registration_deadline" name="registration_deadline">
    </div>
    <br>
    <div>
        <label for="link">Link Pendaftaran:</label><br>
        <input type="text" id="link" name="link" placeholder="contoh: https://gemastik.kemdikbud.go.id">
    </div>
    <br>
    <div>
        <label for="description">Deskripsi Lomba:</label><br>
        <textarea id="description" name="description" placeholder="Deskripsikan perlombaan..."></textarea>
    </div>
    <br>
    <button type="submit">Tambah Kompetisi</button>
</form>
@endsection
