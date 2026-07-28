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
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('primary_talent');
            $table->decimal('confidence_score', 5, 2); // e.g. 96.00 representing 96%
            $table->json('supporting_talents'); // other talents: e.g. [{"talent": "Programming", "confidence": 90.00}]
            $table->json('reasoning'); // list of reasons/bullet points
            $table->json('career_recommendations')->nullable();
            $table->json('extracurricular_recommendations')->nullable();
            $table->json('competition_recommendations')->nullable();
            $table->json('development_targets')->nullable();
            $table->string('model_version')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
