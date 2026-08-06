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
use App\Models\InstitutionAnnouncement;
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

        if (!in_array($user->role, ['siswa', 'mahasiswa', 'umum'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya akun Siswa/Mahasiswa/Umum yang dapat mengakses dashboard siswa.'
            ], 403);
        }
        $student = Student::firstOrCreate(
            ['user_id' => $user->id],
            [
                'institution_id' => null,
                'classroom_id' => null,
            ]
        );

        if (!$student->institution_id && $student->classroom_id && $student->classroom) {
            $student->institution_id = $student->classroom->institution_id;
            $student->save();
        }

        $student->load(['user', 'classroom', 'institution.user']);

        $grades = AcademicGrade::where('student_id', $student->id)->get();
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
                'grades' => $grades,
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
        $student = Student::firstOrCreate(['user_id' => $user->id]);

        $validator = Validator::make($request->all(), [
            'hobbies' => 'present|array',
            'interests' => 'present|array',
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
        $student = Student::firstOrCreate(['user_id' => $user->id]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|in:akademik,olahraga,seni,sains,teknologi,keagamaan,lainnya',
            'level' => 'required|in:sekolah,kecamatan,kabupaten,provinsi,nasional,internasional',
            'rank' => 'required|string|max:100', // e.g. Juara 1, Finalis
            'description' => 'nullable|string',
            'certificate' => 'nullable', // Can be file or base64 string
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $certPath = null;
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/certificates'), $fileName);
            $certPath = 'uploads/certificates/' . $fileName;
        } elseif ($request->filled('certificate')) {
            $certData = $request->input('certificate');
            if (preg_match('/^data:image\/(\w+);base64,/', $certData, $type)) {
                $certData = substr($certData, strpos($certData, ',') + 1);
                $type = strtolower($type[1]);
                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'pdf'])) {
                    $type = 'png';
                }
                $decoded = base64_decode($certData);
                if ($decoded !== false) {
                    $fileName = uniqid() . '.' . $type;
                    if (!file_exists(public_path('uploads/certificates'))) {
                        mkdir(public_path('uploads/certificates'), 0777, true);
                    }
                    file_put_contents(public_path('uploads/certificates/' . $fileName), $decoded);
                    $certPath = 'uploads/certificates/' . $fileName;
                }
            } else {
                $certPath = $certData;
            }
        }

        $autoVerify = ($user->role === 'umum') || is_null($student->institution_id);

        $achievement = Achievement::create([
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

        return response()->json([
            'success' => true,
            'message' => $autoVerify ? 'Achievement saved successfully.' : 'Achievement uploaded successfully. Awaiting verification.',
            'achievement' => $achievement
        ], 201);
    }

    /**
     * Delete an achievement.
     */
    public function deleteAchievement(Request $request, $id)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.'
            ], 404);
        }

        $achievement = Achievement::where('id', $id)
            ->where('student_id', $student->id)
            ->first();

        if (!$achievement) {
            return response()->json([
                'success' => false,
                'message' => 'Achievement not found or unauthorized.'
            ], 404);
        }

        // Delete certificate file from storage if exists
        if ($achievement->certificate_path && file_exists(public_path($achievement->certificate_path))) {
            @unlink(public_path($achievement->certificate_path));
        }

        $achievement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sertifikat prestasi berhasil dihapus.'
        ]);
    }

    /**
     * Fetch active Interest/Bakat tests and questions.
     */
    public function getRiasecTest()
    {
        $test = InterestTest::where('is_active', true)->first();

        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'No active test found'
            ], 404);
        }

        $categories = ['Realistic', 'Investigative', 'Artistic', 'Social', 'Enterprising', 'Conventional'];
        $sampledQuestions = collect();

        foreach ($categories as $cat) {
            $catQuestions = \App\Models\InterestTestQuestion::where('interest_test_id', $test->id)
                ->where('category', $cat)
                ->inRandomOrder()
                ->take(3)
                ->get();
            $sampledQuestions = $sampledQuestions->merge($catQuestions);
        }

        $test->setRelation('questions', $sampledQuestions->shuffle());

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
        $student = Student::firstOrCreate(['user_id' => $user->id]);

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

        $values = array_column($request->answers, 'value');
        if (count($values) >= 6 && count(array_unique($values)) === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Jawaban Anda terlalu seragam (diisi dengan nilai yang sama untuk semua soal). Mohon isi tes kuesioner sesuai dengan kecenderungan minat aktual Anda.'
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
     * Reset RIASEC test result and answers for the student.
     */
    public function resetRiasecTest(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student record not found.'], 404);
        }

        InterestTestResult::where('student_id', $student->id)->delete();
        InterestTestAnswer::where('student_id', $student->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hasil tes RIASEC berhasil di-reset.'
        ]);
    }

    /**
     * Reset AI Analysis report for the student.
     */
    public function resetAiAnalysis(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student record not found.'], 404);
        }

        AiAnalysis::where('student_id', $student->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Laporan analisis AI berhasil di-reset.'
        ]);
    }

    /**
     * Run high-fidelity simulation of the AI Talent Recommendation Engine.
     * Evaluates grades, achievements, interest test scores, and hobbies.
     */
    public function analyzeTalent(Request $request)
    {
        $user = $request->user();
        $student = Student::firstOrCreate(['user_id' => $user->id]);
        $student->load(['academicGrades', 'achievements', 'interestTestResults']);

        // Gather metrics for analysis
        $grades = $student->academicGrades;
        $achievements = $student->achievements;
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
            $geminiKey = env('GEMINI_API_KEY') ?: (getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? null));
            $openrouterKey = env('OPENROUTER_API_KEY') ?: (getenv('OPENROUTER_API_KEY') ?: ($_ENV['OPENROUTER_API_KEY'] ?? null));

            // Call Python AI Service (Multi-Engine: DeepSeek-R1 / Gemini + Local ML)
            $response = \Illuminate\Support\Facades\Http::timeout(5)->post('http://127.0.0.1:5001/predict', [
                'riasec' => $testResult ? $testResult->scores : new \stdClass(),
                'grades' => $grades->groupBy('subject_name')->map(function ($items) {
                    return floatval($items->avg('score'));
                })->toArray(),
                'achievements' => $achievements->map(function ($ach) {
                    return [
                        'title' => $ach->title,
                        'category' => $ach->category,
                        'level' => $ach->level,
                        'rank' => $ach->rank ?? '',
                    ];
                })->toArray(),
                'hobbies' => $hobbies,
                'interests' => $interests,
                'gemini_api_key' => $geminiKey,
                'openrouter_api_key' => $openrouterKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Clear previous analysis
                AiAnalysis::where('student_id', $student->id)->delete();

                $analysis = AiAnalysis::create([
                    'student_id' => $student->id,
                    'primary_talent' => $data['primary_talent'],
                    'analisis_mendalam' => $data['analisis_mendalam'] ?? null,
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

        // 1. Calculate Average Grades (Fallback using keywords)
        $mathGrades = $grades->filter(function ($g) {
            $name = strtolower($g->subject_name);
            return str_contains($name, 'matematika') || str_contains($name, 'kalkulus') || str_contains($name, 'statistika') || str_contains($name, 'aljabar') || str_contains($name, 'logika');
        });
        $avgMath = $mathGrades->isEmpty() ? ($grades->where('subject_name', 'Matematika')->avg('score') ?? 70.00) : $mathGrades->avg('score');

        $infoGrades = $grades->filter(function ($g) {
            $name = strtolower($g->subject_name);
            return str_contains($name, 'informatika') || str_contains($name, 'komputer') || str_contains($name, 'pemrograman') || str_contains($name, 'program') || str_contains($name, 'coding') || str_contains($name, 'algoritma') || str_contains($name, 'jaringan') || str_contains($name, 'data') || str_contains($name, 'web') || str_contains($name, 'mobile') || str_contains($name, 'kecerdasan') || str_contains($name, 'software') || str_contains($name, 'rekayasa') || str_contains($name, 'sistem') || str_contains($name, 'it') || str_contains($name, 'tik') || str_contains($name, 'rpl') || str_contains($name, 'cyber');
        });
        $avgInformatika = $infoGrades->isEmpty() ? ($grades->where('subject_name', 'Informatika')->avg('score') ?? 70.00) : $infoGrades->avg('score');

        $sainsGrades = $grades->filter(function ($g) {
            $name = strtolower($g->subject_name);
            return str_contains($name, 'fisika') || str_contains($name, 'kimia') || str_contains($name, 'biologi') || str_contains($name, 'sains') || str_contains($name, 'ipa');
        });
        $avgSains = $sainsGrades->isEmpty() ? ($grades->where('subject_name', 'Fisika')->avg('score') ?? 70.00) : $sainsGrades->avg('score');

        $englishGrades = $grades->filter(function ($g) {
            $name = strtolower($g->subject_name);
            return str_contains($name, 'inggris') || str_contains($name, 'english');
        });
        $avgEnglish = $englishGrades->isEmpty() ? ($grades->where('subject_name', 'Bahasa Inggris')->avg('score') ?? 70.00) : $englishGrades->avg('score');

        $aviationGrades = $grades->filter(function ($g) {
            $name = strtolower($g->subject_name);
            return str_contains($name, 'terbang') || str_contains($name, 'aerodinamika') || str_contains($name, 'penerbangan') || str_contains($name, 'navigasi') || str_contains($name, 'pesawat') || str_contains($name, 'general aircraft') || str_contains($name, 'air law') || str_contains($name, 'kinerja manusia') || str_contains($name, 'prosedur operasional') || str_contains($name, 'aviation') || str_contains($name, 'pilot') || str_contains($name, 'dirgantara');
        });
        $avgAviation = $aviationGrades->isEmpty() ? 0.00 : $aviationGrades->avg('score');

        // 2. Fetch RIASEC Profile
        $riasec = $testResult ? $testResult->scores : [
            'Realistic' => 50, 'Investigative' => 50, 'Artistic' => 50,
            'Social' => 50, 'Enterprising' => 50, 'Conventional' => 50
        ];
        $dominant = $testResult ? $testResult->dominant_category : 'Investigative';

        $hobbiesString = implode(' ', $hobbies);
        $interestsString = implode(' ', $interests);
        $allText = strtolower($hobbiesString . ' ' . $interestsString);
        
        $isCulinary = str_contains($allText, 'masak') || 
                      str_contains($allText, 'boga') || 
                      str_contains($allText, 'kuliner') || 
                      str_contains($allText, 'chef') || 
                      str_contains($allText, 'koki') || 
                      str_contains($allText, 'makanan') ||
                      $grades->contains(function($g) {
                          $name = strtolower($g->subject_name);
                          return str_contains($name, 'boga') || str_contains($name, 'masak') || str_contains($name, 'makanan') || str_contains($name, 'patisserie') || str_contains($name, 'gizi');
                      });

        $isMusic = str_contains($allText, 'musik') || 
                   str_contains($allText, 'nyanyi') || 
                   str_contains($allText, 'vokal') || 
                   str_contains($allText, 'tari') || 
                   str_contains($allText, 'sing') || 
                   str_contains($allText, 'dance') || 
                   str_contains($allText, 'talent') ||
                        $grades->contains(function($g) {
                           $name = strtolower($g->subject_name);
                           return str_contains($name, 'musik') || str_contains($name, 'vokal') || str_contains($name, 'solfeggio') || str_contains($name, 'harmoni') || str_contains($name, 'diksi') || str_contains($name, 'instrumen');
                       });

        $isSports = str_contains($allText, 'olahraga') || str_contains($allText, 'atlet') || str_contains($allText, 'bola') || str_contains($allText, 'senam') || str_contains($allText, 'lari') || str_contains($allText, 'renang') || str_contains($allText, 'futsal') || str_contains($allText, 'badminton') || str_contains($allText, 'silat') || str_contains($allText, 'karate') ||
                    $grades->contains(function($g) {
                        $name = strtolower($g->subject_name);
                        return str_contains($name, 'olahraga') || str_contains($name, 'penjas') || str_contains($name, 'atletik') || (str_contains($name, 'fisik') && !str_contains($name, 'fisika')) || str_contains($name, 'kesehatan rekreasi');
                    });

        $isMedical = str_contains($allText, 'medis') || str_contains($allText, 'dokter') || str_contains($allText, 'perawat') || str_contains($allText, 'sakit') || str_contains($allText, 'obat') || str_contains($allText, 'farmasi') || str_contains($allText, 'bidan') || str_contains($allText, 'klinik') || str_contains($allText, 'anatomi') ||
                     $grades->contains(function($g) {
                         $name = strtolower($g->subject_name);
                         return str_contains($name, 'anatomi') || str_contains($name, 'farmasi') || str_contains($name, 'klinis') || str_contains($name, 'perawat') || str_contains($name, 'bidan') || str_contains($name, 'gigi') || str_contains($name, 'patologi');
                     });

        $isAgriculture = str_contains($allText, 'tani') || str_contains($allText, 'kebun') || str_contains($allText, 'tanah') || str_contains($allText, 'botani') || str_contains($allText, 'agro') || str_contains($allText, 'ternak') || str_contains($allText, 'hutan') || str_contains($allText, 'tanaman') ||
                         $grades->contains(function($g) {
                             $name = strtolower($g->subject_name);
                             return str_contains($name, 'tani') || str_contains($name, 'tanah') || str_contains($name, 'kebun') || str_contains($name, 'botani') || str_contains($name, 'agro') || str_contains($name, 'ternak') || str_contains($name, 'hama') || str_contains($name, 'klimatologi');
                         });

        $isFishery = str_contains($allText, 'ikan') || str_contains($allText, 'perikanan') || str_contains($allText, 'perairan') || str_contains($allText, 'kelautan') || str_contains($allText, 'maritim') || str_contains($allText, 'akuakultur') || str_contains($allText, 'mancing') || str_contains($allText, 'pancing') || str_contains($allText, 'iktiologi') || str_contains($allText, 'seafood') ||
                     $grades->contains(function($g) {
                         $name = strtolower($g->subject_name);
                         return str_contains($name, 'ikan') || str_contains($name, 'perikanan') || str_contains($name, 'perairan') || str_contains($name, 'kelautan') || str_contains($name, 'maritim') || str_contains($name, 'akuakultur') || str_contains($name, 'iktiologi') || str_contains($name, 'kualitas air') || str_contains($name, 'hidrobiologi') || str_contains($name, 'oceanografi');
                     });

        // Check if there is a highly specific interest that doesn't fit standard categories
        $hasCustomInterest = false;
        $customInterestName = '';

        // 3. AI Inference Simulation Matrix
        $talentScores = [
            'Robotik' => 50,
            'Programming' => 50,
            'Sains & Riset' => 50,
            'Desain Kreatif & UI/UX' => 50,
            'Bisnis & Kewirausahaan' => 50,
            'Sosial & Pendidikan' => 50,
            'Seni Kuliner & Tata Boga' => 50,
            'Seni Musik & Pertunjukan' => 50,
            'Olahraga & Kesehatan Fisik' => 50,
            'Kesehatan & Keperawatan (Medis)' => 50,
            'Pertanian & Agroteknologi' => 50,
            'Perikanan & Kelautan' => 50,
            'Penerbangan & Kedirgantaraan' => 50
        ];
        if ($isCulinary) {
            $talentScores['Seni Kuliner & Tata Boga'] += 35;
        }
        if ($isMusic) {
            $talentScores['Seni Musik & Pertunjukan'] += 35;
        }
        if ($isSports) {
            $talentScores['Olahraga & Kesehatan Fisik'] += 35;
        }
        if ($isMedical) {
            $talentScores['Kesehatan & Keperawatan (Medis)'] += 35;
        }
        if ($isAgriculture) {
            $talentScores['Pertanian & Agroteknologi'] += 35;
        }
        if ($isFishery) {
            $talentScores['Perikanan & Kelautan'] += 50;
        }
        
        $isAviation = str_contains($allText, 'terbang') || str_contains($allText, 'pilot') || str_contains($allText, 'dirgantara') || str_contains($allText, 'penerbang') || str_contains($allText, 'penerbangan') || str_contains($allText, 'pesawat') || !$aviationGrades->isEmpty();
        if ($isAviation) {
            $talentScores['Penerbangan & Kedirgantaraan'] += 50;
        }

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
            $talentScores['Kesehatan & Keperawatan (Medis)'] += 10;
            $talentScores['Perikanan & Kelautan'] += 15;
        }
        if ($avgAviation > 85) {
            $talentScores['Penerbangan & Kedirgantaraan'] += 20;
        }

        // Rule: Heuristics based on RIASEC
        $talentScores['Robotik'] += ($riasec['Realistic'] * 0.3) + ($riasec['Investigative'] * 0.2);
        $talentScores['Programming'] += ($riasec['Investigative'] * 0.4) + ($riasec['Conventional'] * 0.1);
        $talentScores['Sains & Riset'] += ($riasec['Investigative'] * 0.5);
        $talentScores['Desain Kreatif & UI/UX'] += ($riasec['Artistic'] * 0.5);
        $talentScores['Bisnis & Kewirausahaan'] += ($riasec['Enterprising'] * 0.4) + ($riasec['Social'] * 0.1);
        $talentScores['Sosial & Pendidikan'] += ($riasec['Social'] * 0.4) + ($riasec['Enterprising'] * 0.1);
        $talentScores['Seni Kuliner & Tata Boga'] += ($riasec['Realistic'] * 0.3) + ($riasec['Artistic'] * 0.1);
        $talentScores['Seni Musik & Pertunjukan'] += ($riasec['Artistic'] * 0.4) + ($riasec['Social'] * 0.1);
        $talentScores['Olahraga & Kesehatan Fisik'] += ($riasec['Realistic'] * 0.4);
        $talentScores['Kesehatan & Keperawatan (Medis)'] += ($riasec['Investigative'] * 0.3) + ($riasec['Social'] * 0.2);
        $talentScores['Pertanian & Agroteknologi'] += ($riasec['Realistic'] * 0.3) + ($riasec['Investigative'] * 0.2);
        $talentScores['Perikanan & Kelautan'] += ($riasec['Realistic'] * 0.3) + ($riasec['Investigative'] * 0.3);
        $talentScores['Penerbangan & Kedirgantaraan'] += ($riasec['Realistic'] * 0.3) + ($riasec['Investigative'] * 0.3);

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
            if (str_contains($hLower, 'masak') || str_contains($hLower, 'boga') || str_contains($hLower, 'kuliner') || str_contains($hLower, 'chef')) {
                $talentScores['Seni Kuliner & Tata Boga'] += 15;
            }
            if (str_contains($hLower, 'musik') || str_contains($hLower, 'nyanyi') || str_contains($hLower, 'tari') || str_contains($hLower, 'vokal') || str_contains($hLower, 'sing')) {
                $talentScores['Seni Musik & Pertunjukan'] += 15;
            }
            if (str_contains($hLower, 'olahraga') || str_contains($hLower, 'bola') || str_contains($hLower, 'senam') || str_contains($hLower, 'lari') || str_contains($hLower, 'futsal')) {
                $talentScores['Olahraga & Kesehatan Fisik'] += 15;
            }
            if (str_contains($hLower, 'dokter') || str_contains($hLower, 'rawat') || str_contains($hLower, 'obat') || str_contains($hLower, 'sakit')) {
                $talentScores['Kesehatan & Keperawatan (Medis)'] += 15;
            }
            if (str_contains($hLower, 'tani') || str_contains($hLower, 'kebun') || str_contains($hLower, 'tanaman') || str_contains($hLower, 'ternak')) {
                $talentScores['Pertanian & Agroteknologi'] += 15;
            }
            if (str_contains($hLower, 'mancing') || str_contains($hLower, 'pancing') || str_contains($hLower, 'ikan') || str_contains($hLower, 'laut') || str_contains($hLower, 'perairan')) {
                $talentScores['Perikanan & Kelautan'] += 20;
            }
            if (str_contains($hLower, 'simulator') || str_contains($hLower, 'pesawat') || str_contains($hLower, 'terbang') || str_contains($hLower, 'dirgantara')) {
                $talentScores['Penerbangan & Kedirgantaraan'] += 15;
            }
        }

        // Rule: Heuristics based on interests
        foreach ($interests as $i) {
            $iLower = strtolower($i);
            if (str_contains($iLower, 'coding') || str_contains($iLower, 'pemrograman') || str_contains($iLower, 'developer') || str_contains($iLower, 'program')) {
                $talentScores['Programming'] += 20;
            }
            if (str_contains($iLower, 'robot') || str_contains($iLower, 'arduino') || str_contains($iLower, 'iot') || str_contains($iLower, 'hardware')) {
                $talentScores['Robotik'] += 20;
            }
            if (str_contains($iLower, 'riset') || str_contains($iLower, 'sains') || str_contains($iLower, 'penelitian') || str_contains($iLower, 'analis')) {
                $talentScores['Sains & Riset'] += 20;
            }
            if (str_contains($iLower, 'desain') || str_contains($iLower, 'gambar') || str_contains($iLower, 'art') || str_contains($iLower, 'creative')) {
                $talentScores['Desain Kreatif & UI/UX'] += 20;
            }
            if (str_contains($iLower, 'bisnis') || str_contains($iLower, 'usaha') || str_contains($iLower, 'wirausaha') || str_contains($iLower, 'dagang') || str_contains($iLower, 'marketing')) {
                $talentScores['Bisnis & Kewirausahaan'] += 20;
            }
            if (str_contains($iLower, 'sosial') || str_contains($iLower, 'didik') || str_contains($iLower, 'guru') || str_contains($iLower, 'dosen') || str_contains($iLower, 'ajar')) {
                $talentScores['Sosial & Pendidikan'] += 20;
            }
            if (str_contains($iLower, 'masak') || str_contains($iLower, 'boga') || str_contains($iLower, 'kuliner') || str_contains($iLower, 'chef') || str_contains($iLower, 'koki')) {
                $talentScores['Seni Kuliner & Tata Boga'] += 20;
            }
            if (str_contains($iLower, 'musik') || str_contains($iLower, 'nyanyi') || str_contains($iLower, 'vokal') || str_contains($iLower, 'tari') || str_contains($iLower, 'sing') || str_contains($iLower, 'dance')) {
                $talentScores['Seni Musik & Pertunjukan'] += 20;
            }
            if (str_contains($iLower, 'olahraga') || str_contains($iLower, 'atlet') || str_contains($iLower, 'bola') || str_contains($iLower, 'lari') || str_contains($iLower, 'futsal')) {
                $talentScores['Olahraga & Kesehatan Fisik'] += 20;
            }
            if (str_contains($iLower, 'medis') || str_contains($iLower, 'dokter') || str_contains($iLower, 'perawat') || str_contains($iLower, 'bidan') || str_contains($iLower, 'klinik') || str_contains($iLower, 'obat') || str_contains($iLower, 'sehat')) {
                $talentScores['Kesehatan & Keperawatan (Medis)'] += 20;
            }
            if (str_contains($iLower, 'tani') || str_contains($iLower, 'kebun') || str_contains($iLower, 'tanaman') || str_contains($iLower, 'agro') || str_contains($iLower, 'ternak')) {
                $talentScores['Pertanian & Agroteknologi'] += 20;
            }
            if (str_contains($iLower, 'ikan') || str_contains($iLower, 'mancing') || str_contains($iLower, 'laut') || str_contains($iLower, 'perairan') || str_contains($iLower, 'akuakultur')) {
                $talentScores['Perikanan & Kelautan'] += 20;
            }
            if (str_contains($iLower, 'terbang') || str_contains($iLower, 'pilot') || str_contains($iLower, 'dirgantara') || str_contains($iLower, 'penerbangan') || str_contains($iLower, 'pesawat')) {
                $talentScores['Penerbangan & Kedirgantaraan'] += 20;
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
            if (str_contains($achLower, 'masak') || str_contains($achLower, 'boga') || str_contains($achLower, 'kuliner') || str_contains($achLower, 'makanan') || str_contains($achLower, 'baking') || str_contains($achLower, 'koki')) {
                $talentScores['Seni Kuliner & Tata Boga'] += $multiplier;
            }
            if (str_contains($achLower, 'musik') || str_contains($achLower, 'nyanyi') || str_contains($achLower, 'tari') || str_contains($achLower, 'vokal') || str_contains($achLower, 'sing') || str_contains($achLower, 'talent') || str_contains($achLower, 'seni')) {
                $talentScores['Seni Musik & Pertunjukan'] += $multiplier;
            }
            if (str_contains($achLower, 'olahraga') || str_contains($achLower, 'bola') || str_contains($achLower, 'juara') || str_contains($achLower, 'tanding') || str_contains($achLower, 'futsal') || str_contains($achLower, 'atlet')) {
                $talentScores['Olahraga & Kesehatan Fisik'] += $multiplier;
            }
            if (str_contains($achLower, 'medis') || str_contains($achLower, 'dokter') || str_contains($achLower, 'kesehatan') || str_contains($achLower, 'palang merah') || str_contains($achLower, 'pmr')) {
                $talentScores['Kesehatan & Keperawatan (Medis)'] += $multiplier;
            }
            if (str_contains($achLower, 'tani') || str_contains($achLower, 'kebun') || str_contains($achLower, 'agro') || str_contains($achLower, 'pangan')) {
                $talentScores['Pertanian & Agroteknologi'] += $multiplier;
            }
            if (str_contains($achLower, 'mancing') || str_contains($achLower, 'pancing') || str_contains($achLower, 'ikan') || str_contains($achLower, 'perikanan') || str_contains($achLower, 'perairan') || str_contains($achLower, 'kelautan')) {
                $talentScores['Perikanan & Kelautan'] += $multiplier + 15;
            }
            if (str_contains($achLower, 'terbang') || str_contains($achLower, 'dirgantara') || str_contains($achLower, 'aeromodelling') || str_contains($achLower, 'pilot') || str_contains($achLower, 'pesawat')) {
                $talentScores['Penerbangan & Kedirgantaraan'] += $multiplier;
            }
        }

        // Domain dampening: Dampen tech domain scores if student has NO tech signal
        $hasTechSignal = ($avgInformatika >= 70) ||
                         $achievements->contains(function($a) {
                             $t = strtolower($a->title);
                             return str_contains($t, 'robot') || str_contains($t, 'coding') || str_contains($t, 'komputer') || str_contains($t, 'pemrograman') || str_contains($t, 'software') || str_contains($t, 'aplikasi');
                         }) ||
                         str_contains($allText, 'coding') || str_contains($allText, 'program') || str_contains($allText, 'robot') || str_contains($allText, 'arduino') || str_contains($allText, 'software') || str_contains($allText, 'komputer') || str_contains($allText, 'web') || str_contains($allText, 'app');

        if (!$hasTechSignal) {
            $talentScores['Programming'] = max(5, $talentScores['Programming'] * 0.05);
            $talentScores['Robotik'] = max(5, $talentScores['Robotik'] * 0.05);
        }

        // Academic Dampening Rule: Dampen scores of domains with zero academic matches if the student has grades.
        if (!$grades->isEmpty()) {
            foreach ($talentScores as $domain => $score) {
                // Check if this domain has academic relevance
                $hasAcademicMatch = false;
                if ($domain === 'Robotik' && ($avgInformatika > 0 || $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'listrik') || str_contains($n, 'kelistrikan') || str_contains($n, 'elektro') || str_contains($n, 'mekatronik') || str_contains($n, 'mesin') || str_contains($n, 'bubut') || str_contains($n, 'las') || str_contains($n, 'welding') || str_contains($n, 'otomotif'); }))) $hasAcademicMatch = true;
                elseif ($domain === 'Programming' && ($avgInformatika > 0 || $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'rpl') || str_contains($n, 'tkj') || str_contains($n, 'jaringan') || str_contains($n, 'database') || str_contains($n, 'basis data'); }))) $hasAcademicMatch = true;
                elseif ($domain === 'Sains & Riset' && $avgSains > 0) $hasAcademicMatch = true;
                elseif ($domain === 'Desain Kreatif & UI/UX' && ($avgInformatika > 0 || $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'seni') || str_contains($n, 'desain') || str_contains($n, 'rupa') || str_contains($n, 'gambar') || str_contains($n, 'grafis') || str_contains($n, 'multimedia') || str_contains($n, 'animasi') || str_contains($n, 'busana') || str_contains($n, 'jahit') || str_contains($n, 'kriya') || str_contains($n, 'arsitektur'); }))) $hasAcademicMatch = true;
                elseif ($domain === 'Seni Kuliner & Tata Boga' && $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'boga') || str_contains($n, 'masak') || str_contains($n, 'makanan') || str_contains($n, 'patisserie') || str_contains($n, 'pastry') || str_contains($n, 'gizi') || str_contains($n, 'kuliner') || str_contains($n, 'katering'); })) $hasAcademicMatch = true;
                elseif ($domain === 'Seni Musik & Pertunjukan' && $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'musik') || str_contains($n, 'vokal') || str_contains($n, 'sing') || str_contains($n, 'tari') || str_contains($n, 'dance') || str_contains($n, 'teater') || str_contains($n, 'koreografi'); })) $hasAcademicMatch = true;
                elseif ($domain === 'Olahraga & Kesehatan Fisik' && $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'olahraga') || str_contains($n, 'penjas') || str_contains($n, 'atletik') || (str_contains($n, 'fisik') && !str_contains($n, 'fisika')); })) $hasAcademicMatch = true;
                elseif ($domain === 'Kesehatan & Keperawatan (Medis)' && $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'anatomi') || str_contains($n, 'farmasi') || str_contains($n, 'perawat') || str_contains($n, 'bidan') || str_contains($n, 'medis') || str_contains($n, 'kesehatan') || str_contains($n, 'klinis') || str_contains($n, 'fisiologi') || str_contains($n, 'patologi') || str_contains($n, 'mikrobiologi') || str_contains($n, 'parasitologi') || str_contains($n, 'farmakologi'); })) $hasAcademicMatch = true;
                elseif ($domain === 'Pertanian & Agroteknologi' && $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'tani') || str_contains($n, 'kebun') || str_contains($n, 'botani') || str_contains($n, 'agro') || str_contains($n, 'hama') || str_contains($n, 'tanaman') || str_contains($n, 'klimatologi') || str_contains($n, 'peternakan') || str_contains($n, 'ternak') || str_contains($n, 'pakan'); })) $hasAcademicMatch = true;
                elseif ($domain === 'Perikanan & Kelautan' && $grades->contains(function($g) { $n = strtolower($g->subject_name); return str_contains($n, 'ikan') || str_contains($n, 'perikanan') || str_contains($n, 'perairan') || str_contains($n, 'kelautan') || str_contains($n, 'akuakultur') || str_contains($n, 'budidaya') || str_contains($n, 'maritim'); })) $hasAcademicMatch = true;
                elseif ($domain === 'Penerbangan & Kedirgantaraan' && $avgAviation > 0) $hasAcademicMatch = true;
                
                // If no academic match, dampen the score by 50%
                if (!$hasAcademicMatch) {
                    $hasAch = $achievements->contains(function($a) use ($domain) {
                        $t = strtolower($a->title);
                        if ($domain === 'Perikanan & Kelautan') return str_contains($t, 'mancing') || str_contains($t, 'ikan') || str_contains($t, 'perikanan') || str_contains($t, 'kelautan');
                        if ($domain === 'Seni Kuliner & Tata Boga') return str_contains($t, 'masak') || str_contains($t, 'boga') || str_contains($t, 'kuliner');
                        if ($domain === 'Olahraga & Kesehatan Fisik') return str_contains($t, 'olahraga') || str_contains($t, 'atlet') || str_contains($t, 'bola') || str_contains($t, 'lari');
                        return false;
                    });
                    
                    $hasInterest = false;
                    foreach ($interests as $interest) {
                        $intLower = strtolower($interest);
                        if ($domain === 'Perikanan & Kelautan' && (str_contains($intLower, 'mancing') || str_contains($intLower, 'ikan') || str_contains($intLower, 'laut'))) $hasInterest = true;
                        if ($domain === 'Seni Kuliner & Tata Boga' && (str_contains($intLower, 'masak') || str_contains($intLower, 'kuliner') || str_contains($intLower, 'chef'))) $hasInterest = true;
                        if ($domain === 'Olahraga & Kesehatan Fisik' && (str_contains($intLower, 'olahraga') || str_contains($intLower, 'atlet') || str_contains($intLower, 'bola'))) $hasInterest = true;
                    }
                    
                    if (!($hasAch && $hasInterest)) {
                        $talentScores[$domain] = $score * 0.5;
                    }
                }
            }
        }

        // Find initial top candidate
        arsort($talentScores);
        $topCandidate = key($talentScores);

        // Domain Affinity Matrix: Boost related supporting talents based on primary domain
        $affinityMap = [
            'Perikanan & Kelautan' => [['Sains & Riset', 40], ['Pertanian & Agroteknologi', 35], ['Bisnis & Kewirausahaan', 25], ['Sosial & Pendidikan', 20]],
            'Pertanian & Agroteknologi' => [['Sains & Riset', 40], ['Perikanan & Kelautan', 35], ['Bisnis & Kewirausahaan', 25], ['Kesehatan & Keperawatan (Medis)', 20]],
            'Programming' => [['Robotik', 40], ['Sains & Riset', 35], ['Desain Kreatif & UI/UX', 30], ['Bisnis & Kewirausahaan', 20]],
            'Robotik' => [['Programming', 40], ['Sains & Riset', 35], ['Desain Kreatif & UI/UX', 25]],
            'Desain Kreatif & UI/UX' => [['Programming', 30], ['Seni Musik & Pertunjukan', 25], ['Bisnis & Kewirausahaan', 25]],
            'Bisnis & Kewirausahaan' => [['Sosial & Pendidikan', 35], ['Desain Kreatif & UI/UX', 25], ['Programming', 20], ['Pertanian & Agroteknologi', 20]],
            'Sains & Riset' => [['Perikanan & Kelautan', 35], ['Pertanian & Agroteknologi', 35], ['Kesehatan & Keperawatan (Medis)', 30], ['Programming', 25]],
            'Kesehatan & Keperawatan (Medis)' => [['Sains & Riset', 40], ['Olahraga & Kesehatan Fisik', 30], ['Sosial & Pendidikan', 25]],
            'Olahraga & Kesehatan Fisik' => [['Kesehatan & Keperawatan (Medis)', 35], ['Sosial & Pendidikan', 25]],
            'Seni Kuliner & Tata Boga' => [['Bisnis & Kewirausahaan', 35], ['Pertanian & Agroteknologi', 25], ['Seni Musik & Pertunjukan', 20]],
            'Seni Musik & Pertunjukan' => [['Desain Kreatif & UI/UX', 30], ['Seni Kuliner & Tata Boga', 20], ['Sosial & Pendidikan', 20]],
            'Sosial & Pendidikan' => [['Bisnis & Kewirausahaan', 35], ['Sains & Riset', 25], ['Kesehatan & Keperawatan (Medis)', 20]]
        ];

        if (isset($affinityMap[$topCandidate])) {
            foreach ($affinityMap[$topCandidate] as $affItem) {
                list($affTalent, $boost) = $affItem;
                if (isset($talentScores[$affTalent])) {
                    $talentScores[$affTalent] += $boost;
                }
            }
        }

        // Re-sort after domain affinity boost
        arsort($talentScores);
        $primary = key($talentScores);
        $topScore = current($talentScores);
        
        // Calculate dynamic primary confidence score (scaled between 85% and 98%)
        $maxPossibleScore = 150; // Reference max score
        $primaryVal = round(min(98.0, max(85.0, ($topScore / $maxPossibleScore) * 100)), 1);
        
        // Remove primary from array to extract top 3 supporting talents
        $tempScores = $talentScores;
        if (isset($tempScores[$primary])) {
            unset($tempScores[$primary]);
        }

        $supporting = [];
        $counter = 0;
        foreach ($tempScores as $talent => $score) {
            if ($counter >= 3) break;
            
            // Calculate supporting talent percentage proportional to primary score (always smaller than primaryVal)
            $ratio = $score / max(1, $topScore);
            $calculatedConf = round(min($primaryVal - 3.0, max(45.0, $primaryVal * $ratio * 0.85)), 1);
            
            $supporting[] = [
                'talent' => $talent,
                'confidence' => $calculatedConf
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
        $matchedAchCount = 0;
        foreach ($achievements as $ach) {
            $achLower = strtolower($ach->title);
            $isMatch = false;
            if ($primary === 'Robotik' && (str_contains($achLower, 'robot') || str_contains($achLower, 'stem') || str_contains($achLower, 'mekanik'))) $isMatch = true;
            elseif ($primary === 'Programming' && (str_contains($achLower, 'informasi') || str_contains($achLower, 'coding') || str_contains($achLower, 'komputer') || str_contains($achLower, 'pemrograman'))) $isMatch = true;
            elseif ($primary === 'Sains & Riset' && (str_contains($achLower, 'sains') || str_contains($achLower, 'fisika') || str_contains($achLower, 'matematika') || str_contains($achLower, 'karya tulis') || str_contains($achLower, 'penelitian'))) $isMatch = true;
            elseif ($primary === 'Desain Kreatif & UI/UX' && (str_contains($achLower, 'desain') || str_contains($achLower, 'poster') || str_contains($achLower, 'seni') || str_contains($achLower, 'ui/ux'))) $isMatch = true;
            elseif ($primary === 'Seni Kuliner & Tata Boga' && (str_contains($achLower, 'masak') || str_contains($achLower, 'boga') || str_contains($achLower, 'kuliner') || str_contains($achLower, 'makanan') || str_contains($achLower, 'baking') || str_contains($achLower, 'koki'))) $isMatch = true;
            elseif ($primary === 'Seni Musik & Pertunjukan' && (str_contains($achLower, 'musik') || str_contains($achLower, 'nyanyi') || str_contains($achLower, 'tari') || str_contains($achLower, 'vokal') || str_contains($achLower, 'sing') || str_contains($achLower, 'talent') || str_contains($achLower, 'seni'))) $isMatch = true;
            elseif ($primary === 'Olahraga & Kesehatan Fisik' && (str_contains($achLower, 'olahraga') || str_contains($achLower, 'bola') || str_contains($achLower, 'juara') || str_contains($achLower, 'tanding') || str_contains($achLower, 'futsal') || str_contains($achLower, 'atlet') || str_contains($achLower, 'lari') || str_contains($achLower, 'marathon'))) $isMatch = true;
            elseif ($primary === 'Kesehatan & Keperawatan (Medis)' && (str_contains($achLower, 'medis') || str_contains($achLower, 'dokter') || str_contains($achLower, 'kesehatan') || str_contains($achLower, 'palang merah') || str_contains($achLower, 'pmr') || str_contains($achLower, 'perawat') || str_contains($achLower, 'bidan'))) $isMatch = true;
            elseif ($primary === 'Pertanian & Agroteknologi' && (str_contains($achLower, 'tani') || str_contains($achLower, 'kebun') || str_contains($achLower, 'agro') || str_contains($achLower, 'pangan'))) $isMatch = true;
            elseif ($primary === 'Perikanan & Kelautan' && (str_contains($achLower, 'mancing') || str_contains($achLower, 'pancing') || str_contains($achLower, 'ikan') || str_contains($achLower, 'perikanan') || str_contains($achLower, 'perairan') || str_contains($achLower, 'kelautan'))) $isMatch = true;
            elseif ($primary === 'Penerbangan & Kedirgantaraan' && (str_contains($achLower, 'terbang') || str_contains($achLower, 'dirgantara') || str_contains($achLower, 'aeromodelling') || str_contains($achLower, 'pilot') || str_contains($achLower, 'pesawat'))) $isMatch = true;
            
            if ($isMatch) {
                $matchedAchCount++;
            }
        }
        if ($matchedAchCount > 0) {
            $reasoning[] = "Memiliki " . $matchedAchCount . " prestasi yang relevan dan terverifikasi di bidang " . strtolower($primary);
        }
        if (count($hobbies) > 0) {
            $reasoning[] = "Minat personal ditopang oleh hobi Anda: " . implode(', ', array_slice($hobbies, 0, 3));
        }
        if ($isFishery && $primary === 'Perikanan & Kelautan') {
            $reasoning[] = "Memiliki rekam nilai akademik dan riwayat prestasi yang bersangkut paut dengan ekologi perairan, akuakultur, dan perikanan.";
        }
        if ($isCulinary && $primary === 'Seni Kuliner & Tata Boga') {
            $reasoning[] = "Memiliki minat kuat dan riwayat prestasi di bidang boga, kuliner, serta teknik pengolahan pangan.";
        }
        if ($isMusic && $primary === 'Seni Musik & Pertunjukan') {
            $reasoning[] = "Memiliki minat kuat, bakat alami, dan prestasi terverifikasi di bidang musik dan seni pertunjukan.";
        }
        if ($isSports && $primary === 'Olahraga & Kesehatan Fisik') {
            $reasoning[] = "Menunjukkan stamina fisik yang kuat dan catatan prestasi di bidang olahraga.";
        }
        if ($isMedical && $primary === 'Kesehatan & Keperawatan (Medis)') {
            $reasoning[] = "Tertarik pada ilmu kesehatan, anatomi tubuh, dan pengabdian medis masyarakat.";
        }
        if ($isAgriculture && $primary === 'Pertanian & Agroteknologi') {
            $reasoning[] = "Menunjukkan rekam nilai akademik dan ketertarikan kuat di bidang botani, ilmu tanah, dan pertanian.";
        }
        if ($isAviation && $primary === 'Penerbangan & Kedirgantaraan') {
            $reasoning[] = "Menunjukkan minat kuat dan rekam nilai akademik cemerlang di bidang dirgantara, operasional penerbangan, serta teknologi aeronautika.";
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
            case 'Seni Kuliner & Tata Boga':
                $careers = ['Chef / Koki Profesional', 'Pastry Chef', 'Food Stylist', 'Restaurant Manager / Owner'];
                $extracurriculars = ['Klub Memasak / Tata Boga', 'Kewirausahaan Kuliner'];
                $competitions = ['Lomba Masak Nasional', 'Salon Culinaire Indonesia', 'WorldSkills Culinary Arts'];
                $targets = ['Mempelajari teknik sanitasi & kebersihan pangan', 'Mengembangkan resep kreasi boga orisinal'];
                break;
            case 'Seni Musik & Pertunjukan':
                $careers = ['Penyanyi / Vokalis Profesional', 'Komponis / Music Producer', 'Guru Musik / Vokal', 'Music Director / Arranger'];
                $extracurriculars = ['Paduan Suara / Choir', 'Klub Band / Musik', 'Teater & Seni Pertunjukan'];
                $competitions = ['Festival dan Lomba Seni Siswa Nasional (FLS2N) - Menyanyi', 'Got Talent Competition', 'Lomba Cipta Lagu Nasional'];
                $targets = ['Mempelajari teori harmoni dan aransemen musik', 'Melatih teknik vokal/instrumen secara konsisten'];
                break;
            case 'Olahraga & Kesehatan Fisik':
                $careers = ['Atlet Profesional', 'Pelatih Olahraga / Coach', 'Guru Penjasorkes', 'Fisioterapis Olahraga'];
                $extracurriculars = ['Klub Futsal / Sepakbola', 'Klub Basket / Badminton', 'Pramuka'];
                $competitions = ['Olimpiade Olahraga Siswa Nasional (O2SN)', 'Pekan Olahraga Mahasiswa (POMNAS)'];
                $targets = ['Meningkatkan ketahanan fisik dan teknik olahraga', 'Mempelajari dasar fisiologi & nutrisi olahraga'];
                break;
            case 'Kesehatan & Keperawatan (Medis)':
                $careers = ['Perawat Profesional', 'Asisten Apoteker', 'Konsultan Kesehatan', 'Bidan / Praktisi Medis'];
                $extracurriculars = ['Palang Merah Remaja (PMR)', 'Klub Sains Keperawatan'];
                $competitions = ['Lomba Kompetensi Siswa (LKS) Health & Social Care', 'Karya Tulis Ilmiah Kesehatan'];
                $targets = ['Mempelajari teknik pertolongan pertama & keperawatan', 'Mengikuti seminar kesehatan dan magang klinis'];
                break;
            case 'Pertanian & Agroteknologi':
                $careers = ['Ahli Agronomi / Agroteknologi', 'Agribisnis Consultant', 'Penyuluh Pertanian', 'Ahli Botani / Peneliti Pangan'];
                $extracurriculars = ['Klub Hidroponik & Kebun Sekolah', 'Klub Pencinta Alam / Agro'];
                $competitions = ['Lomba Inovasi Teknologi Pertanian', 'Pekan Ilmiah Nasional Bidang Ketahanan Pangan'];
                $targets = ['Mempelajari sistem pertanian hidroponik & modern', 'Melakukan riset mandiri tentang kesuburan tanah'];
                break;
            case 'Perikanan & Kelautan':
                $careers = ['Ahli Akuakultur / Budidaya Perairan', 'Marine Biologist / Ahli Kelautan', 'Konsultan Quality Control Perikanan', 'Manager Industri Perikanan'];
                $extracurriculars = ['Klub Akuakultur & Bio-Fisheries', 'Kelompok Studi Ekologi Perairan', 'Klub Research Marine & Fisheries'];
                $competitions = ['Lomba Inovasi Teknologi Akuakultur & Perikanan', 'PIMNAS Bidang Ketahanan Maritim & Perikanan'];
                $targets = ['Mempelajari teknik manajemen kualitas air & resirkulasi akuakultur (RAS)', 'Mengembangkan riset ekosistem perairan & sumber daya laut'];
                break;
            case 'Penerbangan & Kedirgantaraan':
                $careers = ['Commercial Pilot', 'Aerospace Engineer', 'Air Traffic Controller', 'Flight Operations Officer', 'Aircraft Maintenance Engineer'];
                $extracurriculars = ['Klub Aeromodelling', 'Karya Ilmiah Remaja Dirgantara', 'Pramuka Saka Dirgantara'];
                $competitions = ['Lomba Aeromodelling Nasional', 'Olimpiade Dirgantara', 'Kompetisi Robot / UAV Terbang'];
                $targets = ['Memahami regulasi udara dasar (Air Law)', 'Mempelajari prinsip navigasi & meteorologi penerbangan', 'Mengikuti pelatihan simulator penerbangan ground school'];
                break;
            default:
                $careers = ['Teacher / Educator', 'Public Relations Specialist', 'Human Resources Manager', 'Social Worker'];
                $extracurriculars = ['Pramuka', 'Palang Remaja (PMR)', 'OSIS'];
                $competitions = ['Lomba Debat Bahasa Indonesia', 'Kompetisi Pengabdian Sosial'];
                $targets = ['Melatih kemampuan public speaking', 'Mengikuti program volunterisme kemanusiaan'];
                break;
        }

        // Save AI Analysis results
        AiAnalysis::where('student_id', $student->id)->delete();
        
        $analisisMendalam = "Berdasarkan analisis bakat mandiri, Anda menunjukkan potensi dominan di bidang " . $primary . ". " .
            "Kecakapan Anda di bidang ini tecermin dari akumulasi nilai akademik pada mata pelajaran terkait serta riwayat keaktifan non-akademik Anda. " .
            "Untuk analisis narasi yang lebih komprehensif, pastikan API Key Gemini atau OpenRouter Anda terkonfigurasi dengan benar.";

        $analysis = AiAnalysis::create([
            'student_id' => $student->id,
            'primary_talent' => $primary,
            'analisis_mendalam' => $analisisMendalam,
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

    /**
     * Student self-inputs academic grade (for Public / Mandiri users).
     */
    public function saveIndependentGrade(Request $request)
    {
        $user = $request->user();
        $student = Student::firstOrCreate(['user_id' => $user->id]);

        if ($student->institution_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Murid sekolah hanya dapat diinput nilainya oleh Guru.'
            ], 403);
        }

        // Auto-default semester for public/umum users who don't have semesters
        if ($user->role === 'umum') {
            $request->merge(['semester' => 1]);
        }

        $validator = Validator::make($request->all(), [
            'semester' => 'required|integer|min:1|max:14',
            'subject_name' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $grade = AcademicGrade::updateOrCreate(
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

        return response()->json([
            'success' => true,
            'message' => 'Nilai akademik mandiri berhasil disimpan.',
            'grade' => $grade
        ]);
    }

    /**
     * Delete independent academic grade.
     */
    public function deleteIndependentGrade(Request $request, $id)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        if ($student->institution_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Murid sekolah tidak dapat menghapus nilai.'
            ], 403);
        }

        $grade = AcademicGrade::where('id', $id)->where('student_id', $student->id)->first();
        if (!$grade) {
            return response()->json([
                'success' => false,
                'message' => 'Nilai tidak ditemukan.'
            ], 404);
        }

        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil dihapus.'
        ]);
    }

    /**
     * Bulk delete independent academic grades.
     */
    public function bulkDeleteIndependentGrades(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        if ($student->institution_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Murid sekolah tidak dapat menghapus nilai.'
            ], 403);
        }

        $all = $request->input('all', false);
        $ids = $request->input('ids', []);

        if ($all) {
            AcademicGrade::where('student_id', $student->id)->delete();
            return response()->json([
                'success' => true,
                'message' => 'Semua nilai akademik berhasil dihapus.'
            ]);
        }

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada nilai yang dipilih untuk dihapus.'
            ], 400);
        }

        AcademicGrade::where('student_id', $student->id)
            ->whereIn('id', $ids)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nilai yang dipilih berhasil dihapus.'
        ]);
    }

    /**
     * Bulk delete achievements.
     */
    public function bulkDeleteAchievements(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.'
            ], 404);
        }

        $all = $request->input('all', false);
        $ids = $request->input('ids', []);

        if ($all) {
            $achievements = Achievement::where('student_id', $student->id)->get();
            foreach ($achievements as $achievement) {
                if ($achievement->certificate_path && file_exists(public_path($achievement->certificate_path))) {
                    @unlink(public_path($achievement->certificate_path));
                }
                $achievement->delete();
            }
            return response()->json([
                'success' => true,
                'message' => 'Semua sertifikat prestasi berhasil dihapus.'
            ]);
        }

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sertifikat yang dipilih untuk dihapus.'
            ], 400);
        }

        $achievements = Achievement::where('student_id', $student->id)
            ->whereIn('id', $ids)
            ->get();

        foreach ($achievements as $achievement) {
            if ($achievement->certificate_path && file_exists(public_path($achievement->certificate_path))) {
                @unlink(public_path($achievement->certificate_path));
            }
            $achievement->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sertifikat yang dipilih berhasil dihapus.'
        ]);
    }

    /**
     * Get Institution Announcements for Feed (with talent recommendation matching).
     */
    public function getAnnouncements(Request $request)
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        $query = InstitutionAnnouncement::with('institution.user')
            ->where('is_published', true);

        if ($student && $student->institution_id) {
            $query->where('institution_id', $student->institution_id);
        } else if ($user->role === 'institusi') {
            $institution = \App\Models\Institution::where('user_id', $user->id)->first();
            if ($institution) {
                $query->where('institution_id', $institution->id);
            }
        } else if ($user->role === 'guru') {
            $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
            if ($teacher && $teacher->institution_id) {
                $query->where('institution_id', $teacher->institution_id);
            }
        }

        $announcements = $query->orderBy('created_at', 'desc')->get();

        // If no filter matched or empty, return all published announcements so feed is never completely blank
        if ($announcements->isEmpty()) {
            $announcements = InstitutionAnnouncement::with('institution.user')
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Get student's AI analysis / talent profile to determine recommendations
        $aiAnalysis = $student ? AiAnalysis::where('student_id', $student->id)->latest()->first() : null;
        $primaryTalent = null;
        if ($aiAnalysis && !empty($aiAnalysis->kategori_bakat_utama)) {
            $primaryTalent = strtolower($aiAnalysis->kategori_bakat_utama);
        }

        $formatted = $announcements->map(function ($item) use ($primaryTalent) {
            $target = strtolower($item->target_talent ?? 'semua');
            $isRecommended = ($target === 'semua') || ($primaryTalent && str_contains($primaryTalent, $target));

            return [
                'id' => $item->id,
                'title' => $item->title,
                'category' => $item->category,
                'target_talent' => $item->target_talent ?? 'Semua',
                'content' => $item->content,
                'banner_image_url' => $item->banner_image ? asset('storage/' . $item->banner_image) : null,
                'external_link' => $item->external_link,
                'institution_name' => $item->institution ? $item->institution->name : 'Institusi',
                'is_recommended' => $isRecommended,
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                'created_at_formatted' => $item->created_at->isoFormat('D MMMM YYYY'),
            ];
        })->toArray();

        // Include Active Master Competitions posted by Super Admin into the feed
        $masterCompetitions = \App\Models\Competition::where('is_active', true)->orderBy('created_at', 'desc')->get();
        foreach ($masterCompetitions as $comp) {
            $compCategory = strtolower($comp->category);
            $isRecommended = ($primaryTalent && str_contains($primaryTalent, $compCategory));

            $formatted[] = [
                'id' => 'master_' . $comp->id,
                'title' => '[Kompetisi Nasional] ' . $comp->title,
                'category' => 'lomba',
                'target_talent' => ucfirst($comp->category),
                'content' => ($comp->description ?? 'Kompetisi Nasional resmi.') . ($comp->registration_deadline ? "\nBatas Pendaftaran: " . \Carbon\Carbon::parse($comp->registration_deadline)->isoFormat('D MMMM YYYY') : ''),
                'banner_image_url' => $comp->poster_path ? asset('storage/' . $comp->poster_path) : null,
                'external_link' => $comp->link,
                'institution_name' => $comp->organizer ?? 'Pusat (Super Admin)',
                'is_recommended' => $isRecommended,
                'created_at' => $comp->created_at->format('Y-m-d H:i:s'),
                'created_at_formatted' => $comp->created_at->isoFormat('D MMMM YYYY'),
            ];
        }

        return response()->json([
            'success' => true,
            'announcements' => $formatted
        ]);
    }
}
