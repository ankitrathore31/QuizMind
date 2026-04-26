<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InstitutionBattle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'institution_battles';

    protected $fillable = [
        'institution_id',
        'created_by',
        'quiz_id',
        'code',
        'status',
        'battle_type',
        'participating_institutions',
        'students_per_institution',
        'question_timer',
        'total_questions',
        'anti_cheat',
        'show_leaderboard',
        'countdown_starts_at',
        'started_at',
        'finished_at',
        'final_scores',
        'institution_rankings',
        'top_students',
        'student_codes',
    ];

    protected $casts = [
        'participating_institutions' => 'array',
        'final_scores'               => 'array',
        'institution_rankings'       => 'array',
        'top_students'               => 'array',
        'student_codes'              => 'array',
        'anti_cheat'                 => 'boolean',
        'show_leaderboard'           => 'boolean',
        'countdown_starts_at'        => 'datetime',
        'started_at'                 => 'datetime',
        'finished_at'                => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function participants()
    {
        return $this->hasMany(InstitutionBattleParticipant::class, 'battle_id');
    }

    public function answers()
    {
        return $this->hasMany(InstitutionBattleQuestionAnswer::class, 'battle_id');
    }

    public function history()
    {
        return $this->hasMany(InstitutionBattleHistory::class, 'battle_id');
    }

    // ── Code Generation ────────────────────────────────────────────────

    /**
     * Generate a unique 8-char master battle code (e.g. JICR9XHV).
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }


    public static function institutionSubSuffixes(): array
    {
        return ['S2X9', 'S3Y8'];   // index 0 = school 2, index 1 = school 3
    }

    /**
     * Return the institution invite code for the given school slot (1-based school number).
     * School 1 is the host — returns null.
     */
    public function institutionCodeForSlot(int $schoolNumber): ?string
    {
        $suffixes = self::institutionSubSuffixes();
        $idx      = $schoolNumber - 2;   // school 2 → idx 0, school 3 → idx 1

        if ($idx < 0 || $idx >= count($suffixes)) {
            return null;
        }

        return $this->code . '-' . $suffixes[$idx];
    }

    /**
     * Given a full code string (master or sub-code), return the master code.
     *
     * Master code is always exactly 8 alphanumeric uppercase chars.
     * Sub-codes are MASTERCODE-SUFFIX (e.g. JICR9XHV-S2X9).
     */
    public static function resolveMasterCode(string $raw): string
    {
        $raw = strtoupper(trim($raw));

        // If it contains a dash after position 8, take only the first 8 chars
        if (strlen($raw) > 8 && $raw[8] === '-') {
            return substr($raw, 0, 8);
        }

        return $raw;
    }

    /**
     * Given a full code, determine which school slot (1-based) it belongs to.
     * Returns 1 for master code (host), 2 for first sub-code, 3 for second, etc.
     * Returns null if the code doesn't match any known slot.
     */
    public function resolveSchoolSlot(string $raw): ?int
    {
        $raw = strtoupper(trim($raw));

        // Plain master code → host (slot 1)
        if ($raw === $this->code) {
            return 1;
        }

        foreach (self::institutionSubSuffixes() as $idx => $suffix) {
            if ($raw === $this->code . '-' . $suffix) {
                return $idx + 2;  // slot 2, 3, …
            }
        }

        return null;
    }

    /**
     * Return the student join code for a given school slot (1-based).
     * Format: MASTERCODE-STU1, MASTERCODE-STU2, MASTERCODE-STU3
     */
    public function studentCodeForSlot(int $schoolNumber): string
    {
        return $this->code . '-STU' . $schoolNumber;
    }

    /**
     * Resolve a student join code back to a school slot.
     * Returns null if it doesn't match any student code.
     */
    public function resolveStudentSlot(string $raw): ?int
    {
        $raw   = strtoupper(trim($raw));
        $total = $this->battle_type === '3school' ? 3 : 2;

        for ($s = 1; $s <= $total; $s++) {
            if ($raw === $this->studentCodeForSlot($s)) {
                return $s;
            }
        }

        return null;
    }

    // ── Accessors ──────────────────────────────────────────────────────

    public function getInviteUrlAttribute(): string
    {
        return route('institution.battle.join', $this->code);
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['setup', 'registration', 'countdown', 'in_progress']);
    }

    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }

    // ── Helpers ────────────────────────────────────────────────────────

    public function hasParticipant(int $userId): bool
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }

    public function getParticipantCount(): int
    {
        return $this->participants()->where('disqualified', false)->count();
    }

    public function getInstitutionParticipants(int $instId)
    {
        return $this->participants()
            ->where('institution_id', $instId)
            ->with('user')
            ->get();
    }

    public function getInstitutionScore(int $instId): int
    {
        return $this->participants()
            ->where('institution_id', $instId)
            ->where('disqualified', false)
            ->sum('score');
    }

    public function getInstitutionStats(int $instId): array
    {
        $participants = $this->getInstitutionParticipants($instId);
        $total        = max(1, $this->total_questions ?? 1);
        $count        = max(1, $participants->count());

        return [
            'institution_id'   => $instId,
            'total_students'   => $participants->count(),
            'total_score'      => $participants->sum('score'),
            'average_score'    => round($participants->avg('score') ?? 0, 1),
            'total_correct'    => $participants->sum('correct'),
            'total_wrong'      => $participants->sum('wrong'),
            'average_accuracy' => (int) round(($participants->sum('correct') / ($total * $count)) * 100),
            'top_student'      => $participants->sortByDesc('score')->first(),
        ];
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    public function canStart(): bool
    {
        return in_array($this->status, ['setup', 'registration'])
            && $this->participants()->count() >= 2;
    }

    public function getLeaderboard(int $limit = 50)
    {
        return $this->participants()
            ->with('user', 'institution')
            ->where('disqualified', false)
            ->orderByDesc('score')
            ->limit($limit)
            ->get();
    }

    public function getInstitutionRankings(): array
    {
        if ($this->institution_rankings) {
            return $this->institution_rankings;
        }

        return collect($this->participating_institutions ?? [])
            ->map(fn($instId) => [
                'institution_id'   => $instId,
                'institution_name' => Institution::find($instId)?->name ?? 'Unknown',
                ...$this->getInstitutionStats($instId),
            ])
            ->sortByDesc('total_score')
            ->values()
            ->all();
    }
}