<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventBenefit extends Model
{
    protected $fillable = ['event_id', 'benefit', 'order_index'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
