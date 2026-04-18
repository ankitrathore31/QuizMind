<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'code',
        'principal_name',
        'address',
        'email',
        'phone',
        'city',
        'state',
        'type',
        'is_active',
        'profile_complete',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────

    // The admin user who owns/manages this institution
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // All users who typed this institution's code in their ref_code field
    // users.ref_code = institutions.code
    public function affiliatedUsers()
    {
        return User::where('ref_code', $this->code)
            ->where('role', 'student')
            ->with('student');
    }

    // ── Computed helpers ──────────────────────────────────────────

    // Get all students (Student model) who belong to this institution
    public function getStudents()
    {
        return Student::whereHas('user', function ($q) {
            $q->where('ref_code', $this->code)
              ->where('role', 'student');
        })->with('user')->get();
    }

    // Total count of affiliated students
    public function getTotalStudentsAttribute(): int
    {
        return User::where('ref_code', $this->code)
            ->where('role', 'student')
            ->count();
    }

    // Average accuracy across all affiliated students
    public function getAvgAccuracyAttribute(): float
    {
        $students = $this->getStudents();
        if ($students->isEmpty()) return 0;

        $totalCorrect = $students->sum('total_correct');
        $totalWrong   = $students->sum('total_wrong');
        $total        = $totalCorrect + $totalWrong;

        return $total > 0 ? round(($totalCorrect / $total) * 100, 1) : 0;
    }

    // ── Code generation ───────────────────────────────────────────

    public static function generateCode(): string
    {
        do {
            $code = 'INST-' . strtoupper(Str::random(5));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}