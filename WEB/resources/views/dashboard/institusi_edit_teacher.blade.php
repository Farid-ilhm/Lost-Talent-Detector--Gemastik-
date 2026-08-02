@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 600px; margin: 24px auto;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.5rem; font-weight: 800;">Edit Data Guru: {{ $teacher->user->name }}</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Perbarui data profil & mata pelajaran pengajar.</p>
    </div>

    <form action="/institution/teachers/{{ $teacher->id }}/update" method="POST">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="name" class="form-label">Nama Guru:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $teacher->user->name) }}" required>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="email" class="form-label">Email Login Guru:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $teacher->user->email) }}" required>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="nip" class="form-label">NIP Guru:</label>
            <input type="text" id="nip" name="nip" class="form-control" value="{{ old('nip', $teacher->nip) }}">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="subject" class="form-label">Mata Pelajaran Utama:</label>
            <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject', $teacher->subject) }}">
        </div>

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
