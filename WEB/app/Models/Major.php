<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Major extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'name',
        'code',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class);
    }
}
