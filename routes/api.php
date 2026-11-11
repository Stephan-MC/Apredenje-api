<?php

use App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [Controllers\AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [Controllers\AuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/reset-password', [Controllers\AuthController::class, 'resetPassword'])->name('password.store');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [Controllers\AuthController::class, 'logout'])->name('logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResources([
        'admin' => Controllers\AdminController::class,
        'staff' => Controllers\StaffController::class,
        'trainer' => Controllers\TrainerController::class,
        'trainee' => Controllers\TraineeController::class,
        'intern' => Controllers\InternController::class,
        'training' => Controllers\TrainingController::class,
        'session' => Controllers\SessionController::class,
        'batch-type' => Controllers\BatchTypeController::class,
        'batch' => Controllers\BatchController::class,
        'quiz' => Controllers\QuizController::class,
        'payment' => Controllers\PaymentController::class,
    ]);
});
