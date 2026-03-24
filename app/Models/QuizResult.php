<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    use HasFactory;

    protected $table = 'quiz_results';

    protected $fillable = [
        'user_id',
        'quiz_id',
        'type',         // 'solo' | '1v1' | 'group' | 'team'
        'score',
        'total_q',
        'accuracy',
        'xp_earned',
        'subject',
        'topic',
        'difficulty',
        'time_taken',   // seconds
        'answer_log',   // JSON [{idx, correct}]
    ];

    protected $casts = [
        'answer_log' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}