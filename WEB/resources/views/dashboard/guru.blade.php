@extends('layouts.app')

@section('content')
<h2>Dashboard Guru / Pembina</h2>
<hr>

<!-- Profil Guru -->
<h3>Profil Guru</h3>
<p>Nama Guru: <strong>{{ $teacher->user->name }}</strong></p>
<p>NIP: {{ $teacher->nip ?? '-' }}</p>
<p>Mata Pelajaran Diampu: {{ $teacher->subject ?? '-' }}</p>

<hr>

<!-- Verifikasi Prestasi Siswa -->
<h3>1. Verifikasi Sertifikat Prestasi Siswa</h3>
@if($pendingAchievements->isEmpty())
    <p>Tidak ada pengajuan prestasi siswa yang perlu diverifikasi.</p>
@else
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Nama Murid</th>
                <th>Judul Prestasi</th>
                <th>Kategori</th>
                <th>Tingkat</th>
                <th>Peringkat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingAchievements as $ach)
                <tr>
                    <td>{{ $ach->student->user->name }}</td>
                    <td>{{ $ach->title }}</td>
                    <td>{{ $ach->category }}</td>
                    <td>{{ $ach->level }}</td>
                    <td>{{ $ach->rank }}</td>
                    <td>
                        <form action="/teacher/achievements/{{ $ach->id }}/verify" method="POST">
                            @csrf
                            <button type="submit">Verifikasi / Setujui</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<hr>

<!-- Kelola Nilai & Catatan Murid -->
<h3>2. Kelola Nilai Rapor & Catatan Murid</h3>
@if($students->isEmpty())
    <p>Belum ada murid yang terdaftar di institusi Anda.</p>
@else
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>NISN</th>
                <th>Kelas</th>
                <th>Input Nilai</th>
                <th>Input Catatan BK / Perkembangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $st)
                <tr>
                    <td>{{ $st->user->name }}</td>
                    <td>{{ $st->nisn ?? '-' }}</td>
                    <td>{{ $st->classroom->name ?? '-' }}</td>
                    <td>
                        <!-- Form Input Nilai -->
                        <form action="/teacher/grades" method="POST" style="margin: 0;">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $st->id }}">
                            
                            <label>Sem:</label>
                            <select name="semester" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                            </select>
                            
                            <label>Mapel:</label>
                            <input type="text" name="subject_name" required placeholder="Nama Mapel / Matkul" list="subjects-{{ $st->id }}" style="width: 150px;">
                            <datalist id="subjects-{{ $st->id }}">
                                <option value="Matematika">
                                <option value="Informatika">
                                <option value="Fisika">
                                <option value="Bahasa Inggris">
                            </datalist>
                            
                            <label>Nilai:</label>
                            <input type="number" name="score" step="0.01" min="0" max="100" style="width: 50px;" required>
                            
                            <button type="submit">Simpan</button>
                        </form>
                    </td>
                    <td>
                        <!-- Form Input Catatan -->
                        <form action="/teacher/notes" method="POST" style="margin: 0;">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $st->id }}">
                            
                            <input type="text" name="notes" placeholder="Catatan kepribadian/minat..." required>
                            <button type="submit">Simpan</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
