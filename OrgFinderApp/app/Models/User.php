<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'role',  'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminOfficer(): bool
    {
        return $this->role === 'admin_officer';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
    public function profile()
    {
    return $this->hasOne(UserProfile::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function organizationAccess()
    {
        return $this->hasMany(OrganizationAccess::class);
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_access')->withPivot('position');
    }
}
