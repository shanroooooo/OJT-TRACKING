<?php

use Illuminate\Support\Facades\Route;

// Admin Panel Routes
Route::get('/admin/login', function () {
    return view('admin.login');
});

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});

Route::get('/admin/users', function () {
    return view('admin.users');
});

Route::get('/supervisor/dashboard', function () {
    return view('admin.supervisor-dashboard');
});

Route::get('/', function () {
    return redirect('/admin/login');
});

// Authentication Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Student Dashboard Routes
    Route::get('/dashboard', [App\Http\Controllers\StudentDashboardController::class, 'index'])
        ->name('student.dashboard')
        ->middleware('student.profile');
        
    // Profile Creation Route
    Route::get('/profile/create', function () {
        return view('student.profile-create');
    })->name('profile.create');
        
    // Logout Route
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
