<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('interest_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interest_test_id')->constrained('interest_tests')->onDelete('cascade');
            $table->text('question_text');
            $table->string('category'); // RIASEC categories, e.g. Realistic, Investigative, etc.
            $table->json('options')->nullable(); // Optional: if it's multiple choice. Default is 1-5 Likert scale.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_test_questions');
    }
};
