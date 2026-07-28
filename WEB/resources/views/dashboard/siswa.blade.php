@extends('layouts.app')

@section('content')
<h2>Dashboard Siswa / Pengguna Mandiri</h2>
<hr>

<!-- 1. Profil Pengguna -->
<h3>1. Data Profil</h3>
<p>Nama: <strong>{{ $student->user->name }}</strong></p>
<p>Email: {{ $student->user->email }}</p>
<p>No. Telp: {{ $student->user->phone ?? '-' }}</p>
<p>Asal Institusi: {{ $student->institution->name ?? 'Pengguna Umum (Mandiri)' }}</p>
<p>Kelas: {{ $student->classroom->name ?? '-' }}</p>
<p>Kepribadian MBTI: {{ $student->personality ?? '-' }}</p>
<p>Hobi saat ini: {{ $student->hobbies ? implode(', ', $student->hobbies) : '-' }}</p>
<p>Minat saat ini: {{ $student->interests ? implode(', ', $student->interests) : '-' }}</p>

<!-- Form update minat & hobi -->
<form action="/student/interests" method="POST">
    @csrf
    <h4>Update Minat, Hobi & MBTI</h4>
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
    <div>
        <label for="personality">Kepribadian / MBTI:</label><br>
        <input type="text" id="personality" name="personality" value="{{ $student->personality }}" placeholder="contoh: INTJ">
    </div>
    <br>
    <button type="submit">Simpan Profil Minat</button>
</form>

<hr>

<!-- 2. Nilai Akademik -->
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

<hr>

<!-- 3. Prestasi -->
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

<hr>

<!-- 4. Tes Minat Bakat RIASEC -->
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

<hr>

<!-- 5. Analisis AI Detektor Bakat -->
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
@endsection
