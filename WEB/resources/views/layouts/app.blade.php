<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lost Talent Detector</title>
</head>
<body>
    <header>
        <h1>Lost Talent Detector</h1>
        @auth
            <p>Selamat datang, <strong>{{ Auth::user()->name }}</strong> (Role: {{ Auth::user()->role }})</p>
            <form action="/logout" method="POST" style="display:inline;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @else
            <nav>
                <a href="/">Home</a> | 
                <a href="/login">Login</a> | 
                <a href="/register">Register</a>
            </nav>
        @endauth
        <hr>
    </header>

    <main>
        <!-- Alerts -->
        @if(session('success'))
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin-bottom: 10px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 10px;">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 10px;">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer>
        <hr>
        <p>&copy; 2026 Lost Talent Detector</p>
    </footer>
</body>
</html>


