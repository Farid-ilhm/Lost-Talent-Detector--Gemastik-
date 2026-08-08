SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `academic_grades`;
CREATE TABLE `academic_grades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `semester` int NOT NULL,
  `subject_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_grades_student_id_foreign` (`student_id`),
  KEY `academic_grades_created_by_foreign` (`created_by`),
  CONSTRAINT `academic_grades_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `academic_grades_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `academic_years`;
CREATE TABLE `academic_years` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `academic_years_institution_id_foreign` (`institution_id`),
  CONSTRAINT `academic_years_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `achievements`;
CREATE TABLE `achievements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('akademik','olahraga','seni','sains','teknologi','keagamaan','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` enum('sekolah','kecamatan','kabupaten','provinsi','nasional','internasional') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rank` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificate_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `achievements_student_id_foreign` (`student_id`),
  KEY `achievements_verified_by_foreign` (`verified_by`),
  CONSTRAINT `achievements_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `achievements_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `ai_analyses`;
CREATE TABLE `ai_analyses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `primary_talent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `analisis_mendalam` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `confidence_score` decimal(5,2) NOT NULL,
  `supporting_talents` json NOT NULL,
  `reasoning` json NOT NULL,
  `career_recommendations` json DEFAULT NULL,
  `extracurricular_recommendations` json DEFAULT NULL,
  `competition_recommendations` json DEFAULT NULL,
  `development_targets` json DEFAULT NULL,
  `model_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `analyzed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_analyses_student_id_foreign` (`student_id`),
  CONSTRAINT `ai_analyses_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `academic_year_id` bigint unsigned DEFAULT NULL,
  `semester` int DEFAULT NULL,
  `present` int NOT NULL DEFAULT '0',
  `sick` int NOT NULL DEFAULT '0',
  `permit` int NOT NULL DEFAULT '0',
  `alpha` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_student_id_foreign` (`student_id`),
  KEY `attendances_academic_year_id_foreign` (`academic_year_id`),
  CONSTRAINT `attendances_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `classrooms`;
CREATE TABLE `classrooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `major_id` bigint unsigned DEFAULT NULL,
  `academic_year_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classrooms_institution_id_foreign` (`institution_id`),
  KEY `classrooms_major_id_foreign` (`major_id`),
  KEY `classrooms_academic_year_id_foreign` (`academic_year_id`),
  CONSTRAINT `classrooms_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classrooms_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `classrooms_major_id_foreign` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `competitions`;
CREATE TABLE `competitions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `organizer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_deadline` date DEFAULT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `poster_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `competitions` (`id`, `title`, `category`, `organizer`, `registration_deadline`, `link`, `description`, `poster_path`, `is_active`, `created_at`, `updated_at`) VALUES ('9', 'GEMASTIK XV 2026', 'teknologi', 'Puspresnas', '2026-08-18', 'https://kemdiktisaintek.go.id/announcement/article/penawaran-gemastik-2026', 'GEMASTIK XV (2022) adalah Pagelaran Mahasiswa Nasional Bidang Teknologi Informasi dan Komunikasi ke-15 yang mengusung tema “TIK untuk Indonesia Pulih Lebih Cepat Bangkit Lebih Kuat”. Babak final ajang ini sukses diselenggarakan secara luring di Fakultas Ilmu Komputer Universitas Brawijaya (FILKOM UB), dengan Institut Teknologi Sepuluh Nopember (ITS) keluar sebagai juara umum', 'competitions/pn4R1lUMURk0vHcfmeQ77pWSW6ZhqiXcxI2iyFkg.jpg', '1', '2026-08-06 04:16:29', '2026-08-06 09:51:23');
INSERT INTO `competitions` (`id`, `title`, `category`, `organizer`, `registration_deadline`, `link`, `description`, `poster_path`, `is_active`, `created_at`, `updated_at`) VALUES ('10', 'Gemastik', 'teknologi', 'Binus', '2026-08-19', 'https://kemdiktisaintek.go.id/announcement/article/penawaran-gemastik-2026', 'Lorem Ipsum DOlor sit amet', 'competitions/cTzVumGYGJ7AaERdLLjHhCZ6SDiWGJx2VnlqHr1T.jpg', '1', '2026-08-06 10:00:55', '2026-08-06 10:00:55');
INSERT INTO `competitions` (`id`, `title`, `category`, `organizer`, `registration_deadline`, `link`, `description`, `poster_path`, `is_active`, `created_at`, `updated_at`) VALUES ('11', 'Gemastik', 'sains', 'Binus', '2026-08-20', 'https://kemdiktisaintek.go.id/announcement/article/penawaran-gemastik-2026', 'Lorem Ipsum Dolor Sit Amet', 'competitions/Q6jJ2mrJ4kuZCWpWTKd6iMUvfugrZNVyagZA0wMV.jpg', '1', '2026-08-06 10:02:40', '2026-08-06 10:02:40');

DROP TABLE IF EXISTS `custom_notifications`;
CREATE TABLE `custom_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `custom_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `extracurriculars`;
CREATE TABLE `extracurriculars` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `extracurriculars_student_id_foreign` (`student_id`),
  CONSTRAINT `extracurriculars_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `institution_announcements`;
CREATE TABLE `institution_announcements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('pengumuman','beasiswa','pelatihan','lomba','kegiatan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pengumuman',
  `target_talent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Semua',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `banner_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '1',
  `expired_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institution_announcements_institution_id_foreign` (`institution_id`),
  CONSTRAINT `institution_announcements_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `institutions`;
CREATE TABLE `institutions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `npsn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('sekolah','universitas','lembaga_kursus','komunitas') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `institutions_npsn_unique` (`npsn`),
  KEY `institutions_user_id_foreign` (`user_id`),
  CONSTRAINT `institutions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `interest_test_answers`;
CREATE TABLE `interest_test_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `interest_test_question_id` bigint unsigned NOT NULL,
  `answer_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interest_test_answers_student_id_foreign` (`student_id`),
  KEY `interest_test_answers_interest_test_question_id_foreign` (`interest_test_question_id`),
  CONSTRAINT `interest_test_answers_interest_test_question_id_foreign` FOREIGN KEY (`interest_test_question_id`) REFERENCES `interest_test_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interest_test_answers_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `interest_test_questions`;
CREATE TABLE `interest_test_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `interest_test_id` bigint unsigned NOT NULL,
  `question_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interest_test_questions_interest_test_id_foreign` (`interest_test_id`),
  CONSTRAINT `interest_test_questions_interest_test_id_foreign` FOREIGN KEY (`interest_test_id`) REFERENCES `interest_tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('19', '1', 'Saya suka memperbaiki barang-barang mekanik atau instalasi listrik.', 'Realistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('20', '1', 'Saya suka merakit robot, perangkat keras komputer, atau melakukan pertukangan kayu.', 'Realistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('21', '1', 'Saya senang beraktivitas di luar ruangan dan melakukan pekerjaan fisik.', 'Realistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('22', '1', 'Saya tertarik merawat mesin, motor, atau peralatan teknik otomotif.', 'Realistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('23', '1', 'Saya suka bekerja dengan alat pertukangan, perkakas, atau bahan konstruksi.', 'Realistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('24', '1', 'Saya lebih memilih aktivitas operasional praktis dibanding hanya membaca teori.', 'Realistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('25', '1', 'Saya suka memecahkan masalah matematika yang rumit dan logika.', 'Investigative', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('26', '1', 'Saya senang melakukan eksperimen sains atau meneliti teori-teori baru.', 'Investigative', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('27', '1', 'Saya senang menulis program komputer (coding) untuk memecahkan masalah.', 'Investigative', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('28', '1', 'Saya suka mengamati fenomena alam, analisis data, dan mencari tahu cara kerja suatu sistem.', 'Investigative', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('29', '1', 'Saya menikmati kegiatan membaca jurnal ilmiah atau artikel riset mendalam.', 'Investigative', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('30', '1', 'Saya merasa tertantang saat harus memecahkan teka-teki logika atau algoritma rumit.', 'Investigative', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('31', '1', 'Saya senang menggambar, melukis, mendesain grafis, atau mengedit video.', 'Artistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('32', '1', 'Saya suka menulis cerita pendek, puisi, novel, atau membuat aransemen musik.', 'Artistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('33', '1', 'Saya menikmati bermain alat musik, bernyanyi, atau berakting di panggung.', 'Artistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('34', '1', 'Saya suka mengekspresikan ide dan perasaan melalui karya seni, fotografi, atau animasi.', 'Artistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('35', '1', 'Saya tertarik dengan desain interior, tata busana, atau seni visual.', 'Artistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('36', '1', 'Saya lebih senang bekerja secara bebas dan kreatif tanpa terikat aturan yang kaku.', 'Artistic', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('37', '1', 'Saya senang membantu orang lain ketika mereka sedang menghadapi masalah pribadi.', 'Social', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('38', '1', 'Saya suka mengajar, membimbing, atau melatih orang lain tentang hal baru.', 'Social', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('39', '1', 'Saya menikmati kerja sama dalam tim dan melakukan pengabdian masyarakat.', 'Social', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('40', '1', 'Saya merasa puas ketika bisa menjadi konselor atau pendengar yang baik bagi teman.', 'Social', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('41', '1', 'Saya suka berpartisipasi dalam kegiatan sukarelawan atau kegiatan kemanusiaan.', 'Social', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('42', '1', 'Saya senang menyambut orang baru dan menciptakan suasana ramah dalam lingkungan sosial.', 'Social', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('43', '1', 'Saya suka memimpin proyek kelompok atau menjadi ketua dalam organisasi.', 'Enterprising', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('44', '1', 'Saya senang merencanakan strategi penjualan, berbisnis, atau menawarkan ide baru.', 'Enterprising', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('45', '1', 'Saya suka bernegosiasi dan berbicara di depan umum untuk meyakinkan orang lain.', 'Enterprising', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('46', '1', 'Saya tertarik memulai usaha mandiri atau menjalankan proyek kewirausahaan.', 'Enterprising', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('47', '1', 'Saya menikmati tantangan mengambil keputusan penting dan mengarahkan tim.', 'Enterprising', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('48', '1', 'Saya senang melakukan presentasi pitch untuk mempromosikan produk atau ide baru.', 'Enterprising', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('49', '1', 'Saya suka menyusun dokumen, berkas, atau data secara teratur dan sistematis.', 'Conventional', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('50', '1', 'Saya senang menghitung anggaran, menganalisis laporan keuangan, atau mencatat transaksi.', 'Conventional', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('51', '1', 'Saya lebih menyukai pekerjaan dengan aturan dan prosedur yang jelas daripada yang tidak terstruktur.', 'Conventional', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('52', '1', 'Saya senang mengorganisir jadwal, membuat checklist kerja, dan mengelola database.', 'Conventional', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('53', '1', 'Saya teliti dalam memeriksa ketepatan rincian data dan angka agar tidak ada kesalahan.', 'Conventional', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');
INSERT INTO `interest_test_questions` (`id`, `interest_test_id`, `question_text`, `category`, `options`, `created_at`, `updated_at`) VALUES ('54', '1', 'Saya nyaman bekerja dalam struktur administrasi yang tertata rapi dan memiliki pedoman baku.', 'Conventional', NULL, '2026-08-02 04:17:13', '2026-08-02 04:17:13');

DROP TABLE IF EXISTS `interest_test_results`;
CREATE TABLE `interest_test_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `interest_test_id` bigint unsigned NOT NULL,
  `scores` json NOT NULL,
  `dominant_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `interest_test_results_student_id_foreign` (`student_id`),
  KEY `interest_test_results_interest_test_id_foreign` (`interest_test_id`),
  CONSTRAINT `interest_test_results_interest_test_id_foreign` FOREIGN KEY (`interest_test_id`) REFERENCES `interest_tests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interest_test_results_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `interest_tests`;
CREATE TABLE `interest_tests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `interest_tests` (`id`, `title`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'Tes Minat Bakat RIASEC', 'Tes minat bakat berbasis RIASEC (Realistic, Investigative, Artistic, Social, Enterprising, Conventional) untuk mengidentifikasi kecenderungan minat karir dan bakat Anda.', '1', '2026-07-29 06:35:37', '2026-07-29 06:35:37');

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `majors`;
CREATE TABLE `majors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `institution_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `majors_institution_id_foreign` (`institution_id`),
  CONSTRAINT `majors_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2026_07_28_000001_create_institutions_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2026_07_28_000002_create_majors_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2026_07_28_000003_create_academic_years_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2026_07_28_000004_create_classrooms_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2026_07_28_000005_create_teachers_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2026_07_28_000006_create_students_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2026_07_28_000007_create_academic_grades_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2026_07_28_000008_create_achievements_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2026_07_28_000009_create_organizations_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2026_07_28_000010_create_extracurriculars_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2026_07_28_000011_create_attendances_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2026_07_28_000012_create_teacher_notes_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2026_07_28_000013_create_interest_tests_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('17', '2026_07_28_000014_create_interest_test_questions_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('18', '2026_07_28_000015_create_interest_test_answers_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('19', '2026_07_28_000016_create_interest_test_results_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('20', '2026_07_28_000017_create_ai_analyses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('21', '2026_07_28_000018_create_competitions_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('22', '2026_07_28_000019_create_custom_notifications_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('23', '2026_07_28_043251_create_personal_access_tokens_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('24', '2026_07_30_000001_update_users_and_students_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('25', '2026_08_03_120949_add_analisis_mendalam_to_ai_analyses_table', '2');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('26', '2026_08_05_000001_create_institution_announcements_table', '3');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('27', '2026_08_06_000001_add_expired_at_to_institution_announcements_table', '4');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('28', '2026_08_06_041104_add_expired_at_to_institution_announcements_table', '4');

DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organizations_student_id_foreign` (`student_id`),
  CONSTRAINT `organizations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned DEFAULT NULL,
  `classroom_id` bigint unsigned DEFAULT NULL,
  `nisn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nim` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semester` int DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hobbies` json DEFAULT NULL,
  `interests` json DEFAULT NULL,
  `personality` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_nisn_unique` (`nisn`),
  UNIQUE KEY `students_nim_unique` (`nim`),
  KEY `students_user_id_foreign` (`user_id`),
  KEY `students_institution_id_foreign` (`institution_id`),
  KEY `students_classroom_id_foreign` (`classroom_id`),
  KEY `students_parent_user_id_foreign` (`parent_user_id`),
  CONSTRAINT `students_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `students_parent_user_id_foreign` FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `teacher_notes`;
CREATE TABLE `teacher_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint unsigned NOT NULL,
  `teacher_id` bigint unsigned NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `teacher_notes_student_id_foreign` (`student_id`),
  KEY `teacher_notes_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `teacher_notes_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_notes_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `teachers`;
CREATE TABLE `teachers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `institution_id` bigint unsigned NOT NULL,
  `nip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teachers_nip_unique` (`nip`),
  KEY `teachers_user_id_foreign` (`user_id`),
  KEY `teachers_institution_id_foreign` (`institution_id`),
  CONSTRAINT `teachers_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','institusi','guru','orang_tua','siswa','mahasiswa','umum') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `avatar`, `otp_code`, `otp_expires_at`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', 'Super Administrator', 'domiini1c.id@gmail.com', NULL, '$2y$12$.tauhG4CNCrOmOCcYRaOdedT340flefOV1kXNp/iGc2h.2YbxFzyu', 'admin', '081234567890', NULL, NULL, NULL, 'active', NULL, '2026-07-29 06:35:38', '2026-08-06 05:29:33');

SET FOREIGN_KEY_CHECKS=1;
