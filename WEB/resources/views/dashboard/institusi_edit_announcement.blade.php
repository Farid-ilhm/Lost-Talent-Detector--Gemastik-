@extends('layouts.app')

@section('content')
<div class="main-header">
    <div class="hero-title-section">
        <h1 class="hero-title">Edit Informasi / Pengumuman</h1>
        <p class="hero-subtitle">Perbarui detail postingan pengumuman atau peluang bakat untuk siswa Anda.</p>
    </div>
</div>

<div class="content-box" style="margin-top: 24px;">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 class="section-title" style="margin: 0;"><i class="fa-solid fa-pen-to-square"></i> Edit Postingan #{{ $announcement->id }}</h3>
        <a href="/institution/announcements" class="btn-primary-dark" style="background-color: var(--bg-pill); color: var(--text-dark); text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="/institution/announcements/{{ $announcement->id }}/update" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <div style="grid-column: span 2;">
                <label for="title" class="form-label">Judul Postingan / Informasi:</label>
                <input type="text" id="title" name="title" class="form-control" required value="{{ old('title', $announcement->title) }}">
            </div>
            <div>
                <label for="category" class="form-label">Kategori Informasi:</label>
                <select id="category" name="category" class="form-control" required style="width: 100%;">
                    <option value="pengumuman" {{ $announcement->category === 'pengumuman' ? 'selected' : '' }}>Pengumuman Umum</option>
                    <option value="beasiswa" {{ $announcement->category === 'beasiswa' ? 'selected' : '' }}>Beasiswa & Pendanaan</option>
                    <option value="pelatihan" {{ $announcement->category === 'pelatihan' ? 'selected' : '' }}>Pelatihan & Course</option>
                    <option value="lomba" {{ $announcement->category === 'lomba' ? 'selected' : '' }}>Lomba & Kompetisi</option>
                    <option value="kegiatan" {{ $announcement->category === 'kegiatan' ? 'selected' : '' }}>Kegiatan / Ekstrakurikuler</option>
                </select>
            </div>
            <div>
                <label for="target_talent" class="form-label">Target Rekomendasi Bakat:</label>
                <select id="target_talent" name="target_talent" class="form-control" style="width: 100%;">
                    <option value="Semua" {{ $announcement->target_talent === 'Semua' ? 'selected' : '' }}>Semua Siswa (General)</option>
                    <option value="Technology" {{ $announcement->target_talent === 'Technology' ? 'selected' : '' }}>Technology & Coding</option>
                    <option value="Artistic" {{ $announcement->target_talent === 'Artistic' ? 'selected' : '' }}>Art, Design & Music</option>
                    <option value="Leadership" {{ $announcement->target_talent === 'Leadership' ? 'selected' : '' }}>Leadership & Business</option>
                    <option value="Science" {{ $announcement->target_talent === 'Science' ? 'selected' : '' }}>Science & Research</option>
                    <option value="Sports" {{ $announcement->target_talent === 'Sports' ? 'selected' : '' }}>Sports & Physical</option>
                    <option value="Academic" {{ $announcement->target_talent === 'Academic' ? 'selected' : '' }}>Academic & Languages</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="content" class="form-label">Isi / Detail Informasi:</label>
            <textarea id="content" name="content" class="form-control" rows="5" required>{{ old('content', $announcement->content) }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <div>
                <label for="external_link" class="form-label">Link Luar / Form Pendaftaran (Opsional):</label>
                <input type="url" id="external_link" name="external_link" class="form-control" value="{{ old('external_link', $announcement->external_link) }}">
            </div>
            <div>
                <label for="banner_image" class="form-label">Ganti Gambar Banner (Opsional):</label>
                <input type="file" id="banner_image" name="banner_image" class="form-control" accept="image/*">
                @if($announcement->banner_image)
                    <span style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-top: 4px;">Banner saat ini tersimpan.</span>
                @endif
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 600;">
                <input type="checkbox" name="is_published" value="1" {{ $announcement->is_published ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                Publikasikan Langsung ke Aplikasi Siswa
            </label>
        </div>

        <button type="submit" class="btn-primary-dark">
            <i class="fa-solid fa-save"></i> Simpan Perubahan
        </button>
    </form>
</div>
@endsection
