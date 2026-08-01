<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Institution;
use App\Models\AcademicGrade;
use App\Models\Achievement;
use App\Models\TeacherNote;
use App\Models\InterestTest;
use App\Models\InterestTestAnswer;
use App\Models\InterestTestResult;
use App\Models\AiAnalysis;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Route user based on role to their dashboard.
     */
    public function index()
    {
        // Automatically clean up expired competitions
        \App\Models\Competition::whereNotNull('registration_deadline')
            ->where('registration_deadline', '<', \Carbon\Carbon::now()->toDateString())
            ->delete();

        $user = Auth::user();

        if ($user->role === 'siswa' || $user->role === 'mahasiswa' || $user->role === 'umum') {
            $student = Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'institution_id' => null,
                    'classroom_id' => null,
                ]
            );
            $student->load(['classroom', 'institution.user']);

            $grades = AcademicGrade::where('student_id', $student->id)->get();
            $achievements = Achievement::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
            $testResult = InterestTestResult::where('student_id', $student->id)->latest()->first();
            $aiAnalysis = AiAnalysis::where('student_id', $student->id)->latest()->first();
            
            // Get active test
            $activeTest = InterestTest::where('is_active', true)->with('questions')->first();

            return view('dashboard.siswa', compact('student', 'grades', 'achievements', 'testResult', 'aiAnalysis', 'activeTest'));
        }

        if ($user->role === 'guru') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            $students = Student::where('institution_id', $teacher->institution_id)->with(['user', 'classroom'])->get();
            
            $pendingAchievements = Achievement::whereHas('student', function ($q) use ($teacher) {
                $q->where('institution_id', $teacher->institution_id);
            })->where('is_verified', false)->with('student.user')->get();

            return view('dashboard.guru', compact('teacher', 'students', 'pendingAchievements'));
        }

        if ($user->role === 'institusi') {
            $institution = Institution::where('user_id', $user->id)->first();
            
            $teachersCount = Teacher::where('institution_id', $institution->id)->count();
            $classrooms = Classroom::where('institution_id', $institution->id)->with('students')->get();
            $studentsCount = Student::where('institution_id', $institution->id)->count();
            $teachers = Teacher::where('institution_id', $institution->id)->with('user')->get();

            return view('dashboard.institusi', compact('institution', 'teachersCount', 'classrooms', 'studentsCount', 'teachers'));
        }

        if ($user->role === 'admin') {
            $usersCount = User::count();
            $verifiedInstitutionsCount = Institution::where('is_verified', true)->count();
            $pendingInstitutionsCount = Institution::where('is_verified', false)->count();
            $aiAnalysesCount = AiAnalysis::count();
            $institutions = Institution::with('user')->get();
            $competitions = \App\Models\Competition::orderBy('created_at', 'desc')->get();

            return view('dashboard.admin', compact(
                'usersCount', 
                'verifiedInstitutionsCount', 
                'pendingInstitutionsCount', 
                'aiAnalysesCount', 
                'institutions', 
                'competitions'
            ));
        }

        // Default fallback
        return response('Dashboard polos. Selamat datang ' . $user->name);
    }

    /**
     * Save/Update student interests & hobbies.
     */
    public function saveInterests(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $hobbies = array_filter(array_map('trim', explode(',', $request->input('hobbies', ''))));
        $interests = array_filter(array_map('trim', explode(',', $request->input('interests', ''))));

        $student->hobbies = $hobbies;
        $student->interests = $interests;
        $student->save();

        return redirect('/dashboard')->with('success', 'Minat dan hobi berhasil diperbarui.');
    }

    /**
     * Student uploads achievement.
     */
    public function saveAchievement(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required',
            'level' => 'required',
            'rank' => 'required|string',
        ]);

        $autoVerify = is_null($student->institution_id);

        Achievement::create([
            'student_id' => $student->id,
            'title' => $request->title,
            'category' => $request->category,
            'level' => $request->level,
            'rank' => $request->rank,
            'description' => $request->description,
            'is_verified' => $autoVerify,
        ]);

        $msg = $autoVerify 
            ? 'Sertifikat prestasi berhasil disimpan.' 
            : 'Sertifikat prestasi berhasil diajukan untuk verifikasi Guru.';

        return redirect('/dashboard')->with('success', $msg);
    }

    /**
     * Student self-inputs academic grade (for Public / Mandiri users).
     */
    public function studentSaveGrade(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if ($student->institution_id !== null) {
            return redirect('/dashboard')->with('error', 'Murid sekolah hanya dapat diinput nilainya oleh Guru.');
        }

        $request->validate([
            'semester' => 'required|integer',
            'subject_name' => 'required|string',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        AcademicGrade::updateOrCreate(
            [
                'student_id' => $student->id,
                'semester' => $request->semester,
                'subject_name' => $request->subject_name,
            ],
            [
                'score' => $request->score,
                'created_by' => $user->id,
            ]
        );

        return redirect('/dashboard')->with('success', 'Nilai akademik mandiri berhasil disimpan.');
    }

    /**
     * Submit RIASEC Test answers from the web dashboard.
     */
    public function saveTest(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $request->validate([
            'test_id' => 'required',
            'answers' => 'required|array',
        ]);

        // Clear existing answers for this test
        InterestTestAnswer::where('student_id', $student->id)->delete();

        $scores = [
            'Realistic' => 0, 'Investigative' => 0, 'Artistic' => 0,
            'Social' => 0, 'Enterprising' => 0, 'Conventional' => 0
        ];
        $counts = [
            'Realistic' => 0, 'Investigative' => 0, 'Artistic' => 0,
            'Social' => 0, 'Enterprising' => 0, 'Conventional' => 0
        ];

        foreach ($request->answers as $qId => $val) {
            InterestTestAnswer::create([
                'student_id' => $student->id,
                'interest_test_question_id' => $qId,
                'answer_value' => $val,
            ]);

            $question = \App\Models\InterestTestQuestion::find($qId);
            if ($question && isset($scores[$question->category])) {
                $scores[$question->category] += intval($val);
                $counts[$question->category]++;
            }
        }

        // Normalize
        $normalized = [];
        $dominant = 'Realistic';
        $hi = -1;
        foreach ($scores as $cat => $total) {
            $cnt = $counts[$cat];
            $pct = $cnt > 0 ? round(($total / ($cnt * 5)) * 100) : 0;
            $normalized[$cat] = $pct;
            if ($pct > $hi) {
                $hi = $pct;
                $dominant = $cat;
            }
        }

        InterestTestResult::where('student_id', $student->id)->delete();
        InterestTestResult::create([
            'student_id' => $student->id,
            'interest_test_id' => $request->test_id,
            'scores' => $normalized,
            'dominant_category' => $dominant,
        ]);

        return redirect('/dashboard')->with('success', 'Jawaban tes berhasil disimpan.');
    }

    /**
     * Trigger AI analysis from dashboard.
     */
    public function triggerAi()
    {
        $user = Auth::user();
        
        // Emulate request to trigger analyzeTalent
        $apiController = new \App\Http\Controllers\Api\StudentApiController();
        
        $request = new Request();
        $request->setUserResolver(function() use ($user) {
            return $user;
        });

        $res = $apiController->analyzeTalent($request);
        $data = $res->getData();

        if (isset($data->success) && $data->success) {
            return redirect('/dashboard')->with('success', 'Analisis AI berhasil diperbarui!');
        }

        return redirect('/dashboard')->with('error', $data->message ?? 'Gagal memicu analisis AI.');
    }

    /**
     * Teacher inputs student grade.
     */
    public function teacherSaveGrade(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'semester' => 'required|integer',
            'subject_name' => 'required',
            'score' => 'required|numeric',
        ]);

        AcademicGrade::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'semester' => $request->semester,
                'subject_name' => $request->subject_name,
            ],
            [
                'score' => $request->score,
                'created_by' => Auth::id(),
            ]
        );

        return redirect('/dashboard')->with('success', 'Nilai murid berhasil dimasukkan.');
    }

    /**
     * Teacher verifies a student's achievement.
     */
    public function teacherVerify($id)
    {
        $ach = Achievement::findOrFail($id);
        $ach->is_verified = true;
        $ach->verified_by = Auth::id();
        $ach->save();

        return redirect('/dashboard')->with('success', 'Sertifikat berhasil diverifikasi.');
    }

    /**
     * Teacher inputs notes.
     */
    public function teacherSaveNote(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'notes' => 'required',
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->first();

        TeacherNote::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'teacher_id' => $teacher->id,
            ],
            [
                'notes' => $request->notes,
            ]
        );

        return redirect('/dashboard')->with('success', 'Catatan perkembangan berhasil disimpan.');
    }

    /**
     * Teacher saves both student grades and notes in a single request.
     */
    public function teacherSaveStudentData(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'semester' => 'nullable|integer',
            'subject_name' => 'nullable|string',
            'score' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $teacher = Teacher::where('user_id', Auth::id())->first();

        // 1. Save Grade if filled
        if ($request->filled('subject_name') && $request->filled('score') && $request->filled('semester')) {
            AcademicGrade::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'semester' => $request->semester,
                    'subject_name' => $request->subject_name,
                ],
                [
                    'score' => $request->score,
                    'created_by' => Auth::id(),
                ]
            );
        }

        // 2. Save Note if filled
        if ($request->filled('notes')) {
            TeacherNote::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'teacher_id' => $teacher->id,
                ],
                [
                    'notes' => $request->notes,
                ]
            );
        }

        return redirect('/dashboard')->with('success', 'Data nilai dan catatan murid berhasil disimpan.');
    }

    /**
     * Institution registers a teacher.
     */
    public function institutionSaveTeacher(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        $inst = Institution::where('user_id', Auth::id())->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'status' => 'active',
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'institution_id' => $inst->id,
            'nip' => $request->nip,
            'subject' => $request->subject,
        ]);

        return redirect('/dashboard')->with('success', 'Guru berhasil didaftarkan.');
    }

    /**
     * Admin verifies/approves an institution.
     */
    public function adminVerifyInstitution($id)
    {
        $inst = Institution::findOrFail($id);
        $inst->is_verified = true;
        $inst->save();

        return redirect('/dashboard')->with('success', 'Institusi berhasil diverifikasi.');
    }

    /**
     * Admin rejects and deletes an institution registration.
     */
    /**
     * Show edit page for an institution.
     */
    public function adminEditInstitution($id)
    {
        $institution = Institution::with('user')->findOrFail($id);
        return view('dashboard.admin_edit_institution', compact('institution'));
    }

    /**
     * Update an institution's data.
     */
    public function adminUpdateInstitution(Request $request, $id)
    {
        $inst = Institution::findOrFail($id);
        $user = $inst->user;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'npsn' => 'required|string|unique:institutions,npsn,' . $inst->id,
            'type' => 'required|in:sekolah,universitas',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $inst->update([
            'npsn' => $request->npsn,
            'type' => $request->type,
        ]);

        return redirect('/dashboard')->with('success', 'Data institusi berhasil diperbarui.');
    }

    /**
     * Admin deletes an institution (unified delete for verified/unverified).
     */
    public function adminDeleteInstitution($id)
    {
        $inst = Institution::findOrFail($id);
        $user = $inst->user;
        
        $inst->delete();
        if ($user) {
            $user->delete();
        }

        return redirect('/dashboard')->with('success', 'Institusi berhasil dihapus.');
    }

    /**
     * Admin saves a new competition master entry.
     */
    public function adminSaveCompetition(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required',
            'organizer' => 'nullable|string',
            'registration_deadline' => 'nullable|date',
        ]);

        \App\Models\Competition::create([
            'title' => $request->title,
            'category' => $request->category,
            'organizer' => $request->organizer,
            'registration_deadline' => $request->registration_deadline,
            'link' => $request->link,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect('/dashboard')->with('success', 'Kompetisi berhasil ditambahkan ke database master.');
    }

    /**
     * Show edit page for a competition.
     */
    public function adminEditCompetition($id)
    {
        $competition = \App\Models\Competition::findOrFail($id);
        return view('dashboard.admin_edit_competition', compact('competition'));
    }

    /**
     * Update a competition's data.
     */
    public function adminUpdateCompetition(Request $request, $id)
    {
        $competition = \App\Models\Competition::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required',
            'organizer' => 'nullable|string',
            'registration_deadline' => 'nullable|date',
            'link' => 'nullable|url',
        ]);

        $competition->update([
            'title' => $request->title,
            'category' => $request->category,
            'organizer' => $request->organizer,
            'registration_deadline' => $request->registration_deadline,
            'link' => $request->link,
            'description' => $request->description,
        ]);

        return redirect('/dashboard')->with('success', 'Kompetisi berhasil diperbarui.');
    }

    /**
     * Delete a competition.
     */
    public function adminDeleteCompetition($id)
    {
        $competition = \App\Models\Competition::findOrFail($id);
        $competition->delete();

        return redirect('/dashboard')->with('success', 'Kompetisi berhasil dihapus.');
    }

    /**
     * Show edit page for a teacher.
     */
    public function institutionEditTeacher($id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);
        
        $inst = Institution::where('user_id', Auth::id())->first();
        if ($teacher->institution_id !== $inst->id) {
            abort(403);
        }

        return view('dashboard.institusi_edit_teacher', compact('teacher'));
    }

    /**
     * Update a teacher's data.
     */
    public function institutionUpdateTeacher(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        $user = $teacher->user;

        $inst = Institution::where('user_id', Auth::id())->first();
        if ($teacher->institution_id !== $inst->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nip' => 'nullable|string',
            'subject' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $userUpdate = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userUpdate['password'] = Hash::make($request->password);
        }

        $user->update($userUpdate);

        $teacher->update([
            'nip' => $request->nip,
            'subject' => $request->subject,
        ]);

        return redirect('/dashboard')->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Delete a teacher from the institution.
     */
    public function institutionDeleteTeacher($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        $inst = Institution::where('user_id', Auth::id())->first();
        if ($teacher->institution_id !== $inst->id) {
            abort(403);
        }

        $user = $teacher->user;
        $teacher->delete();
        if ($user) {
            $user->delete();
        }

        return redirect('/dashboard')->with('success', 'Guru berhasil dihapus.');
    }

    /**
     * Show edit page for a student (Teacher panel).
     */
    public function teacherEditStudent($id)
    {
        $student = Student::with(['user', 'classroom.major'])->findOrFail($id);

        $teacher = Teacher::where('user_id', Auth::id())->first();
        if ($student->institution_id !== $teacher->institution_id) {
            abort(403);
        }

        return view('dashboard.guru_edit_student', compact('student'));
    }

    /**
     * Update a student's profile (Teacher panel).
     */
    public function teacherUpdateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $user = $student->user;

        $teacher = Teacher::where('user_id', Auth::id())->first();
        if ($student->institution_id !== $teacher->institution_id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nisn' => 'nullable|string|unique:students,nisn,' . $student->id,
            'nim' => 'nullable|string|unique:students,nim,' . $student->id,
            'classroom' => 'nullable|string|max:50',
            'major' => 'nullable|string|max:50',
            'semester' => 'nullable|integer|min:1|max:14',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $userUpdate = [
            'name' => $request->name,
            'email' => $request->email,
        ];
        if ($request->filled('password')) {
            $userUpdate['password'] = Hash::make($request->password);
        }
        $user->update($userUpdate);

        $studentUpdate = [
            'nisn' => $request->nisn,
            'nim' => $request->nim,
            'semester' => $request->semester,
        ];

        if ($request->filled('classroom') || ($student->user->role === 'mahasiswa' && $request->filled('major'))) {
            $academicYear = \App\Models\AcademicYear::firstOrCreate(
                [
                    'institution_id' => $teacher->institution_id,
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
                    'institution_id' => $teacher->institution_id,
                ]);
                $majorId = $major->id;
            }

            $classroomName = $student->user->role === 'mahasiswa' ? ("Semester " . $request->semester) : $request->classroom;

            if ($classroomName) {
                $classroom = \App\Models\Classroom::firstOrCreate([
                    'name' => $classroomName,
                    'institution_id' => $teacher->institution_id,
                    'academic_year_id' => $academicYear->id,
                    'major_id' => $majorId,
                ]);
                $studentUpdate['classroom_id'] = $classroom->id;
            }
        }

        $student->update($studentUpdate);

        return redirect('/dashboard')->with('success', 'Data murid berhasil diperbarui.');
    }

    /**
     * Delete a student account (Teacher panel).
     */
    public function teacherDeleteStudent($id)
    {
        $student = Student::findOrFail($id);
        
        $teacher = Teacher::where('user_id', Auth::id())->first();
        if ($student->institution_id !== $teacher->institution_id) {
            abort(403);
        }

        $user = $student->user;
        $student->delete();
        if ($user) {
            $user->delete();
        }

        return redirect('/dashboard')->with('success', 'Akun murid berhasil dihapus.');
    }

    /**
     * Delete a classroom from the institution.
     */
    public function institutionDeleteClassroom($id)
    {
        $classroom = Classroom::findOrFail($id);

        $inst = Institution::where('user_id', Auth::id())->first();
        if ($classroom->institution_id !== $inst->id) {
            abort(403);
        }

        // Dissociate students in this classroom
        Student::where('classroom_id', $classroom->id)->update(['classroom_id' => null]);

        $classroom->delete();

        return redirect('/dashboard')->with('success', 'Kelas berhasil dihapus.');
    }
}
