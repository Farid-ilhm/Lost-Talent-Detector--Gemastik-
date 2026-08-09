<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Baru - Lost Talent Detector</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app_custom.css') }}?v=1.2">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F7F5F0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        .register-wrapper {
            width: 100%;
            max-width: 960px;
            margin: 40px 20px;
            box-sizing: border-box;
        }
        .register-card {
            background-color: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.04);
            border: 1px solid #E2DDD5;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }
        .register-header {
            text-align: center;
            padding: 32px 32px 16px 32px;
            border-bottom: 1px solid #FAF8F5;
        }
        .back-link {
            position: absolute;
            top: 24px;
            left: 24px;
            font-size: 0.88rem;
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
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 24px 32px 32px 32px;
            gap: 32px;
            box-sizing: border-box;
        }
        .form-column {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
            box-sizing: border-box;
        }
        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #1C1917;
            margin-bottom: 6px;
            display: block;
        }
        .form-control {
            width: 100%;
            max-width: 100%;
            border: 1px solid #E2DDD5;
            background-color: #FAF8F5;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            color: #1C1917;
            box-sizing: border-box;
            outline: none;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #A8A29E;
            background-color: #FFFFFF;
        }
        .password-toggle-wrapper {
            position: relative;
        }
        .password-toggle-btn {
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
            height: 100%;
        }
        .btn-submit {
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
            width: 100%;
            margin-top: 12px;
        }
        .btn-submit:hover {
            background-color: #334155;
        }
        .default-info-card {
            background-color: #FAF8F5;
            border: 1.5px dashed #E2DDD5;
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            color: #78716C;
            height: 100%;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }
        
        /* Responsive */
        @media (max-width: 900px) {
            .form-grid {
                grid-template-columns: 1fr;
                padding: 16px 20px 24px 20px;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-wrapper" style="position: relative;">
        
        <div class="register-card">
            <!-- Header -->
            <div class="register-header">
                <a href="/" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                </a>
                <div style="margin: 0 auto 12px; width: 48px; height: 48px;">
                    <img src="{{ asset('icon.png') }}" alt="Lost Talent Logo" style="width: 100%; height: 100%; border-radius: 14px; object-fit: cover;">
                </div>
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1C1917; margin: 0; letter-spacing: -0.02em;">Pendaftaran Akun Baru</h2>
                <p style="color: #78716C; font-size: 0.9rem; margin-top: 6px; margin-bottom: 0;">Lengkapi data profil Anda untuk memulai deteksi analisis bakat AI.</p>
            </div>

            <!-- Alerts -->
            @if ($errors->any())
                <div style="background-color: #FEE2E2; color: #991B1B; padding: 14px 24px; font-size: 0.88rem; font-weight: 600; border-bottom: 1px solid #FCA5A5;">
                    <ul style="margin: 0; padding-left: 16px; list-style-type: disc;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="/register" method="POST" style="margin: 0;">
                @csrf
                <div class="form-grid">
                    
                    <!-- Left Column: Primary Data -->
                    <div class="form-column">
                        <div style="font-size: 0.95rem; font-weight: 800; color: #1C1917; border-bottom: 1px solid #FAF8F5; padding-bottom: 8px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-user-tag" style="color: #D97706;"></i> Data Akun & Kontak
                        </div>

                        <div>
                            <label for="role" class="form-label">Role / Tipe Pengguna:</label>
                            <select id="role" name="role" class="form-control" required style="font-weight: 600; cursor: pointer;">
                                <option value="siswa" {{ old('role') == 'siswa' ? 'selected' : '' }}>Siswa (Sekolah Menengah)</option>
                                <option value="mahasiswa" {{ old('role') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa (Perguruan Tinggi)</option>
                                <option value="umum" {{ old('role') == 'umum' ? 'selected' : '' }}>Pengguna Umum / Mandiri</option>
                                <option value="institusi" {{ old('role') == 'institusi' ? 'selected' : '' }}>Institusi (Sekolah/Universitas)</option>
                            </select>
                        </div>

                        <div>
                            <label for="name" id="label-name" class="form-label">Nama Lengkap:</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Masukkan nama lengkap">
                        </div>

                        <div>
                            <label for="email" class="form-label">Alamat Email:</label>
                            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="nama@email.com">
                        </div>

                        <div>
                            <label for="phone" class="form-label">No. Telepon / WhatsApp:</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08123456789">
                        </div>

                        <div>
                            <label for="password" class="form-label">Password (Minimal 8 karakter):</label>
                            <div class="password-toggle-wrapper">
                                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" title="Lihat Password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">Konfirmasi Password:</label>
                            <div class="password-toggle-wrapper">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="••••••••">
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password_confirmation', this)" title="Lihat Password">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Secondary/Role-Specific Data -->
                    <div class="form-column">
                        <div style="font-size: 0.95rem; font-weight: 800; color: #1C1917; border-bottom: 1px solid #FAF8F5; padding-bottom: 8px; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-building-user" style="color: #D97706;"></i> Detail Profil Tambahan
                        </div>

                        <!-- Fallback message for Pengguna Umum -->
                        <div id="default-info-container" style="display: none;">
                            <div class="default-info-card">
                                <i class="fa-solid fa-user-check" style="font-size: 2.5rem; color: #D97706;"></i>
                                <div>
                                    <strong style="display: block; color: #1C1917; font-size: 0.95rem; margin-bottom: 6px;">Pengguna Umum (Mandiri)</strong>
                                    <span style="font-size: 0.82rem; line-height: 1.5; display: block;">Anda mendaftar sebagai pengguna mandiri. Tidak ada data profil tambahan yang diperlukan. Silakan langsung klik tombol daftar di bawah.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Conditional Fields managed by register.js -->
                        <div id="npsn-container" style="display: none;">
                            <label for="npsn" class="form-label">NPSN (Nomor Pokok Sekolah Nasional):</label>
                            <input type="text" id="npsn" name="npsn" class="form-control" value="{{ old('npsn') }}" placeholder="Contoh: 10801234">
                        </div>

                        <div id="address-container" style="display: none;">
                            <label for="address" class="form-label">Alamat Lengkap Institusi:</label>
                            <textarea id="address" name="address" class="form-control" rows="4" placeholder="Tuliskan alamat jalan, RT/RW, kecamatan, kota/kabupaten..." style="resize: none;"></textarea>
                        </div>

                        <div id="school-container" style="display: none;">
                            <label for="institution_id" class="form-label">Pilih Sekolah / Universitas Anda:</label>
                            <select id="institution_id" name="institution_id" class="form-control">
                                <option value="">-- Pengguna Umum (Mandiri) --</option>
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->user->name }} (NPSN: {{ $inst->npsn }})</option>
                                @endforeach
                            </select>
                            <span style="font-size: 0.75rem; color: #78716C; margin-top: 6px; display: block; line-height: 1.4;">
                                <i class="fa-solid fa-circle-info" style="color: #D97706; margin-right: 4px;"></i>
                                Jika nama sekolah/universitas Anda belum terdaftar dalam pilihan, silakan pilih opsi <strong>-- Pengguna Umum (Mandiri) --</strong>.
                            </span>
                        </div>

                        <div id="nisn-container" style="display: none;">
                            <label for="nisn" class="form-label">NISN (Nomor Induk Siswa Nasional):</label>
                            <input type="text" id="nisn" name="nisn" class="form-control" value="{{ old('nisn') }}" placeholder="Contoh: 0051234567">
                        </div>

                        <div id="class-container" style="display: none;">
                            <label for="classroom" class="form-label">Nama Kelas (Tingkat/Kelompok):</label>
                            <input type="text" id="classroom" name="classroom" class="form-control" value="{{ old('classroom') }}" placeholder="Contoh: XII IPA 1, 10-B">
                        </div>

                        <div id="major-container" style="display: none;">
                            <label for="major" class="form-label">Jurusan:</label>
                            <input type="text" id="major" name="major" class="form-control" value="{{ old('major') }}" placeholder="Contoh: IPA, Teknik Informatika">
                        </div>

                        <div id="nim-container" style="display: none;">
                            <label for="nim" class="form-label">NIM (Nomor Induk Mahasiswa):</label>
                            <input type="text" id="nim" name="nim" class="form-control" value="{{ old('nim') }}" placeholder="Contoh: 2108101010">
                        </div>

                        <div id="semester-container" style="display: none;">
                            <label for="semester" class="form-label">Semester Saat Ini:</label>
                            <input type="number" id="semester" name="semester" min="1" max="14" class="form-control" value="{{ old('semester') }}" placeholder="1 - 14">
                        </div>
                    </div>

                </div>

                <!-- Submit Button Area (Centered bottom) -->
                <div style="padding: 0 32px 32px 32px; box-sizing: border-box;">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-user-plus"></i> Daftar Akun Sekarang
                    </button>
                    <div style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: #78716C;">
                        Sudah memiliki akun? <a href="/login" style="color: #1C1917; font-weight: 700; text-decoration: none; border-bottom: 1.5px solid #1C1917;">Masuk disini</a>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <script src="{{ asset('js/register.js') }}"></script>
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

        // Handle show/hide of the general user fallback card
        function updateDefaultInfo() {
            const role = document.getElementById('role').value;
            const defaultInfo = document.getElementById('default-info-container');
            if (role === 'umum') {
                defaultInfo.style.display = 'block';
            } else {
                defaultInfo.style.display = 'none';
            }
        }
        document.getElementById('role').addEventListener('change', updateDefaultInfo);
        window.addEventListener('load', updateDefaultInfo);
    </script>
</body>
</html>
