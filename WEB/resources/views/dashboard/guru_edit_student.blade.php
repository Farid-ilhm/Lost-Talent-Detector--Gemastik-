@extends('layouts.app')

@section('content')
<h2>Edit Murid: {{ $student->user->name }}</h2>
<hr>

@if ($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="/teacher/students/{{ $student->id }}/update" method="POST">
    @csrf
    <div>
        <label for="name">Nama Murid:</label><br>
        <input type="text" id="name" name="name" value="{{ old('name', $student->user->name) }}" required style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="email">Email Login:</label><br>
        <input type="email" id="email" name="email" value="{{ old('email', $student->user->email) }}" required style="width: 300px;">
    </div>
    <br>
    
    @if($student->user->role === 'siswa')
        <div>
            <label for="nisn">NISN Murid:</label><br>
            <input type="text" id="nisn" name="nisn" value="{{ old('nisn', $student->nisn) }}" required style="width: 300px;">
        </div>
        <br>
        <div>
            <label for="classroom">Nama Kelas (Tingkat):</label><br>
            <input type="text" id="classroom" name="classroom" value="{{ old('classroom', $student->classroom->name ?? '') }}" required style="width: 300px;">
        </div>
        <br>
        <div>
            <label for="major">Jurusan:</label><br>
            <input type="text" id="major" name="major" value="{{ old('major', $student->classroom->major->name ?? '') }}" required style="width: 300px;">
        </div>
        <br>
    @elseif($student->user->role === 'mahasiswa')
        <div>
            <label for="nim">NIM Mahasiswa:</label><br>
            <input type="text" id="nim" name="nim" value="{{ old('nim', $student->nim) }}" required style="width: 300px;">
        </div>
        <br>
        <div>
            <label for="semester">Semester Saat Ini (Angka):</label><br>
            <input type="number" id="semester" name="semester" min="1" max="14" value="{{ old('semester', $student->semester) }}" required style="width: 300px;">
        </div>
        <br>
        <div>
            <label for="major">Jurusan:</label><br>
            <input type="text" id="major" name="major" value="{{ old('major', $student->classroom->major->name ?? '') }}" required style="width: 300px;">
        </div>
        <br>
    @endif

    <div>
        <label for="password">Password Baru (Kosongkan jika tidak ingin diubah):</label><br>
        <input type="password" id="password" name="password" style="width: 300px;" placeholder="Minimal 8 karakter">
    </div>
    <br>
    <div>
        <label for="password_confirmation">Konfirmasi Password Baru:</label><br>
        <input type="password" id="password_confirmation" name="password_confirmation" style="width: 300px;">
    </div>
    <br>
    <button type="submit">Simpan Perubahan</button>
    <a href="/dashboard" style="margin-left: 10px;">Batal</a>
</form>
@endsection
