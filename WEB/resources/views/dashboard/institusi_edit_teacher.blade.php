@extends('layouts.app')

@section('content')
<h2>Edit Guru: {{ $teacher->user->name }}</h2>
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

<form action="/institution/teachers/{{ $teacher->id }}/update" method="POST">
    @csrf
    <div>
        <label for="name">Nama Guru:</label><br>
        <input type="text" id="name" name="name" value="{{ old('name', $teacher->user->name) }}" required style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="email">Email Login Guru:</label><br>
        <input type="email" id="email" name="email" value="{{ old('email', $teacher->user->email) }}" required style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="nip">NIP Guru:</label><br>
        <input type="text" id="nip" name="nip" value="{{ old('nip', $teacher->nip) }}" style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="subject">Mata Pelajaran Utama:</label><br>
        <input type="text" id="subject" name="subject" value="{{ old('subject', $teacher->subject) }}" style="width: 300px;">
    </div>
    <br>
    <div>
        <label for="password">Password Baru (Kosongkan jika tidak ingin diubah):</label><br>
        <input type="password" id="password" name="password" style="width: 300px;" placeholder="Minimal 8 karakter">
    </div>
    <br>
    <div>
        <label for="password_confirmation">Konfirmasi Password Baru:</label><br>
        <input type="password" id="password_confirmation" name="password_confirmation" style="width: 300px;">
    </div>
    <br>
    <button type="submit">Simpan Perubahan</button>
    <a href="/dashboard" style="margin-left: 10px;">Batal</a>
</form>
@endsection
