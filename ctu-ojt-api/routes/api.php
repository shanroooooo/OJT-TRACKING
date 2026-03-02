<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SupervisorController;
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
        // Profile management
        Route::post('/profile',        [StudentProfileController::class, 'store']);
        Route::get('/profile',         [StudentProfileController::class, 'show']);
        Route::put('/profile',         [StudentProfileController::class, 'update']);
        Route::patch('/profile',       [StudentProfileController::class, 'update']);
        Route::delete('/profile',      [StudentProfileController::class, 'destroy']);
        Route::get('/profile/summary', [StudentProfileController::class, 'summary']);
        
        // Time logging
        Route::post('/time-in',   [TimeLogController::class, 'timeIn']);
        Route::patch('/time-out', [TimeLogController::class, 'timeOut']);
        Route::get('/today',      [TimeLogController::class, 'today']);
        Route::get('/logs',       [TimeLogController::class, 'myLogs']);
    });

    // ---------------------------------------------------------------
    // Supervisor-only routes
    // ---------------------------------------------------------------
    Route::middleware('role:supervisor')->prefix('supervisor')->group(function () {
        Route::get('/dashboard', [SupervisorController::class, 'dashboard']);
        Route::get('/students', [SupervisorController::class, 'students']);
        Route::get('/students/{studentProfile}', [SupervisorController::class, 'studentDetails']);
        Route::get('/students/{studentProfile}/progress', [SupervisorController::class, 'studentProgress']);
        Route::get('/students/{studentProfile}/export', [SupervisorController::class, 'exportStudentReport']);
        Route::get('/time-logs', [SupervisorController::class, 'timeLogs']);
        Route::post('/time-logs/{log}/review', [SupervisorController::class, 'reviewTimeLog']);
        Route::post('/time-logs/bulk-review', [SupervisorController::class, 'bulkReviewLogs']);
    });

    // ---------------------------------------------------------------
    // Admin-only routes
    // ---------------------------------------------------------------
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::put('/users/{user}', [AdminController::class, 'updateUser']);
        Route::patch('/users/{user}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);
        Route::patch('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus']);
        
        Route::get('/student-profiles', [AdminController::class, 'studentProfiles']);
        Route::get('/system-logs', [AdminController::class, 'systemLogs']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
    });

    // ---------------------------------------------------------------
    // Admin + Supervisor shared routes
    // ---------------------------------------------------------------
    Route::middleware('role:admin,supervisor')->prefix('manage')->group(function () {
        // e.g. Route::get('/logs', [LogController::class, 'index']);
    });

});
