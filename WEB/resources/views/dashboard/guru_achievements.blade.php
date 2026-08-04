@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Verifikasi Prestasi Siswa</h1>
        <p class="hero-subtitle">Periksa dan berikan verifikasi/persetujuan untuk sertifikat prestasi yang diajukan oleh siswa Anda.</p>
    </div>
</div>

<!-- Verifikasi Prestasi Siswa -->
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
                        <th style="width: 50px; text-align: center;">No.</th>
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
                            <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
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
                                    <button type="submit" class="btn-primary-dark" style="padding: 6px 14px; font-size: 0.8rem; border: none; cursor: pointer;">
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
@endsection
