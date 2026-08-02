<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
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
    Route::post('/teacher/student-data', [DashboardController::class, 'teacherSaveStudentData']);
    Route::get('/teacher/students/{id}/edit', [DashboardController::class, 'teacherEditStudent']);
    Route::post('/teacher/students/{id}/update', [DashboardController::class, 'teacherUpdateStudent']);
    Route::post('/teacher/students/{id}/delete', [DashboardController::class, 'teacherDeleteStudent']);

    // Institution Actions
    Route::post('/institution/teachers', [DashboardController::class, 'institutionSaveTeacher']);
    Route::get('/institution/teachers/{id}/edit', [DashboardController::class, 'institutionEditTeacher']);
    Route::post('/institution/teachers/{id}/update', [DashboardController::class, 'institutionUpdateTeacher']);
    Route::post('/institution/teachers/{id}/delete', [DashboardController::class, 'institutionDeleteTeacher']);
    Route::post('/institution/classrooms/{id}/delete', [DashboardController::class, 'institutionDeleteClassroom']);

    // Admin Actions
    Route::post('/admin/institutions/{id}/verify', [DashboardController::class, 'adminVerifyInstitution']);
    Route::get('/admin/institutions/{id}/edit', [DashboardController::class, 'adminEditInstitution']);
    Route::post('/admin/institutions/{id}/update', [DashboardController::class, 'adminUpdateInstitution']);
    Route::post('/admin/institutions/{id}/delete', [DashboardController::class, 'adminDeleteInstitution']);
    Route::post('/admin/competitions', [DashboardController::class, 'adminSaveCompetition']);
    Route::post('/admin/competitions/delete-multiple', [DashboardController::class, 'adminDeleteMultipleCompetitions']);
    Route::get('/admin/competitions/{id}/edit', [DashboardController::class, 'adminEditCompetition']);
    Route::post('/admin/competitions/{id}/update', [DashboardController::class, 'adminUpdateCompetition']);
    Route::post('/admin/competitions/{id}/delete', [DashboardController::class, 'adminDeleteCompetition']);
});
