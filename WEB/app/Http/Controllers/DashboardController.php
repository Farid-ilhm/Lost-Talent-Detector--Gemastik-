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
use App\Models\InstitutionAnnouncement;
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
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect('/login')->withErrors([
                'email' => 'Akun Siswa/Mahasiswa/Umum hanya dapat diakses melalui aplikasi Mobile.'
            ]);
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
            $usersCount = User::where('role', '!=', 'admin')->count();
            $verifiedInstitutionsCount = Institution::where('is_verified', true)->count();
            $pendingInstitutionsCount = Institution::where('is_verified', false)->count();
            $aiAnalysesCount = AiAnalysis::count();

            // Counts by role for the chart
            $roleCounts = [
                'siswa' => User::where('role', 'siswa')->count(),
                'mahasiswa' => User::where('role', 'mahasiswa')->count(),
                'guru' => User::where('role', 'guru')->count(),
                'institusi' => User::where('role', 'institusi')->count(),
                'umum' => User::where('role', 'umum')->count(),
            ];

            return view('dashboard.admin', compact(
                'usersCount', 
                'verifiedInstitutionsCount', 
                'pendingInstitutionsCount', 
                'aiAnalysesCount',
                'roleCounts'
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
            'certificate' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $certPath = null;
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $certPath = 'uploads/certificates/' . $fileName;
        }

        $autoVerify = ($user->role === 'umum') || is_null($student->institution_id);

        Achievement::create([
            'student_id' => $student->id,
            'title' => $request->title,
            'category' => $request->category,
            'level' => $request->level,
            'rank' => $request->rank,
            'certificate_path' => $certPath,
            'description' => $request->description,
            'is_verified' => $autoVerify,
        ]);

        if (!$autoVerify) {
            $teachers = \App\Models\Teacher::where('institution_id', $student->institution_id)->get();
            foreach ($teachers as $t) {
                \App\Models\CustomNotification::create([
                    'user_id' => $t->user_id,
                    'title' => 'Verifikasi Sertifikat Baru',
                    'message' => 'Siswa "' . $user->name . '" telah mengajukan sertifikat "' . $request->title . '" untuk diverifikasi.',
                    'type' => 'system',
                    'is_read' => false,
                ]);
            }
        }

        $msg = $autoVerify 
            ? 'Sertifikat prestasi berhasil disimpan.' 
            : 'Sertifikat prestasi berhasil diajukan untuk verifikasi Guru.';

        return redirect('/dashboard')->with('success', $msg);
    }

    /**
     * Delete an achievement (Student panel).
     */
    public function deleteAchievement($id)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        $ach = Achievement::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if ($ach->certificate_path && file_exists(public_path($ach->certificate_path))) {
            @unlink(public_path($ach->certificate_path));
        }

        $ach->delete();

        return redirect('/dashboard')->with('success', 'Sertifikat prestasi berhasil dihapus.');
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

        $uniqueValues = array_unique(array_values($request->answers));
        if (count($request->answers) >= 6 && count($uniqueValues) === 1) {
            return redirect()->back()->withErrors([
                'answers' => 'Jawaban Anda terlalu seragam (diisi dengan nilai yang sama untuk semua soal). Mohon isi tes kuesioner sesuai dengan kecenderungan minat aktual Anda.'
            ]);
        }

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
     * Reset RIASEC test result from web dashboard.
     */
    public function resetTestWeb()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        if ($student) {
            InterestTestResult::where('student_id', $student->id)->delete();
            InterestTestAnswer::where('student_id', $student->id)->delete();
        }
        return redirect('/dashboard')->with('success', 'Hasil tes RIASEC berhasil di-reset.');
    }

    /**
     * Reset AI Analysis result from web dashboard.
     */
    public function resetAiWeb()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $student->id)->first();
        if ($student) {
            AiAnalysis::where('student_id', $student->id)->delete();
        }
        return redirect('/dashboard')->with('success', 'Laporan analisis AI berhasil di-reset.');
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

        return redirect('/teacher/achievements')->with('success', 'Sertifikat berhasil diverifikasi.');
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

        return redirect('/teacher/grades')->with('success', 'Data nilai dan catatan murid berhasil disimpan.');
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

        return redirect('/institution/teachers')->with('success', 'Guru berhasil didaftarkan.');
    }

    /**
     * Admin verifies/approves an institution.
     */
    public function adminVerifyInstitution($id)
    {
        $inst = Institution::findOrFail($id);
        $inst->is_verified = true;
        $inst->save();

        return redirect('/admin/institutions')->with('success', 'Institusi berhasil diverifikasi.');
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

        return redirect('/admin/institutions')->with('success', 'Data institusi berhasil diperbarui.');
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

        return redirect('/admin/institutions')->with('success', 'Institusi berhasil dihapus.');
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
            'link' => 'nullable|url',
            'poster' => 'nullable|image|max:2048',
        ]);

        $posterPath = null;
        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('competitions', 'public');
        }

        \App\Models\Competition::create([
            'title' => $request->title,
            'category' => $request->category,
            'organizer' => $request->organizer,
            'registration_deadline' => $request->registration_deadline,
            'link' => $request->link,
            'description' => $request->description,
            'poster_path' => $posterPath,
            'is_active' => true,
        ]);

        return redirect('/admin/competitions')->with('success', 'Kompetisi berhasil ditambahkan ke database master.');
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
            'poster' => 'nullable|image|max:2048',
        ]);

        $posterPath = $competition->poster_path;
        if ($request->hasFile('poster')) {
            $posterPath = $request->file('poster')->store('competitions', 'public');
        }

        $competition->update([
            'title' => $request->title,
            'category' => $request->category,
            'organizer' => $request->organizer,
            'registration_deadline' => $request->registration_deadline,
            'link' => $request->link,
            'description' => $request->description,
            'poster_path' => $posterPath,
        ]);

        return redirect('/admin/competitions')->with('success', 'Kompetisi berhasil diperbarui.');
    }

    /**
     * Delete a competition.
     */
    public function adminDeleteCompetition($id)
    {
        $competition = \App\Models\Competition::findOrFail($id);
        $competition->delete();

        return redirect('/admin/competitions')->with('success', 'Kompetisi berhasil dihapus.');
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

        return redirect('/institution/teachers')->with('success', 'Data guru berhasil diperbarui.');
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

        return redirect('/institution/teachers')->with('success', 'Guru berhasil dihapus.');
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

        return redirect('/teacher/students')->with('success', 'Data murid berhasil diperbarui.');
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

        return redirect('/teacher/students')->with('success', 'Akun murid berhasil dihapus.');
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

        return redirect('/institution/classrooms')->with('success', 'Kelas berhasil dihapus.');
    }

    /**
     * Institution Classrooms View
     */
    public function institutionClassroomsView()
    {
        $user = Auth::user();
        if ($user->role !== 'institusi') {
            abort(403);
        }

        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $classrooms = Classroom::where('institution_id', $institution->id)->with('students')->get();

        return view('dashboard.institusi_classrooms', compact('institution', 'classrooms'));
    }

    /**
     * Institution Teachers View
     */
    public function institutionTeachersView()
    {
        $user = Auth::user();
        if ($user->role !== 'institusi') {
            abort(403);
        }

        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $teachers = Teacher::where('institution_id', $institution->id)->with('user')->get();

        return view('dashboard.institusi_teachers', compact('institution', 'teachers'));
    }

    /**
     * Teacher Achievements View
     */
    public function teacherAchievementsView()
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            abort(403);
        }

        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $pendingAchievements = Achievement::whereHas('student', function ($q) use ($teacher) {
            $q->where('institution_id', $teacher->institution_id);
        })->where('is_verified', false)->with('student.user')->get();

        return view('dashboard.guru_achievements', compact('teacher', 'pendingAchievements'));
    }

    /**
     * Teacher Grades & Notes View
     */
    public function teacherGradesView()
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            abort(403);
        }

        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $students = Student::where('institution_id', $teacher->institution_id)->with(['user', 'classroom'])->get();

        return view('dashboard.guru_grades', compact('teacher', 'students'));
    }

    /**
     * Teacher Students List View
     */
    public function teacherStudentsView()
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            abort(403);
        }

        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $students = Student::where('institution_id', $teacher->institution_id)->with(['user', 'classroom'])->get();

        return view('dashboard.guru_students', compact('teacher', 'students'));
    }

    /**
     * Delete multiple master competitions at once (bulk delete).
     */
    public function adminDeleteMultipleCompetitions(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $ids = $request->input('comp_ids', []);
        if (count($ids) > 0) {
            \App\Models\Competition::whereIn('id', $ids)->delete();
            return redirect('/admin/competitions')->with('success', count($ids) . ' kompetisi berhasil dihapus sekaligus.');
        }

        return redirect('/admin/competitions')->with('error', 'Tidak ada kompetisi yang dipilih.');
    }

    /**
     * Admin Institutions View
     */
    public function adminInstitutionsView()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        $institutions = Institution::with('user')->get();
        return view('dashboard.admin_institutions', compact('institutions'));
    }

    /**
     * Admin Competitions View
     */
    public function adminCompetitionsView()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
        $this->purgeExpiredContent();
        $competitions = \App\Models\Competition::orderBy('created_at', 'desc')->get();
        return view('dashboard.admin_competitions', compact('competitions'));
    }

    /**
     * Admin Users View with Search/Filter
     */
    public function adminUsersView(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $query = User::query()->where('role', '!=', 'admin');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->get();
        $institutions = Institution::where('is_verified', true)->with('user')->get();

        return view('dashboard.admin_users', compact('users', 'institutions'));
    }

    /**
     * Admin Save User
     */
    public function adminSaveUser(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:siswa,mahasiswa,umum,guru,institusi,admin',
            'phone' => 'nullable|string',
            'institution_id' => 'required_if:role,guru,siswa,mahasiswa|nullable|exists:institutions,id',
            'npsn' => 'required_if:role,institusi|nullable|string|unique:institutions,npsn',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => 'active',
        ]);

        if (in_array($request->role, ['siswa', 'mahasiswa', 'umum'])) {
            Student::create([
                'user_id' => $user->id,
                'institution_id' => $request->institution_id,
            ]);
        } elseif ($request->role === 'guru') {
            Teacher::create([
                'user_id' => $user->id,
                'institution_id' => $request->institution_id,
            ]);
        } elseif ($request->role === 'institusi') {
            Institution::create([
                'user_id' => $user->id,
                'npsn' => $request->npsn,
                'type' => 'sekolah',
                'is_verified' => true,
            ]);
        }

        return redirect('/admin/users')->with('success', 'User ' . $user->name . ' berhasil ditambahkan.');
    }

    /**
     * Admin Edit User
     */
    public function adminEditUser($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $user = User::with(['student', 'teacher', 'institution'])->findOrFail($id);
        $institutions = Institution::where('is_verified', true)->with('user')->get();

        return view('dashboard.admin_edit_user', compact('user', 'institutions'));
    }

    /**
     * Admin Update User
     */
    public function adminUpdateUser(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:siswa,mahasiswa,umum,guru,institusi,admin',
            'phone' => 'nullable|string',
            'password' => 'nullable|string|min:8',
            'institution_id' => 'required_if:role,guru,siswa,mahasiswa|nullable|exists:institutions,id',
            'npsn' => 'required_if:role,institusi|nullable|string|unique:institutions,npsn,' . ($user->institution->id ?? 'NULL'),
        ]);

        $oldRole = $user->role;

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // Delete old profile if role changed
        if ($oldRole !== $request->role) {
            if (in_array($oldRole, ['siswa', 'mahasiswa', 'umum'])) {
                Student::where('user_id', $user->id)->delete();
            } elseif ($oldRole === 'guru') {
                Teacher::where('user_id', $user->id)->delete();
            } elseif ($oldRole === 'institusi') {
                Institution::where('user_id', $user->id)->delete();
            }
        }

        // Create or update new profile
        if (in_array($request->role, ['siswa', 'mahasiswa', 'umum'])) {
            Student::updateOrCreate(
                ['user_id' => $user->id],
                ['institution_id' => $request->institution_id]
            );
        } elseif ($request->role === 'guru') {
            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                ['institution_id' => $request->institution_id]
            );
        } elseif ($request->role === 'institusi') {
            Institution::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'npsn' => $request->npsn,
                    'type' => 'sekolah',
                    'is_verified' => true,
                ]
            );
        }

        return redirect('/admin/users')->with('success', 'User ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Admin Delete User
     */
    public function adminDeleteUser($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect('/admin/users')->with('success', 'User berhasil dihapus.');
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi baru minimal harus 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = Auth::user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
        }

        $user->password = \Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Kata sandi Anda berhasil diperbarui!');
    }

    /**
     * Update Profile (Web)
     */
    public function updateProfileWeb(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ];

        if ($user->role === 'institusi') {
            $rules['address'] = 'required|string';
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        if ($user->role === 'institusi') {
            $institution = \App\Models\Institution::where('user_id', $user->id)->first();
            if ($institution) {
                $institution->address = $request->address;
                $institution->save();
            }
        }

        return redirect()->back()->with('success', 'Profil Anda berhasil diperbarui!');
    }

    /**
     * Get User Notifications
     */
    public function getNotifications()
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['admin', 'guru'])) {
            return response()->json(['count' => 0, 'notifications' => []]);
        }

        $notifications = \App\Models\CustomNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark User Notifications as Read
     */
    public function markNotificationsRead()
    {
        $user = Auth::user();
        if ($user && in_array($user->role, ['admin', 'guru'])) {
            \App\Models\CustomNotification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Cleanup expired announcements & master competitions from DB and storage.
     */
    private function purgeExpiredContent()
    {
        $today = \Carbon\Carbon::today();

        // 1. Purge expired Institution Announcements
        $expiredAnnouncements = InstitutionAnnouncement::whereNotNull('expired_at')
            ->where('expired_at', '<', $today)
            ->get();

        foreach ($expiredAnnouncements as $ann) {
            if ($ann->banner_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($ann->banner_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($ann->banner_image);
            }
            $ann->delete();
        }

        // 2. Purge expired Master Competitions
        $expiredCompetitions = \App\Models\Competition::whereNotNull('registration_deadline')
            ->where('registration_deadline', '<', $today)
            ->get();

        foreach ($expiredCompetitions as $comp) {
            if ($comp->poster_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($comp->poster_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($comp->poster_path);
            }
            $comp->delete();
        }
    }

    /**
     * View Institution Announcements page.
     */
    public function institutionAnnouncementsView()
    {
        $this->purgeExpiredContent();

        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $announcements = InstitutionAnnouncement::where('institution_id', $institution->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.institusi_announcements', compact('institution', 'announcements'));
    }

    /**
     * Save new Institution Announcement.
     */
    public function institutionSaveAnnouncement(Request $request)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:pengumuman,beasiswa,pelatihan,lomba,kegiatan',
            'target_talent' => 'nullable|string|max:100',
            'content' => 'required|string',
            'external_link' => 'nullable|url',
            'banner_image' => 'nullable|image|max:2048',
            'expired_at' => 'nullable|date|after_or_equal:today',
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('announcements', 'public');
        }

        InstitutionAnnouncement::create([
            'institution_id' => $institution->id,
            'title' => $request->title,
            'category' => $request->category,
            'target_talent' => $request->target_talent ?? 'Semua',
            'content' => $request->content,
            'banner_image' => $bannerPath,
            'external_link' => $request->external_link,
            'expired_at' => $request->expired_at,
            'is_published' => true,
        ]);

        return redirect('/institution/announcements')->with('success', 'Informasi/Pengumuman berhasil dipublikasikan.');
    }

    /**
     * Show edit page for an announcement.
     */
    public function institutionEditAnnouncement($id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $announcement = InstitutionAnnouncement::where('id', $id)
            ->where('institution_id', $institution->id)
            ->firstOrFail();

        return view('dashboard.institusi_edit_announcement', compact('announcement'));
    }

    /**
     * Update an announcement.
     */
    public function institutionUpdateAnnouncement(Request $request, $id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $announcement = InstitutionAnnouncement::where('id', $id)
            ->where('institution_id', $institution->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:pengumuman,beasiswa,pelatihan,lomba,kegiatan',
            'target_talent' => 'nullable|string|max:100',
            'content' => 'required|string',
            'external_link' => 'nullable|url',
            'banner_image' => 'nullable|image|max:2048',
            'expired_at' => 'nullable|date',
        ]);

        if ($request->hasFile('banner_image')) {
            $announcement->banner_image = $request->file('banner_image')->store('announcements', 'public');
        }

        $announcement->title = $request->title;
        $announcement->category = $request->category;
        $announcement->target_talent = $request->target_talent ?? 'Semua';
        $announcement->content = $request->content;
        $announcement->external_link = $request->external_link;
        $announcement->expired_at = $request->expired_at;
        $announcement->is_published = true;
        $announcement->save();

        return redirect('/institution/announcements')->with('success', 'Informasi/Pengumuman berhasil diperbarui.');
    }

    /**
     * Delete an announcement.
     */
    public function institutionDeleteAnnouncement($id)
    {
        $user = Auth::user();
        $institution = Institution::where('user_id', $user->id)->firstOrFail();
        $announcement = InstitutionAnnouncement::where('id', $id)
            ->where('institution_id', $institution->id)
            ->firstOrFail();

        if ($announcement->banner_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($announcement->banner_image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($announcement->banner_image);
        }

        $announcement->delete();

        return redirect('/institution/announcements')->with('success', 'Informasi/Pengumuman berhasil dihapus.');
    }
}
