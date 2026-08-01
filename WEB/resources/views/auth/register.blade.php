@extends('layouts.app')

@section('content')
<h2>Pendaftaran Akun Baru</h2>
<form action="/register" method="POST">
    @csrf
    <div>
        <label for="role">Role Pengguna:</label><br>
        <select id="role" name="role" required>
            <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa (Sekolah Menengah)</option>
            <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa (Perguruan Tinggi)</option>
            <option value="umum" {{ old('role') == 'umum' ? 'selected' : '' }}>Pengguna Umum / Mandiri</option>
            <option value="institusi" {{ old('role') == 'institusi' ? 'selected' : '' }}>Institusi (Sekolah/Universitas)</option>
        </select>
    </div>
    <br>
    <div>
        <label for="name" id="label-name">Nama Lengkap:</label><br>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
    </div>
    <br>
    <div>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
    </div>
    <br>
    <div>
        <label for="phone">No. Telepon/WhatsApp:</label><br>
        <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
    </div>
    <br>
    
    <div id="npsn-container" style="display: none;">
        <label for="npsn">NPSN (Nomor Pokok Sekolah Nasional):</label><br>
        <input type="text" id="npsn" name="npsn" value="{{ old('npsn') }}">
        <br><br>
    </div>

    <div id="school-container" style="display: none;">
        <label for="institution_id">Pilih Sekolah / Universitas Anda:</label><br>
        <select id="institution_id" name="institution_id">
            <option value="">-- Pengguna Umum (Mandiri) --</option>
            @foreach($institutions as $inst)
                <option value="{{ $inst->id }}">{{ $inst->user->name }} (NPSN: {{ $inst->npsn }})</option>
            @endforeach
        </select>
        <br><br>
    </div>

    <div id="nisn-container" style="display: none;">
        <label for="nisn">NISN (Nomor Induk Siswa Nasional):</label><br>
        <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}">
        <br><br>
    </div>

    <div id="class-container" style="display: none;">
        <label for="classroom">Nama Kelas (Tingkat/Kelompok):</label><br>
        <input type="text" id="classroom" name="classroom" value="{{ old('classroom') }}" placeholder="Contoh: XII, XI, X, atau 10-A, 11-B">
        <br><br>
    </div>

    <div id="major-container" style="display: none;">
        <label for="major">Jurusan:</label><br>
        <input type="text" id="major" name="major" value="{{ old('major') }}">
        <br><br>
    </div>

    <div id="nim-container" style="display: none;">
        <label for="nim">NIM (Nomor Induk Mahasiswa):</label><br>
        <input type="text" id="nim" name="nim" value="{{ old('nim') }}">
        <br><br>
    </div>

    <div id="semester-container" style="display: none;">
        <label for="semester">Semester Saat Ini:</label><br>
        <input type="number" id="semester" name="semester" min="1" max="14" value="{{ old('semester') }}">
        <br><br>
    </div>

    <div>
        <label for="password">Password (Minimal 8 karakter):</label><br>
        <input type="password" id="password" name="password" required>
    </div>
    <br>
    <div>
        <label for="password_confirmation">Konfirmasi Password:</label><br>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
    </div>
    <br>
    <button type="submit">Daftar Akun</button>
</form>

<script src="{{ asset('js/register.js') }}"></script>

<p>Sudah punya akun? <a href="/login">Masuk disini</a></p>
@endsection
