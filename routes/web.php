<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AIQuizController;
use App\Http\Controllers\MyQuizHistoryController;
use App\Http\Controllers\BattleController;
use App\Http\Controllers\InstitutionBattleController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\InstitutionCodeController;
use App\Http\Controllers\InstitutionQuizController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/student/dashboard',  [StudentController::class, 'dashboard'])->middleware('auth')->name('student.dashboard');
Route::post('/profile/setup', [StudentController::class, 'saveProfile'])->name('student.profile.save');
Route::get('/stats', [StudentController::class, 'stats'])->name('student.stats');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/auth/google',          [AuthenticatedSessionController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthenticatedSessionController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::prefix('student/quiz')->middleware(['auth'])->name('student.quiz.')->group(function () {

    // ── Main page ──────────────────────────────────────────────────────────
    Route::get('/',                    [AIQuizController::class, 'index'])->name('index');

    // ── Tutor Chat (MUST be before /{quiz} wildcard) ───────────────────────
    Route::get('/tutor',               [AIQuizController::class, 'tutorIndex'])->name('tutor.index');
    Route::post('/tutor/chat',         [AIQuizController::class, 'tutorChat'])->name('tutor.chat');
    Route::post('/tutor/new',          [AIQuizController::class, 'tutorNewSession'])->name('tutor.new');
    Route::get('/tutor/sessions',      [AIQuizController::class, 'tutorSessions'])->name('tutor.sessions');
    Route::get('/tutor/session/{id}',  [AIQuizController::class, 'tutorSession'])->name('tutor.session');
    Route::delete('/tutor/session/{id}', [AIQuizController::class, 'tutorDeleteSession'])->name('tutor.session.delete');

    // ── Generators ────────────────────────────────────────────────────────
    Route::post('/generate/topic',     [AIQuizController::class, 'generateTopic'])->name('generate.topic');
    Route::post('/generate/pdf',       [AIQuizController::class, 'generatePdf'])->name('generate.pdf');
    Route::post('/generate/image',     [AIQuizController::class, 'generateImage'])->name('generate.image');
    Route::post('/generate/standard',  [AIQuizController::class, 'generateStandard'])->name('generate.standard');

    // ── Manual & Solo ─────────────────────────────────────────────────────
    Route::post('/manual/save',        [AIQuizController::class, 'saveManual'])->name('manual.save');
    Route::post('/submit/solo',        [AIQuizController::class, 'submitSolo'])->name('submit.solo');

    // ── Suggestions ───────────────────────────────────────────────────────
    Route::get('/suggestions',         [AIQuizController::class, 'suggestions'])->name('suggestions');

    // ── Delete quiz (wildcard — MUST be last) ─────────────────────────────
    Route::delete('/{quiz}',           [AIQuizController::class, 'destroy'])->name('destroy');
});
Route::post('/student/quiz/tutor/gen-quiz', [AIQuizController::class, 'tutorGenQuiz'])
    ->name('student.quiz.tutor.gen_quiz');

Route::prefix('student')->middleware(['auth'])->group(function () {

    // ── My Quiz History ────────────────────────────────────────────────────
    Route::get('/history',                        [MyQuizHistoryController::class, 'index'])->name('student.history.index');
    Route::delete('/history/result/{id}',         [MyQuizHistoryController::class, 'deleteResult'])->name('student.history.result.delete');
    Route::delete('/history/quiz/{id}',           [MyQuizHistoryController::class, 'deleteQuiz'])->name('student.history.quiz.delete');
    Route::get('/history/result/{id}/detail',     [MyQuizHistoryController::class, 'getResultDetail'])->name('student.history.result.detail');
});

Route::prefix('student/battle')->middleware(['auth'])->name('student.battle.')->group(function () {

    // ── Setup ──────────────────────────────────────────────────────────────
    Route::get('/setup',                    [BattleController::class, 'setup'])->name('setup');
    Route::post('/create',                  [BattleController::class, 'createRoom'])->name('create');

    // ── Join ───────────────────────────────────────────────────────────────
    // Blank join form  →  GET /student/battle/join
    Route::get('/join',                     [BattleController::class, 'joinPage'])->name('join.page');

    // Pre-filled via invite link  →  GET /student/battle/join/QM-XXXX
    Route::get('/join/{code}',              [BattleController::class, 'joinPage'])->name('join.code');

    // Team invite link  →  GET /student/battle/team/QM-XXXX/a
    Route::get('/team/{code}/{team}',       [BattleController::class, 'joinPage'])->name('team.join');

    // Join submit (AJAX POST)
    Route::post('/join',                    [BattleController::class, 'joinRoom'])->name('join');

    // Room lookup for code preview (AJAX GET)
    Route::get('/lookup/{code}',            [BattleController::class, 'lookupRoom'])->name('lookup');

    // ── Lobby ──────────────────────────────────────────────────────────────
    Route::get('/lobby/{code}',             [BattleController::class, 'lobby'])->name('lobby');
    Route::post('/start',                   [BattleController::class, 'startBattle'])->name('start');
    Route::get('/lobby-state/{code}',  [BattleController::class, 'lobbyState'])->name('lobby.state');
    Route::get('/arena-state/{code}',  [BattleController::class, 'arenaState'])->name('arena.state');

    // ── Arena ──────────────────────────────────────────────────────────────
    Route::get('/arena/{code}',             [BattleController::class, 'arena'])->name('arena');
    Route::post('/answer',                  [BattleController::class, 'submitAnswer'])->name('answer');
    Route::post('/violation',               [BattleController::class, 'reportViolation'])->name('violation');

    // ── Results ────────────────────────────────────────────────────────────
    Route::get('/results/{code}',           [BattleController::class, 'results'])->name('results');
    Route::post('/rematch',                 [BattleController::class, 'rematch'])->name('rematch');

    // ── History ────────────────────────────────────────────────────────────
    Route::get('/history',                  [BattleController::class, 'history'])->name('history');
});
Route::middleware(['auth', 'verified'])
    ->prefix('institution')
    ->name('institution.')
    ->group(function () {

        // ✅ Dashboard page
        Route::get('/dashboard', [InstitutionController::class, 'index'])
            ->name('dashboard');

        // ✅ Students PAGE
        Route::get('/students', [InstitutionController::class, 'studentsPage'])
            ->name('students');

        // ✅ Students DATA (AJAX)
        Route::get('/students/data', [InstitutionController::class, 'students'])
            ->name('students.data');

        // ✅ Settings PAGE
        Route::get('/settings', [InstitutionController::class, 'settingsPage'])
            ->name('settings');

        // ✅ Update settings
        Route::put('/settings', [InstitutionController::class, 'updateSettings'])
            ->name('settings.update');
    });
Route::get('/institution/aiquiz',        [InstitutionQuizController::class, 'index'])->name('institution.aiquiz');
Route::post('/institution/quiz/generate/topic',    [InstitutionQuizController::class, 'generateTopic'])->name('institution.quiz.generate.topic');
Route::post('/institution/quiz/generate/pdf',      [InstitutionQuizController::class, 'generatePdf'])->name('institution.quiz.generate.pdf');
Route::post('/institution/quiz/generate/image',    [InstitutionQuizController::class, 'generateImage'])->name('institution.quiz.generate.image');
Route::post('/institution/quiz/generate/standard', [InstitutionQuizController::class, 'generateStandard'])->name('institution.quiz.generate.standard');
Route::post('/institution/quiz/manual/save',       [InstitutionQuizController::class, 'saveManual'])->name('institution.quiz.manual.save');
Route::post('/institution/quiz/submit/solo',       [InstitutionQuizController::class, 'submitSolo'])->name('institution.quiz.submit.solo');
Route::get('/institution/quiz/suggestions',        [InstitutionQuizController::class, 'suggestions'])->name('institution.quiz.suggestions');
Route::delete('/institution/quiz/{quiz}',          [InstitutionQuizController::class, 'destroy']);
Route::middleware(['auth'])->prefix('institution-code')->name('institution.code.')->group(function () {

    Route::post('/validate', [InstitutionCodeController::class, 'validate'])
        ->name('validate');

    Route::post('/join', [InstitutionCodeController::class, 'join'])
        ->name('join');
});

Route::middleware('auth')
    ->prefix('institution')
    ->name('institution.')
    ->group(function () {

        // Battle management (host only)
        Route::prefix('battle')->name('battle.')->group(function () {
            Route::post('/index',        [InstitutionBattleController::class, 'createBattle'])->name('index');
            Route::post('/create',        [InstitutionBattleController::class, 'createBattle'])->name('create');
            Route::get('/manage/{code}',  [InstitutionBattleController::class, 'manage'])->name('manage');
            Route::get('/results/{code}', [InstitutionBattleController::class, 'results'])->name('results');

            Route::post('/start',         [InstitutionBattleController::class, 'startBattle'])->name('start');
            Route::post('/next-question', [InstitutionBattleController::class, 'nextQuestion'])->name('next-question');
            Route::post('/end',           [InstitutionBattleController::class, 'endBattle'])->name('end');
            Route::post('/rematch',       [InstitutionBattleController::class, 'rematch'])->name('rematch');

            // Host state polling
            Route::get('/state/{code}',       [InstitutionBattleController::class, 'manageState'])->name('state');
        });
    });

require __DIR__ . '/auth.php';
