@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Papan Informasi & Pengumuman Komunitas</h1>
        <p class="hero-subtitle">Publikasikan pengumuman, beasiswa, pelatihan bakat, lomba, atau kegiatan untuk siswa institusi Anda.</p>
    </div>
</div>

@if(!$institution->is_verified)
    <div class="alert-custom alert-warning" style="margin-top: 16px;">
        <i class="fa-solid fa-clock-rotate-left" style="font-size: 1.2rem;"></i>
        <div>
            <strong>Akun Institusi Menunggu Verifikasi Super Admin!</strong><br>
            <span style="font-weight: 500; font-size: 0.85rem;">Hubungi administrator atau tunggu persetujuan verifikasi untuk mempublikasikan pengumuman.</span>
        </div>
    </div>
@else
    <!-- Daftar Informasi & Pengumuman -->
    <div class="content-box" style="margin-top: 24px;">
        <div class="section-title-row" style="margin-top: 0;">
            <h3 class="section-title"><i class="fa-solid fa-bullhorn"></i> Informasi & Pengumuman Terpublikasi</h3>
        </div>

        @if($announcements->isEmpty())
            <div class="alert-custom alert-warning">
                <i class="fa-solid fa-info-circle"></i>
                <span>Belum ada postingan informasi/pengumuman yang dipublikasikan.</span>
            </div>
        @else
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">No.</th>
                            <th>Judul Informasi</th>
                            <th>Kategori</th>
                            <th>Target Bakat</th>
                            <th>Tautan Eksternal</th>
                            <th>Tanggal Buat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($announcements as $a)
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);">{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $a->title }}</strong>
                                    @if(!$a->is_published)
                                        <span style="font-size: 0.75rem; background: #F3F4F6; color: #6B7280; padding: 2px 8px; border-radius: 4px; margin-left: 6px;">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="card-cat-badge" style="text-transform: capitalize;">
                                        @if($a->category === 'beasiswa') <i class="fa-solid fa-graduation-cap"></i>
                                        @elseif($a->category === 'pelatihan') <i class="fa-solid fa-laptop-code"></i>
                                        @elseif($a->category === 'lomba') <i class="fa-solid fa-trophy"></i>
                                        @elseif($a->category === 'kegiatan') <i class="fa-solid fa-users"></i>
                                        @else <i class="fa-solid fa-bullhorn"></i>
                                        @endif
                                        {{ $a->category }}
                                    </span>
                                </td>
                                <td>
                                    <span style="background-color: #EEF2FF; color: #4338CA; padding: 4px 10px; border-radius: 999px; font-size: 0.8rem; font-weight: 600;">
                                        <i class="fa-solid fa-star"></i> {{ $a->target_talent ?? 'Semua' }}
                                    </span>
                                </td>
                                <td>
                                    @if($a->external_link)
                                        <a href="{{ $a->external_link }}" target="_blank" style="color: var(--text-dark); text-decoration: underline; font-size: 0.85rem;">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Link
                                        </a>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">-</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted);">
                                    {{ $a->created_at->format('d M Y') }}
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <a href="/institution/announcements/{{ $a->id }}/edit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: var(--bg-pill); color: var(--text-dark);">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </a>
                                        <form action="/institution/announcements/{{ $a->id }}/delete" method="POST" style="margin:0;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan informasi ini?');">
                                            @csrf
                                            <button type="submit" class="btn-primary-dark" style="padding: 6px 12px; font-size: 0.8rem; background-color: #FBE3E2; color: #991B1B; border: none; cursor: pointer;">
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

    <!-- Form Buat Postingan Informasi Baru -->
    <div class="content-box" style="margin-top: 24px;">
        <div class="section-title-row" style="margin-top: 0;">
            <h3 class="section-title"><i class="fa-solid fa-plus-circle"></i> Buat Informasi / Pengumuman Baru</h3>
        </div>

        <form action="/institution/announcements" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div style="grid-column: span 2;">
                    <label for="title" class="form-label">Judul Postingan / Informasi:</label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="contoh: Pendaftaran Beasiswa Talenta Digital 2026">
                </div>
                <div>
                    <label for="category" class="form-label">Kategori Informasi:</label>
                    <select id="category" name="category" class="form-control" required style="width: 100%;">
                        <option value="pengumuman">Pengumuman Umum</option>
                        <option value="beasiswa">Beasiswa & Pendanaan</option>
                        <option value="pelatihan">Pelatihan & Course</option>
                        <option value="lomba">Lomba & Kompetisi</option>
                        <option value="kegiatan">Kegiatan / Ekstrakurikuler</option>
                    </select>
                </div>
                <div>
                    <label for="target_talent" class="form-label">Target Rekomendasi Bakat:</label>
                    <select id="target_talent" name="target_talent" class="form-control" style="width: 100%;">
                        <option value="Semua">Semua Siswa (General)</option>
                        <option value="Technology">Technology & Coding</option>
                        <option value="Artistic">Art, Design & Music</option>
                        <option value="Leadership">Leadership & Business</option>
                        <option value="Science">Science & Research</option>
                        <option value="Sports">Sports & Physical</option>
                        <option value="Academic">Academic & Languages</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="content" class="form-label">Isi / Detail Informasi:</label>
                <textarea id="content" name="content" class="form-control" rows="5" required placeholder="Tuliskan deskripsi lengkap, persyaratan, serta petunjuk untuk siswa..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label for="external_link" class="form-label">Link Luar / Form Pendaftaran (Opsional):</label>
                    <input type="url" id="external_link" name="external_link" class="form-control" placeholder="https://contoh-link-pendaftaran.com">
                </div>
                <div>
                    <label for="banner_image" class="form-label">Upload Gambar Banner (Opsional):</label>
                    <input type="file" id="banner_image" name="banner_image" class="form-control" accept="image/*">
                </div>
                <div>
                    <label for="expired_at" class="form-label">Tanggal Kedaluwarsa (Auto-Hapus):</label>
                    <input type="date" id="expired_at" name="expired_at" class="form-control" min="{{ date('Y-m-d') }}">
                    <span style="font-size: 0.72rem; color: var(--text-muted);">Akan otomatis dihapus dari database & server saat tanggal ini lewat.</span>
                </div>
            </div>

            <button type="submit" class="btn-primary-dark" style="margin-top: 8px;">
                <i class="fa-solid fa-paper-plane"></i> Publikasikan Informasi
            </button>
        </form>
    </div>
@endif
@endsection
