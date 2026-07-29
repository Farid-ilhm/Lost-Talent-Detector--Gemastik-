<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'institution_id',
        'classroom_id',
        'nisn',
        'nim',
        'semester',
        'birth_date',
        'gender',
        'hobbies',
        'interests',
        'personality',
        'parent_user_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hobbies' => 'array',
        'interests' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function academicGrades()
    {
        return $this->hasMany(AcademicGrade::class);
    }

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }

    public function extracurriculars()
    {
        return $this->hasMany(Extracurricular::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function teacherNotes()
    {
        return $this->hasMany(TeacherNote::class);
    }

    public function interestTestAnswers()
    {
        return $this->hasMany(InterestTestAnswer::class);
    }

    public function interestTestResults()
    {
        return $this->hasMany(InterestTestResult::class);
    }

    public function aiAnalyses()
    {
        return $this->hasMany(AiAnalysis::class);
    }
}
