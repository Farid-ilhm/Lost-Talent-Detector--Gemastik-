@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 640px; margin: 24px auto;">
    <div style="text-align: center; margin-bottom: 24px;">
        <div class="app-brand-icon" style="margin: 0 auto 12px; width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-dark);">Pendaftaran Akun Baru</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Pilih tipe akun Anda dan lengkapi data profil untuk memulai analisis bakat.</p>
    </div>

    <form action="/register" method="POST">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="role" class="form-label">Role / Tipe Pengguna:</label>
            <select id="role" name="role" class="form-control" required style="font-weight: 600;">
                <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa (Sekolah Menengah)</option>
                <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa (Perguruan Tinggi)</option>
                <option value="umum" {{ old('role') == 'umum' ? 'selected' : '' }}>Pengguna Umum / Mandiri</option>
                <option value="institusi" {{ old('role') == 'institusi' ? 'selected' : '' }}>Institusi (Sekolah/Universitas)</option>
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="name" id="label-name" class="form-label">Nama Lengkap:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Masukkan nama lengkap">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="email" class="form-label">Alamat Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="nama@email.com">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="phone" class="form-label">No. Telepon / WhatsApp:</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08123456789">
        </div>

        <!-- Conditional Fields Managed by register.js -->
        <div id="npsn-container" style="display: none; margin-bottom: 16px;">
            <label for="npsn" class="form-label">NPSN (Nomor Pokok Sekolah Nasional):</label>
            <input type="text" id="npsn" name="npsn" class="form-control" value="{{ old('npsn') }}" placeholder="Contoh: 10801234">
        </div>

        <div id="school-container" style="display: none; margin-bottom: 16px;">
            <label for="institution_id" class="form-label">Pilih Sekolah / Universitas Anda:</label>
            <select id="institution_id" name="institution_id" class="form-control">
                <option value="">-- Pengguna Umum (Mandiri) --</option>
                @foreach($institutions as $inst)
                    <option value="{{ $inst->id }}">{{ $inst->user->name }} (NPSN: {{ $inst->npsn }})</option>
                @endforeach
            </select>
        </div>

        <div id="nisn-container" style="display: none; margin-bottom: 16px;">
            <label for="nisn" class="form-label">NISN (Nomor Induk Siswa Nasional):</label>
            <input type="text" id="nisn" name="nisn" class="form-control" value="{{ old('nisn') }}" placeholder="Contoh: 0051234567">
        </div>

        <div id="class-container" style="display: none; margin-bottom: 16px;">
            <label for="classroom" class="form-label">Nama Kelas (Tingkat/Kelompok):</label>
            <input type="text" id="classroom" name="classroom" class="form-control" value="{{ old('classroom') }}" placeholder="Contoh: XII IPA 1, 10-B">
        </div>

        <div id="major-container" style="display: none; margin-bottom: 16px;">
            <label for="major" class="form-label">Jurusan:</label>
            <input type="text" id="major" name="major" class="form-control" value="{{ old('major') }}" placeholder="Contoh: IPA, Teknik Informatika">
        </div>

        <div id="nim-container" style="display: none; margin-bottom: 16px;">
            <label for="nim" class="form-label">NIM (Nomor Induk Mahasiswa):</label>
            <input type="text" id="nim" name="nim" class="form-control" value="{{ old('nim') }}" placeholder="Contoh: 2108101010">
        </div>

        <div id="semester-container" style="display: none; margin-bottom: 16px;">
            <label for="semester" class="form-label">Semester Saat Ini:</label>
            <input type="number" id="semester" name="semester" min="1" max="14" class="form-control" value="{{ old('semester') }}" placeholder="1 - 14">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="password" class="form-label">Password (Minimal 8 karakter):</label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
        </div>

        <div style="margin-bottom: 24px;">
            <label for="password_confirmation" class="form-label">Konfirmasi Password:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn-primary-dark" style="width: 100%; padding: 14px;">
            <i class="fa-solid fa-user-plus"></i> Daftar Akun Sekarang
        </button>
    </form>

    <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
        Sudah memiliki akun? <a href="/login" style="color: var(--text-dark); font-weight: 700; text-decoration: none;">Masuk disini</a>
    </div>
</div>

<script src="{{ asset('js/register.js') }}"></script>
@endsection
