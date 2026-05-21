<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgChatMessage extends Model
{
    protected $fillable = ['org_id', 'user_id', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
