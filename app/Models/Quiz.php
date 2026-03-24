<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'subject',
        'topic',
        'class',
        'difficulty',
        'source',       // 'topic' | 'pdf' | 'image' | 'manual' | 'standard'
        'questions',    // JSON array
        'total_questions',
        'is_public',
        'play_count',
    ];

    protected $casts = [
        'questions'  => 'array',
        'is_public'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }

    // Scope: quizzes belonging to current user
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Question count accessor
    public function getTotalQuestionsAttribute()
    {
        return is_array($this->questions) ? count($this->questions) : 0;
    }

    // Source label
    public function getSourceLabelAttribute()
    {
        return match($this->source) {
            'topic'    => '🤖 AI Topic',
            'pdf'      => '📄 PDF',
            'image'    => '🖼️ Image',
            'manual'   => '✍️ Manual',
            'standard' => '📚 Standard',
            default    => 'AI',
        };
    }
}