<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'npsn',
        'type',
        'address',
        'phone',
        'website',
        'is_verified',
        'logo',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->user ? $this->user->name : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function majors()
    {
        return $this->hasMany(Major::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
