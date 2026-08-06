<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

            if ($user->status === 'pending_otp') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('verify-otp.show', ['email' => $user->email])
                    ->with('warning', 'Akun Anda belum diaktivasi. Silakan masukkan kode OTP yang telah dikirim ke email Anda.');
            }

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

        // Cleanup any previous unverified registration attempts for this email
        User::where('email', $request->email)->where('status', 'pending_otp')->delete();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:siswa,mahasiswa,umum,institusi'],
            'phone' => ['nullable', 'string'],
            'npsn' => ['required_if:role,institusi', 'nullable', 'string', 'unique:institutions,npsn'],
            'address' => ['required_if:role,institusi', 'nullable', 'string'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'nisn' => ['required_if:role,siswa', 'nullable', 'string', 'unique:students,nisn'],
            'classroom' => ['required_if:role,siswa', 'nullable', 'string', 'max:50'],
            'major' => ['required_if:role,siswa', 'required_if:role,mahasiswa', 'nullable', 'string', 'max:50'],
            'nim' => ['required_if:role,mahasiswa', 'nullable', 'string', 'unique:students,nim'],
            'semester' => ['required_if:role,mahasiswa', 'nullable', 'integer', 'min:1', 'max:14'],
        ]);

        $otpCode = strval(rand(100000, 999999));
        $otpExpiresAt = Carbon::now()->addMinutes(10);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'otp_code' => $otpCode,
            'otp_expires_at' => $otpExpiresAt,
            'status' => 'pending_otp',
        ]);

        try {
            Mail::to($user->email)->send(new SendOtpMail($otpCode, $user->name));
        } catch (\Exception $e) {
            Log::error("Failed to send OTP email to {$user->email}: " . $e->getMessage());
        }

        if ($request->role === 'institusi') {
            \App\Models\Institution::create([
                'user_id' => $user->id,
                'npsn' => $request->npsn,
                'address' => $request->address,
                'type' => 'sekolah',
                'is_verified' => false,
            ]);
        } else {
            $classroomId = null;
            if (($request->role === 'siswa' || $request->role === 'mahasiswa') && $request->filled('institution_id')) {
                $academicYear = \App\Models\AcademicYear::firstOrCreate(
                    [
                        'institution_id' => $request->institution_id,
                        'name' => '2026/2027',
                    ],
                    [
                        'is_active' => true,
                    ]
                );

                $majorId = null;
                if ($request->filled('major')) {
                    $major = \App\Models\Major::firstOrCreate([
                        'name' => $request->major,
                        'institution_id' => $request->institution_id,
                    ]);
                    $majorId = $major->id;
                }

                $classroomName = $request->role === 'mahasiswa' ? ("Semester " . $request->semester) : $request->classroom;

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

            Student::create([
                'user_id' => $user->id,
                'institution_id' => in_array($request->role, ['siswa', 'mahasiswa']) ? $request->institution_id : null,
                'classroom_id' => $classroomId,
                'nisn' => $request->role === 'siswa' ? $request->nisn : null,
                'nim' => $request->role === 'mahasiswa' ? $request->nim : null,
                'semester' => $request->role === 'mahasiswa' ? $request->semester : null,
            ]);
        }

        return redirect()->route('verify-otp.show', ['email' => $user->email])
            ->with('success', 'Registrasi berhasil! Kode OTP telah dikirimkan ke email Anda.');
    }

    public function showVerifyOtp(Request $request)
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        $email = $request->query('email', session('email'));
        return view('auth.verify-otp', compact('email'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp_code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.'])->withInput();
        }

        if ($user->otp_code !== $request->otp_code) {
            return back()->withErrors(['otp_code' => 'Kode OTP yang Anda masukkan salah.'])->withInput();
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return back()->withErrors(['otp_code' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.'])->withInput();
        }

        $user->status = 'active';
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->email_verified_at = Carbon::now();
        $user->save();

        if ($user->role === 'institusi') {
            $admins = User::where('role', 'admin')->get();
            $institution = \App\Models\Institution::where('user_id', $user->id)->first();
            $npsn = $institution ? $institution->npsn : '-';

            foreach ($admins as $admin) {
                \App\Models\CustomNotification::create([
                    'user_id' => $admin->id,
                    'title' => 'Institusi Baru Mendaftar',
                    'message' => 'Sekolah/Univ "' . $user->name . '" baru saja mendaftar dan menunggu verifikasi Anda.',
                    'type' => 'system',
                    'is_read' => false,
                ]);

                // Send email notification to each admin
                try {
                    \Illuminate\Support\Facades\Mail::to($admin->email)->send(
                        new \App\Mail\NewInstitutionRegisteredMail($user, $npsn)
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send admin email notification for new institution: " . $e->getMessage());
                }
            }
        }
 
        if (in_array($user->role, ['siswa', 'mahasiswa', 'umum'])) {
            return redirect('/login')->with('success', 'Akun Anda berhasil diverifikasi! Silakan login melalui aplikasi Mobile.');
        }

        Auth::login($user);
        return redirect('/dashboard')->with('success', 'Akun berhasil diverifikasi dan diaktifkan!');
    }

    public function resendWebOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $otpCode = strval(rand(100000, 999999));
        $user->otp_code = $otpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        try {
            Mail::to($user->email)->send(new SendOtpMail($otpCode, $user->name));
        } catch (\Exception $e) {
            Log::error("Failed to resend OTP email: " . $e->getMessage());
        }

        return back()->with('success', 'Kode OTP baru telah berhasil dikirimkan ke email Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
