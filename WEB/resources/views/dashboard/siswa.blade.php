@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Invest in your talent</h1>
        <p class="hero-subtitle">Dashboard Siswa & Pengguna Mandiri - Lost Talent Detector</p>
    </div>

    <!-- Category Filter Pills (Matching Photo Navigation) -->
    <div class="category-pills-container">
        <button type="button" class="cat-pill active" onclick="switchTab('profil')" id="btn-profil">
            <span class="cat-pill-icon"><i class="fa-solid fa-user" style="font-size: 0.75rem;"></i></span>
            1. Data Profil
        </button>
        <button type="button" class="cat-pill" onclick="switchTab('akademik')" id="btn-akademik">
            <span class="cat-pill-icon"><i class="fa-solid fa-graduation-cap" style="font-size: 0.75rem;"></i></span>
            2. Nilai Akademik
        </button>
        <button type="button" class="cat-pill" onclick="switchTab('prestasi')" id="btn-prestasi">
            <span class="cat-pill-icon"><i class="fa-solid fa-award" style="font-size: 0.75rem;"></i></span>
            3. Prestasi & Sertifikat
        </button>
        <button type="button" class="cat-pill" onclick="switchTab('riasec')" id="btn-riasec">
            <span class="cat-pill-icon"><i class="fa-solid fa-compass" style="font-size: 0.75rem;"></i></span>
            4. Tes RIASEC
        </button>
        <button type="button" class="cat-pill" onclick="switchTab('ai')" id="btn-ai">
            <span class="cat-pill-icon"><i class="fa-solid fa-wand-magic-sparkles" style="font-size: 0.75rem;"></i></span>
            5. Hasil Analisis AI
        </button>
    </div>
</div>

<!-- Overview Stats Cards (4 Pastel Cards Grid) -->
<div class="cards-grid" style="margin-top: 8px; margin-bottom: 8px;">
    <!-- Card 1: Pink - User Info -->
    <div class="pastel-card card-pink">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-user-graduate"></i> Status Siswa</span>
            <span class="card-rating-badge"><i class="fa-solid fa-check" style="color: #10B981;"></i> Aktif</span>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Nama Pengguna</div>
            <h3 class="card-title" style="margin-bottom: 4px;">{{ $student->user->name }}</h3>
            <div style="font-size: 0.85rem; color: var(--text-muted);">{{ $student->institution->user->name ?? 'Pengguna Mandiri' }}</div>
        </div>
        <div class="card-footer-row" style="margin-top: 12px;">
            <span class="card-meta-text">{{ $student->user->email }}</span>
        </div>
    </div>

    <!-- Card 2: Sand - Academic Grades Count -->
    <div class="pastel-card card-sand">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-book"></i> Nilai Akademik</span>
            <span class="card-rating-badge"><i class="fa-solid fa-star" style="color: #F59E0B;"></i> {{ $grades->count() }} Mapel</span>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Rata-rata Nilai</div>
            <h3 class="card-title" style="margin-bottom: 4px;">
                {{ $grades->isEmpty() ? 'Belum Ada' : number_format($grades->avg('score'), 1) }}
            </h3>
            <div style="font-size: 0.85rem; color: var(--text-muted);">Riwayat Rapor Terdaftar</div>
        </div>
        <div class="card-footer-row" style="margin-top: 12px;">
            <span class="card-meta-text">Semester {{ $student->semester ?? 'Aktif' }}</span>
        </div>
    </div>

    <!-- Card 3: Lavender - Achievements Count -->
    <div class="pastel-card card-lavender">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-trophy"></i> Prestasi</span>
            <span class="card-rating-badge"><i class="fa-solid fa-certificate"></i> {{ $achievements->count() }} Total</span>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Status Sertifikat</div>
            <h3 class="card-title" style="margin-bottom: 4px;">
                {{ $achievements->where('is_verified', true)->count() }} Terverifikasi
            </h3>
            <div style="font-size: 0.85rem; color: var(--text-muted);">Piagam & Penghargaan</div>
        </div>
        <div class="card-footer-row" style="margin-top: 12px;">
            <span class="card-meta-text">{{ $achievements->where('is_verified', false)->count() }} Menunggu</span>
        </div>
    </div>

    <!-- Card 4: Mint - AI Talent Prediction -->
    <div class="pastel-card card-mint">
        <div class="card-header-row">
            <span class="card-cat-badge"><i class="fa-solid fa-brain"></i> Prediksi Bakat AI</span>
            <span class="card-rating-badge" style="background-color: #FEF3C7; color: #92400E;">⚡ AI Ready</span>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Bakat Dominan</div>
            <h3 class="card-title" style="margin-bottom: 4px;">
                {{ $aiAnalysis ? $aiAnalysis->primary_talent : 'Belum Dianalisis' }}
            </h3>
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                {{ $aiAnalysis ? 'Confidence: '.$aiAnalysis->confidence_score.'%' : 'Klik Hasil Analisis AI' }}
            </div>
        </div>
        <div class="card-footer-row" style="margin-top: 12px;">
            <span class="card-meta-text">Model: {{ $aiAnalysis->model_version ?? 'v1.0-gemini' }}</span>
        </div>
    </div>
</div>

<!-- PAGE 1: PROFIL PENGGUNA -->
<div id="page-profil" class="page-section content-box">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-id-card"></i> 1. Data Profil Pengguna</h3>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px; background-color: #FAFAF8; padding: 20px; border-radius: 20px; border: 1px solid var(--border-subtle);">
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Nama Lengkap</div>
            <div style="font-weight: 700; font-size: 1.05rem;">{{ $student->user->name }}</div>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Email</div>
            <div style="font-weight: 600;">{{ $student->user->email }}</div>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">No. Telepon</div>
            <div style="font-weight: 600;">{{ $student->user->phone ?? '-' }}</div>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Institusi</div>
            <div style="font-weight: 600;">{{ $student->institution->user->name ?? 'Pengguna Umum (Mandiri)' }}</div>
        </div>

        @if($student->user->role === 'siswa')
            <div>
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">NISN</div>
                <div style="font-weight: 600;">{{ $student->nisn ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Kelas</div>
                <div style="font-weight: 600;">{{ $student->classroom->name ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Jurusan</div>
                <div style="font-weight: 600;">{{ $student->classroom->major->name ?? '-' }}</div>
            </div>
        @elseif($student->user->role === 'mahasiswa')
            <div>
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">NIM</div>
                <div style="font-weight: 600;">{{ $student->nim ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Semester Saat Ini</div>
                <div style="font-weight: 600;">Semester {{ $student->semester ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Jurusan / Program Studi</div>
                <div style="font-weight: 600;">{{ $student->classroom->major->name ?? '-' }}</div>
            </div>
        @endif

        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Hobi Saat Ini</div>
            <div style="font-weight: 600;">{{ $student->hobbies ? implode(', ', $student->hobbies) : '-' }}</div>
        </div>
        <div>
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">Minat saat ini</div>
            <div style="font-weight: 600;">{{ $student->interests ? implode(', ', $student->interests) : '-' }}</div>
        </div>
    </div>

    <!-- Form update minat & hobi -->
    <form action="/student/interests" method="POST">
        @csrf
        <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 16px;">Update Minat & Hobi</h4>
        <div style="margin-bottom: 16px;">
            <label for="hobbies" class="form-label">Hobi (Pisahkan dengan koma):</label>
            <input type="text" id="hobbies" name="hobbies" class="form-control" value="{{ $student->hobbies ? implode(', ', $student->hobbies) : '' }}" placeholder="contoh: Coding, Robotik, Basket, Desain Grafis">
        </div>
        <div style="margin-bottom: 20px;">
            <label for="interests" class="form-label">Minat / Ketertarikan (Pisahkan dengan koma):</label>
            <input type="text" id="interests" name="interests" class="form-control" value="{{ $student->interests ? implode(', ', $student->interests) : '' }}" placeholder="contoh: Artificial Intelligence, Data Science, UI/UX Design">
        </div>
        <button type="submit" class="btn-primary-dark">
            <i class="fa-solid fa-floppy-disk"></i> Simpan Profil Minat
        </button>
    </form>
</div>

<!-- PAGE 2: NILAI AKADEMIK -->
<div id="page-akademik" class="page-section content-box" style="display: none;">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-graduation-cap"></i> 2. Riwayat Nilai Akademik (Rapor)</h3>
    </div>

    @if($grades->isEmpty())
        <div class="alert-custom alert-warning">
            <i class="fa-solid fa-circle-info"></i>
            <span>Belum ada nilai akademik yang diinput.</span>
        </div>
    @else
        <div class="table-responsive" style="margin-bottom: 24px;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th>Mata Pelajaran / Mata Kuliah</th>
                        <th>Nilai (Score)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grades as $grade)
                        <tr>
                            <td><span class="card-cat-badge">Semester {{ $grade->semester }}</span></td>
                            <td><strong>{{ $grade->subject_name }}</strong></td>
                            <td><strong style="color: #059669; font-size: 1rem;">{{ $grade->score }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($student->institution_id === null)
        <div style="background-color: #FAFAF8; padding: 20px; border-radius: 20px; border: 1px solid var(--border-subtle);">
            <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 16px;">Input Nilai Akademik Mandiri</h4>
            <form action="/student/grades" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label for="semester" class="form-label">Semester:</label>
                        <select id="semester" name="semester" class="form-control" required>
                            <option value="1">Semester 1</option>
                            <option value="2">Semester 2</option>
                            <option value="3">Semester 3</option>
                            <option value="4">Semester 4</option>
                            <option value="5">Semester 5</option>
                            <option value="6">Semester 6</option>
                        </select>
                    </div>
                    <div>
                        <label for="subject_name" class="form-label">Nama Mapel / Matkul:</label>
                        <input type="text" id="subject_name" name="subject_name" class="form-control" required placeholder="Contoh: Matematika, Algoritma, Fisika" list="student-subject-suggestions">
                        <datalist id="student-subject-suggestions">
                            <option value="Matematika">
                            <option value="Informatika">
                            <option value="Fisika">
                            <option value="Bahasa Inggris">
                        </datalist>
                    </div>
                    <div>
                        <label for="score" class="form-label">Nilai (0 - 100):</label>
                        <input type="number" id="score" name="score" step="0.01" min="0" max="100" class="form-control" required placeholder="85.50">
                    </div>
                </div>
                <button type="submit" class="btn-primary-dark">
                    <i class="fa-solid fa-plus"></i> Simpan Nilai Mandiri
                </button>
            </form>
        </div>
    @endif
</div>

<!-- PAGE 3: PRESTASI & SERTIFIKAT -->
<div id="page-prestasi" class="page-section content-box" style="display: none;">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-award"></i> 3. Riwayat Prestasi & Sertifikat</h3>
    </div>

    @if($achievements->isEmpty())
        <div class="alert-custom alert-warning">
            <i class="fa-solid fa-circle-info"></i>
            <span>Belum ada sertifikat prestasi yang diajukan.</span>
        </div>
    @else
        <div class="table-responsive" style="margin-bottom: 24px;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Judul Prestasi</th>
                        <th>Kategori</th>
                        <th>Tingkat</th>
                        <th>Peringkat</th>
                        <th>Bukti</th>
                        <th>Status Verifikasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($achievements as $ach)
                        <tr>
                            <td><strong>{{ $ach->title }}</strong></td>
                            <td><span class="card-cat-badge">{{ ucfirst($ach->category) }}</span></td>
                            <td>{{ ucfirst($ach->level) }}</td>
                            <td>{{ $ach->rank }}</td>
                            <td>
                                @if($ach->certificate_path)
                                    <a href="{{ asset($ach->certificate_path) }}" target="_blank" style="color: #2563EB; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-file-image"></i> Lihat
                                    </a>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($ach->is_verified)
                                    <span class="card-rating-badge" style="background-color: #D1F5E4; color: #065F46;">
                                        <i class="fa-solid fa-circle-check"></i> Terverifikasi
                                    </span>
                                @else
                                    <span class="card-rating-badge" style="background-color: #FEF3C7; color: #92400E;">
                                        <i class="fa-solid fa-clock"></i> Menunggu Verifikasi
                                    </span>
                                @endif
                            </td>
                            <td>
                                <form action="/student/achievements/{{ $ach->id }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sertifikat prestasi ini?')" style="margin: 0; display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-primary-dark" style="background-color: #FBE3E2; color: #991B1B; border: none; padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 10px; cursor: pointer; transition: all 0.2s;">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div style="background-color: #FAFAF8; padding: 20px; border-radius: 20px; border: 1px solid var(--border-subtle);">
        <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 16px;">Ajukan Sertifikat Prestasi Baru</h4>
        
        @if($student->institution_id)
            <div style="background-color: #FFFBEB; border: 1px solid #FDE68A; color: #B45309; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-info" style="font-size: 1.1rem; color: #D97706;"></i>
                <span>Sertifikat prestasi ini memerlukan persetujuan dan verifikasi oleh Guru atau Dosen Anda sebelum dinyatakan sah.</span>
            </div>
        @endif

        <form action="/student/achievements" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="title" class="form-label">Nama / Judul Prestasi:</label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="contoh: Juara 1 Lomba Coding Gemastik">
                </div>
                <div>
                    <label for="category" class="form-label">Kategori Prestasi:</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="teknologi">Teknologi / IT</option>
                        <option value="sains">Sains / Matematika</option>
                        <option value="olahraga">Olahraga</option>
                        <option value="seni">Seni & Desain</option>
                        <option value="keagamaan">Keagamaan</option>
                        <option value="akademik">Akademik Lainnya</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label for="level" class="form-label">Tingkat Kompetisi:</label>
                    <select id="level" name="level" class="form-control" required>
                        <option value="sekolah">Sekolah</option>
                        <option value="kecamatan">Kecamatan</option>
                        <option value="kabupaten">Kabupaten/Kota</option>
                        <option value="provinsi">Provinsi</option>
                        <option value="nasional">Nasional</option>
                        <option value="internasional">Internasional</option>
                    </select>
                </div>
                <div>
                    <label for="rank" class="form-label">Peringkat / Juara:</label>
                    <input type="text" id="rank" name="rank" class="form-control" required placeholder="contoh: Juara 1, Harapan 2">
                </div>
            </div>
            <div style="margin-bottom: 16px;">
                <label for="certificate" class="form-label">Unggah Bukti Sertifikat (Gambar / PDF):</label>
                <input type="file" id="certificate" name="certificate" class="form-control" accept="image/*,application/pdf" required style="padding: 8px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label for="description" class="form-label">Deskripsi Singkat:</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Deskripsikan kompetisi secara singkat..."></textarea>
            </div>
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-paper-plane"></i> Ajukan Prestasi
            </button>
        </form>
    </div>
</div>

<!-- PAGE 4: TES RIASEC -->
<div id="page-riasec" class="page-section content-box" style="display: none;">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-compass"></i> 4. Kuesioner Minat Bakat RIASEC</h3>
    </div>

    @if($testResult)
        <div class="pastel-card card-lavender" style="margin-bottom: 24px; min-height: auto;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Hasil Tes RIASEC Terakhir</div>
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--text-dark);">Dominan: {{ $testResult->dominant_category }}</h3>
                </div>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    @foreach($testResult->scores as $category => $score)
                        <span class="card-rating-badge" style="padding: 6px 12px; background-color: #FFFFFF;">
                            {{ $category }}: <strong>{{ $score }}%</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($activeTest)
        <div class="alert-custom alert-success" style="margin-bottom: 20px;">
            <i class="fa-solid fa-lightbulb"></i>
            <span><strong>Petunjuk:</strong> Tes ini tidak memiliki jawaban benar/salah. Pilih skala 1 (Sangat Tidak Suka) s/d 5 (Sangat Suka) sesuai minat aktual Anda.</span>
        </div>

        <form action="/student/test" method="POST">
            @csrf
            <input type="hidden" name="test_id" value="{{ $activeTest->id }}">
            <h4 style="font-size: 1.1rem; font-weight: 800;">{{ $activeTest->title }}</h4>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">{{ $activeTest->description }}</p>

            <div class="table-responsive" style="margin-bottom: 24px;">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Pernyataan</th>
                            <th style="width: 50px; text-align: center;">1</th>
                            <th style="width: 50px; text-align: center;">2</th>
                            <th style="width: 50px; text-align: center;">3</th>
                            <th style="width: 50px; text-align: center;">4</th>
                            <th style="width: 50px; text-align: center;">5</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeTest->questions as $q)
                            <tr>
                                <td><strong>{{ $q->question_text }}</strong></td>
                                <td style="text-align: center;"><input type="radio" name="answers[{{ $q->id }}]" value="1" required></td>
                                <td style="text-align: center;"><input type="radio" name="answers[{{ $q->id }}]" value="2"></td>
                                <td style="text-align: center;"><input type="radio" name="answers[{{ $q->id }}]" value="3"></td>
                                <td style="text-align: center;"><input type="radio" name="answers[{{ $q->id }}]" value="4"></td>
                                <td style="text-align: center;"><input type="radio" name="answers[{{ $q->id }}]" value="5"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-paper-plane"></i> Kirim Jawaban Tes
            </button>
        </form>
    @else
        <p style="color: var(--text-muted);">Tidak ada kuesioner minat bakat aktif saat ini.</p>
    @endif
</div>

<!-- PAGE 5: HASIL AI -->
<div id="page-ai" class="page-section content-box" style="display: none;">
    <div class="section-title-row" style="margin-top: 0; align-items: center;">
        <h3 class="section-title"><i class="fa-solid fa-wand-magic-sparkles"></i> 5. Hasil Analisis AI Detektor Bakat</h3>
        <form action="/student/analyze" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-rotate"></i> Mulai / Perbarui Analisis AI
            </button>
        </form>
    </div>

    @if($aiAnalysis)
        <div class="pastel-card card-mint" style="margin-top: 16px; min-height: auto;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <span class="card-cat-badge"><i class="fa-solid fa-brain"></i> Prediksi Bakat Utama</span>
                    <h2 style="font-size: 1.8rem; font-weight: 800; color: var(--text-dark); margin-top: 6px;">{{ $aiAnalysis->primary_talent }}</h2>
                </div>
                <div class="card-rating-badge" style="font-size: 1.1rem; padding: 8px 16px; background-color: #FFFFFF;">
                    Tingkat Kepercayaan: <strong>{{ $aiAnalysis->confidence_score }}%</strong>
                </div>
            </div>
        </div>

        @if($aiAnalysis->analisis_mendalam)
            <div style="background-color: #FAFAF8; padding: 24px; border-radius: 20px; border: 1px solid var(--border-subtle); margin-top: 20px;">
                <h4 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 12px; color: var(--text-dark); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-feather-pointed" style="color: #6366F1;"></i> Analisis Mendalam & Saran AI
                </h4>
                <p style="color: var(--text-dark); line-height: 1.7; font-size: 0.95rem; margin: 0; text-align: justify; white-space: pre-line;">
                    {{ $aiAnalysis->analisis_mendalam }}
                </p>
            </div>
        @endif

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            <!-- Supporting Talents -->
            <div style="background-color: #FAFAF8; padding: 20px; border-radius: 20px; border: 1px solid var(--border-subtle);">
                <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 12px;"><i class="fa-solid fa-star"></i> Bakat Pendukung</h4>
                <ul style="padding-left: 20px; color: var(--text-dark); line-height: 1.8;">
                    @foreach($aiAnalysis->supporting_talents as $st)
                        <li><strong>{{ $st['talent'] }}</strong> (Confidence: {{ $st['confidence'] }}%)</li>
                    @endforeach
                </ul>
            </div>

            <!-- Career Recommendations -->
            <div style="background-color: #FAFAF8; padding: 20px; border-radius: 20px; border: 1px solid var(--border-subtle);">
                <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 12px;"><i class="fa-solid fa-briefcase"></i> Rekomendasi Karir</h4>
                <ul style="padding-left: 20px; color: var(--text-dark); line-height: 1.8;">
                    @foreach($aiAnalysis->career_recommendations as $career)
                        <li>{{ $career }}</li>
                    @endforeach
                </ul>
            </div>

            <!-- Competition Recommendations -->
            <div style="background-color: #FAFAF8; padding: 20px; border-radius: 20px; border: 1px solid var(--border-subtle);">
                <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 12px;"><i class="fa-solid fa-trophy"></i> Rekomendasi Lomba (GEMASTIK)</h4>
                <ul style="padding-left: 20px; color: var(--text-dark); line-height: 1.8;">
                    @foreach($aiAnalysis->competition_recommendations as $comp)
                        <li>{{ $comp }}</li>
                    @endforeach
                </ul>
            </div>

            <!-- Explainable AI Reasoning -->
            <div style="background-color: #FAFAF8; padding: 20px; border-radius: 20px; border: 1px solid var(--border-subtle);">
                <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 12px;"><i class="fa-solid fa-circle-info"></i> Penjelasan Analisis (XAI)</h4>
                <ul style="padding-left: 20px; color: var(--text-dark); line-height: 1.8;">
                    @foreach($aiAnalysis->reasoning as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div style="margin-top: 16px; font-size: 0.85rem; color: var(--text-muted); text-align: right;">
            Model Version: {{ $aiAnalysis->model_version }} | Dianalisis Pada: {{ $aiAnalysis->analyzed_at }}
        </div>
    @else
        <div class="alert-custom alert-warning" style="margin-top: 16px;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>Belum ada hasil analisis AI. Silakan klik tombol <strong>"Mulai / Perbarui Analisis AI"</strong> di atas.</span>
        </div>
    @endif
</div>

<script src="{{ asset('js/siswa_dashboard.js') }}"></script>
@endsection
