<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MyQuizHistoryController;
use App\Http\Controllers\BattleController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\InstitutionBattleController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\InstitutionCodeController;
use App\Http\Controllers\InstitutionQuizController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {

    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])
        ->name('student.dashboard');

    Route::get('/student/profile/setup', [StudentController::class, 'profileSetupPage'])
        ->name('student.profile.setup');

    Route::get('/student/profile', [StudentController::class, 'profilePage'])
        ->name('student.profile');

    Route::post('/student/profile/save', [StudentController::class, 'saveProfile'])
        ->name('student.profile.save');

    Route::get('/student/stats', [StudentController::class, 'stats'])
        ->name('student.stats');

    Route::get('/student/account/delete', [StudentController::class, 'deleteAccountPage'])
        ->name('student.account.delete.page');

    Route::delete('/student/account/delete', [StudentController::class, 'deleteAccount'])
        ->name('student.account.delete');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/auth/google',          [AuthenticatedSessionController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthenticatedSessionController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::prefix('student/quiz')->middleware(['auth'])->name('student.quiz.')->group(function () {

    Route::get('/',                    [QuizController::class, 'index'])->name('index');

    Route::get('/tutor',               [QuizController::class, 'tutorIndex'])->name('tutor.index');
    Route::post('/tutor/chat',         [QuizController::class, 'tutorChat'])->name('tutor.chat');
    Route::post('/tutor/new',          [QuizController::class, 'tutorNewSession'])->name('tutor.new');
    Route::get('/tutor/sessions',      [QuizController::class, 'tutorSessions'])->name('tutor.sessions');
    Route::get('/tutor/session/{id}',  [QuizController::class, 'tutorSession'])->name('tutor.session');
    Route::delete('/tutor/session/{id}', [QuizController::class, 'tutorDeleteSession'])->name('tutor.session.delete');

    Route::post('/generate/topic',     [QuizController::class, 'generateTopic'])->name('generate.topic');
    Route::post('/generate/pdf',       [QuizController::class, 'generatePdf'])->name('generate.pdf');
    Route::post('/generate/image',     [QuizController::class, 'generateImage'])->name('generate.image');
    Route::post('/generate/standard',  [QuizController::class, 'generateStandard'])->name('generate.standard');

    Route::post('/manual/save',        [QuizController::class, 'saveManual'])->name('manual.save');
    Route::post('/submit/solo',        [QuizController::class, 'submitSolo'])->name('submit.solo');

    Route::get('/suggestions',         [QuizController::class, 'suggestions'])->name('suggestions');

    Route::delete('/{quiz}',           [QuizController::class, 'destroy'])->name('destroy');
});
Route::post('/student/quiz/tutor/gen-quiz', [QuizController::class, 'tutorGenQuiz'])
    ->name('student.quiz.tutor.gen_quiz');

Route::prefix('student')->middleware(['auth'])->group(function () {

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

        Route::get('/dashboard', [InstitutionController::class, 'index'])
            ->name('dashboard');

        Route::get('/students', [InstitutionController::class, 'studentsPage'])
            ->name('students');

        Route::get('/students/data', [InstitutionController::class, 'students'])
            ->name('students.data');

        Route::get('/settings', [InstitutionController::class, 'settingsPage'])
            ->name('settings');

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

Route::middleware(['auth'])->prefix('institution')->name('institution.')->group(function () {

    Route::get('battle/setup', [InstitutionBattleController::class, 'setup'])->name('battle.setup');
    Route::post('battle/create', [InstitutionBattleController::class, 'createBattle'])->name('battle.create');

    Route::get('battle/setup-page/{code}', [InstitutionBattleController::class, 'setupPage'])->name('battle.setup-page');
    Route::get('battle/lobby-state/{code}', [InstitutionBattleController::class, 'lobbyState'])->name('battle.lobby-state');

    Route::post('battle/start-registration/{code}', [InstitutionBattleController::class, 'startRegistration'])->name('battle.start-registration');
    Route::post('battle/start', [InstitutionBattleController::class, 'startBattle'])->name('battle.start');

    Route::get('battle/join-page', [InstitutionBattleController::class, 'institutionJoinPage'])->name('battle.join.page');
    Route::post('battle/lookup', [InstitutionBattleController::class, 'lookupBattle'])->name('battle.lookup');        // NEW
    Route::post('battle/join', [InstitutionBattleController::class, 'institutionJoin'])->name('battle.inst.join');

    Route::post('battle/join-arena', [InstitutionBattleController::class, 'joinBattle'])->name('battle.join.post');

    Route::get('battle/arena/{code}', [InstitutionBattleController::class, 'arena'])->name('battle.arena');
    Route::get('battle/arena-state/{code}', [InstitutionBattleController::class, 'arenaState'])->name('battle.arena-state');

    Route::post('battle/answer', [InstitutionBattleController::class, 'submitAnswer'])->name('battle.answer');
    Route::post('battle/violation', [InstitutionBattleController::class, 'reportViolation'])->name('battle.violation');

    Route::get('battle/results/{code}', [InstitutionBattleController::class, 'results'])->name('battle.results');
    Route::get('battle/history', [InstitutionBattleController::class, 'history'])->name('battle.history');
});

Route::prefix('student')->name('student.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/certificates',        [CertificateController::class, 'index'])->name('certificates');
    Route::get('/certificates/{id}',   [CertificateController::class, 'show'])->name('certificates.show');
});

require __DIR__ . '/auth.php';
