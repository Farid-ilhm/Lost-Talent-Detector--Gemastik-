<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterestTestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'interest_test_id',
        'question_text',
        'category',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function interestTest()
    {
        return $this->belongsTo(InterestTest::class);
    }

    public function answers()
    {
        return $this->hasMany(InterestTestAnswer::class);
    }
}
