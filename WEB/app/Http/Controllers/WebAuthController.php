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
            $user = Auth::user();
            if (in_array($user->role, ['siswa', 'mahasiswa', 'umum'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Akun Siswa/Mahasiswa/Umum hanya dapat diakses melalui aplikasi Mobile. Silakan gunakan aplikasi Mobile.',
                ])->onlyInput('email');
            }

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
        $institutions = \App\Models\Institution::where('is_verified', true)->with(['user', 'classrooms'])->get();
        return view('auth.register', compact('institutions'));
    }

    public function register(Request $request)
    {
        // Clean empty strings for optional relation inputs
        if ($request->input('institution_id') === '') {
            $request->merge(['institution_id' => null]);
        }
        if ($request->input('classroom') === '') {
            $request->merge(['classroom' => null]);
        }
        if ($request->input('major') === '') {
            $request->merge(['major' => null]);
        }
        if ($request->input('semester') === '') {
            $request->merge(['semester' => null]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:siswa,mahasiswa,umum,institusi'],
            'phone' => ['nullable', 'string'],
            'npsn' => ['required_if:role,institusi', 'nullable', 'string', 'unique:institutions,npsn'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'nisn' => ['required_if:role,siswa', 'nullable', 'string', 'unique:students,nisn'],
            'classroom' => ['required_if:role,siswa', 'nullable', 'string', 'max:50'],
            'major' => ['required_if:role,siswa', 'required_if:role,mahasiswa', 'nullable', 'string', 'max:50'],
            'nim' => ['required_if:role,mahasiswa', 'nullable', 'string', 'unique:students,nim'],
            'semester' => ['required_if:role,mahasiswa', 'nullable', 'integer', 'min:1', 'max:14'],
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
                'npsn' => $request->npsn,
                'type' => 'sekolah',
                'is_verified' => false, // Set false until approved by Admin
            ]);
        } else {
            $classroomId = null;
            if (($request->role === 'siswa' || $request->role === 'mahasiswa') && $request->filled('institution_id')) {
                // Find or create default academic year for this school/university
                $academicYear = \App\Models\AcademicYear::firstOrCreate(
                    [
                        'institution_id' => $request->institution_id,
                        'name' => '2026/2027',
                    ],
                    [
                        'is_active' => true,
                    ]
                );

                // Find or create Major if filled
                $majorId = null;
                if ($request->filled('major')) {
                    $major = \App\Models\Major::firstOrCreate([
                        'name' => $request->major,
                        'institution_id' => $request->institution_id,
                    ]);
                    $majorId = $major->id;
                }

                // For mahasiswa, classroom name defaults to "Semester [Number]"
                $classroomName = $request->role === 'mahasiswa' ? ("Semester " . $request->semester) : $request->classroom;

                // Find or create classroom
                if ($classroomName) {
                    $classroom = \App\Models\Classroom::firstOrCreate([
                        'name' => $classroomName,
                        'institution_id' => $request->institution_id,
                        'academic_year_id' => $academicYear->id,
                        'major_id' => $majorId,
                    ]);
                    $classroomId = $classroom->id;
                }
            }

            // Create student profile
            Student::create([
                'user_id' => $user->id,
                'institution_id' => in_array($request->role, ['siswa', 'mahasiswa']) ? $request->institution_id : null,
                'classroom_id' => $classroomId,
                'nisn' => $request->role === 'siswa' ? $request->nisn : null,
                'nim' => $request->role === 'mahasiswa' ? $request->nim : null,
                'semester' => $request->role === 'mahasiswa' ? $request->semester : null,
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
