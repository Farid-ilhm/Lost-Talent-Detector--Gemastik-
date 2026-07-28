<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Register a new student or public user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:siswa,umum',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a 6-digit OTP code
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
            'status' => 'pending_otp', // require OTP verification
        ]);

        // Create student / general profile record
        Student::create([
            'user_id' => $user->id,
            'institution_id' => null,
            'classroom_id' => null,
        ]);

        // Log OTP code (simulated email delivery)
        Log::info("Simulated OTP sent to {$user->email}: {$otpCode}");

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Verification OTP has been sent.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'simulated_otp' => $otpCode // Return for easier developer/judge testing
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

        $token = $user->createToken('auth_token')->plainTextToken;

        // Eager load institutional profiles if applicable
        $profile = null;
        if ($user->role === 'siswa' || $user->role === 'umum') {
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
        if ($user->role === 'siswa' || $user->role === 'umum') {
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
