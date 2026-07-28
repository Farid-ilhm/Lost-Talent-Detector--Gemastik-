@extends('layouts.app')

@section('content')
<h2>Pendaftaran Akun Baru</h2>
<form action="/register" method="POST">
    @csrf
    <div>
        <label for="name">Nama Lengkap:</label><br>
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
    <div>
        <label for="role">Role Pengguna:</label><br>
        <select id="role" name="role" required>
            <option value="siswa">Siswa / Mahasiswa</option>
            <option value="umum">Pengguna Umum / Mandiri</option>
        </select>
    </div>
    <br>
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
<p>Sudah punya akun? <a href="/login">Masuk disini</a></p>
@endsection
