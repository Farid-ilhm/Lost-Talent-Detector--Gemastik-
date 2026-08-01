@extends('layouts.app')

@section('content')
<h2>Edit Kompetisi: {{ $competition->title }}</h2>
<hr>

@if ($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="/admin/competitions/{{ $competition->id }}/update" method="POST">
    @csrf
    <div>
        <label for="title">Judul Kompetisi:</label><br>
        <input type="text" id="title" name="title" value="{{ old('title', $competition->title) }}" required style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="category">Kategori Lomba:</label><br>
        <select id="category" name="category" required style="width: 308px;">
            <option value="teknologi" {{ old('category', $competition->category) == 'teknologi' ? 'selected' : '' }}>Teknologi / IT</option>
            <option value="sains" {{ old('category', $competition->category) == 'sains' ? 'selected' : '' }}>Sains / MIPA</option>
            <option value="seni" {{ old('category', $competition->category) == 'seni' ? 'selected' : '' }}>Seni / Budaya</option>
            <option value="olahraga" {{ old('category', $competition->category) == 'olahraga' ? 'selected' : '' }}>Olahraga</option>
            <option value="lainnya" {{ old('category', $competition->category) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
        </select>
    </div>
    <br>
    <div>
        <label for="organizer">Penyelenggara / Institusi:</label><br>
        <input type="text" id="organizer" name="organizer" value="{{ old('organizer', $competition->organizer) }}" style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="registration_deadline">Batas Pendaftaran:</label><br>
        <input type="date" id="registration_deadline" name="registration_deadline" value="{{ old('registration_deadline', $competition->registration_deadline ? $competition->registration_deadline->format('Y-m-d') : '') }}" style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="link">Link Pendaftaran:</label><br>
        <input type="url" id="link" name="link" value="{{ old('link', $competition->link) }}" style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="description">Deskripsi Lomba:</label><br>
        <textarea id="description" name="description" style="width: 300px; height: 100px;">{{ old('description', $competition->description) }}</textarea>
    </div>
    <br>
    <button type="submit">Simpan Perubahan</button>
    <a href="/dashboard" style="margin-left: 10px;">Batal</a>
</form>
@endsection
