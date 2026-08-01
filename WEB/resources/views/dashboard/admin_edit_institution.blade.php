@extends('layouts.app')

@section('content')
<h2>Edit Institusi: {{ $institution->user->name }}</h2>
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

<form action="/admin/institutions/{{ $institution->id }}/update" method="POST">
    @csrf
    <div>
        <label for="name">Nama Institusi:</label><br>
        <input type="text" id="name" name="name" value="{{ old('name', $institution->user->name) }}" required style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="email">Email Admin:</label><br>
        <input type="email" id="email" name="email" value="{{ old('email', $institution->user->email) }}" required style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="phone">No. Telepon / WhatsApp:</label><br>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $institution->user->phone) }}" style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="npsn">NPSN (Nomor Pokok Sekolah Nasional):</label><br>
        <input type="text" id="npsn" name="npsn" value="{{ old('npsn', $institution->npsn) }}" required style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="type">Tipe Institusi:</label><br>
        <select id="type" name="type" required style="width: 308px;">
            <option value="sekolah" {{ old('type', $institution->type) == 'sekolah' ? 'selected' : '' }}>Sekolah (SD/SMP/SMA/SMK)</option>
            <option value="universitas" {{ old('type', $institution->type) == 'universitas' ? 'selected' : '' }}>Universitas / Perguruan Tinggi</option>
        </select>
    </div>
    <br>
    <button type="submit">Simpan Perubahan</button>
    <a href="/dashboard" style="margin-left: 10px;">Batal</a>
</form>
@endsection
