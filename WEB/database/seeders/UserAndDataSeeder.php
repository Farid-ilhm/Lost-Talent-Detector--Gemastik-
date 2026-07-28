<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Institution;
use App\Models\Major;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\AcademicGrade;
use App\Models\Achievement;
use App\Models\Organization;
use App\Models\Extracurricular;
use App\Models\Attendance;
use App\Models\TeacherNote;
use App\Models\InterestTest;
use App\Models\InterestTestAnswer;
use App\Models\InterestTestResult;
use App\Models\AiAnalysis;
use App\Models\CustomNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserAndDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Administrator
        $adminUser = User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@losttalent.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        // 2. Seed Institution
        $schoolUser = User::create([
            'name' => 'SMA Negeri 1 Jakarta',
            'email' => 'sman1jkt@school.id',
            'password' => Hash::make('password'),
            'role' => 'institusi',
            'phone' => '0215551234',
            'status' => 'active',
        ]);

        $institution = Institution::create([
            'user_id' => $schoolUser->id,
            'npsn' => '20103245',
            'type' => 'sekolah',
            'address' => 'Jl. Budi Utomo No.7, Sawah Besar, Jakarta Pusat',
            'phone' => '0215551234',
            'website' => 'https://sman1jkt.sch.id',
            'is_verified' => true,
        ]);

        // Seed Majors
        $majorIpa = Major::create([
            'institution_id' => $institution->id,
            'name' => 'MIPA (Matematika dan Ilmu Pengetahuan Alam)',
            'code' => 'MIPA',
        ]);

        $majorIps = Major::create([
            'institution_id' => $institution->id,
            'name' => 'IPS (Ilmu Pengetahuan Sosial)',
            'code' => 'IPS',
        ]);

        // Seed Academic Year
        $academicYear = AcademicYear::create([
            'institution_id' => $institution->id,
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        // Seed Classrooms
        $classX1 = Classroom::create([
            'institution_id' => $institution->id,
            'name' => 'X MIPA 1',
            'major_id' => $majorIpa->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $classXI1 = Classroom::create([
            'institution_id' => $institution->id,
            'name' => 'XI MIPA 1',
            'major_id' => $majorIpa->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $classXIPS1 = Classroom::create([
            'institution_id' => $institution->id,
            'name' => 'X IPS 1',
            'major_id' => $majorIps->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // 3. Seed Teacher
        $teacherUser = User::create([
            'name' => 'Budi Santoso, S.Kom.',
            'email' => 'budi@school.id',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'phone' => '087712345678',
            'status' => 'active',
        ]);

        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'institution_id' => $institution->id,
            'nip' => '198001012005011002',
            'subject' => 'Informatika & Robotik',
        ]);

        // 4. Seed Parent
        $parentUser = User::create([
            'name' => 'Joko Widodo (Orang Tua Andi)',
            'email' => 'parent@example.com',
            'password' => Hash::make('password'),
            'role' => 'orang_tua',
            'phone' => '085298765432',
            'status' => 'active',
        ]);

        // 5. Seed Student (Andi)
        $studentUser = User::create([
            'name' => 'Andi Pratama',
            'email' => 'andi@student.id',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'phone' => '089911223344',
            'status' => 'active',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'institution_id' => $institution->id,
            'classroom_id' => $classXI1->id,
            'nisn' => '0054321098',
            'birth_date' => '2009-05-14',
            'gender' => 'L',
            'hobbies' => ['Coding', 'Robotics', 'Gaming', 'Reading Sci-Fi'],
            'interests' => ['Artificial Intelligence', 'Embedded Systems', 'Mathematics'],
            'personality' => 'INTJ (Introverted, Intuitive, Thinking, Judging)',
            'parent_user_id' => $parentUser->id,
        ]);

        // Seed Andi's Academic Grades
        $grades = [
            ['semester' => 1, 'subject_name' => 'Matematika', 'score' => 92.50],
            ['semester' => 1, 'subject_name' => 'Informatika', 'score' => 95.00],
            ['semester' => 1, 'subject_name' => 'Fisika', 'score' => 88.00],
            ['semester' => 1, 'subject_name' => 'Bahasa Inggris', 'score' => 86.00],
            ['semester' => 2, 'subject_name' => 'Matematika', 'score' => 94.00],
            ['semester' => 2, 'subject_name' => 'Informatika', 'score' => 97.50],
            ['semester' => 2, 'subject_name' => 'Fisika', 'score' => 90.00],
            ['semester' => 2, 'subject_name' => 'Bahasa Inggris', 'score' => 88.50],
        ];
        foreach ($grades as $g) {
            AcademicGrade::create([
                'student_id' => $student->id,
                'semester' => $g['semester'],
                'subject_name' => $g['subject_name'],
                'score' => $g['score'],
                'created_by' => $teacherUser->id,
            ]);
        }

        // Seed Andi's Achievements
        Achievement::create([
            'student_id' => $student->id,
            'title' => 'Juara 2 Olimpiade Sains Nasional (OSN) Bidang Informatika Tingkat Kabupaten',
            'category' => 'sains',
            'level' => 'kabupaten',
            'rank' => 'Juara 2',
            'certificate_path' => 'certificates/osn_kabupaten_andi.pdf',
            'description' => 'Meraih juara 2 dalam seleksi kompetisi pemrograman kompetitif OSN Informatika tingkat Kabupaten.',
            'is_verified' => true,
            'verified_by' => $teacherUser->id,
        ]);

        Achievement::create([
            'student_id' => $student->id,
            'title' => 'Juara 1 Lomba Robotika Cerdas Cermat STEM Regional',
            'category' => 'teknologi',
            'level' => 'provinsi',
            'rank' => 'Juara 1',
            'certificate_path' => 'certificates/robotika_stem_andi.pdf',
            'description' => 'Membangun robot line follower tercepat dengan sistem mikrokontroler Arduino.',
            'is_verified' => true,
            'verified_by' => $teacherUser->id,
        ]);

        // Seed Andi's Organizations
        Organization::create([
            'student_id' => $student->id,
            'name' => 'Coding and Robotics Club (CRC) SMAN 1 Jakarta',
            'role' => 'Wakil Ketua Divisi Robotika',
            'start_date' => '2024-07-15',
            'end_date' => '2025-06-30',
            'description' => 'Membantu merancang kurikulum pembelajaran dasar robotika dan melatih anggota baru.',
        ]);

        // Seed Andi's Extracurriculars
        Extracurricular::create([
            'student_id' => $student->id,
            'name' => 'Robotik',
            'score' => 'A',
            'notes' => 'Menunjukkan bakat luar biasa dalam desain sensorik dan pemrograman mikrokontroler.',
        ]);

        Extracurricular::create([
            'student_id' => $student->id,
            'name' => 'Karya Ilmiah Remaja (KIR)',
            'score' => 'A',
            'notes' => 'Aktif melakukan penelitian di bidang teknologi robotik ramah lingkungan.',
        ]);

        // Seed Andi's Attendances
        Attendance::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester' => 1,
            'present' => 110,
            'sick' => 1,
            'permit' => 0,
            'alpha' => 0,
        ]);

        // Seed Teacher Notes
        TeacherNote::create([
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'notes' => 'Andi memiliki ketertarikan tinggi pada logika algoritma dan teknologi otomatisasi. Sangat direkomendasikan untuk dibina dalam olimpiade komputer atau lomba robotik tingkat nasional.',
        ]);

        // Get RIASEC Test and Seed Andi's Test Answers & Result
        $riasec = InterestTest::where('title', 'like', '%RIASEC%')->first();
        if ($riasec) {
            $questions = $riasec->questions;
            foreach ($questions as $q) {
                // Populate realistic/investigative highly, conventional moderately, artistic low
                $ansVal = 3;
                if ($q->category == 'Investigative') {
                    $ansVal = 5;
                } elseif ($q->category == 'Realistic') {
                    $ansVal = 5;
                } elseif ($q->category == 'Conventional') {
                    $ansVal = 4;
                } elseif ($q->category == 'Artistic') {
                    $ansVal = 1;
                } elseif ($q->category == 'Social') {
                    $ansVal = 2;
                }

                InterestTestAnswer::create([
                    'student_id' => $student->id,
                    'interest_test_question_id' => $q->id,
                    'answer_value' => (string) $ansVal,
                ]);
            }

            InterestTestResult::create([
                'student_id' => $student->id,
                'interest_test_id' => $riasec->id,
                'scores' => [
                    'Realistic' => 85,
                    'Investigative' => 96,
                    'Artistic' => 30,
                    'Social' => 45,
                    'Enterprising' => 50,
                    'Conventional' => 75,
                ],
                'dominant_category' => 'Investigative',
            ]);
        }

        // Seed Andi's AI Analysis
        AiAnalysis::create([
            'student_id' => $student->id,
            'primary_talent' => 'Robotik',
            'confidence_score' => 96.00,
            'supporting_talents' => [
                ['talent' => 'Programming', 'confidence' => 90.00],
                ['talent' => 'Engineering', 'confidence' => 87.00],
                ['talent' => 'Research', 'confidence' => 74.00]
            ],
            'reasoning' => [
                'Nilai Matematika tinggi (Rata-rata 93.25)',
                'Nilai Informatika sangat tinggi (Rata-rata 96.25)',
                'Aktif dalam Ekstrakurikuler Robotik (Nilai A)',
                'Pernah meraih prestasi tingkat regional/provinsi di bidang STEM/Robotik',
                'Memiliki hobi Coding dan ketertarikan AI yang kuat',
                'Hasil tes RIASEC dominan pada aspek Investigative (96%) dan Realistic (85%)'
            ],
            'career_recommendations' => [
                'Robotics Engineer',
                'AI Researcher',
                'Embedded Systems Developer',
                'Software Engineer'
            ],
            'extracurricular_recommendations' => [
                'Klub Robotika Tingkat Lanjut',
                'Web & Game Development'
            ],
            'competition_recommendations' => [
                'GEMASTIK - Pengembangan Perangkat Lunak',
                'Lomba Robotika Nasional (BARON)',
                'Olimpiade Sains Nasional Informatika'
            ],
            'development_targets' => [
                'Mempelajari machine learning tingkat lanjut (Python, PyTorch)',
                'Mengikuti sertifikasi Embedded Systems Developer',
                'Membangun portfolio proyek Internet of Things (IoT)'
            ],
            'model_version' => 'lost-talent-xgb-v1.0',
            'analyzed_at' => Carbon::now(),
        ]);


        // 6. Seed Public User (Bambang)
        $publicUser = User::create([
            'name' => 'Bambang Hermawan',
            'email' => 'bambang@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'umum',
            'phone' => '081299887766',
            'status' => 'active',
        ]);

        $publicStudent = Student::create([
            'user_id' => $publicUser->id,
            'institution_id' => null,
            'classroom_id' => null,
            'nisn' => null,
            'birth_date' => '2004-11-20',
            'gender' => 'L',
            'hobbies' => ['Web Development', 'Blogging', 'Digital Painting'],
            'interests' => ['Programming', 'UI/UX Design', 'Creative Writing'],
            'personality' => 'ENFP (Extraverted, Intuitive, Feeling, Perceiving)',
        ]);

        // Seed Bambang's Achievements (Unverified by school)
        Achievement::create([
            'student_id' => $publicStudent->id,
            'title' => 'Finalis Hackathon UI/UX Design Competition',
            'category' => 'teknologi',
            'level' => 'nasional',
            'rank' => 'Finalis',
            'certificate_path' => 'certificates/hackathon_bambang.pdf',
            'description' => 'Mendesain prototipe aplikasi bank digital untuk inklusi keuangan di pedesaan.',
            'is_verified' => false,
            'verified_by' => null,
        ]);

        // Seed Bambang's RIASEC test answers & results
        if ($riasec) {
            foreach ($questions as $q) {
                // Populate artistic/investigative highly, conventional low
                $ansVal = 3;
                if ($q->category == 'Artistic') {
                    $ansVal = 5;
                } elseif ($q->category == 'Social') {
                    $ansVal = 4;
                } elseif ($q->category == 'Investigative') {
                    $ansVal = 4;
                } elseif ($q->category == 'Conventional') {
                    $ansVal = 2;
                }

                InterestTestAnswer::create([
                    'student_id' => $publicStudent->id,
                    'interest_test_question_id' => $q->id,
                    'answer_value' => (string) $ansVal,
                ]);
            }

            InterestTestResult::create([
                'student_id' => $publicStudent->id,
                'interest_test_id' => $riasec->id,
                'scores' => [
                    'Realistic' => 60,
                    'Investigative' => 80,
                    'Artistic' => 88,
                    'Social' => 75,
                    'Enterprising' => 70,
                    'Conventional' => 50,
                ],
                'dominant_category' => 'Artistic',
            ]);
        }

        // Seed Bambang's AI Analysis
        AiAnalysis::create([
            'student_id' => $publicStudent->id,
            'primary_talent' => 'Creative Web & UI/UX Design',
            'confidence_score' => 88.00,
            'supporting_talents' => [
                ['talent' => 'Frontend Development', 'confidence' => 82.00],
                ['talent' => 'Digital Content Writing', 'confidence' => 78.00]
            ],
            'reasoning' => [
                'Dominasi tinggi pada tes RIASEC kategori Artistic (88%) dan Investigative (80%)',
                'Memiliki hobi Web Development dan Digital Painting',
                'Ketertarikan kuat pada UI/UX Design dan Creative Writing',
                'Berpengalaman menjadi Finalis Hackathon UI/UX Design tingkat nasional'
            ],
            'career_recommendations' => [
                'UI/UX Designer',
                'Frontend Developer',
                'Product Designer',
                'Creative Content Creator'
            ],
            'extracurricular_recommendations' => [],
            'competition_recommendations' => [
                'GEMASTIK - Desain Pengalaman Pengguna (UI/UX Design)'
            ],
            'development_targets' => [
                'Membangun portofolio desain UI/UX di Behance/Dribbble',
                'Mempelajari framework modern frontend (React/Vue/Vite)',
                'Mempelajari prinsip riset pengguna (User Research)'
            ],
            'model_version' => 'lost-talent-xgb-v1.0',
            'analyzed_at' => Carbon::now(),
        ]);

        // 7. Seed Custom Notifications
        CustomNotification::create([
            'user_id' => $studentUser->id,
            'title' => 'Analisis AI Selesai',
            'message' => 'Hasil analisis bakat utama Anda ("Robotik") telah diterbitkan oleh AI. Silakan periksa dashboard Anda.',
            'type' => 'ai_ready',
        ]);

        CustomNotification::create([
            'user_id' => $studentUser->id,
            'title' => 'Rekomendasi Lomba Baru',
            'message' => 'Lomba "GEMASTIK - Pengembangan Perangkat Lunak" sangat cocok dengan profil bakat Anda. Batas pendaftaran 15 Agustus.',
            'type' => 'info_lomba',
        ]);
    }
}
