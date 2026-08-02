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

        $autoVerify = is_null($student->institution_id);

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
            // 0. Attempt to call Google Gemini API if key is set
            $geminiKey = env('GEMINI_API_KEY') ?: (getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? null));
            if ($geminiKey) {
                try {
                    // Compile ALL student academic grades dynamically
                    $gradesGrouped = $grades->groupBy('subject_name')->map(function ($items) {
                        return round($items->avg('score'), 2);
                    });
                    
                    $gradesListString = '';
                    if ($gradesGrouped->isEmpty()) {
                        $gradesListString = "Tidak ada data nilai rapor/akademik.";
                    } else {
                        foreach ($gradesGrouped as $subject => $score) {
                            $gradesListString .= "- {$subject}: {$score}\n";
                        }
                    }

                    $riasec = $testResult ? $testResult->scores : [
                        'Realistic' => 50, 'Investigative' => 50, 'Artistic' => 50,
                        'Social' => 50, 'Enterprising' => 50, 'Conventional' => 50
                    ];
                    $dominant = $testResult ? $testResult->dominant_category : 'Investigative';

                    $hobbiesString = count($hobbies) > 0 ? implode(', ', $hobbies) : 'Tidak ada';
                    $interestsString = count($interests) > 0 ? implode(', ', $interests) : 'Tidak ada';
                    $achievementsString = $achievements->count() > 0 
                        ? $achievements->map(function ($ach) { return $ach->title . " (" . $ach->level . ")"; })->implode(', ')
                        : 'Tidak ada';

                    $prompt = "Anda adalah AI Detektor Bakat untuk aplikasi Lost Talent Detector.
Tugas Anda adalah memprediksi bakat siswa secara cerdas dan personal berdasarkan profil di bawah ini. Analisis nama mata pelajaran, prestasi, hobi, dan tes minat secara teliti agar rekomendasi akurat dan relevan dengan bidang keahlian aktual (seperti Perikanan, Kelautan, Pertanian, Medis, Teknik, Desain, dll).

Profil Siswa:
- Peran: {$user->role}
- Hobi: {$hobbiesString}
- Minat: {$interestsString}
- Nilai Rata-rata Pelajaran/Mata Kuliah:
{$gradesListString}
- Daftar Prestasi / Sertifikat: {$achievementsString}
- Kategori Dominan RIASEC: {$dominant} (Skor detail: Realistic: {$riasec['Realistic']}%, Investigative: {$riasec['Investigative']}%, Artistic: {$riasec['Artistic']}%, Social: {$riasec['Social']}%, Enterprising: {$riasec['Enterprising']}%, Conventional: {$riasec['Conventional']}%)

Keluarkan hasil prediksi dalam format JSON dengan struktur berikut dan jangan sertakan format Markdown/Keterangan teks apa pun selain JSON:
{
  \"primary_talent\": \"Bakat utama yang paling dominan (contoh: Perikanan & Kelautan, Pertanian & Agroteknologi, Robotik, Programming, Desain Kreatif & UI/UX, Bisnis & Kewirausahaan, Sains & Riset, Seni Kuliner & Tata Boga, Seni Musik & Pertunjukan, Olahraga & Kesehatan Fisik, Kesehatan & Keperawatan (Medis), dll)\",
  \"confidence_score\": 95,
  \"supporting_talents\": [
    {\"talent\": \"Bakat pendukung 1\", \"confidence\": 85},
    {\"talent\": \"Bakat pendukung 2\", \"confidence\": 75},
    {\"talent\": \"Bakat pendukung 3\", \"confidence\": 60}
  ],
  \"reasoning\": [
    \"Alasan 1 berdasarkan detail profil...\",
    \"Alasan 2...\",
    \"Alasan 3...\"
  ],
  \"career_recommendations\": [
    \"Pekerjaan 1\", \"Pekerjaan 2\", \"Pekerjaan 3\"
  ],
  \"competition_recommendations\": [
    \"Rekomendasi Lomba 1\", \"Lomba 2\"
  ],
  \"development_targets\": [
    \"Target pengembangan diri 1\", \"Target 2\"
  ]
}";

                    $geminiResponse = \Illuminate\Support\Facades\Http::timeout(5)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post("https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key={$geminiKey}", [
                            'contents' => [
                                [
                                    'parts' => [
                                        ['text' => $prompt]
                                    ]
                                ]
                            ],
                            'generationConfig' => [
                                'responseMimeType' => 'application/json',
                            ]
                        ]);

                    if ($geminiResponse->successful()) {
                        $resJson = $geminiResponse->json();
                        $text = $resJson['candidates'][0]['content']['parts'][0]['text'] ?? '';
                        $data = json_decode(trim($text), true);
 
                        if ($data && isset($data['primary_talent'])) {
                            // Save AI Analysis results
                            AiAnalysis::where('student_id', $student->id)->delete();
                            
                            $analysis = AiAnalysis::create([
                                'student_id' => $student->id,
                                'primary_talent' => $data['primary_talent'],
                                'confidence_score' => floatval($data['confidence_score'] ?? 90),
                                'supporting_talents' => $data['supporting_talents'] ?? [],
                                'reasoning' => $data['reasoning'] ?? [],
                                'career_recommendations' => $data['career_recommendations'] ?? [],
                                'extracurricular_recommendations' => $data['development_targets'] ?? [],
                                'competition_recommendations' => $data['competition_recommendations'] ?? [],
                                'development_targets' => $data['development_targets'] ?? [],
                                'model_version' => 'gemini-1.5-flash-api',
                                'analyzed_at' => Carbon::now(),
                            ]);
 
                            return response()->json([
                                'success' => true,
                                'message' => 'AI Talent Analysis ran successfully via Gemini LLM.',
                                'source' => 'gemini_api',
                                'analysis' => $analysis
                            ]);
                        } else {
                            \Illuminate\Support\Facades\Log::warning('Gemini API returned invalid JSON structure: ' . $text);
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::error('Gemini API request failed with status: ' . $geminiResponse->status() . ' - Response: ' . $geminiResponse->body());
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gemini API exception: ' . $e->getMessage());
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('Gemini API key is not configured or empty.');
            }

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
                        return str_contains($name, 'olahraga') || str_contains($name, 'penjas') || str_contains($name, 'atletik') || str_contains($name, 'fisik') || str_contains($name, 'kesehatan rekreasi');
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
        if (count($interests) > 0) {
            $primaryInterest = trim($interests[0]);
            if (!empty($primaryInterest) && strlen($primaryInterest) > 2) {
                $customInterestName = ucwords($primaryInterest);
                $hasCustomInterest = true;
                
                $lowerInterest = strtolower($primaryInterest);
                $standards = ['robot', 'coding', 'program', 'sains', 'riset', 'desain', 'ui', 'ux', 'bisnis', 'usaha', 'sosial', 'didik', 'masak', 'boga', 'kuliner', 'chef', 'koki', 'musik', 'vokal', 'olahraga', 'atlet', 'medis', 'dokter', 'perawat', 'tani', 'kebun', 'tanah', 'botani', 'agro', 'ternak', 'hutan', 'tanaman', 'ikan', 'perikanan', 'perairan', 'kelautan', 'maritim', 'mancing', 'pancing', 'iktiologi', 'akuakultur'];
                foreach ($standards as $s) {
                    if (str_contains($lowerInterest, $s)) {
                        $hasCustomInterest = false;
                        break;
                    }
                }
            }
        }

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
            'Perikanan & Kelautan' => 50
        ];

        if ($hasCustomInterest) {
            $talentScores[$customInterestName] = 100; // Strong base score
            
            $cleanStem = function($word) {
                $w = strtolower($word);
                if (str_starts_with($w, 'ber')) $w = substr($w, 3);
                elseif (str_starts_with($w, 'per')) $w = substr($w, 3);
                elseif (str_starts_with($w, 'pe')) $w = substr($w, 2);
                elseif (str_starts_with($w, 'me')) $w = substr($w, 2);
                if (str_ends_with($w, 'an')) $w = substr($w, 0, -2);
                return $w;
            };

            $interestWords = array_filter(explode(' ', strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $customInterestName))), function($w) {
                return strlen($w) > 2;
            });
            
            foreach ($hobbies as $h) {
                $hLower = strtolower($h);
                foreach ($interestWords as $w) {
                    $stem = $cleanStem($w);
                    if (str_contains($hLower, $w) || (!empty($stem) && str_contains($hLower, $stem))) {
                        $talentScores[$customInterestName] += 25;
                    }
                }
            }
            
            foreach ($grades as $g) {
                $gLower = strtolower($g->subject_name);
                foreach ($interestWords as $w) {
                    $stem = $cleanStem($w);
                    if (str_contains($gLower, $w) || (!empty($stem) && str_contains($gLower, $stem))) {
                        $talentScores[$customInterestName] += 15;
                    }
                }
            }
        }
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

        // Find initial top candidate
        arsort($talentScores);
        $topCandidate = $hasCustomInterest ? $customInterestName : key($talentScores);

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
        $primary = $hasCustomInterest ? $customInterestName : key($talentScores);
        $primaryVal = 99.0; // Scaled UI confidence
        
        // Remove primary from array to extract top 3 supporting talents
        $tempScores = $talentScores;
        if (isset($tempScores[$primary])) {
            unset($tempScores[$primary]);
        }

        $topSuppScore = count($tempScores) > 0 ? reset($tempScores) : 1;
        $basePercentages = [85.0, 75.0, 65.0];
        $supporting = [];
        $counter = 0;
        foreach ($tempScores as $talent => $score) {
            if ($counter >= 3) break;
            $ratio = $score / max(1, $topSuppScore);
            $calculatedConf = round(min(92.0, max(45.0, $basePercentages[$counter] * $ratio)), 1);
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
        if ($achievements->count() > 0) {
            $reasoning[] = "Memiliki " . $achievements->count() . " prestasi yang relevan dan terverifikasi di bidang " . strtolower($primary);
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
        if ($hasCustomInterest && $primary === $customInterestName) {
            $reasoning[] = "Memiliki bakat spesifik dan fokus pengembangan diri yang kuat di bidang " . $primary . ".";
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
            default:
                if ($hasCustomInterest && $primary === $customInterestName) {
                    $careers = [
                        $primary . " Professional",
                        "Spesialis " . $primary,
                        "Konsultan " . $primary,
                        "Pendidik / Praktisi " . $primary
                    ];
                    $extracurriculars = [
                        "Klub / Komunitas " . $primary,
                        "Karya Tulis Ilmiah Bidang " . $primary
                    ];
                    $competitions = [
                        "Kompetisi Nasional " . $primary,
                        "Lomba Inovasi Mahasiswa " . $primary,
                        "Festival / Pameran " . $primary
                    ];
                    $targets = [
                        "Meningkatkan keahlian praktis di bidang " . $primary,
                        "Membangun portofolio karya dan proyek " . $primary
                    ];
                } else {
                    $careers = ['Teacher / Educator', 'Public Relations Specialist', 'Human Resources Manager', 'Social Worker'];
                    $extracurriculars = ['Pramuka', 'Palang Merah Remaja (PMR)', 'OSIS'];
                    $competitions = ['Lomba Debat Bahasa Indonesia', 'Kompetisi Pengabdian Sosial'];
                    $targets = ['Melatih kemampuan public speaking', 'Mengikuti program volunterisme kemanusiaan'];
                }
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
}
