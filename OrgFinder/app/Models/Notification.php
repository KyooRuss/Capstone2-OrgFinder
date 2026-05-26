<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'type',
        'title',
        'body',
        'data',
        'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Helper: create a notification row
    public static function send(int $userId, string $type, string $title, string $body, ?int $orgId = null, ?array $data = null): self
    {
        return self::create([
            'user_id'         => $userId,
            'organization_id' => $orgId,
            'type'            => $type,
            'title'           => $title,
            'body'            => $body,
            'data'            => $data,
            'is_read'         => false,
        ]);
    }
}
