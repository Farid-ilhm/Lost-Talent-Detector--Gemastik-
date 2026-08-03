@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 540px; margin: 24px auto;">
    <div style="text-align: center; margin-bottom: 24px;">
        <div class="app-brand-icon" style="margin: 0 auto 12px; width: 56px; height: 56px; font-size: 1.5rem; background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; border-radius: 14px; display: flex; align-items: center; justify-content: center;">
            <i class="fa-solid fa-envelope-open-text"></i>
        </div>
        <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-dark);">Verifikasi Kode OTP</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
            Masukkan 6-digit kode verifikasi yang telah dikirim ke email 
            <strong style="color: var(--text-dark);">{{ $email ?? old('email') }}</strong>.
        </p>
    </div>

    @if (session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ session('warning') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('verify-otp.post') }}" method="POST">
        @csrf
        <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

        <div style="margin-bottom: 20px;">
            <label for="otp_code" class="form-label" style="text-align: center; display: block; font-weight: 700;">6-Digit Kode OTP:</label>
            <input type="text" id="otp_code" name="otp_code" class="form-control" maxlength="6" pattern="[0-9]{6}" required placeholder="123456" 
                   style="text-align: center; font-size: 1.8rem; font-weight: 800; letter-spacing: 12px; padding: 12px;" autofocus>
        </div>

        <button type="submit" class="btn-primary-dark" style="width: 100%; padding: 14px; font-size: 1rem;">
            <i class="fa-solid fa-shield-halved"></i> Verifikasi & Aktifkan Akun
        </button>
    </form>

    <form action="{{ route('resend-otp.post') }}" method="POST" style="margin-top: 16px; text-align: center;">
        @csrf
        <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px;">Tidak menerima email?</p>
        <button type="submit" style="background: none; border: none; color: #4f46e5; font-weight: 700; cursor: pointer; text-decoration: underline; font-size: 0.9rem;">
            <i class="fa-solid fa-rotate-right"></i> Kirim Ulang Kode OTP
        </button>
    </form>

    <div style="text-align: center; margin-top: 24px; font-size: 0.9rem; color: var(--text-muted); border-top: 1px solid #e2e8f0; padding-top: 16px;">
        Kembali ke <a href="/login" style="color: var(--text-dark); font-weight: 700; text-decoration: none;">Halaman Login</a>
    </div>
</div>
@endsection
