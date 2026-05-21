<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        'year_level',
        'program',
        'interest',
        'skill_to_improve',
        'preferred_activity',
        'profile_completed',
        'profile_photo',
    ];

    protected $casts = [
        'interest' => 'array',
        'skill_to_improve' => 'array',
        'preferred_activity' => 'array',
        'profile_completed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}