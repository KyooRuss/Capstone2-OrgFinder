<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationPhoto extends Model
{
    protected $fillable = ['organization_id', 'photo_path', 'order_index'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
