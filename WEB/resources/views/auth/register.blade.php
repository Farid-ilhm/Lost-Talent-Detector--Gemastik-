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
            <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa (Sekolah Menengah)</option>
            <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa (Perguruan Tinggi)</option>
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

    <div id="nisn-container" style="display: none;">
        <label for="nisn">NISN (Nomor Induk Siswa Nasional):</label><br>
        <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}">
        <br><br>
    </div>

    <div id="class-container" style="display: none;">
        <label for="classroom">Nama Kelas / Jurusan:</label><br>
        <input type="text" id="classroom" name="classroom" value="{{ old('classroom') }}">
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

<script>
    function toggleFormFields() {
        var roleSelect = document.getElementById('role');
        var role = roleSelect.value;

        // Container Elements
        var npsnContainer = document.getElementById('npsn-container');
        var schoolContainer = document.getElementById('school-container');
        var nisnContainer = document.getElementById('nisn-container');
        var classContainer = document.getElementById('class-container');
        var nimContainer = document.getElementById('nim-container');
        var semesterContainer = document.getElementById('semester-container');

        // Input Elements
        var npsnInput = document.getElementById('npsn');
        var nisnInput = document.getElementById('nisn');
        var classroomInput = document.getElementById('classroom');
        var nimInput = document.getElementById('nim');
        var semSelect = document.getElementById('semester');

        // Default: Hide all conditional blocks
        npsnContainer.style.display = 'none';
        schoolContainer.style.display = 'none';
        nisnContainer.style.display = 'none';
        classContainer.style.display = 'none';
        nimContainer.style.display = 'none';
        semesterContainer.style.display = 'none';

        // Clear required tags
        npsnInput.removeAttribute('required');
        nisnInput.removeAttribute('required');
        classroomInput.removeAttribute('required');
        nimInput.removeAttribute('required');
        semSelect.removeAttribute('required');

        if (role === 'institusi') {
            npsnContainer.style.display = 'block';
            npsnInput.setAttribute('required', 'required');
        } else if (role === 'siswa') {
            schoolContainer.style.display = 'block';
            nisnContainer.style.display = 'block';
            classContainer.style.display = 'block';
            
            nisnInput.setAttribute('required', 'required');
            classroomInput.setAttribute('required', 'required');
        } else if (role === 'mahasiswa') {
            schoolContainer.style.display = 'block';
            nimContainer.style.display = 'block';
            semesterContainer.style.display = 'block';
            
            nimInput.setAttribute('required', 'required');
            semSelect.setAttribute('required', 'required');
        }
    }
    
    document.getElementById('role').addEventListener('change', toggleFormFields);
    
    window.addEventListener('load', function() {
        toggleFormFields();
    });
</script>

<p>Sudah punya akun? <a href="/login">Masuk disini</a></p>
@endsection
