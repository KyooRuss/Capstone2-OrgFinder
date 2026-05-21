<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationAccess extends Model
{
    protected $table = 'organization_access';

    protected $fillable = ['organization_id', 'user_id', 'position'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
