<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Talent Detector - GEMASTIK</title>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/app_custom.css') }}">
    <style>
        .landing-hero-card {
            background-color: #FFFFFF;
            border-radius: var(--radius-lg);
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            border: 1px solid var(--border-subtle);
        }
    </style>
</head>
<body style="background-color: var(--bg-main);">

    <!-- 1. TOP NAVBAR LANDING PAGE -->
    <header class="landing-navbar" style="border-bottom: 1px solid var(--border-subtle); background-color: rgba(247, 245, 240, 0.9); backdrop-filter: blur(10px); position: sticky; top: 0; z-index: 1000;">
        <a href="/" class="nav-brand">
            <div class="nav-brand-logo">
                <i class="fa-solid fa-brain"></i>
            </div>
            <div>
                <span style="display: block; line-height: 1.2;">Lost Talent Detector</span>
                <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Intelijen Bakat AI</span>
            </div>
        </a>

        <ul class="nav-links">
            <li><a href="#beranda">Beranda</a></li>
            <li><a href="#penjelasan">Web & Mobile App</a></li>
            <li><a href="#fitur">Fitur & Modul</a></li>
            <li><a href="/login">Masuk / Login</a></li>
        </ul>

        <div class="nav-auth-buttons">
            <a href="/login" class="btn-primary-dark" style="padding: 10px 22px; font-size: 0.9rem;">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk (Login)
            </a>
            <a href="/register" class="btn-primary-dark" style="padding: 10px 22px; font-size: 0.9rem; background-color: var(--bg-pill); color: var(--text-dark);">
                <i class="fa-solid fa-user-plus"></i> Pendaftaran
            </a>
        </div>
    </header>

    <!-- CONTAINER UTAMA LANDING PAGE -->
    <main style="max-width: 1200px; margin: 0 auto; padding: 40px 24px; display: flex; flex-direction: column; gap: 48px;">

        <!-- 2. HERO SECTION PUBLIK -->
        <section id="beranda" class="landing-hero-card" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px; align-items: center; background: linear-gradient(135deg, #FFFFFF 0%, #FAF8F5 100%);">
            <div>
                <h1 style="font-size: 2.8rem; font-weight: 800; line-height: 1.15; color: var(--text-dark); margin-bottom: 20px;">
                    Deteksi Bakat Terpendam & Kembangkan Masa Depan
                </h1>

                <p style="font-size: 1.1rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 32px;">
                    Platform terpadu analisis potensi diri siswa & mahasiswa melalui integrasi nilai akademik, sertifikat prestasi terverifikasi, dan tes kuesioner minat psikometrik RIASEC berbasis Artificial Intelligence.
                </p>

                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <a href="/login" class="btn-primary-dark" style="padding: 16px 32px; font-size: 1rem;">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
                    </a>
                    <a href="/register" class="btn-primary-dark" style="padding: 16px 32px; font-size: 1rem; background-color: var(--bg-pill); color: var(--text-dark);">
                        <i class="fa-solid fa-user-plus"></i> Daftar Akun Baru
                    </a>
                </div>
            </div>

            <!-- Quick Overview Card Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="pastel-card card-pink" style="padding: 20px; min-height: 140px;">
                    <div style="font-size: 1.8rem; margin-bottom: 8px;"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div style="font-weight: 800; font-size: 1rem;">Nilai Rapor</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Analisis Akademik</div>
                </div>

                <div class="pastel-card card-sand" style="padding: 20px; min-height: 140px;">
                    <div style="font-size: 1.8rem; margin-bottom: 8px;"><i class="fa-solid fa-award"></i></div>
                    <div style="font-weight: 800; font-size: 1rem;">Sertifikat</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Verifikasi Prestasi</div>
                </div>

                <div class="pastel-card card-lavender" style="padding: 20px; min-height: 140px;">
                    <div style="font-size: 1.8rem; margin-bottom: 8px;"><i class="fa-solid fa-compass"></i></div>
                    <div style="font-weight: 800; font-size: 1rem;">Tes RIASEC</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">6 Dimensi Minat</div>
                </div>

                <div class="pastel-card card-mint" style="padding: 20px; min-height: 140px;">
                    <div style="font-size: 1.8rem; margin-bottom: 8px;"><i class="fa-solid fa-brain"></i></div>
                    <div style="font-weight: 800; font-size: 1rem;">AI Engine</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Rekomendasi Bakat</div>
                </div>
            </div>
        </section>

        <!-- 3. SECTION PENJELASAN PERAN APLIKASI WEB VS MOBILE -->
        <section id="penjelasan" class="landing-hero-card" style="background-color: #FAFAF8;">
            <div style="text-align: center; max-width: 650px; margin: 0 auto 36px auto;">
                <h2 style="font-size: 2rem; font-weight: 800; color: var(--text-dark);">Penjelasan Fungsi Aplikasi Web & Mobile App</h2>
                <p style="color: var(--text-muted); font-size: 1rem; margin-top: 8px;">
                    Platform Lost Talent Detector membagi peran spesifik antara versi Web dan Aplikasi Mobile untuk memberikan pengalaman terbaik bagi pengguna.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 28px;">
                <!-- Card Peran Web -->
                <div class="pastel-card card-sand" style="padding: 32px; min-height: auto; border-radius: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                        <div class="app-brand-icon" style="width: 56px; height: 56px; font-size: 1.5rem;">
                            <i class="fa-solid fa-desktop"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--text-dark);">Peran Aplikasi Web</h3>
                            <span class="card-cat-badge">Untuk Sekolah, Guru Pembina, & Admin</span>
                        </div>
                    </div>
                    <ul style="padding-left: 20px; font-size: 0.95rem; color: var(--text-dark); line-height: 1.8;">
                        <li style="margin-bottom: 8px;"><strong>Verifikasi Sertifikat Prestasi</strong>: Guru dan pihak sekolah memeriksa & memverifikasi keaslian piagam lomba murid.</li>
                        <li style="margin-bottom: 8px;"><strong>Input & Analisis Rapor Kolektif</strong>: Pengelolaan data kelas dan input nilai mata pelajaran unggulan masal.</li>
                        <li style="margin-bottom: 8px;"><strong>Dashboard Super Admin</strong>: Pengelolaan master data kompetisi GEMASTIK dan persetujuan institusi terdaftar.</li>
                        <li><strong>Laporan Analisis Terpusat</strong>: Visualisasi grafik perkembangan bakat seluruh siswa sekolah.</li>
                    </ul>
                </div>

                <!-- Card Peran Mobile -->
                <div class="pastel-card card-mint" style="padding: 32px; min-height: auto; border-radius: 24px;">
                    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                        <div class="app-brand-icon" style="width: 56px; height: 56px; font-size: 1.5rem; background-color: #059669;">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <div>
                            <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--text-dark);">Peran Aplikasi Mobile</h3>
                            <span class="card-cat-badge" style="background-color: #FFFFFF;">Untuk Siswa & Mahasiswa</span>
                        </div>
                    </div>
                    <ul style="padding-left: 20px; font-size: 0.95rem; color: var(--text-dark); line-height: 1.8;">
                        <li style="margin-bottom: 8px;"><strong>Kuesioner Minat RIASEC Interaktif</strong>: Siswa dapat mengisi tes kuesioner minat bakat 6 dimensi kapan saja melalui smartphone.</li>
                        <li style="margin-bottom: 8px;"><strong>Upload Sertifikat Cepat</strong>: Mengambil foto piagam dan mengunggahnya secara langsung via kamera ponsel.</li>
                        <li style="margin-bottom: 8px;"><strong>Hasil Prediksi Bakat AI Real-time</strong>: Menerima rekomendasi karir & potensi bakat secara instan.</li>
                        <li><strong>Notifikasi Lomba GEMASTIK</strong>: Pemberitahuan rekomendasi kompetisi dan pengingat batas waktu pendaftaran.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- 4. SECTION FITUR & MODUL UTAMA -->
        <section id="fitur">
            <div style="text-align: center; max-width: 650px; margin: 0 auto 36px auto;">
                <h2 style="font-size: 2rem; font-weight: 800; color: var(--text-dark);">Modul Analisis Bakat Terpadu</h2>
                <p style="color: var(--text-muted); font-size: 1rem; margin-top: 8px;">
                    Empat pilar utama yang diproses oleh AI Engine untuk menghasilkan analisis intelijen bakat yang akurat.
                </p>
            </div>

            <div class="cards-grid">
                <!-- Card 1: Pink -->
                <div class="pastel-card card-pink" style="padding: 28px;">
                    <div class="card-header-row">
                        <span class="card-cat-badge"><i class="fa-solid fa-graduation-cap"></i> Nilai Akademik</span>
                    </div>
                    <h3 class="card-title" style="font-size: 1.25rem;">Analisis Nilai Rapor & Mata Pelajaran Unggulan</h3>
                    <div class="card-footer-row">
                        <span class="card-meta-text">Deteksi Konsistensi Akademik</span>
                        <span class="card-cat-badge" style="background-color: #FFFFFF;">Data Rapor</span>
                    </div>
                </div>

                <!-- Card 2: Sand -->
                <div class="pastel-card card-sand" style="padding: 28px;">
                    <div class="card-header-row">
                        <span class="card-cat-badge"><i class="fa-solid fa-award"></i> Piagam Prestasi</span>
                    </div>
                    <h3 class="card-title" style="font-size: 1.25rem;">Verifikasi Sertifikat & Portfolio Kompetisi</h3>
                    <div class="card-footer-row">
                        <span class="card-meta-text">Tingkat Sekolah s/d Internasional</span>
                        <span class="card-cat-badge" style="background-color: #FFFFFF;">Terverifikasi</span>
                    </div>
                </div>

                <!-- Card 3: Lavender -->
                <div class="pastel-card card-lavender" style="padding: 28px;">
                    <div class="card-header-row">
                        <span class="card-cat-badge"><i class="fa-solid fa-compass"></i> Tes RIASEC</span>
                    </div>
                    <h3 class="card-title" style="font-size: 1.25rem;">Kuesioner Minat Bakat Tipe RIASEC (6 Dimensi)</h3>
                    <div class="card-footer-row">
                        <span class="card-meta-text">Profil Minat Psikometrik</span>
                        <span class="card-cat-badge" style="background-color: #FFFFFF;">6 Skala</span>
                    </div>
                </div>

                <!-- Card 4: Mint -->
                <div class="pastel-card card-mint" style="padding: 28px;">
                    <div class="card-header-row">
                        <span class="card-cat-badge"><i class="fa-solid fa-brain"></i> AI Engine</span>
                    </div>
                    <h3 class="card-title" style="font-size: 1.25rem;">Prediksi Bakat Utama & Rekomendasi Karir / Lomba</h3>
                    <div class="card-footer-row">
                        <span class="card-meta-text">Explainable AI (XAI) System</span>
                        <span class="card-cat-badge" style="background-color: #FFFFFF;">AI Precision</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. CALL TO ACTION BOX -->
        <section class="landing-hero-card" style="background-color: #1C1917; color: #FFFFFF; padding: 40px 48px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 24px;">
                <div style="max-width: 600px;">
                    <h3 style="font-size: 1.8rem; font-weight: 800; color: #FFFFFF;">Siap Menemukan Potensi Bakat Terbaik Anda?</h3>
                    <p style="color: #A8A29E; font-size: 1.05rem; margin-top: 8px;">Dapatkan analisis intelijen bakat lengkap & rekomendasi perlombaan secara gratis sekarang.</p>
                </div>
                <div style="display: flex; gap: 14px;">
                    <a href="/login" class="btn-primary-dark" style="background-color: #FFFFFF; color: #1C1917; padding: 14px 28px; font-size: 1rem;">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
                    </a>
                    <a href="/register" class="btn-primary-dark" style="background-color: #334155; color: #FFFFFF; padding: 14px 28px; font-size: 1rem;">
                        <i class="fa-solid fa-user-plus"></i> Daftar Akun
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- 6. FOOTER -->
    <footer style="border-top: 1px solid var(--border-subtle); padding: 32px 24px; background-color: #FFFFFF; font-size: 0.9rem; color: var(--text-muted);">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>&copy; 2026 Lost Talent Detector.</div>
            <div style="display: flex; gap: 20px;">
                <a href="#beranda" style="color: var(--text-muted); text-decoration: none;">Beranda</a>
                <a href="#penjelasan" style="color: var(--text-muted); text-decoration: none;">Peran Web & Mobile</a>
                <a href="#fitur" style="color: var(--text-muted); text-decoration: none;">Fitur Utama</a>
                <a href="/login" style="color: var(--text-muted); text-decoration: none; font-weight: 700; color: var(--text-dark);">Login</a>
                <a href="/register" style="color: var(--text-muted); text-decoration: none; font-weight: 700; color: var(--text-dark);">Register</a>
            </div>
        </div>
    </footer>

</body>
</html>
