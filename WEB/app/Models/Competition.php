<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Competition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'organizer',
        'registration_deadline',
        'link',
        'description',
        'poster_path',
        'is_active',
    ];

    protected $casts = [
        'registration_deadline' => 'date',
        'is_active' => 'boolean',
    ];
}
