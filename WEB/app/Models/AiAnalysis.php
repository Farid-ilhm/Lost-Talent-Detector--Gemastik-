<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiAnalysis extends Model
{
    use HasFactory;

    protected $table = 'ai_analyses';

    protected $fillable = [
        'student_id',
        'primary_talent',
        'analisis_mendalam',
        'confidence_score',
        'supporting_talents',
        'reasoning',
        'career_recommendations',
        'extracurricular_recommendations',
        'competition_recommendations',
        'development_targets',
        'model_version',
        'analyzed_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'supporting_talents' => 'array',
        'reasoning' => 'array',
        'career_recommendations' => 'array',
        'extracurricular_recommendations' => 'array',
        'competition_recommendations' => 'array',
        'development_targets' => 'array',
        'analyzed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
