<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterestTestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'interest_test_question_id',
        'answer_value',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function question()
    {
        return $this->belongsTo(InterestTestQuestion::class, 'interest_test_question_id');
    }
}
