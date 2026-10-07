<?php

use App\Http\Controllers\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CycleController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\AssessmentController;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.login'));

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, SetLocaleMiddleware::LOCALES, true), 404);
    session(['locale' => $locale]);

    return redirect()->back();
})->name('lang.switch');

// Employee form (no pre-verification)
Route::get('/assessment/{slug}', [AssessmentController::class, 'show'])->name('assessment.show');
Route::post('/assessment/{slug}/submit', [AssessmentController::class, 'submit'])
    ->middleware('throttle:20,1')
    ->name('assessment.submit');

// Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');

    Route::middleware(['auth', 'role:ceo,gm,admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/cycles', [CycleController::class, 'index'])->name('cycles.index');
        Route::post('/cycles', [CycleController::class, 'store'])->name('cycles.store');

        Route::get('/cycles/{cycle}/questions', [QuestionController::class, 'index'])->name('questions.index');
        Route::post('/cycles/{cycle}/questions', [QuestionController::class, 'store'])->name('questions.store');
        Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

        Route::get('/assessments', [AdminAssessmentController::class, 'index'])->name('assessments.index');
        // Export routes must be declared before the {assessment} wildcard.
        Route::get('/assessments/export/excel', [AdminAssessmentController::class, 'exportExcel'])->name('assessments.export.excel');
        Route::get('/assessments/export/csv', [AdminAssessmentController::class, 'exportCsv'])->name('assessments.export.csv');
        Route::get('/assessments/{assessment}', [AdminAssessmentController::class, 'show'])->name('assessments.show');
        Route::get('/assessments/{assessment}/pdf', [AdminAssessmentController::class, 'pdf'])->name('assessments.pdf');
    });
});
