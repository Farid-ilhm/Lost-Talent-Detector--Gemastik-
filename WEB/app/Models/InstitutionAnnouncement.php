<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'title',
        'category',
        'target_talent',
        'content',
        'banner_image',
        'external_link',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
