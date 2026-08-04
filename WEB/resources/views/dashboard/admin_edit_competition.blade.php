@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 600px; margin: 24px auto;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.5rem; font-weight: 800;">Edit Kompetisi: {{ $competition->title }}</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Perbarui master data perlombaan & kompetisi nasional.</p>
    </div>

    <form action="/admin/competitions/{{ $competition->id }}/update" method="POST">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="title" class="form-label">Judul Kompetisi:</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $competition->title) }}" required>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="category" class="form-label">Kategori Lomba:</label>
            <select id="category" name="category" class="form-control" required style="font-weight: 600;">
                <option value="teknologi" {{ old('category', $competition->category) == 'teknologi' ? 'selected' : '' }}>Teknologi / IT</option>
                <option value="sains" {{ old('category', $competition->category) == 'sains' ? 'selected' : '' }}>Sains / MIPA</option>
                <option value="seni" {{ old('category', $competition->category) == 'seni' ? 'selected' : '' }}>Seni / Budaya</option>
                <option value="olahraga" {{ old('category', $competition->category) == 'olahraga' ? 'selected' : '' }}>Olahraga</option>
                <option value="lainnya" {{ old('category', $competition->category) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="organizer" class="form-label">Penyelenggara / Institusi:</label>
            <input type="text" id="organizer" name="organizer" class="form-control" value="{{ old('organizer', $competition->organizer) }}">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="registration_deadline" class="form-label">Batas Pendaftaran:</label>
            <input type="date" id="registration_deadline" name="registration_deadline" class="form-control" value="{{ old('registration_deadline', $competition->registration_deadline ? $competition->registration_deadline->format('Y-m-d') : '') }}">
        </div>

        <div style="margin-bottom: 24px;">
            <label for="description" class="form-label">Deskripsi Lomba:</label>
            <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $competition->description) }}</textarea>
        </div>

        <div style="display: flex; gap: 12px; align-items: center;">
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
            <a href="/admin/competitions" class="btn-primary-dark" style="background-color: var(--bg-pill); color: var(--text-dark); text-decoration: none; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; height: 42px; padding: 0 20px;">Batal</a>
        </div>
    </form>
</div>
@endsection
