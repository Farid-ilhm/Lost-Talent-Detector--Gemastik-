<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add nim and semester to students table if not exists
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'nim')) {
                $table->string('nim')->unique()->nullable()->after('nisn');
            }
            if (!Schema::hasColumn('students', 'semester')) {
                $table->integer('semester')->nullable()->after('nim');
            }
        });

        // Update users role ENUM to include 'mahasiswa'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'institusi', 'guru', 'orang_tua', 'siswa', 'mahasiswa', 'umum') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'nim')) {
                $table->dropColumn('nim');
            }
            if (Schema::hasColumn('students', 'semester')) {
                $table->dropColumn('semester');
            }
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'institusi', 'guru', 'orang_tua', 'siswa', 'umum') NOT NULL");
    }
};
