@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Input Nilai & Catatan Murid</h1>
        <p class="hero-subtitle">Input data nilai rapor mata pelajaran dan berikan masukan/catatan minat bakat untuk memicu analisis AI.</p>
    </div>
</div>

<!-- Kelola Nilai & Catatan Murid -->
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
                        <th style="width: 50px; text-align: center;">No.</th>
                        <th>Nama Siswa</th>
                        <th>NISN</th>
                        <th>Kelas</th>
                        <th>Form Input Nilai & Catatan Perkembangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $st)
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
                            <td><strong>{{ $st->user->name }}</strong></td>
                            <td>{{ $st->nisn ?? '-' }}</td>
                            <td><span class="card-cat-badge">{{ $st->classroom->name ?? '-' }}</span></td>
                            <td>
                                <form action="/teacher/student-data" method="POST" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin: 0;">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $st->id }}">
                                    
                                    <select name="semester" class="form-control" style="width: auto; padding: 6px 10px; font-size: 0.85rem;" required>
                                        <option value="">Sem</option>
                                        <option value="1">Sem 1</option>
                                        <option value="2">Sem 2</option>
                                        <option value="3">Sem 3</option>
                                        <option value="4">Sem 4</option>
                                        <option value="5">Sem 5</option>
                                        <option value="6">Sem 6</option>
                                    </select>
                                    
                                    <input type="text" name="subject_name" class="form-control" placeholder="Nama Mapel" list="subjects-{{ $st->id }}" style="width: 130px; padding: 6px 10px; font-size: 0.85rem;" required>
                                    <datalist id="subjects-{{ $st->id }}">
                                        <option value="Matematika">
                                        <option value="Informatika">
                                        <option value="Fisika">
                                        <option value="Kimia">
                                        <option value="Biologi">
                                        <option value="Seni Budaya">
                                        <option value="Penjasorkes">
                                        <option value="Bahasa Inggris">
                                        <option value="Bahasa Indonesia">
                                    </datalist>
                                    
                                    <input type="number" name="score" step="0.01" min="0" max="100" class="form-control" placeholder="Nilai" style="width: 80px; padding: 6px 10px; font-size: 0.85rem;" required>
                                    
                                    <button type="submit" class="btn-primary-dark" style="padding: 6px 14px; font-size: 0.8rem; border: none; cursor: pointer;">
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
@endsection
