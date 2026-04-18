<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorChat extends Model
{
    protected $fillable = ['user_id', 'subject', 'title', 'messages'];

    protected $casts = [
        'messages' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMessageCountAttribute(): int
    {
        return intdiv(count($this->messages ?? []), 2);
    }

    // ✅ FIX: Always store messages as safe strings
    public function setMessagesAttribute($value)
    {
        $this->attributes['messages'] = json_encode(
            collect($value)->map(function ($msg) {

                $content = $msg['content'] ?? '';

                if (!is_string($content)) {
                    $content = json_encode($content, JSON_UNESCAPED_UNICODE);
                }

                return [
                    'role' => $msg['role'] ?? 'user',
                    'content' => $content,
                ];
            })->toArray()
        );
    }

    // ✅ FIX: Always return clean strings
    public function getMessagesAttribute($value)
    {
        $messages = json_decode($value, true) ?? [];

        return collect($messages)->map(function ($msg) {

            $content = $msg['content'] ?? '';

            if (!is_string($content)) {
                $content = json_encode($content, JSON_UNESCAPED_UNICODE);
            }

            return [
                'role' => $msg['role'] ?? 'user',
                'content' => $content,
            ];
        })->toArray();
    }
}