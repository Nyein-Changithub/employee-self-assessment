<?php

use App\Http\Controllers\Admin\AssessmentController as AdminAssessmentController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AssessmentPeriodController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LookupController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
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

    Route::middleware(['auth', 'staff'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');

        Route::middleware('permission:periods.manage')->group(function () {
            Route::get('/assessment-periods', [AssessmentPeriodController::class, 'index'])->name('periods.index');
            Route::post('/assessment-periods', [AssessmentPeriodController::class, 'store'])->name('periods.store');
        });

        Route::middleware('permission:questions.manage')->group(function () {
            Route::get('/assessment-periods/{period}/questions', [QuestionController::class, 'index'])->name('questions.index');
            Route::post('/assessment-periods/{period}/questions', [QuestionController::class, 'store'])->name('questions.store');
            Route::put('/questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
            Route::delete('/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
        });

        foreach (['departments' => 'departments.manage', 'positions' => 'positions.manage'] as $type => $permission) {
            Route::middleware("permission:{$permission}")->group(function () use ($type) {
                Route::get("/{$type}", [LookupController::class, 'index'])->defaults('type', $type)->name("{$type}.index");
                Route::get("/{$type}/create", [LookupController::class, 'create'])->defaults('type', $type)->name("{$type}.create");
                Route::post("/{$type}", [LookupController::class, 'store'])->defaults('type', $type)->name("{$type}.store");
                Route::get("/{$type}/{id}", [LookupController::class, 'show'])->defaults('type', $type)->name("{$type}.show");
                Route::get("/{$type}/{id}/edit", [LookupController::class, 'edit'])->defaults('type', $type)->name("{$type}.edit");
                Route::put("/{$type}/{id}", [LookupController::class, 'update'])->defaults('type', $type)->name("{$type}.update");
                Route::delete("/{$type}/{id}", [LookupController::class, 'destroy'])->defaults('type', $type)->name("{$type}.destroy");
            });
        }

        Route::get('/assessments', [AdminAssessmentController::class, 'index'])->middleware('permission:submissions.view')->name('assessments.index');
        // Export routes must be declared before the {assessment} wildcard.
        Route::middleware('permission:submissions.export')->group(function () {
            Route::get('/assessments/export/excel', [AdminAssessmentController::class, 'exportExcel'])->name('assessments.export.excel');
            Route::get('/assessments/export/csv', [AdminAssessmentController::class, 'exportCsv'])->name('assessments.export.csv');
            Route::get('/assessments/{assessment}/pdf', [AdminAssessmentController::class, 'pdf'])->name('assessments.pdf');
        });
        Route::get('/assessments/{assessment}', [AdminAssessmentController::class, 'show'])->middleware('permission:submissions.view')->name('assessments.show');

        // Access control
        Route::resource('users', UserController::class)->middleware('permission:users.manage');
        Route::resource('roles', RoleController::class)->except('show')->middleware('permission:roles.manage');
        Route::resource('permissions', PermissionController::class)->except('show')->middleware('permission:permissions.manage');
    });
});
