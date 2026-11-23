<?php

use App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [Controllers\AuthController::class, 'register'])->name('register')->middleware("throttle:6,1");
Route::post('/login', [Controllers\AuthController::class, 'login'])->name('login')->middleware("throttle:6,1");
Route::post('/forgot-password', [Controllers\AuthController::class, 'forgotPassword'])->name('password.email')->middleware("throttle:6,1");
Route::post('/reset-password', [Controllers\AuthController::class, 'resetPassword'])->name('password.store')->middleware("throttle:6,1");

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [Controllers\AuthController::class, 'logout'])->name('logout');

    Route::get('/user', function (Request $request) {
        return $request->user()->toResource();
    });

    Route::apiResources([
        'admin' => Controllers\AdminController::class,
        'staff' => Controllers\StaffController::class,
        'trainer' => Controllers\TrainerController::class,
        'trainee' => Controllers\TraineeController::class,
        'internship' => Controllers\InternshipController::class,
        'training' => Controllers\TrainingController::class,
        'session' => Controllers\SessionController::class,
        'batch-type' => Controllers\BatchTypeController::class,
        'batch' => Controllers\BatchController::class,
        'payment' => Controllers\PaymentController::class,
    ]);

    Route::apiResource('training.quiz', Controllers\QuizController::class)->scoped();
});
