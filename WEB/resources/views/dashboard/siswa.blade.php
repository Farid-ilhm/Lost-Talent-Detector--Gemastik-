@extends('layouts.app')

@section('content')
<h2>Dashboard Siswa / Pengguna Mandiri</h2>

<!-- Navigasi Halaman / Pages -->
<div style="margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border: 1px solid #ddd;">
    <strong>Navigasi Halaman:</strong> 
    <button type="button" onclick="switchTab('profil')" id="btn-profil" style="font-weight: bold;">1. Data Profil</button> | 
    <button type="button" onclick="switchTab('akademik')" id="btn-akademik">2. Nilai Akademik</button> | 
    <button type="button" onclick="switchTab('prestasi')" id="btn-prestasi">3. Prestasi & Sertifikat</button> | 
    <button type="button" onclick="switchTab('riasec')" id="btn-riasec">4. Tes RIASEC</button> | 
    <button type="button" onclick="switchTab('ai')" id="btn-ai">5. Hasil Analisis AI</button>
</div>

<!-- PAGE 1: PROFIL PENGGUNA -->
<div id="page-profil" class="page-section">
    <h3>1. Data Profil</h3>
    <p>Nama: <strong>{{ $student->user->name }}</strong></p>
    <p>Email: {{ $student->user->email }}</p>
    <p>No. Telp: {{ $student->user->phone ?? '-' }}</p>
    <p>Asal Institusi: {{ $student->institution->user->name ?? 'Pengguna Umum (Mandiri)' }}</p>
    @if($student->user->role === 'siswa')
        <p>NISN: {{ $student->nisn ?? '-' }}</p>
        <p>Kelas: {{ $student->classroom->name ?? '-' }}</p>
        <p>Jurusan: {{ $student->classroom->major->name ?? '-' }}</p>
    @elseif($student->user->role === 'mahasiswa')
        <p>NIM: {{ $student->nim ?? '-' }}</p>
        <p>Semester Saat Ini: {{ $student->semester ?? '-' }}</p>
        <p>Jurusan: {{ $student->classroom->major->name ?? '-' }}</p>
    @endif
    <p>Hobi saat ini: {{ $student->hobbies ? implode(', ', $student->hobbies) : '-' }}</p>
    <p>Minat saat ini: {{ $student->interests ? implode(', ', $student->interests) : '-' }}</p>

    <!-- Form update minat & hobi -->
    <form action="/student/interests" method="POST">
        @csrf
        <h4>Update Minat & Hobi</h4>
        <div>
            <label for="hobbies">Hobi (Pisahkan dengan koma):</label><br>
            <input type="text" id="hobbies" name="hobbies" value="{{ $student->hobbies ? implode(', ', $student->hobbies) : '' }}" placeholder="contoh: Coding, Robotik, Basket">
        </div>
        <br>
        <div>
            <label for="interests">Minat / Ketertarikan (Pisahkan dengan koma):</label><br>
            <input type="text" id="interests" name="interests" value="{{ $student->interests ? implode(', ', $student->interests) : '' }}" placeholder="contoh: AI, Data Science">
        </div>
        <br>
        <button type="submit">Simpan Profil Minat</button>
    </form>
</div>

<!-- PAGE 2: NILAI AKADEMIK -->
<div id="page-akademik" class="page-section" style="display: none;">
    <h3>2. Riwayat Nilai Akademik (Rapor)</h3>
    @if($grades->isEmpty())
        <p>Belum ada nilai akademik yang diinput oleh Guru/Admin.</p>
    @else
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>Mata Pelajaran</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grades as $grade)
                    <tr>
                        <td>{{ $grade->semester }}</td>
                        <td>{{ $grade->subject_name }}</td>
                        <td>{{ $grade->score }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($student->institution_id === null)
        <br>
        <form action="/student/grades" method="POST">
            @csrf
            <h4>Input Nilai Akademik Mandiri</h4>
            <div>
                <label for="semester">Semester:</label>
                <select id="semester" name="semester" required>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                </select>
            </div>
            <br>
            <div>
                <label for="subject_name">Nama Mapel / Matkul:</label><br>
                <input type="text" id="subject_name" name="subject_name" required placeholder="Contoh: Matematika, Algoritma, Fisika" list="student-subject-suggestions">
                <datalist id="student-subject-suggestions">
                    <option value="Matematika">
                    <option value="Informatika">
                    <option value="Fisika">
                    <option value="Bahasa Inggris">
                </datalist>
            </div>
            <br>
            <div>
                <label for="score">Nilai (0 - 100):</label><br>
                <input type="number" id="score" name="score" step="0.01" min="0" max="100" required>
            </div>
            <br>
            <button type="submit">Simpan Nilai Mandiri</button>
        </form>
    @endif
</div>

<!-- PAGE 3: PRESTASI & SERTIFIKAT -->
<div id="page-prestasi" class="page-section" style="display: none;">
    <h3>3. Riwayat Prestasi & Sertifikat</h3>
    @if($achievements->isEmpty())
        <p>Belum ada sertifikat prestasi yang diajukan.</p>
    @else
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Judul Prestasi</th>
                    <th>Kategori</th>
                    <th>Tingkat</th>
                    <th>Peringkat</th>
                    <th>Status Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($achievements as $ach)
                    <tr>
                        <td>{{ $ach->title }}</td>
                        <td>{{ $ach->category }}</td>
                        <td>{{ $ach->level }}</td>
                        <td>{{ $ach->rank }}</td>
                        <td>
                            @if($ach->is_verified)
                                <strong style="color: green;">Terverifikasi</strong>
                            @else
                                <span style="color: orange;">Menunggu Verifikasi</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <br>
    <!-- Form upload prestasi -->
    <form action="/student/achievements" method="POST">
        @csrf
        <h4>Ajukan Sertifikat Prestasi Baru</h4>
        <div>
            <label for="title">Nama / Judul Prestasi:</label><br>
            <input type="text" id="title" name="title" required placeholder="contoh: Juara 1 Lomba Coding">
        </div>
        <br>
        <div>
            <label for="category">Kategori Prestasi:</label><br>
            <select id="category" name="category" required>
                <option value="teknologi">Teknologi / IT</option>
                <option value="sains">Sains / Matematika</option>
                <option value="olahraga">Olahraga</option>
                <option value="seni">Seni & Desain</option>
                <option value="keagamaan">Keagamaan</option>
                <option value="akademik">Akademik Lainnya</option>
                <option value="lainnya">Lainnya</option>
            </select>
        </div>
        <br>
        <div>
            <label for="level">Tingkat Kompetisi:</label><br>
            <select id="level" name="level" required>
                <option value="sekolah">Sekolah</option>
                <option value="kecamatan">Kecamatan</option>
                <option value="kabupaten">Kabupaten/Kota</option>
                <option value="provinsi">Provinsi</option>
                <option value="nasional">Nasional</option>
                <option value="internasional">Internasional</option>
            </select>
        </div>
        <br>
        <div>
            <label for="rank">Peringkat / Juara:</label><br>
            <input type="text" id="rank" name="rank" required placeholder="contoh: Juara 1, Harapan 2, Partisipan">
        </div>
        <br>
        <div>
            <label for="description">Deskripsi Singkat:</label><br>
            <textarea id="description" name="description" placeholder="Deskripsikan lomba secara singkat..."></textarea>
        </div>
        <br>
        <button type="submit">Ajukan Prestasi</button>
    </form>
</div>

<!-- PAGE 4: TES RIASEC -->
<div id="page-riasec" class="page-section" style="display: none;">
    <h3>4. Kuesioner Minat Bakat RIASEC</h3>
    @if($testResult)
        <p>Anda sudah menyelesaikan Tes RIASEC.</p>
        <p>Kategori Dominan: <strong>{{ $testResult->dominant_category }}</strong></p>
        <p>Skor Hasil Tes:</p>
        <ul>
            @foreach($testResult->scores as $category => $score)
                <li>{{ $category }}: {{ $score }}%</li>
            @endforeach
        </ul>
        <p><em>*Anda dapat mengisi ulang tes di bawah ini untuk memperbarui minat Anda.</em></p>
    @endif

    @if($activeTest)
        <form action="/student/test" method="POST">
            @csrf
            <input type="hidden" name="test_id" value="{{ $activeTest->id }}">
            <h4>{{ $activeTest->title }}</h4>
            <p>{{ $activeTest->description }}</p>
            <p><em>Pilih skala penilaian dari 1 (Sangat Tidak Suka) sampai 5 (Sangat Suka) untuk setiap pernyataan berikut:</em></p>
            
            <table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th>Pernyataan</th>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeTest->questions as $q)
                        <tr>
                            <td>{{ $q->question_text }} (Kategori: {{ $q->category }})</td>
                            <td><input type="radio" name="answers[{{ $q->id }}]" value="1" required></td>
                            <td><input type="radio" name="answers[{{ $q->id }}]" value="2"></td>
                            <td><input type="radio" name="answers[{{ $q->id }}]" value="3"></td>
                            <td><input type="radio" name="answers[{{ $q->id }}]" value="4"></td>
                            <td><input type="radio" name="answers[{{ $q->id }}]" value="5"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <br>
            <button type="submit">Kirim Jawaban Tes</button>
        </form>
    @else
        <p>Tidak ada kuesioner minat bakat aktif saat ini.</p>
    @endif
</div>

<!-- PAGE 5: HASIL AI -->
<div id="page-ai" class="page-section" style="display: none;">
    <h3>5. Hasil Analisis AI Detektor Bakat</h3>
    <form action="/student/analyze" method="POST">
        @csrf
        <button type="submit" style="font-size: 16px; padding: 10px;">Mulai / Perbarui Analisis AI</button>
    </form>
    <br>

    @if($aiAnalysis)
        <div style="background-color: #f1f8ff; border: 1px solid #c8e1ff; padding: 15px;">
            <h4>Prediksi Bakat Utama: <span style="color: blue; font-size: 20px;">{{ $aiAnalysis->primary_talent }}</span></h4>
            <p>Tingkat Kepercayaan AI (Confidence Score): <strong>{{ $aiAnalysis->confidence_score }}%</strong></p>
            
            <p>Bakat Pendukung Lainnya:</p>
            <ul>
                @foreach($aiAnalysis->supporting_talents as $st)
                    <li>{{ $st['talent'] }} (Kepercayaan: {{ $st['confidence'] }}%)</li>
                @endforeach
            </ul>

            <p>Penjelasan Hasil Analisis (Explainable AI):</p>
            <ul>
                @foreach($aiAnalysis->reasoning as $reason)
                    <li>{{ $reason }}</li>
                @endforeach
            </ul>

            <p>Rekomendasi Karir Masa Depan:</p>
            <ul>
                @foreach($aiAnalysis->career_recommendations as $career)
                    <li>{{ $career }}</li>
                @endforeach
            </ul>

            <p>Rekomendasi Perlombaan (GEMASTIK, dll):</p>
            <ul>
                @foreach($aiAnalysis->competition_recommendations as $comp)
                    <li>{{ $comp }}</li>
                @endforeach
            </ul>

            <p>Rekomendasi Ekstrakurikuler Relevan:</p>
            <ul>
                @if(empty($aiAnalysis->extracurricular_recommendations))
                    <li>-</li>
                @else
                    @foreach($aiAnalysis->extracurricular_recommendations as $extra)
                        <li>{{ $extra }}</li>
                    @endforeach
                @endif
            </ul>

            <p>Target Pengembangan Diri Selanjutnya:</p>
            <ul>
                @foreach($aiAnalysis->development_targets as $target)
                    <li>{{ $target }}</li>
                @endforeach
            </ul>

            <p><small>Model AI Versi: {{ $aiAnalysis->model_version }} | Dianalisis Pada: {{ $aiAnalysis->analyzed_at }}</small></p>
        </div>
    @else
        <p>Belum ada hasil analisis AI. Silakan klik tombol <strong>"Mulai / Perbarui Analisis AI"</strong> di atas.</p>
    @endif
</div>

<script src="{{ asset('js/siswa_dashboard.js') }}"></script>
@endsection
