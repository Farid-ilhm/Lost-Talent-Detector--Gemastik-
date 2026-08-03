<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Institution;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Get list of registered institutions.
     */
    public function getInstitutions()
    {
        $institutions = Institution::with('user')->get()->map(function ($inst) {
            return [
                'id' => $inst->id,
                'name' => $inst->name,
                'npsn' => $inst->npsn,
                'type' => $inst->type,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $institutions
        ]);
    }

    /**
     * Register a new user (siswa, mahasiswa, umum, institusi).
     */
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:siswa,mahasiswa,umum,institusi',
            'phone' => 'nullable|string',
            'npsn' => 'required_if:role,institusi|nullable|string|unique:institutions,npsn',
            'institution_id' => 'nullable|exists:institutions,id',
            'nisn' => 'required_if:role,siswa|nullable|string|unique:students,nisn',
            'classroom' => 'required_if:role,siswa|nullable|string|max:50',
            'major' => 'required_if:role,siswa|required_if:role,mahasiswa|nullable|string|max:50',
            'nim' => 'required_if:role,mahasiswa|nullable|string|unique:students,nim',
            'semester' => 'required_if:role,mahasiswa|nullable|integer|min:1|max:14',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

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
            Institution::create([
                'user_id' => $user->id,
                'npsn' => $request->npsn,
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

        return response()->json([
            'success' => true,
            'requires_otp' => true,
            'message' => 'Registrasi berhasil. Silakan masukkan kode OTP yang dikirim ke email Anda.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ], 201);
    }

    /**
     * Verify OTP to activate account.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if ($user->otp_code !== $request->otp_code) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code'
            ], 400);
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired'
            ], 400);
        }

        // Activate user
        $user->status = 'active';
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Issue token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Account successfully verified and activated.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Resend OTP code to user's email.
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $otpCode = strval(rand(100000, 999999));
        $user->otp_code = $otpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        try {
            Mail::to($user->email)->send(new SendOtpMail($otpCode, $user->name));
        } catch (\Exception $e) {
            Log::error("Failed to resend OTP email to {$user->email}: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP baru telah dikirimkan ke email Anda.'
        ]);
    }

    /**
     * Log in a user.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        if ($user->status === 'pending_otp') {
            return response()->json([
                'success' => false,
                'message' => 'Account is not activated yet. Please verify OTP.',
                'requires_otp' => true,
                'email' => $user->email
            ], 403);
        }

        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Administrator hanya dapat diakses melalui Web Dashboard.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Eager load institutional profiles if applicable
        $profile = null;
        if ($user->role === 'siswa' || $user->role === 'mahasiswa' || $user->role === 'umum') {
            $profile = Student::where('user_id', $user->id)->with(['classroom', 'institution'])->first();
        } elseif ($user->role === 'institusi') {
            $profile = Institution::where('user_id', $user->id)->first();
        } elseif ($user->role === 'guru') {
            $profile = Teacher::where('user_id', $user->id)->with('institution')->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'status' => $user->status,
                'profile' => $profile
            ]
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        
        $profile = null;
        if ($user->role === 'siswa' || $user->role === 'mahasiswa' || $user->role === 'umum') {
            $profile = Student::where('user_id', $user->id)
                ->with(['classroom', 'institution', 'parent'])
                ->first();
        } elseif ($user->role === 'institusi') {
            $profile = Institution::where('user_id', $user->id)->first();
        } elseif ($user->role === 'guru') {
            $profile = Teacher::where('user_id', $user->id)->with('institution')->first();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'profile' => $profile
            ]
        ]);
    }

    /**
     * Update authenticated user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string',
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'avatar' => 'nullable|string', // URL or base64 profile picture
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('avatar')) {
            $user->avatar = $request->avatar;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Log out a user (revoke tokens).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
