<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        $institutions = \App\Models\Institution::where('is_verified', true)->get();
        return view('auth.register', compact('institutions'));
    }

    public function register(Request $request)
    {
        // Clean empty string for institution_id to pass nullable rule
        if ($request->input('institution_id') === '') {
            $request->merge(['institution_id' => null]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:siswa,umum,institusi'],
            'phone' => ['nullable', 'string'],
            'npsn' => ['required_if:role,institusi', 'nullable', 'string', 'unique:institutions,npsn'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        if ($request->role === 'institusi') {
            \App\Models\Institution::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'npsn' => $request->npsn,
                'type' => 'sekolah',
                'is_verified' => false, // Set false until approved by Admin
            ]);
        } else {
            // Create student profile for siswa / umum
            Student::create([
                'user_id' => $user->id,
                'institution_id' => $request->role === 'siswa' ? $request->institution_id : null,
                'classroom_id' => null,
            ]);
        }

        Auth::login($user);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
