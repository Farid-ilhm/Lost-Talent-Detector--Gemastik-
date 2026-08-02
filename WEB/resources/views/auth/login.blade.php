@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 540px; margin: 24px auto;">
    <div style="text-align: center; margin-bottom: 24px;">
        <div class="app-brand-icon" style="margin: 0 auto 12px; width: 56px; height: 56px; font-size: 1.5rem;">
            <i class="fa-solid fa-shapes"></i>
        </div>
        <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-dark);">Selamat Datang Kembali</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">Masuk ke platform Lost Talent Detector untuk mengelola & mendeteksi potensi bakat.</p>
    </div>

    <form action="/login" method="POST">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="email" class="form-label">Alamat Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="nama@email.com">
        </div>

        <div style="margin-bottom: 24px;">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn-primary-dark" style="width: 100%; padding: 14px;">
            <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
        </button>
    </form>

    <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
        Belum memiliki akun? <a href="/register" style="color: var(--text-dark); font-weight: 700; text-decoration: none;">Daftar akun baru</a>
    </div>
</div>
@endsection
