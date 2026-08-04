@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 600px; margin: 24px auto;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.5rem; font-weight: 800;">Edit Institusi: {{ $institution->user->name }}</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Perbarui data profil & NPSN institusi terdaftar.</p>
    </div>

    <form action="/admin/institutions/{{ $institution->id }}/update" method="POST">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="name" class="form-label">Nama Institusi:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $institution->user->name) }}" required>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="email" class="form-label">Email Admin Institusi:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $institution->user->email) }}" required>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="phone" class="form-label">No. Telepon / WhatsApp:</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $institution->user->phone) }}">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="npsn" class="form-label">NPSN (Nomor Pokok Sekolah Nasional):</label>
            <input type="text" id="npsn" name="npsn" class="form-control" value="{{ old('npsn', $institution->npsn) }}" required>
        </div>

        <div style="margin-bottom: 24px;">
            <label for="type" class="form-label">Tipe Institusi:</label>
            <select id="type" name="type" class="form-control" required style="font-weight: 600;">
                <option value="sekolah" {{ old('type', $institution->type) == 'sekolah' ? 'selected' : '' }}>Sekolah (SD/SMP/SMA/SMK)</option>
                <option value="universitas" {{ old('type', $institution->type) == 'universitas' ? 'selected' : '' }}>Universitas / Perguruan Tinggi</option>
            </select>
        </div>

        <div style="display: flex; gap: 12px; align-items: center;">
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
            <a href="/admin/institutions" class="btn-primary-dark" style="background-color: var(--bg-pill); color: var(--text-dark); text-decoration: none; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; height: 42px; padding: 0 20px;">Batal</a>
        </div>
    </form>
</div>
@endsection
