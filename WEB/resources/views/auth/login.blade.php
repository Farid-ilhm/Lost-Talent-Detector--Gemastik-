@extends('layouts.app')

@section('content')
<h2>Login Pengguna</h2>
<form action="/login" method="POST">
    @csrf
    <div>
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
    </div>
    <br>
    <div>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required>
    </div>
    <br>
    <button type="submit">Masuk</button>
</form>
<p>Belum punya akun? <a href="/register">Daftar sekarang</a></p>
@endsection
