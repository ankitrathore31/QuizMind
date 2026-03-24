<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AIQuizController;
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
Route::prefix('student/quiz')->group(function () {
Route::get('/Aiquiz', [AIQuizController::class, 'index'])->name('student.quiz.index');
Route::post('/generate/topic',    [AIQuizController::class, 'generateTopic'])->name('student.quiz.generate.topic');
Route::post('/generate/pdf',      [AIQuizController::class, 'generatePdf'])->name('student.quiz.generate.pdf');
Route::post('/generate/image',    [AIQuizController::class, 'generateImage'])->name('student.quiz.generate.image');
Route::post('/generate/standard', [AIQuizController::class, 'generateStandard'])->name('student.quiz.generate.standard');
Route::post('/manual/save',       [AIQuizController::class, 'saveManual'])->name('student.quiz.manual.save');

// Play & results
Route::post('/submit-solo',       [AIQuizController::class, 'submitSolo'])->name('student.quiz.submit.solo');
Route::delete('/{quiz}',          [AIQuizController::class, 'destroy'])->name('student.quiz.destroy');
});
// AI Tutor & suggestions
Route::post('/tutor-chat',        [AIQuizController::class, 'tutorChat'])->name('tutor.chat');
Route::get('/suggestions',        [AIQuizController::class, 'suggestions'])->name('student.quiz.suggestions');

require __DIR__ . '/auth.php';
