<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterestTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'interest_test_id',
        'scores',
        'dominant_category',
    ];

    protected $casts = [
        'scores' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function interestTest()
    {
        return $this->belongsTo(InterestTest::class);
    }
}
