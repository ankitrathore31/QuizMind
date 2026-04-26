<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class CertificateController extends Controller
{

    private function allCertificates(Student $student): array
    {
        $now = now();

        return [
            // ── JOIN / MILESTONE ────────────────────────────────────────────────
            [
                'id'          => 'welcome_aboard',
                'title'       => 'Welcome Aboard',
                'subtitle'    => 'Certificate of Enrollment',
                'description' => 'Awarded for joining the QuizMind learning community.',
                'icon'        => '🎓',
                'color'       => '#7C5CFC',
                'color2'      => '#A78BFA',
                'category'    => 'Milestone',
                'criteria'    => 'Join QuizMind',
                'earned'      => true,   // everyone who reaches this page has joined
                'earned_at'   => $student->created_at?->format('F d, Y') ?? $now->format('F d, Y'),
                'future'      => false,
                'progress'    => 100,
                'target'      => null,
            ],

            // ── LEVEL CERTIFICATES ──────────────────────────────────────────────
            [
                'id'          => 'level_5',
                'title'       => 'Rising Scholar',
                'subtitle'    => 'Level 5 Achievement',
                'description' => 'Awarded for reaching Level 5 on QuizMind.',
                'icon'        => '⭐',
                'color'       => '#00D4FF',
                'color2'      => '#38BDF8',
                'category'    => 'Level',
                'criteria'    => 'Reach Level 5',
                'earned'      => $student->level >= 5,
                'earned_at'   => $student->level >= 5 ? $now->format('F d, Y') : null,
                'future'      => $student->level < 5,
                'progress'    => min(100, ($student->level / 5) * 100),
                'target'      => 'Level 5',
            ],
            [
                'id'          => 'level_10',
                'title'       => 'Bright Mind',
                'subtitle'    => 'Level 10 Achievement',
                'description' => 'Awarded for reaching Level 10 — you are in the top tier of learners.',
                'icon'        => '🌟',
                'color'       => '#FFB800',
                'color2'      => '#FDE68A',
                'category'    => 'Level',
                'criteria'    => 'Reach Level 10',
                'earned'      => $student->level >= 10,
                'earned_at'   => $student->level >= 10 ? $now->format('F d, Y') : null,
                'future'      => $student->level < 10,
                'progress'    => min(100, ($student->level / 10) * 100),
                'target'      => 'Level 10',
            ],
            [
                'id'          => 'level_25',
                'title'       => 'Master Scholar',
                'subtitle'    => 'Level 25 Achievement',
                'description' => 'The prestigious Master Scholar certificate — awarded to the elite few who reach Level 25.',
                'icon'        => '👑',
                'color'       => '#FF6B9D',
                'color2'      => '#F9A8D4',
                'category'    => 'Level',
                'criteria'    => 'Reach Level 25',
                'earned'      => $student->level >= 25,
                'earned_at'   => $student->level >= 25 ? $now->format('F d, Y') : null,
                'future'      => $student->level < 25,
                'progress'    => min(100, ($student->level / 25) * 100),
                'target'      => 'Level 25',
            ],

            // ── QUIZ / MCQ PERFORMANCE ──────────────────────────────────────────
            [
                'id'          => 'quiz_10',
                'title'       => 'Quiz Explorer',
                'subtitle'    => 'Certificate of Participation',
                'description' => 'Awarded for completing 10 quizzes — your curiosity is your superpower.',
                'icon'        => '📝',
                'color'       => '#00E396',
                'color2'      => '#6EE7B7',
                'category'    => 'Quiz',
                'criteria'    => 'Complete 10 Quizzes',
                'earned'      => $student->total_quizzes >= 10,
                'earned_at'   => $student->total_quizzes >= 10 ? $now->format('F d, Y') : null,
                'future'      => $student->total_quizzes < 10,
                'progress'    => min(100, ($student->total_quizzes / 10) * 100),
                'target'      => '10 Quizzes',
            ],
            [
                'id'          => 'quiz_50',
                'title'       => 'Quiz Veteran',
                'subtitle'    => 'Certificate of Dedication',
                'description' => 'Awarded for completing 50 quizzes. Your commitment to learning is extraordinary.',
                'icon'        => '🏅',
                'color'       => '#F59E0B',
                'color2'      => '#FCD34D',
                'category'    => 'Quiz',
                'criteria'    => 'Complete 50 Quizzes',
                'earned'      => $student->total_quizzes >= 50,
                'earned_at'   => $student->total_quizzes >= 50 ? $now->format('F d, Y') : null,
                'future'      => $student->total_quizzes < 50,
                'progress'    => min(100, ($student->total_quizzes / 50) * 100),
                'target'      => '50 Quizzes',
            ],
            [
                'id'          => 'perfect_score',
                'title'       => 'Perfect Mind',
                'subtitle'    => 'Certificate of Excellence',
                'description' => 'Awarded for achieving a 100% score on any quiz. Perfection achieved!',
                'icon'        => '💯',
                'color'       => '#EF4444',
                'color2'      => '#FCA5A5',
                'category'    => 'Quiz',
                'criteria'    => 'Score 100% on a Quiz',
                'earned'      => false,  // TODO: check quiz_attempts for perfect score
                'earned_at'   => null,
                'future'      => true,
                'progress'    => min(100, $student->accuracy ?? 0),
                'target'      => '100% Accuracy',
            ],
            [
                'id'          => 'high_accuracy',
                'title'       => 'Sharp Intellect',
                'subtitle'    => 'Certificate of Accuracy',
                'description' => 'Awarded for maintaining above 85% overall accuracy across all quizzes.',
                'icon'        => '🎯',
                'color'       => '#8B5CF6',
                'color2'      => '#C4B5FD',
                'category'    => 'Quiz',
                'criteria'    => 'Maintain 85%+ Accuracy',
                'earned'      => ($student->accuracy ?? 0) >= 85,
                'earned_at'   => ($student->accuracy ?? 0) >= 85 ? $now->format('F d, Y') : null,
                'future'      => ($student->accuracy ?? 0) < 85,
                'progress'    => min(100, (($student->accuracy ?? 0) / 85) * 100),
                'target'      => '85% Accuracy',
            ],

            // ── XP / EXP CERTIFICATES ──────────────────────────────────────────
            [
                'id'          => 'xp_1000',
                'title'       => 'XP Collector',
                'subtitle'    => '1,000 XP Milestone',
                'description' => 'Awarded for accumulating 1,000 XP through quizzes and battles.',
                'icon'        => '⚡',
                'color'       => '#06B6D4',
                'color2'      => '#67E8F9',
                'category'    => 'XP',
                'criteria'    => 'Earn 1,000 XP',
                'earned'      => ($student->xp ?? 0) >= 1000,
                'earned_at'   => ($student->xp ?? 0) >= 1000 ? $now->format('F d, Y') : null,
                'future'      => ($student->xp ?? 0) < 1000,
                'progress'    => min(100, (($student->xp ?? 0) / 1000) * 100),
                'target'      => '1,000 XP',
            ],
            [
                'id'          => 'xp_5000',
                'title'       => 'XP Champion',
                'subtitle'    => '5,000 XP Milestone',
                'description' => 'Awarded for the remarkable achievement of earning 5,000 total XP.',
                'icon'        => '🔋',
                'color'       => '#10B981',
                'color2'      => '#6EE7B7',
                'category'    => 'XP',
                'criteria'    => 'Earn 5,000 XP',
                'earned'      => ($student->xp ?? 0) >= 5000,
                'earned_at'   => ($student->xp ?? 0) >= 5000 ? $now->format('F d, Y') : null,
                'future'      => ($student->xp ?? 0) < 5000,
                'progress'    => min(100, (($student->xp ?? 0) / 5000) * 100),
                'target'      => '5,000 XP',
            ],
            [
                'id'          => 'xp_highest',
                'title'       => 'XP Legend',
                'subtitle'    => 'Highest XP Earner',
                'description' => 'Awarded to students who reach the top of the XP leaderboard.',
                'icon'        => '🏆',
                'color'       => '#D97706',
                'color2'      => '#FDE68A',
                'category'    => 'XP',
                'criteria'    => 'Reach Top 3 on XP Board',
                'earned'      => false,  // TODO: check leaderboard rank
                'earned_at'   => null,
                'future'      => true,
                'progress'    => min(100, (($student->xp ?? 0) / 5000) * 100),
                'target'      => 'Top 3 Leaderboard',
            ],

            // ── STREAK CERTIFICATES ─────────────────────────────────────────────
            [
                'id'          => 'streak_7',
                'title'       => 'Week Warrior',
                'subtitle'    => '7-Day Streak Certificate',
                'description' => 'Awarded for maintaining a 7-day learning streak without a single miss.',
                'icon'        => '🔥',
                'color'       => '#F97316',
                'color2'      => '#FDBA74',
                'category'    => 'Streak',
                'criteria'    => 'Maintain a 7-Day Streak',
                'earned'      => ($student->streak ?? 0) >= 7,
                'earned_at'   => ($student->streak ?? 0) >= 7 ? $now->format('F d, Y') : null,
                'future'      => ($student->streak ?? 0) < 7,
                'progress'    => min(100, (($student->streak ?? 0) / 7) * 100),
                'target'      => '7 Day Streak',
            ],
            [
                'id'          => 'streak_30',
                'title'       => 'Unstoppable',
                'subtitle'    => '30-Day Streak Certificate',
                'description' => 'The elite Unstoppable award — only the most dedicated students earn this.',
                'icon'        => '💥',
                'color'       => '#DC2626',
                'color2'      => '#FCA5A5',
                'category'    => 'Streak',
                'criteria'    => 'Maintain a 30-Day Streak',
                'earned'      => ($student->streak ?? 0) >= 30,
                'earned_at'   => ($student->streak ?? 0) >= 30 ? $now->format('F d, Y') : null,
                'future'      => ($student->streak ?? 0) < 30,
                'progress'    => min(100, (($student->streak ?? 0) / 30) * 100),
                'target'      => '30 Day Streak',
            ],

            // ── BATTLE CERTIFICATES ─────────────────────────────────────────────
            [
                'id'          => 'battle_first_win',
                'title'       => 'First Blood',
                'subtitle'    => 'Battle Victory Certificate',
                'description' => 'Awarded for winning your very first Battle on QuizMind.',
                'icon'        => '⚔️',
                'color'       => '#7C3AED',
                'color2'      => '#A78BFA',
                'category'    => 'Battle',
                'criteria'    => 'Win 1 Battle',
                'earned'      => ($student->total_battles_won ?? 0) >= 1,
                'earned_at'   => ($student->total_battles_won ?? 0) >= 1 ? $now->format('F d, Y') : null,
                'future'      => ($student->total_battles_won ?? 0) < 1,
                'progress'    => min(100, (($student->total_battles_won ?? 0) / 1) * 100),
                'target'      => '1 Battle Win',
            ],
            [
                'id'          => 'battle_10_wins',
                'title'       => 'Battle Commander',
                'subtitle'    => '10 Battle Victories',
                'description' => 'Awarded for dominating 10 battles. You are a force to be reckoned with.',
                'icon'        => '🛡️',
                'color'       => '#1D4ED8',
                'color2'      => '#93C5FD',
                'category'    => 'Battle',
                'criteria'    => 'Win 10 Battles',
                'earned'      => ($student->total_battles_won ?? 0) >= 10,
                'earned_at'   => ($student->total_battles_won ?? 0) >= 10 ? $now->format('F d, Y') : null,
                'future'      => ($student->total_battles_won ?? 0) < 10,
                'progress'    => min(100, (($student->total_battles_won ?? 0) / 10) * 100),
                'target'      => '10 Battle Wins',
            ],

            // ── ANNUAL / YEARLY ─────────────────────────────────────────────────
            [
                'id'          => 'year_1',
                'title'       => 'One Year Strong',
                'subtitle'    => 'Annual Achievement',
                'description' => 'Awarded for being an active QuizMind member for a full year.',
                'icon'        => '🗓️',
                'color'       => '#059669',
                'color2'      => '#6EE7B7',
                'category'    => 'Annual',
                'criteria'    => '1 Year on QuizMind',
                'earned'      => $student->created_at && $student->created_at->diffInYears($now) >= 1,
                'earned_at'   => ($student->created_at && $student->created_at->diffInYears($now) >= 1)
                                    ? $student->created_at->addYear()->format('F d, Y') : null,
                'future'      => !($student->created_at && $student->created_at->diffInYears($now) >= 1),
                'progress'    => $student->created_at
                                    ? min(100, ($student->created_at->diffInDays($now) / 365) * 100)
                                    : 0,
                'target'      => '365 Days Active',
            ],
            [
                'id'          => 'highest_mcq_year',
                'title'       => 'MCQ Champion of the Year',
                'subtitle'    => 'Annual Top Performer',
                'description' => 'Awarded to the student with the highest number of MCQ completions in the academic year.',
                'icon'        => '🏆',
                'color'       => '#B45309',
                'color2'      => '#FDE68A',
                'category'    => 'Annual',
                'criteria'    => 'Most MCQs in the Year',
                'earned'      => false, // TODO: compare with all students
                'earned_at'   => null,
                'future'      => true,
                'progress'    => min(100, ($student->total_quizzes / 100) * 100),
                'target'      => 'Top MCQ Solver',
            ],

            // ── MASTER ──────────────────────────────────────────────────────────
            [
                'id'          => 'master',
                'title'       => 'QuizMind Master',
                'subtitle'    => 'The Ultimate Certificate',
                'description' => 'The highest honor on QuizMind — awarded for reaching Level 25, 85%+ accuracy, 30-day streak, and 50+ quizzes.',
                'icon'        => '🌈',
                'color'       => '#7C5CFC',
                'color2'      => '#FF6B9D',
                'category'    => 'Master',
                'criteria'    => 'Level 25 + 85% Acc + 30d Streak + 50 Quizzes',
                'earned'      => $student->level >= 25
                                    && ($student->accuracy ?? 0) >= 85
                                    && ($student->streak ?? 0) >= 30
                                    && $student->total_quizzes >= 50,
                'earned_at'   => null,
                'future'      => true,
                'progress'    => round((
                                    min(100, ($student->level / 25) * 100) +
                                    min(100, (($student->accuracy ?? 0) / 85) * 100) +
                                    min(100, (($student->streak ?? 0) / 30) * 100) +
                                    min(100, ($student->total_quizzes / 50) * 100)
                                ) / 4),
                'target'      => '4 Conditions Met',
            ],
        ];
    }

    /**
     * Main certificates page.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'student') abort(403);

        $student      = $user->getOrCreateStudent();
        $certificates = $this->allCertificates($student);

        $earned  = array_filter($certificates, fn($c) => $c['earned']);
        $future  = array_filter($certificates, fn($c) => !$c['earned']);
        $categories = array_unique(array_column($certificates, 'category'));

        return view('student.profile.certificate', compact(
            'user', 'student', 'certificates', 'earned', 'future', 'categories'
        ));
    }

    /**
     * Single certificate view (for print / share).
     */
    public function show(string $id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'student') abort(403);

        $student      = $user->getOrCreateStudent();
        $certificates = $this->allCertificates($student);

        $cert = collect($certificates)->firstWhere('id', $id);

        if (!$cert || !$cert['earned']) {
            return redirect()->route('student.certificates')
                ->with('error', 'Certificate not yet earned.');
        }

        return view('student.certificate-print', compact('user', 'student', 'cert'));
    }
}