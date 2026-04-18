<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'college',
        'ref_code',
    ];

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    // Check if student profile is set up
    public function hasStudentProfile()
    {
        return optional($this->student)->is_profile_complete;
    }

    public function getOrCreateStudent()
    {
        return $this->student ?? $this->student()->create([
            'level'            => 1,
            'xp'               => 0,
            'streak'           => 0,
            'total_quizzes'    => 0,
            'total_correct'    => 0,
            'total_wrong'      => 0,
            'total_battles_won'  => 0,
            'total_battles_lost' => 0,
            'badges'           => [],
            'subjects_interest' => [],
            'is_profile_complete' => false,
        ]);
    }
}
