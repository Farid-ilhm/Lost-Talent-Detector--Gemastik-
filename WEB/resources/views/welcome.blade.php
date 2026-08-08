<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Talent Detector</title>
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
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
        .landing-navbar.nav-hidden {
            transform: translateY(-100%) !important;
        }
    </style>

</head>
<body style="background-color: var(--bg-main);">

    <!-- 1. TOP NAVBAR LANDING PAGE -->
    <header class="landing-navbar">
        <div class="landing-navbar-inner">
            <a href="/" class="nav-brand">
                <img src="{{ asset('icon.png') }}" alt="Lost Talent Detector Logo" style="width: 44px; height: 44px; border-radius: 14px; object-fit: cover;">
                <div>
                    <span style="display: block; line-height: 1.2;">Lost Talent Detector</span>
                    <span style="font-size: 0.7rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Intelijen Bakat AI</span>
                </div>
            </a>

            <ul class="nav-links">
                <li><a href="#beranda">Beranda</a></li>
                <li><a href="#penjelasan">Web & Mobile App</a></li>
                <li><a href="#fitur">Fitur & Modul</a></li>
                <li><a href="#kontak">Kontak</a></li>
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
        </div>
    </header>


    <!-- CONTAINER UTAMA LANDING PAGE -->
    <main style="max-width: 1200px; margin: 84px auto 0; padding: 40px 24px; display: flex; flex-direction: column; gap: 48px;">


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
                        <li style="margin-bottom: 8px;"><strong>Dashboard Super Admin</strong>: Pengelolaan master data kompetisi dan persetujuan institusi terdaftar.</li>
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

            <div class="cards-grid-2x2">
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

        <!-- SECTION KONTAK / HUBUNGI KAMI -->
        <section id="kontak" class="landing-hero-card" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: start; background-color: #FFFFFF; padding: 48px;">
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div>
                    <span style="background-color: #FEF3C7; color: #D97706; padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; display: inline-block; margin-bottom: 12px;">
                        KONTAK LOST TALENT
                    </span>
                    <h2 style="font-size: 2.2rem; font-weight: 800; color: var(--text-dark); margin: 0; line-height: 1.2;">
                        Hubungi Kantor Layanan Kami
                    </h2>
                    <p style="color: var(--text-muted); font-size: 1rem; margin-top: 12px; line-height: 1.6;">
                        Kami selalu siap menjawab pertanyaan Anda mengenai pendaftaran, kemitraan sekolah, atau kendala teknis sistem.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <!-- Item 1: Telepon/WA -->
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 44px; height: 44px; background-color: #1C1917; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-phone" style="color: #FFFFFF; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-dark);">Telepon / WhatsApp</div>
                            <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 2px;">+62 895-3243-54052 (Customer Service)</div>
                        </div>
                    </div>

                    <!-- Item 2: Email Resmi -->
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 44px; height: 44px; background-color: #1C1917; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fa-solid fa-envelope" style="color: #FFFFFF; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-dark);">Email Resmi</div>
                            <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 2px;">domiini1c.id@gmail.com</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background-color: #FFFFFF; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid var(--border-subtle); padding: 32px; width: 100%; box-sizing: border-box;">
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); margin-top: 0; margin-bottom: 24px;">Kirim Pesan Langsung</h3>
                
                <form id="contactForm" onsubmit="handleContactSubmit(event)">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Nama Lengkap</label>
                            <input type="text" name="name" required placeholder="Masukkan nama..." style="width: 100%; border: 1px solid #E7E5E4; background-color: #F5F5F4; border-radius: 12px; padding: 12px 16px; font-size: 0.95rem; color: #1C1917; box-sizing: border-box; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#A8A29E'" onblur="this.style.borderColor='#E7E5E4'">
                        </div>
                        <div>
                            <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Alamat Email</label>
                            <input type="email" name="email" required placeholder="nama@email.com" style="width: 100%; border: 1px solid #E7E5E4; background-color: #F5F5F4; border-radius: 12px; padding: 12px 16px; font-size: 0.95rem; color: #1C1917; box-sizing: border-box; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#A8A29E'" onblur="this.style.borderColor='#E7E5E4'">
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Subjek / Topik</label>
                        <div style="position: relative;">
                            <select name="subject" required style="width: 100%; border: 1px solid #EAB308; background-color: #FAF8F5; border-radius: 12px; padding: 12px 16px; font-size: 0.95rem; color: #1C1917; outline: none; appearance: none; -webkit-appearance: none; cursor: pointer; font-weight: 600;">
                                <option value="" disabled selected>Pilih Topik Pertanyaan</option>
                                <option value="Umum">Pertanyaan Umum</option>
                                <option value="Akun">Masalah Akun / Registrasi</option>
                                <option value="Kemitraan">Kemitraan Sekolah / Institusi</option>
                                <option value="Kendala">Kendala Teknis / Bug</option>
                            </select>
                            <div style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #1C1917;">
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.9rem;"></i>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">Isi Pesan Anda</label>
                        <textarea name="message" required placeholder="Tuliskan pesan Anda di sini..." style="width: 100%; border: 1px solid #E7E5E4; background-color: #F5F5F4; border-radius: 12px; padding: 12px 16px; font-size: 0.95rem; color: #1C1917; box-sizing: border-box; min-height: 120px; resize: vertical; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#A8A29E'" onblur="this.style.borderColor='#E7E5E4'"></textarea>
                    </div>

                    <button type="submit" style="width: 100%; background-color: #1C1917; color: #FFFFFF; font-weight: 800; border-radius: 12px; padding: 14px; text-transform: uppercase; border: none; letter-spacing: 0.05em; cursor: pointer; transition: background-color 0.2s; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.backgroundColor='#334155'" onmouseout="this.style.backgroundColor='#1C1917'">
                        KIRIM PESAN CS
                    </button>
                </form>
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
    <footer style="background-color: #1C1917; color: #E7E5E4; padding: 64px 24px 32px; border-top: 1px solid #292524; font-size: 0.9rem;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <!-- Top Footer Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 40px; margin-bottom: 48px;">
                
                <!-- Column 1: Brand Info -->
                <div style="display: flex; flex-direction: column; gap: 16px; min-width: 250px;">
                    <a href="/" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: #FFFFFF;">
                        <img src="{{ asset('icon.png') }}" alt="Lost Talent Logo" style="width: 48px; height: 48px; border-radius: 14px; object-fit: cover;">
                        <div>
                            <span style="display: block; font-size: 1.15rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.2;">Lost Talent Detector</span>
                            <span style="font-size: 0.7rem; font-weight: 700; color: #EAB308; text-transform: uppercase; letter-spacing: 0.05em;">Intelijen Bakat AI</span>
                        </div>
                    </a>
                    <p style="color: #A8A29E; line-height: 1.6; margin: 0; font-size: 0.85rem;">
                        Platform terintegrasi analisis potensi akademik dan minat bakat berbasis kecerdasan buatan (AI) untuk membantu mempersiapkan karir terbaik generasi penerus bangsa.
                    </p>

                </div>

                <!-- Column 2: Platform & Fitur -->
                <div>
                    <h4 style="color: #FFFFFF; font-size: 0.95rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0; margin-bottom: 20px;">Platform & Fitur</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                        <li><a href="#fitur" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Analisis Rapor Akademik</a></li>
                        <li><a href="#fitur" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Piagam Prestasi Murid</a></li>
                        <li><a href="#fitur" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Tes Minat RIASEC 6 Dimensi</a></li>
                        <li><a href="#fitur" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Prediksi Bakat & Karir AI</a></li>
                    </ul>
                </div>

                <!-- Column 3: Navigasi Utama -->
                <div>
                    <h4 style="color: #FFFFFF; font-size: 0.95rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0; margin-bottom: 20px;">Navigasi</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                        <li><a href="#beranda" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Beranda</a></li>
                        <li><a href="#penjelasan" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Web vs Mobile App</a></li>
                        <li><a href="#fitur" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Modul Analisis</a></li>
                        <li><a href="#kontak" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Hubungi CS Support</a></li>
                    </ul>
                </div>

                <!-- Column 4: Akses Cepat -->
                <div>
                    <h4 style="color: #FFFFFF; font-size: 0.95rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0; margin-bottom: 20px;">Akses Akun</h4>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                        <li><a href="/login" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Login (Masuk)</a></li>
                        <li><a href="/register" style="color: #A8A29E; text-decoration: none; font-size: 0.88rem; transition: color 0.2s;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#A8A29E'">Registrasi (Daftar Baru)</a></li>
                        <li style="margin-top: 8px;">
                            <span style="display: block; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #78716C; margin-bottom: 6px;">Unduh Aplikasi</span>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <span style="background-color: #292524; color: #FFFFFF; padding: 6px 12px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-mobile-screen"></i> Android APK
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Divider -->
            <div style="border-top: 1px solid #292524; padding-top: 32px; text-align: center; color: #78716C; font-size: 0.8rem;">
                &copy; 2026 Lost Talent Detector. Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>

