@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 540px; margin: 24px auto;">
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="margin: 0 auto 12px; width: 56px; height: 56px;">
            <img src="{{ asset('icon.png') }}" alt="Lost Talent Logo" style="width: 100%; height: 100%; border-radius: 16px; object-fit: cover;">
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
            <div class="password-toggle-wrapper">
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" title="Lihat Password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
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
