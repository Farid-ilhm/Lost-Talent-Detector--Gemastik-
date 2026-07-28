<?php

namespace Database\Seeders;

use App\Models\InterestTest;
use App\Models\InterestTestQuestion;
use Illuminate\Database\Seeder;

class InterestTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $riasec = InterestTest::create([
            'title' => 'Tes Minat Bakat RIASEC',
            'description' => 'Tes minat bakat berbasis RIASEC (Realistic, Investigative, Artistic, Social, Enterprising, Conventional) untuk mengidentifikasi kecenderungan minat karir dan bakat Anda.',
            'is_active' => true,
        ]);

        $questions = [
            // Realistic
            ['question_text' => 'Saya suka memperbaiki barang-barang mekanik atau instalasi listrik.', 'category' => 'Realistic'],
            ['question_text' => 'Saya suka merakit robot, perangkat keras komputer, atau melakukan pertukangan kayu.', 'category' => 'Realistic'],
            ['question_text' => 'Saya senang beraktivitas di luar ruangan dan melakukan pekerjaan fisik.', 'category' => 'Realistic'],

            // Investigative
            ['question_text' => 'Saya suka memecahkan masalah matematika yang rumit dan logika.', 'category' => 'Investigative'],
            ['question_text' => 'Saya senang melakukan eksperimen sains atau meneliti teori-teori baru.', 'category' => 'Investigative'],
            ['question_text' => 'Saya senang menulis program komputer (coding) untuk memecahkan masalah.', 'category' => 'Investigative'],

            // Artistic
            ['question_text' => 'Saya senang menggambar, melukis, mendesain grafis, atau mengedit video.', 'category' => 'Artistic'],
            ['question_text' => 'Saya suka menulis cerita pendek, puisi, novel, atau membuat aransemen musik.', 'category' => 'Artistic'],
            ['question_text' => 'Saya menikmati bermain alat musik, bernyanyi, atau berakting di panggung.', 'category' => 'Artistic'],

            // Social
            ['question_text' => 'Saya senang membantu orang lain ketika mereka sedang menghadapi masalah pribadi.', 'category' => 'Social'],
            ['question_text' => 'Saya suka mengajar, membimbing, atau melatih orang lain tentang hal baru.', 'category' => 'Social'],
            ['question_text' => 'Saya menikmati kerja sama dalam tim dan melakukan pengabdian masyarakat.', 'category' => 'Social'],

            // Enterprising
            ['question_text' => 'Saya suka memimpin proyek kelompok atau menjadi ketua dalam organisasi.', 'category' => 'Enterprising'],
            ['question_text' => 'Saya senang merencanakan strategi penjualan, berbisnis, atau menawarkan ide baru.', 'category' => 'Enterprising'],
            ['question_text' => 'Saya suka bernegosiasi dan berbicara di depan umum untuk meyakinkan orang lain.', 'category' => 'Enterprising'],

            // Conventional
            ['question_text' => 'Saya suka menyusun dokumen, berkas, atau data secara teratur dan sistematis.', 'category' => 'Conventional'],
            ['question_text' => 'Saya senang menghitung anggaran, menganalisis laporan keuangan, atau mencatat transaksi.', 'category' => 'Conventional'],
            ['question_text' => 'Saya lebih menyukai pekerjaan dengan aturan dan prosedur yang jelas daripada yang tidak terstruktur.', 'category' => 'Conventional'],
        ];

        foreach ($questions as $q) {
            InterestTestQuestion::create([
                'interest_test_id' => $riasec->id,
                'question_text' => $q['question_text'],
                'category' => $q['category'],
                'options' => null, // null means standard Likert scale (e.g. 1 to 5)
            ]);
        }
    }
}
