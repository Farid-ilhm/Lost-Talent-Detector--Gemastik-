@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 600px; margin: 24px auto;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.5rem; font-weight: 800;">Edit Data Murid: {{ $student->user->name }}</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Perbarui data profil & akademik siswa/mahasiswa.</p>
    </div>

    <form action="/teacher/students/{{ $student->id }}/update" method="POST">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="name" class="form-label">Nama Murid:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $student->user->name) }}" required>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="email" class="form-label">Email Login:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $student->user->email) }}" required>
        </div>

        @if($student->user->role === 'siswa')
            <div style="margin-bottom: 16px;">
                <label for="nisn" class="form-label">NISN Murid:</label>
                <input type="text" id="nisn" name="nisn" class="form-control" value="{{ old('nisn', $student->nisn) }}" required>
            </div>
            <div style="margin-bottom: 16px;">
                <label for="classroom" class="form-label">Nama Kelas (Tingkat):</label>
                <input type="text" id="classroom" name="classroom" class="form-control" value="{{ old('classroom', $student->classroom->name ?? '') }}" required>
            </div>
            <div style="margin-bottom: 16px;">
                <label for="major" class="form-label">Jurusan:</label>
                <input type="text" id="major" name="major" class="form-control" value="{{ old('major', $student->classroom->major->name ?? '') }}" required>
            </div>
        @elseif($student->user->role === 'mahasiswa')
            <div style="margin-bottom: 16px;">
                <label for="nim" class="form-label">NIM Mahasiswa:</label>
                <input type="text" id="nim" name="nim" class="form-control" value="{{ old('nim', $student->nim) }}" required>
            </div>
            <div style="margin-bottom: 16px;">
                <label for="semester" class="form-label">Semester Saat Ini (Angka):</label>
                <input type="number" id="semester" name="semester" min="1" max="14" class="form-control" value="{{ old('semester', $student->semester) }}" required>
            </div>
            <div style="margin-bottom: 16px;">
                <label for="major" class="form-label">Jurusan:</label>
                <input type="text" id="major" name="major" class="form-control" value="{{ old('major', $student->classroom->major->name ?? '') }}" required>
            </div>
        @endif

        <div style="margin-bottom: 16px;">
            <label for="password" class="form-label">Password Baru (Kosongkan jika tidak diubah):</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 8 karakter">
        </div>

        <div style="margin-bottom: 24px;">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
        </div>

        <div style="display: flex; gap: 12px; align-items: center;">
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
            <a href="/dashboard" class="btn-primary-dark" style="background-color: var(--bg-pill); color: var(--text-dark);">Batal</a>
        </div>
    </form>
</div>
@endsection
