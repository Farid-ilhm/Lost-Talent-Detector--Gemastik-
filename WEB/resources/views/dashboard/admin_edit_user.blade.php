@extends('layouts.app')

@section('content')
<div class="content-box" style="max-width: 600px; margin: 24px auto;">
    <div style="margin-bottom: 20px;">
        <h2 style="font-size: 1.5rem; font-weight: 800;"><i class="fa-solid fa-user-pen" style="color: #6366F1;"></i> Edit Akun Pengguna: {{ $user->name }}</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Perbarui data profil, peran sistem, atau ubah kata sandi akun.</p>
    </div>

    <form action="/admin/users/{{ $user->id }}/update" method="POST">
        @csrf
        <div style="margin-bottom: 16px;">
            <label for="name" class="form-label">Nama Lengkap:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="email" class="form-label">Email Akun:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="phone" class="form-label">No. Telepon / WhatsApp:</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="password" class="form-label">Kata Sandi Baru (Kosongkan jika tidak ingin mengubah):</label>
            <input type="password" id="password" name="password" class="form-control" minlength="8" placeholder="Masukkan kata sandi baru jika ingin diubah...">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="role" class="form-label">Role Pengguna:</label>
            <select id="role" name="role" class="form-control" required style="font-weight: 600;">
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Super Admin</option>
                <option value="institusi" {{ old('role', $user->role) === 'institusi' ? 'selected' : '' }}>Institusi (Sekolah/Univ)</option>
                <option value="guru" {{ old('role', $user->role) === 'guru' ? 'selected' : '' }}>Guru Pembimbing</option>
                <option value="siswa" {{ old('role', $user->role) === 'siswa' ? 'selected' : '' }}>Siswa</option>
                <option value="mahasiswa" {{ old('role', $user->role) === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="umum" {{ old('role', $user->role) === 'umum' ? 'selected' : '' }}>Umum (Mandiri)</option>
            </select>
        </div>

        <!-- Conditional Section for Institution selection (Siswa, Mahasiswa, Guru) -->
        @php
            $currentInstId = null;
            if (in_array($user->role, ['siswa', 'mahasiswa', 'umum']) && $user->student) {
                $currentInstId = $user->student->institution_id;
            } elseif ($user->role === 'guru' && $user->teacher) {
                $currentInstId = $user->teacher->institution_id;
            }
        @endphp
        <div id="section-select-institution" style="display: {{ in_array($user->role, ['siswa', 'mahasiswa', 'guru']) ? 'block' : 'none' }}; margin-bottom: 16px; background-color: #FAFAF8; padding: 12px; border-radius: 12px; border: 1px solid var(--border-subtle);">
            <label for="institution_id" class="form-label" style="color: var(--text-dark); font-weight: 700;">Pilih Institusi:</label>
            <select id="institution_id" name="institution_id" class="form-control" style="margin-bottom: 0;">
                <option value="" {{ is_null($currentInstId) ? 'selected' : '' }}>-- Pengguna Mandiri (Tanpa Institusi) --</option>
                @foreach($institutions as $inst)
                    <option value="{{ $inst->id }}" {{ $currentInstId == $inst->id ? 'selected' : '' }}>{{ $inst->user->name }} (NPSN: {{ $inst->npsn }})</option>
                @endforeach
            </select>
            <p id="guru-warning" style="color: #991B1B; font-size: 0.8rem; margin-top: 6px; display: {{ $user->role === 'guru' ? 'block' : 'none' }}; font-weight: 600;">
                <i class="fa-solid fa-circle-exclamation"></i> Perhatian: Guru Pembimbing wajib dikaitkan dengan satu institusi terdaftar.
            </p>
        </div>

        <!-- Conditional Section for NPSN (Institusi) -->
        <div id="section-npsn" style="display: {{ $user->role === 'institusi' ? 'block' : 'none' }}; margin-bottom: 16px; background-color: #FAFAF8; padding: 12px; border-radius: 12px; border: 1px solid var(--border-subtle);">
            <label for="npsn" class="form-label" style="color: var(--text-dark); font-weight: 700;">NPSN Institusi:</label>
            <input type="text" id="npsn" name="npsn" class="form-control" value="{{ old('npsn', $user->institution->npsn ?? '') }}" placeholder="Contoh: 1029384" style="margin-bottom: 0;">
        </div>

        <div style="display: flex; gap: 12px; align-items: center; margin-top: 24px;">
            <button type="submit" class="btn-primary-dark">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
            </button>
            <a href="/admin/users" class="btn-primary-dark" style="background-color: var(--bg-pill); color: var(--text-dark); text-decoration: none; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; height: 42px; padding: 0 20px;">Batal</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const sectionInstitution = document.getElementById('section-select-institution');
    const sectionNpsn = document.getElementById('section-npsn');
    const institutionIdSelect = document.getElementById('institution_id');
    const guruWarning = document.getElementById('guru-warning');
    const npsnInput = document.getElementById('npsn');

    roleSelect.addEventListener('change', function() {
        const selectedRole = roleSelect.value;
        
        // Reset inputs requirements
        npsnInput.required = false;
        institutionIdSelect.required = false;
        guruWarning.style.display = 'none';

        if (selectedRole === 'siswa' || selectedRole === 'mahasiswa' || selectedRole === 'guru') {
            sectionInstitution.style.display = 'block';
            sectionNpsn.style.display = 'none';
            
            if (selectedRole === 'guru') {
                institutionIdSelect.required = true;
                guruWarning.style.display = 'block';
            }
        } else if (selectedRole === 'institusi') {
            sectionInstitution.style.display = 'none';
            sectionNpsn.style.display = 'block';
            npsnInput.required = true;
        } else {
            sectionInstitution.style.display = 'none';
            sectionNpsn.style.display = 'none';
        }
    });
});
</script>
@endsection
