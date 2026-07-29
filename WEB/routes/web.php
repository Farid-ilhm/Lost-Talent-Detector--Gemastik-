<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::get('/register', [WebAuthController::class, 'showRegister']);
Route::post('/register', [WebAuthController::class, 'register']);
Route::post('/logout', [WebAuthController::class, 'logout']);

// Protected Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Student Actions
    Route::post('/student/interests', [DashboardController::class, 'saveInterests']);
    Route::post('/student/achievements', [DashboardController::class, 'saveAchievement']);
    Route::post('/student/test', [DashboardController::class, 'saveTest']);
    Route::post('/student/analyze', [DashboardController::class, 'triggerAi']);
    Route::post('/student/grades', [DashboardController::class, 'studentSaveGrade']);

    // Teacher Actions
    Route::post('/teacher/grades', [DashboardController::class, 'teacherSaveGrade']);
    Route::post('/teacher/achievements/{id}/verify', [DashboardController::class, 'teacherVerify']);
    Route::post('/teacher/notes', [DashboardController::class, 'teacherSaveNote']);

    // Institution Actions
    Route::post('/institution/teachers', [DashboardController::class, 'institutionSaveTeacher']);

    // Admin Actions
    Route::post('/admin/institutions/{id}/verify', [DashboardController::class, 'adminVerifyInstitution']);
    Route::post('/admin/competitions', [DashboardController::class, 'adminSaveCompetition']);
});
