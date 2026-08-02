@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Dashboard Guru & Pembina</h1>
        <p class="hero-subtitle">Kelola verifikasi prestasi, nilai rapor, & catatan pengembangan murid.</p>
    </div>
</div>

<!-- Teacher Profile Card -->
<div class="pastel-card card-sand" style="margin-top: 16px; min-height: auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <span class="card-cat-badge"><i class="fa-solid fa-chalkboard-user"></i> Profil Pengajar</span>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin-top: 4px;">{{ $teacher->user->name }}</h2>
            <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 2px;">
                NIP: <strong>{{ $teacher->nip ?? '-' }}</strong> | Mapel Diampu: <strong>{{ $teacher->subject ?? '-' }}</strong>
            </div>
        </div>
        <div class="card-rating-badge" style="background-color: #FFFFFF; font-size: 0.9rem; padding: 8px 16px;">
            <i class="fa-solid fa-graduation-cap"></i> {{ $students->count() }} Murid Terdaftar
        </div>
    </div>
</div>

<!-- 1. Verifikasi Prestasi Siswa -->
<div class="content-box" style="margin-top: 24px;">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-circle-check"></i> Verifikasi Sertifikat Prestasi Siswa</h3>
    </div>

    @if($pendingAchievements->isEmpty())
        <div class="alert-custom alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>Tidak ada pengajuan prestasi siswa yang perlu diverifikasi saat ini.</span>
        </div>
    @else
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Murid</th>
                        <th>Judul Prestasi</th>
                        <th>Kategori</th>
                        <th>Tingkat</th>
                        <th>Peringkat</th>
                        <th>Bukti</th>
                        <th>Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingAchievements as $ach)
                        <tr>
                            <td><strong>{{ $ach->student->user->name }}</strong></td>
                            <td>{{ $ach->title }}</td>
                            <td><span class="card-cat-badge">{{ ucfirst($ach->category) }}</span></td>
                            <td>{{ ucfirst($ach->level) }}</td>
                            <td>{{ $ach->rank }}</td>
                            <td>
                                @if($ach->certificate_path)
                                    <a href="{{ asset($ach->certificate_path) }}" target="_blank" class="btn-primary-dark" style="background-color: #EEF2F6; color: #1C1917; padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 10px; border: 1px solid #E2E8F0; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-image"></i> Lihat Bukti
                                    </a>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Tidak ada bukti</span>
                                @endif
                            </td>
                            <td>
                                <form action="/teacher/achievements/{{ $ach->id }}/verify" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn-primary-dark" style="padding: 6px 14px; font-size: 0.8rem;">
                                        <i class="fa-solid fa-check"></i> Verifikasi / Setujui
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- 2. Kelola Nilai & Catatan Murid -->
<div class="content-box" style="margin-top: 24px;">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-pen-to-square"></i> Kelola Nilai Rapor & Catatan Murid</h3>
    </div>

    @if($students->isEmpty())
        <div class="alert-custom alert-warning">
            <i class="fa-solid fa-info-circle"></i>
            <span>Belum ada murid yang terdaftar di institusi Anda.</span>
        </div>
    @else
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>NISN</th>
                        <th>Kelas</th>
                        <th>Form Input Nilai & Catatan Perkembangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $st)
                        <tr>
                            <td><strong>{{ $st->user->name }}</strong></td>
                            <td>{{ $st->nisn ?? '-' }}</td>
                            <td><span class="card-cat-badge">{{ $st->classroom->name ?? '-' }}</span></td>
                            <td>
                                <form action="/teacher/student-data" method="POST" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin: 0;">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $st->id }}">
                                    
                                    <select name="semester" class="form-control" style="width: auto; padding: 6px 10px; font-size: 0.85rem;">
                                        <option value="">Sem</option>
                                        <option value="1">Sem 1</option>
                                        <option value="2">Sem 2</option>
                                        <option value="3">Sem 3</option>
                                        <option value="4">Sem 4</option>
                                        <option value="5">Sem 5</option>
                                        <option value="6">Sem 6</option>
                                    </select>
                                    
                                    <input type="text" name="subject_name" class="form-control" placeholder="Nama Mapel" list="subjects-{{ $st->id }}" style="width: 130px; padding: 6px 10px; font-size: 0.85rem;">
                                    <datalist id="subjects-{{ $st->id }}">
                                        <option value="Matematika">
                                        <option value="Informatika">
                                        <option value="Fisika">
                                        <option value="Bahasa Inggris">
                                    </datalist>
                                    
                                    <input type="number" name="score" step="0.01" min="0" max="100" class="form-control" placeholder="Nilai" style="width: 80px; padding: 6px 10px; font-size: 0.85rem;">
                                    
                                    <input type="text" name="notes" class="form-control" placeholder="Catatan minat/bakat..." style="width: 180px; padding: 6px 10px; font-size: 0.85rem;">
                                    
                                    <button type="submit" class="btn-primary-dark" style="padding: 6px 14px; font-size: 0.8rem;">
                                        <i class="fa-solid fa-floppy-disk"></i> Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- 3. Daftar Murid Terdaftar -->
<div class="content-box" style="margin-top: 24px;">
    <div class="section-title-row" style="margin-top: 0;">
        <h3 class="section-title"><i class="fa-solid fa-users"></i> Daftar Murid Terdaftar</h3>
    </div>

    @if($students->isEmpty())
        <div class="alert-custom alert-warning">
            <i class="fa-solid fa-info-circle"></i>
            <span>Belum ada murid yang terdaftar.</span>
        </div>
    @else
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Murid</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>NISN / NIM</th>
                        <th>Kelas / Semester</th>
                        <th>Aksi Management</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $st)
                        <tr>
                            <td><strong>{{ $st->user->name }}</strong></td>
                            <td>{{ $st->user->email }}</td>
                            <td><span class="card-cat-badge">{{ ucfirst($st->user->role) }}</span></td>
                            <td>
                                @if($st->user->role === 'siswa')
                                    {{ $st->nisn ?? '-' }}
                                @elseif($st->user->role === 'mahasiswa')
                                    {{ $st->nim ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($st->user->role === 'siswa')
                                    {{ $st->classroom->name ?? '-' }} ({{ $st->classroom->major->name ?? '-' }})
                                @elseif($st->user->role === 'mahasiswa')
                                    Semester {{ $st->semester ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <a href="/teacher/students/{{ $st->id }}/edit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--bg-pill); color: var(--text-dark);">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <form action="/teacher/students/{{ $st->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun murid ini secara permanen?');">
                                        @csrf
                                        <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B;">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
