<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;

class CompetitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $competitions = [
            [
                'title' => 'GEMASTIK - Pengembangan Perangkat Lunak',
                'category' => 'teknologi',
                'organizer' => 'Balai Pengembangan Talenta Indonesia (BPTI), Kemendikbudristek',
                'registration_deadline' => '2026-08-15',
                'link' => 'https://gemastik.kemdikbud.go.id',
                'description' => 'Kompetisi merancang dan mengembangkan perangkat lunak yang solutif, inovatif, dan berdaya guna untuk mengatasi berbagai permasalahan nasional.',
                'is_active' => true,
            ],
            [
                'title' => 'GEMASTIK - Pemrograman (Competitive Programming)',
                'category' => 'teknologi',
                'organizer' => 'BPTI, Kemendikbudristek',
                'registration_deadline' => '2026-08-15',
                'link' => 'https://gemastik.kemdikbud.go.id',
                'description' => 'Kompetisi memecahkan berbagai permasalahan algoritmik yang kompleks secara cepat dan akurat menggunakan pemrograman (C++, Java, Python).',
                'is_active' => true,
            ],
            [
                'title' => 'GEMASTIK - Desain Pengalaman Pengguna (UI/UX Design)',
                'category' => 'seni',
                'organizer' => 'BPTI, Kemendikbudristek',
                'registration_deadline' => '2026-08-15',
                'link' => 'https://gemastik.kemdikbud.go.id',
                'description' => 'Kompetisi merancang antarmuka dan pengalaman pengguna yang intuitif, estetis, dan fungsional untuk sebuah aplikasi digital.',
                'is_active' => true,
            ],
            [
                'title' => 'Lomba Robotika Nasional (BARON)',
                'category' => 'teknologi',
                'organizer' => 'Asosiasi Robotika Indonesia',
                'registration_deadline' => '2026-09-01',
                'link' => 'https://baron-robotics.or.id',
                'description' => 'Lomba rancang bangun robot otonom dan robot berbasis IoT untuk kategori mahasiswa, pelajar, dan umum.',
                'is_active' => true,
            ],
            [
                'title' => 'Olimpiade Sains Nasional (OSN) - Informatika',
                'category' => 'sains',
                'organizer' => 'Pusat Prestasi Nasional (Puspresnas)',
                'registration_deadline' => '2026-04-10',
                'link' => 'https://pusatprestasinasional.kemdikbud.go.id',
                'description' => 'Ajang kompetisi sains tingkat nasional bagi siswa SD, SMP, dan SMA sederajat di bidang Informatika/Komputer.',
                'is_active' => true,
            ],
            [
                'title' => 'Hackathon Indonesia AI Innovation',
                'category' => 'teknologi',
                'organizer' => 'Kementerian Komunikasi dan Informatika',
                'registration_deadline' => '2026-10-05',
                'link' => 'https://hackathon.kominfo.go.id',
                'description' => 'Kolaborasi maraton 48 jam untuk menciptakan prototipe aplikasi berbasis Artificial Intelligence untuk sektor kesehatan dan pendidikan.',
                'is_active' => true,
            ],
            [
                'title' => 'National Business Plan Competition (NBPC)',
                'category' => 'lainnya',
                'organizer' => 'Fakultas Ekonomi dan Bisnis Universitas Indonesia',
                'registration_deadline' => '2026-11-20',
                'link' => 'https://nbpc.feb.ui.ac.id',
                'description' => 'Kompetisi perancangan ide bisnis kreatif dan rencana aksi komersialisasi produk yang ramah lingkungan bagi mahasiswa.',
                'is_active' => true,
            ],
            [
                'title' => 'Festival dan Lomba Seni Siswa Nasional (FLS2N)',
                'category' => 'seni',
                'organizer' => 'Puspresnas, Kemendikbudristek',
                'registration_deadline' => '2026-05-30',
                'link' => 'https://fls2n.kemdikbud.go.id',
                'description' => 'Wadah kompetisi bidang seni (menyanyi, seni tari, desain poster, monolog) untuk membina talenta kreatif pelajar Indonesia.',
                'is_active' => true,
            ],
        ];

        foreach ($competitions as $comp) {
            Competition::create($comp);
        }
    }
}
