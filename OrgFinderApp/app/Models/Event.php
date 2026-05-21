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
        'time', 'venue', 'poster', 'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function gains()
    {
        return $this->hasMany(EventBenefit::class)->orderBy('order_index');
    }
}
