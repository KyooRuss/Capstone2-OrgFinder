<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationReason extends Model
{
    protected $fillable = ['organization_id', 'reason', 'order_index'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
