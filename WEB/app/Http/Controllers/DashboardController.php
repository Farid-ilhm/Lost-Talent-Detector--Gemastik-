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
        $user = Auth::user();

        if ($user->role === 'siswa' || $user->role === 'umum') {
            $student = Student::where('user_id', $user->id)
                ->with(['classroom', 'institution'])
                ->first();

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

            return view('dashboard.institusi', compact('institution', 'teachersCount', 'classrooms', 'studentsCount'));
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
        $student->personality = $request->input('personality');
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

        Achievement::create([
            'student_id' => $student->id,
            'title' => $request->title,
            'category' => $request->category,
            'level' => $request->level,
            'rank' => $request->rank,
            'description' => $request->description,
            'is_verified' => false,
        ]);

        return redirect('/dashboard')->with('success', 'Sertifikat prestasi berhasil diajukan untuk verifikasi.');
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
}
