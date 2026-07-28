<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\AcademicGrade;
use App\Models\Achievement;
use App\Models\TeacherNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherApiController extends Controller
{
    /**
     * Get the teacher profile and list of students in the same institution.
     */
    public function getStudentsList(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found'
            ], 404);
        }

        // Get all students enrolled in the same institution
        $students = Student::where('institution_id', $teacher->institution_id)
            ->with(['user', 'classroom'])
            ->get();

        return response()->json([
            'success' => true,
            'teacher' => $teacher,
            'students' => $students
        ]);
    }

    /**
     * Input academic grades for a student.
     */
    public function inputStudentGrades(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'semester' => 'required|integer|between:1,6',
            'subject_name' => 'required|string|max:100',
            'score' => 'required|numeric|between:0,100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify student belongs to the same institution
        $student = Student::find($request->student_id);
        if ($student->institution_id !== $teacher->institution_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Student is not in your institution.'
            ], 403);
        }

        // Create or update grade
        $grade = AcademicGrade::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'semester' => $request->semester,
                'subject_name' => $request->subject_name,
            ],
            [
                'score' => $request->score,
                'created_by' => $user->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Grade recorded successfully',
            'grade' => $grade
        ]);
    }

    /**
     * Verify/Approve a student achievement certificate.
     */
    public function verifyAchievement(Request $request, $achievementId)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
        }

        $achievement = Achievement::with('student')->find($achievementId);

        if (!$achievement) {
            return response()->json([
                'success' => false,
                'message' => 'Achievement not found'
            ], 404);
        }

        // Verify student belongs to same school
        if ($achievement->student->institution_id !== $teacher->institution_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to verify this achievement.'
            ], 403);
        }

        $achievement->is_verified = true;
        $achievement->verified_by = $user->id;
        $achievement->save();

        return response()->json([
            'success' => true,
            'message' => 'Achievement successfully verified.',
            'achievement' => $achievement
        ]);
    }

    /**
     * Input teacher behavioral/development notes for a student.
     */
    public function inputTeacherNote(Request $request)
    {
        $user = $request->user();
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Teacher profile not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $student = Student::find($request->student_id);
        if ($student->institution_id !== $teacher->institution_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Student is not in your institution.'
            ], 403);
        }

        $note = TeacherNote::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'teacher_id' => $teacher->id,
            ],
            [
                'notes' => $request->notes,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Teacher note saved successfully',
            'note' => $note
        ]);
    }
}
