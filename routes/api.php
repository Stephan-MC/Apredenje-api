<?php

use App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
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
