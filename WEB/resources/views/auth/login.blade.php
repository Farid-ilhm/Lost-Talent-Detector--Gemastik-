<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Lost Talent Detector</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('LOGO APK.jpg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app_custom.css') }}">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F7F5F0;
        }
        .split-layout {
            display: flex;
            height: 100vh;
            width: 100vw;
        }
        .banner-side {
            flex: 1;
            background: linear-gradient(135deg, #1C1917 0%, #44403C 100%);
            color: #FFFFFF;
            padding: 64px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }
        .form-side {
            width: 550px;
            background-color: #FFFFFF;
            padding: 64px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
            position: relative;
            box-shadow: -10px 0 30px rgba(0,0,0,0.02);
            border-left: 1px solid #E2DDD5;
        }
        .back-link {
            position: absolute;
            top: 40px;
            right: 40px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #1C1917;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
        }
        .back-link:hover {
            opacity: 0.7;
        }
        .form-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }
        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #1C1917;
            margin-bottom: 6px;
            display: block;
        }
        .form-input {
            width: 100%;
            border: 1px solid #E2DDD5;
            background-color: #FAF8F5;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.95rem;
            color: #1C1917;
            box-sizing: border-box;
            outline: none;
            transition: all 0.2s;
        }
        .form-input:focus {
            border-color: #A8A29E;
            background-color: #FFFFFF;
        }
        .password-wrapper {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #78716C;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-submit {
            width: 100%;
            background-color: #1C1917;
            color: #FFFFFF;
            font-weight: 800;
            border-radius: 12px;
            padding: 16px;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-submit:hover {
            background-color: #334155;
        }
        
        /* Responsive */
        @media (max-width: 900px) {
            .banner-side {
                display: none;
            }
            .form-side {
                width: 100%;
                border-left: none;
            }
        }
    </style>
</head>
<body>
    <div class="split-layout">
        <!-- Left Side: Banner -->
        <div class="banner-side">
            <div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 64px;">
                    <img src="{{ asset('LOGO APK.jpg') }}" alt="Lost Talent Logo" style="width: 48px; height: 48px; border-radius: 14px; object-fit: cover;">
                    <span style="font-size: 1.3rem; font-weight: 800; letter-spacing: -0.02em; color: #FFFFFF;">Lost Talent Detector</span>
                </div>
                
                <h3 style="font-size: 2.4rem; font-weight: 800; line-height: 1.2; color: #FFFFFF; margin-top: 0; margin-bottom: 20px; letter-spacing: -0.02em;">
                    Deteksi Bakat Terpendam Murid & Mahasiswa
                </h3>
                
                <p style="color: #A8A29E; font-size: 1rem; line-height: 1.6; margin: 0; max-width: 500px;">
                    Platform analisis potensi diri berbasis kecerdasan buatan (AI) terintegrasi rapor akademik, sertifikat prestasi, dan tes minat psikometrik RIASEC.
                </p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 16px; border-top: 1px solid #292524; padding-top: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; color: #E7E5E4;">
                    <i class="fa-solid fa-circle-check" style="color: #EAB308; font-size: 1.1rem;"></i>
                    <span>Analisis Akademik Terpadu</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; color: #E7E5E4;">
                    <i class="fa-solid fa-circle-check" style="color: #EAB308; font-size: 1.1rem;"></i>
                    <span>Verifikasi Piagam & Sertifikat Murid</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; font-size: 0.95rem; color: #E7E5E4;">
                    <i class="fa-solid fa-circle-check" style="color: #EAB308; font-size: 1.1rem;"></i>
                    <span>Rekomendasi Karir & Lomba AI Akurat</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="form-side">
            <a href="/" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>
            
            <div class="form-container">
                <div style="margin-bottom: 36px;">
                    <!-- Show logo on mobile only -->
                    <div style="display: none; align-items: center; gap: 10px; margin-bottom: 24px;" class="mobile-logo">
                        <img src="{{ asset('LOGO APK.jpg') }}" alt="Lost Talent Logo" style="width: 40px; height: 40px; border-radius: 10px; object-fit: cover;">
                        <span style="font-size: 1.1rem; font-weight: 800; color: #1C1917;">Lost Talent Detector</span>
                    </div>
                    <h2 style="font-size: 2rem; font-weight: 800; color: #1C1917; margin: 0; letter-spacing: -0.02em;">Selamat Datang</h2>
                    <p style="color: #78716C; font-size: 0.95rem; margin-top: 8px; line-height: 1.4;">Masuk untuk mengelola dan memantau intelijen bakat siswa.</p>
                </div>

                <!-- Alert session status -->
                @if(session('success'))
                    <div style="background-color: #D1F5E4; color: #065F46; padding: 12px 16px; border-radius: 10px; font-size: 0.88rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div style="background-color: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 10px; font-size: 0.88rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div style="background-color: #FEE2E2; color: #991B1B; padding: 12px 16px; border-radius: 10px; font-size: 0.88rem; font-weight: 600; margin-bottom: 20px;">
                        <ul style="margin: 0; padding-left: 16px; list-style-type: disc;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="/login" method="POST" style="margin: 0;">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label for="email" class="form-label">Alamat Email</label>
                        <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}" required placeholder="nama@email.com">
                    </div>

                    <div style="margin-bottom: 28px;">
                        <label for="password" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-input" required placeholder="••••••••">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', this)" title="Lihat Password">
                                <i class="fa-solid fa-eye" style="font-size: 1.1rem;"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
                    </button>
                </form>

                <div style="text-align: center; margin-top: 32px; font-size: 0.95rem; color: #78716C;">
                    Belum memiliki akun? <a href="/register" style="color: #1C1917; font-weight: 700; text-decoration: none; border-bottom: 1.5px solid #1C1917;">Daftar akun baru</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile logo responsive show style helper -->
    <style>
        @media (max-width: 900px) {
            .mobile-logo {
                display: flex !important;
            }
            .back-link {
                top: 24px;
                right: 24px;
            }
            .form-side {
                padding: 40px 24px;
            }
        }
    </style>

    <script>
        function togglePasswordVisibility(inputId, buttonEl) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = buttonEl.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }
    </script>
</body>
</html>
