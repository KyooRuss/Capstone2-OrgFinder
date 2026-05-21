<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'org_name', 'category', 'president', 'vision', 'mission',
        'room_number', 'contact_telegram', 'contact_facebook', 'logo',
    ];

    public function photos()
    {
        return $this->hasMany(OrganizationPhoto::class)->orderBy('order_index');
    }

    public function reasons()
    {
        return $this->hasMany(OrganizationReason::class)->orderBy('order_index');
    }

    public function testimonials()
    {
        return $this->hasMany(OrganizationTestimonial::class)->orderBy('order_index');
    }

    public function accessUsers()
    {
        return $this->hasMany(OrganizationAccess::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'organization_access')->withPivot('position');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function getMembersCountAttribute(): int
    {
        return $this->accessUsers()->count();
    }

    public function getEventsCountAttribute(): int
    {
        return $this->events()->count();
    }
}
