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
Route::get('/verify-otp', [WebAuthController::class, 'showVerifyOtp'])->name('verify-otp.show');
Route::post('/verify-otp', [WebAuthController::class, 'verifyOtp'])->name('verify-otp.post');
Route::post('/resend-otp', [WebAuthController::class, 'resendWebOtp'])->name('resend-otp.post');
Route::post('/logout', [WebAuthController::class, 'logout']);

// Protected Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Student Actions
    Route::post('/student/interests', [DashboardController::class, 'saveInterests']);
    Route::post('/student/achievements', [DashboardController::class, 'saveAchievement']);
    Route::delete('/student/achievements/{id}', [DashboardController::class, 'deleteAchievement']);
    Route::post('/student/test', [DashboardController::class, 'saveTest']);
    Route::post('/student/test/reset', [DashboardController::class, 'resetTestWeb']);
    Route::post('/student/analyze', [DashboardController::class, 'triggerAi']);
    Route::post('/student/analyze/reset', [DashboardController::class, 'resetAiWeb']);
    Route::post('/student/grades', [DashboardController::class, 'studentSaveGrade']);

    // Teacher Actions
    Route::get('/teacher/achievements', [DashboardController::class, 'teacherAchievementsView']);
    Route::get('/teacher/grades', [DashboardController::class, 'teacherGradesView']);
    Route::get('/teacher/students', [DashboardController::class, 'teacherStudentsView']);
    Route::post('/teacher/achievements/{id}/verify', [DashboardController::class, 'teacherVerify']);
    Route::post('/teacher/grades', [DashboardController::class, 'teacherSaveGrade']);
    Route::post('/teacher/notes', [DashboardController::class, 'teacherSaveNote']);
    Route::post('/teacher/student-data', [DashboardController::class, 'teacherSaveStudentData']);
    Route::get('/teacher/students/{id}/edit', [DashboardController::class, 'teacherEditStudent']);
    Route::post('/teacher/students/{id}/update', [DashboardController::class, 'teacherUpdateStudent']);
    Route::post('/teacher/students/{id}/delete', [DashboardController::class, 'teacherDeleteStudent']);

    // Institution Actions
    Route::get('/institution/classrooms', [DashboardController::class, 'institutionClassroomsView']);
    Route::get('/institution/teachers', [DashboardController::class, 'institutionTeachersView']);
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

    // Admin View Routes
    Route::get('/admin/institutions', [DashboardController::class, 'adminInstitutionsView']);
    Route::get('/admin/competitions', [DashboardController::class, 'adminCompetitionsView']);
    Route::get('/admin/users', [DashboardController::class, 'adminUsersView']);
    
    // Admin User CRUD Routes
    Route::post('/admin/users', [DashboardController::class, 'adminSaveUser']);
    Route::get('/admin/users/{id}/edit', [DashboardController::class, 'adminEditUser']);
    Route::post('/admin/users/{id}/update', [DashboardController::class, 'adminUpdateUser']);
    Route::post('/admin/users/{id}/delete', [DashboardController::class, 'adminDeleteUser']);

    // General Profile Settings Route
    Route::post('/profile/change-password', [DashboardController::class, 'changePassword']);

    // Notifications Routes
    Route::get('/notifications', [DashboardController::class, 'getNotifications']);
    Route::post('/notifications/mark-read', [DashboardController::class, 'markNotificationsRead']);
});
