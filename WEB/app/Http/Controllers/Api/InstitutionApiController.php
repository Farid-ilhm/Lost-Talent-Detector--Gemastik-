<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstitutionApiController extends Controller
{
    /**
     * Get statistics dashboard for the institution.
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $institution = Institution::where('user_id', $user->id)->first();

        if (!$institution) {
            $institution = Institution::create([
                'user_id' => $user->id,
                'npsn' => 'NPSN-' . strval(rand(100000, 999999)),
                'type' => 'sekolah',
                'is_verified' => true,
            ]);
        }
        $institution->load('user');

        $teachersCount = Teacher::where('institution_id', $institution->id)->count();
        $classroomsCount = Classroom::where('institution_id', $institution->id)->count();
        $studentsCount = Student::where('institution_id', $institution->id)
            ->whereHas('user', function ($query) {
                $query->whereIn('role', ['siswa', 'mahasiswa', 'umum']);
            })->count();

        // Get unverified achievements count
        $unverifiedAchievementsCount = \App\Models\Achievement::whereHas('student', function ($query) use ($institution) {
            $query->where('institution_id', $institution->id);
        })->where('is_verified', false)->count();

        $teachers = Teacher::where('institution_id', $institution->id)
            ->with('user')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'institution' => $institution,
                'teachers' => $teachers,
                'stats' => [
                    'teachers_count' => $teachersCount,
                    'classrooms_count' => $classroomsCount,
                    'students_count' => $studentsCount,
                    'pending_achievements_count' => $unverifiedAchievementsCount,
                ]
            ]
        ]);
    }

    /**
     * Add a new teacher under the institution.
     */
    public function addTeacher(Request $request)
    {
        $user = $request->user();
        $institution = Institution::where('user_id', $user->id)->first();

        if (!$institution) {
            return response()->json(['success' => false, 'message' => 'Institution profile not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'nip' => 'nullable|string|unique:teachers,nip',
            'subject' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create User record for teacher
        $teacherUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        // Create Teacher record
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'institution_id' => $institution->id,
            'nip' => $request->nip,
            'subject' => $request->subject,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Teacher registered successfully',
            'data' => [
                'user' => $teacherUser,
                'teacher' => $teacher
            ]
        ], 201);
    }

    /**
     * Get classrooms list for the institution.
     */
    public function getClassrooms(Request $request)
    {
        $user = $request->user();
        $institution = Institution::where('user_id', $user->id)->first();

        if (!$institution) {
            return response()->json(['success' => false, 'message' => 'Institution profile not found'], 404);
        }

        $classrooms = Classroom::where('institution_id', $institution->id)
            ->with(['major', 'academicYear'])
            ->withCount('students')
            ->get();

        return response()->json([
            'success' => true,
            'classrooms' => $classrooms
        ]);
    }
}
