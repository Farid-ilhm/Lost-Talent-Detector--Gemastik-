<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Extracurricular extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'score',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
