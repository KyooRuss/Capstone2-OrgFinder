<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id', 'title', 'description', 'date',
        'time', 'venue', 'event_poster', 'status', 'target_departments',
    ];

    protected $casts = [
        'date'               => 'date',
        'target_departments' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function benefits()
    {
        return $this->hasMany(EventBenefit::class)->orderBy('order_index');
    }
}
