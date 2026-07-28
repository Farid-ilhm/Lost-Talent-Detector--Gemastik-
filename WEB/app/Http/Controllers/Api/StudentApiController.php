<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Achievement;
use App\Models\InterestTest;
use App\Models\InterestTestAnswer;
use App\Models\InterestTestResult;
use App\Models\AiAnalysis;
use App\Models\AcademicGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StudentApiController extends Controller
{
    /**
     * Get student details and dashboard metrics.
     */
    public function getDashboard(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)
            ->with(['classroom', 'institution'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found'
            ], 404);
        }

        $gradesCount = AcademicGrade::where('student_id', $student->id)->count();
        $achievements = Achievement::where('student_id', $student->id)->orderBy('created_at', 'desc')->get();
        $testResult = InterestTestResult::where('student_id', $student->id)->latest()->first();
        $aiAnalysis = AiAnalysis::where('student_id', $student->id)->latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $student,
                'metrics' => [
                    'grades_count' => $gradesCount,
                    'achievements_count' => $achievements->count(),
                    'has_test_result' => !is_null($testResult),
                    'has_ai_analysis' => !is_null($aiAnalysis),
                ],
                'achievements' => $achievements,
                'test_result' => $testResult,
                'ai_analysis' => $aiAnalysis,
            ]
        ]);
    }

    /**
     * Update student hobbies and interests.
     */
    public function updateInterestsAndHobbies(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'hobbies' => 'required|array',
            'interests' => 'required|array',
            'personality' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $student->hobbies = $request->hobbies;
        $student->interests = $request->interests;
        if ($request->has('personality')) {
            $student->personality = $request->personality;
        }
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Interests and hobbies updated successfully',
            'student' => $student
        ]);
    }

    /**
     * Upload an achievement.
     */
    public function uploadAchievement(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|in:akademik,olahraga,seni,sains,teknologi,keagamaan,lainnya',
            'level' => 'required|in:sekolah,kecamatan,kabupaten,provinsi,nasional,internasional',
            'rank' => 'required|string|max:100', // e.g. Juara 1, Finalis
            'description' => 'nullable|string',
            'certificate' => 'nullable|string', // Simulated base64 or URL path
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // For demo/GEMASTIK purposes, we'll store path or simulate file storage
        $certPath = null;
        if ($request->filled('certificate')) {
            $certPath = 'certificates/' . uniqid() . '.pdf';
        }

        $achievement = Achievement::create([
            'student_id' => $student->id,
            'title' => $request->title,
            'category' => $request->category,
            'level' => $request->level,
            'rank' => $request->rank,
            'certificate_path' => $certPath,
            'description' => $request->description,
            'is_verified' => false, // Requires teacher or admin verification
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Achievement uploaded successfully. Awaiting verification.',
            'achievement' => $achievement
        ], 201);
    }

    /**
     * Fetch active Interest/Bakat tests and questions.
     */
    public function getRiasecTest()
    {
        $test = InterestTest::where('is_active', true)->with('questions')->first();

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'No active test found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'test' => $test
        ]);
    }

    /**
     * Submit answers for the RIASEC test and calculate aggregate category scores.
     */
    public function submitTestAnswers(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'test_id' => 'required|exists:interest_tests,id',
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|exists:interest_test_questions,id',
            'answers.*.value' => 'required|integer|between:1,5', // Likert scale 1-5
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Delete previous answers for this test if they exist (allows re-taking)
        InterestTestAnswer::where('student_id', $student->id)
            ->whereIn('interest_test_question_id', function ($query) use ($request) {
                $query->select('id')
                    ->from('interest_test_questions')
                    ->where('interest_test_id', $request->test_id);
            })->delete();

        // Save answers
        $scoresByCategory = [
            'Realistic' => 0,
            'Investigative' => 0,
            'Artistic' => 0,
            'Social' => 0,
            'Enterprising' => 0,
            'Conventional' => 0
        ];
        
        $countsByCategory = [
            'Realistic' => 0,
            'Investigative' => 0,
            'Artistic' => 0,
            'Social' => 0,
            'Enterprising' => 0,
            'Conventional' => 0
        ];

        foreach ($request->answers as $ans) {
            InterestTestAnswer::create([
                'student_id' => $student->id,
                'interest_test_question_id' => $ans['question_id'],
                'answer_value' => (string) $ans['value'],
            ]);

            // Query category of the question to calculate totals
            $question = \App\Models\InterestTestQuestion::find($ans['question_id']);
            if ($question && isset($scoresByCategory[$question->category])) {
                $scoresByCategory[$question->category] += $ans['value'];
                $countsByCategory[$question->category]++;
            }
        }

        // Normalize scores to percentage (score out of max score: count * 5)
        $normalizedScores = [];
        $highestScore = -1;
        $dominant = 'Realistic';

        foreach ($scoresByCategory as $category => $total) {
            $count = $countsByCategory[$category];
            $percent = $count > 0 ? round(($total / ($count * 5)) * 100) : 0;
            $normalizedScores[$category] = $percent;

            if ($percent > $highestScore) {
                $highestScore = $percent;
                $dominant = $category;
            }
        }

        // Save summarized result
        InterestTestResult::where('student_id', $student->id)
            ->where('interest_test_id', $request->test_id)
            ->delete();

        $result = InterestTestResult::create([
            'student_id' => $student->id,
            'interest_test_id' => $request->test_id,
            'scores' => $normalizedScores,
            'dominant_category' => $dominant,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Answers submitted successfully. Results compiled.',
            'result' => $result
        ]);
    }

    /**
     * Run high-fidelity simulation of the AI Talent Recommendation Engine.
     * Evaluates grades, achievements, interest test scores, and hobbies.
     */
    public function analyzeTalent(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)
            ->with(['academicGrades', 'achievements', 'interestTestResults'])
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student profile not found'], 404);
        }

        // Gather metrics for analysis
        $grades = $student->academicGrades;
        $achievements = $student->achievements->where('is_verified', true);
        $testResult = $student->interestTestResults->last();
        $hobbies = $student->hobbies ?? [];
        $interests = $student->interests ?? [];

        // Check if there is enough data
        if ($grades->isEmpty() && $achievements->isEmpty() && !$testResult) {
            return response()->json([
                'success' => false,
                'message' => 'Sufficient data is missing for AI analysis. Please ensure you have input grades, uploaded verified achievements, or completed the RIASEC interest test.'
            ], 400);
        }

        try {
            // Attempt to call Python AI Service
            $response = \Illuminate\Support\Facades\Http::timeout(3)->post('http://127.0.0.1:5000/predict', [
                'riasec' => $testResult ? $testResult->scores : new \stdClass(),
                'grades' => $grades->groupBy('subject_name')->map(function ($items) {
                    return floatval($items->avg('score'));
                })->toArray(),
                'achievements' => $achievements->map(function ($ach) {
                    return [
                        'category' => $ach->category,
                        'level' => $ach->level,
                    ];
                })->toArray(),
                'hobbies' => $hobbies,
                'interests' => $interests,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Clear previous analysis
                AiAnalysis::where('student_id', $student->id)->delete();

                $analysis = AiAnalysis::create([
                    'student_id' => $student->id,
                    'primary_talent' => $data['primary_talent'],
                    'confidence_score' => floatval($data['confidence_score']),
                    'supporting_talents' => $data['supporting_talents'],
                    'reasoning' => $data['reasoning'],
                    'career_recommendations' => $data['career_recommendations'],
                    'extracurricular_recommendations' => $data['extracurricular_recommendations'],
                    'competition_recommendations' => $data['competition_recommendations'],
                    'development_targets' => $data['development_targets'],
                    'model_version' => $data['model_version'] ?? 'lost-talent-rf-v1.0',
                    'analyzed_at' => Carbon::now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'AI Talent Analysis ran successfully via Python Service.',
                    'source' => 'python_api',
                    'analysis' => $analysis
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Python AI service offline. Falling back to local PHP simulator: ' . $e->getMessage());
        }

        // 1. Calculate Average Grades (Fallback)
        $avgMath = $grades->where('subject_name', 'Matematika')->avg('score') ?? 70.00;
        $avgInformatika = $grades->where('subject_name', 'Informatika')->avg('score') ?? 70.00;
        $avgSains = $grades->where('subject_name', 'Fisika')->avg('score') ?? 70.00;
        $avgEnglish = $grades->where('subject_name', 'Bahasa Inggris')->avg('score') ?? 70.00;

        // 2. Fetch RIASEC Profile
        $riasec = $testResult ? $testResult->scores : [
            'Realistic' => 50, 'Investigative' => 50, 'Artistic' => 50,
            'Social' => 50, 'Enterprising' => 50, 'Conventional' => 50
        ];
        $dominant = $testResult ? $testResult->dominant_category : 'Investigative';

        // 3. AI Inference Simulation Matrix
        $talentScores = [
            'Robotik' => 50,
            'Programming' => 50,
            'Sains & Riset' => 50,
            'Desain Kreatif & UI/UX' => 50,
            'Bisnis & Kewirausahaan' => 50,
            'Sosial & Pendidikan' => 50
        ];

        // Rule: Heuristics based on grades
        if ($avgInformatika > 85) {
            $talentScores['Programming'] += 20;
            $talentScores['Robotik'] += 15;
            $talentScores['Desain Kreatif & UI/UX'] += 10;
        }
        if ($avgMath > 85) {
            $talentScores['Sains & Riset'] += 15;
            $talentScores['Programming'] += 10;
            $talentScores['Robotik'] += 10;
        }
        if ($avgSains > 85) {
            $talentScores['Robotik'] += 15;
            $talentScores['Sains & Riset'] += 15;
        }

        // Rule: Heuristics based on RIASEC
        $talentScores['Robotik'] += ($riasec['Realistic'] * 0.3) + ($riasec['Investigative'] * 0.2);
        $talentScores['Programming'] += ($riasec['Investigative'] * 0.4) + ($riasec['Conventional'] * 0.1);
        $talentScores['Sains & Riset'] += ($riasec['Investigative'] * 0.5);
        $talentScores['Desain Kreatif & UI/UX'] += ($riasec['Artistic'] * 0.5);
        $talentScores['Bisnis & Kewirausahaan'] += ($riasec['Enterprising'] * 0.4) + ($riasec['Social'] * 0.1);
        $talentScores['Sosial & Pendidikan'] += ($riasec['Social'] * 0.4) + ($riasec['Enterprising'] * 0.1);

        // Rule: Heuristics based on hobbies & interests
        foreach ($hobbies as $h) {
            $hLower = strtolower($h);
            if (str_contains($hLower, 'coding') || str_contains($hLower, 'pemrograman') || str_contains($hLower, 'game')) {
                $talentScores['Programming'] += 15;
            }
            if (str_contains($hLower, 'robot') || str_contains($hLower, 'arduino') || str_contains($hLower, 'rakit')) {
                $talentScores['Robotik'] += 15;
            }
            if (str_contains($hLower, 'gambar') || str_contains($hLower, 'desain') || str_contains($hLower, 'melukis')) {
                $talentScores['Desain Kreatif & UI/UX'] += 15;
            }
            if (str_contains($hLower, 'bisnis') || str_contains($hLower, 'jual') || str_contains($hLower, 'dagang')) {
                $talentScores['Bisnis & Kewirausahaan'] += 15;
            }
        }

        // Rule: Achievements weight
        foreach ($achievements as $ach) {
            $achLower = strtolower($ach->title);
            $multiplier = 10; // Default weight
            if ($ach->level === 'nasional') $multiplier = 15;
            if ($ach->level === 'internasional') $multiplier = 25;

            if (str_contains($achLower, 'robot') || str_contains($achLower, 'stem') || str_contains($achLower, 'mekanik')) {
                $talentScores['Robotik'] += $multiplier;
            }
            if (str_contains($achLower, 'informasi') || str_contains($achLower, 'coding') || str_contains($achLower, 'komputer') || str_contains($achLower, 'pemrograman')) {
                $talentScores['Programming'] += $multiplier;
            }
            if (str_contains($achLower, 'sains') || str_contains($achLower, 'fisika') || str_contains($achLower, 'matematika') || str_contains($achLower, 'karya tulis') || str_contains($achLower, 'penelitian')) {
                $talentScores['Sains & Riset'] += $multiplier;
            }
            if (str_contains($achLower, 'desain') || str_contains($achLower, 'poster') || str_contains($achLower, 'seni') || str_contains($achLower, 'ui/ux')) {
                $talentScores['Desain Kreatif & UI/UX'] += $multiplier;
            }
        }

        // Find primary talent
        arsort($talentScores);
        $primary = key($talentScores);
        $primaryVal = min(99, $talentScores[$primary]); // Cap at 99%
        
        // Remove primary to compile supporting
        unset($talentScores[$primary]);
        $supporting = [];
        $counter = 0;
        foreach ($talentScores as $talent => $score) {
            if ($counter >= 3) break;
            $supporting[] = [
                'talent' => $talent,
                'confidence' => floatval(min(95, $score))
            ];
            $counter++;
        }

        // Compile reasoning points dynamically
        $reasoning = [];
        if ($avgInformatika > 85 && in_array($primary, ['Programming', 'Robotik'])) {
            $reasoning[] = "Nilai rata-rata Informatika Anda sangat tinggi ({$avgInformatika})";
        }
        if ($avgMath > 85) {
            $reasoning[] = "Kemampuan logika Matematika kuat (Nilai rata-rata {$avgMath})";
        }
        if ($testResult) {
            $reasoning[] = "Hasil tes minat bakat RIASEC dominan pada kategori {$dominant} (" . $riasec[$dominant] . "%)";
        }
        if ($achievements->count() > 0) {
            $reasoning[] = "Memiliki " . $achievements->count() . " prestasi yang relevan dan terverifikasi di bidang " . strtolower($primary);
        }
        if (count($hobbies) > 0) {
            $reasoning[] = "Minat personal ditopang oleh hobi Anda: " . implode(', ', array_slice($hobbies, 0, 3));
        }

        // Recommended outputs based on primary
        $careers = [];
        $extracurriculars = [];
        $competitions = [];
        $targets = [];

        switch ($primary) {
            case 'Robotik':
                $careers = ['Robotics Engineer', 'Embedded Systems Developer', 'IoT Architect', 'Automation Specialist'];
                $extracurriculars = ['Klub Robotika', 'Coding Club', 'Karya Ilmiah Remaja'];
                $competitions = ['Lomba Robotika Nasional (BARON)', 'GEMASTIK - Pengembangan Perangkat Lunak'];
                $targets = ['Mempelajari mikrokontroler Arduino/Raspberry Pi', 'Mengembangkan portofolio Internet of Things (IoT)'];
                break;
            case 'Programming':
                $careers = ['Software Engineer', 'Backend Developer', 'Data Scientist', 'System Analyst'];
                $extracurriculars = ['Coding Club', 'Karya Ilmiah Remaja'];
                $competitions = ['GEMASTIK - Pemrograman (Competitive Programming)', 'Hackathon Indonesia AI Innovation'];
                $targets = ['Mempelajari algoritma dan struktur data lanjutan', 'Membangun aplikasi open-source di GitHub'];
                break;
            case 'Sains & Riset':
                $careers = ['Research Scientist', 'Data Analyst', 'Academic Professor', 'Laboratory Researcher'];
                $extracurriculars = ['Karya Ilmiah Remaja', 'Olimpiade Club'];
                $competitions = ['Olimpiade Sains Nasional (OSN) - Informatika', 'GEMASTIK - Karya Tulis Ilmiah TIK'];
                $targets = ['Mempelajari metode penulisan karya ilmiah', 'Membaca jurnal sains bereputasi secara rutin'];
                break;
            case 'Desain Kreatif & UI/UX':
                $careers = ['UI/UX Designer', 'Product Designer', 'Creative Director', 'Graphic Designer'];
                $extracurriculars = ['Klub Desain & Fotografi', 'Pramuka (Pubdok)'];
                $competitions = ['GEMASTIK - Desain Pengalaman Pengguna (UI/UX Design)', 'Festival dan Lomba Seni Siswa Nasional (FLS2N)'];
                $targets = ['Mempelajari software desain Figma/Adobe XD', 'Membuat portofolio case study di Behance'];
                break;
            case 'Bisnis & Kewirausahaan':
                $careers = ['Business Development', 'Product Manager', 'Entrepreneur/Founder', 'Financial Analyst'];
                $extracurriculars = ['Koperasi Siswa', 'Debate Club'];
                $competitions = ['National Business Plan Competition (NBPC)', 'Lomba Debat Nasional'];
                $targets = ['Menyusun proposal model bisnis kanvas (BMC)', 'Mempelajari dasar analisis keuangan & pemasaran'];
                break;
            default: // Sosial & Pendidikan
                $careers = ['Teacher / Educator', 'Public Relations Specialist', 'Human Resources Manager', 'Social Worker'];
                $extracurriculars = ['Pramuka', 'Palang Merah Remaja (PMR)', 'OSIS'];
                $competitions = ['Lomba Debat Bahasa Indonesia', 'Kompetisi Pengabdian Sosial'];
                $targets = ['Melatih kemampuan public speaking', 'Mengikuti program volunterisme kemanusiaan'];
                break;
        }

        // Save AI Analysis results
        AiAnalysis::where('student_id', $student->id)->delete();
        
        $analysis = AiAnalysis::create([
            'student_id' => $student->id,
            'primary_talent' => $primary,
            'confidence_score' => floatval($primaryVal),
            'supporting_talents' => $supporting,
            'reasoning' => $reasoning,
            'career_recommendations' => $careers,
            'extracurricular_recommendations' => $extracurriculars,
            'competition_recommendations' => $competitions,
            'development_targets' => $targets,
            'model_version' => 'lost-talent-xgb-v1.0-simulated',
            'analyzed_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'AI Talent Analysis ran successfully.',
            'analysis' => $analysis
        ]);
    }
}
