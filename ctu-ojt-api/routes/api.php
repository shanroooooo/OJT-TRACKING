<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// Public routes  no token required
// ---------------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ---------------------------------------------------------------
// Protected routes  valid Sanctum token required
// ---------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Any authenticated user
    Route::get('/user',         [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // ---------------------------------------------------------------
    // Student-only routes
    // ---------------------------------------------------------------
    Route::middleware('role:student')->prefix('student')->group(function () {
        Route::post('/time-in',   [TimeLogController::class, 'timeIn']);
        Route::patch('/time-out', [TimeLogController::class, 'timeOut']);
        Route::get('/today',      [TimeLogController::class, 'today']);
        Route::get('/logs',       [TimeLogController::class, 'myLogs']);
    });

    // ---------------------------------------------------------------
    // Supervisor-only routes
    // ---------------------------------------------------------------
    Route::middleware('role:supervisor')->prefix('supervisor')->group(function () {
        // e.g. Route::get('/students', [SupervisorController::class, 'index']);
    });

    // ---------------------------------------------------------------
    // Admin-only routes
    // ---------------------------------------------------------------
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // e.g. Route::get('/users', [AdminController::class, 'index']);
    });

    // ---------------------------------------------------------------
    // Admin + Supervisor shared routes
    // ---------------------------------------------------------------
    Route::middleware('role:admin,supervisor')->prefix('manage')->group(function () {
        // e.g. Route::get('/logs', [LogController::class, 'index']);
    });

});
