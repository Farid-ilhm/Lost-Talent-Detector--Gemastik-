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
            <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa / Mahasiswa</option>
            <option value="umum" {{ old('role') == 'umum' ? 'selected' : '' }}>Pengguna Umum / Mandiri</option>
            <option value="institusi" {{ old('role') == 'institusi' ? 'selected' : '' }}>Institusi (Sekolah/Universitas)</option>
        </select>
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

<script>
    function toggleNpsn() {
        var roleSelect = document.getElementById('role');
        var npsnContainer = document.getElementById('npsn-container');
        var npsnInput = document.getElementById('npsn');
        var schoolContainer = document.getElementById('school-container');
        
        if (roleSelect.value === 'institusi') {
            npsnContainer.style.display = 'block';
            npsnInput.setAttribute('required', 'required');
            schoolContainer.style.display = 'none';
        } else if (roleSelect.value === 'siswa') {
            npsnContainer.style.display = 'none';
            npsnInput.removeAttribute('required');
            schoolContainer.style.display = 'block';
        } else {
            npsnContainer.style.display = 'none';
            npsnInput.removeAttribute('required');
            schoolContainer.style.display = 'none';
        }
    }
    
    document.getElementById('role').addEventListener('change', toggleNpsn);
    window.addEventListener('load', toggleNpsn); // Handle old input redirect triggers
</script>

<p>Sudah punya akun? <a href="/login">Masuk disini</a></p>
@endsection
