<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\TeacherApiController;
use App\Http\Controllers\Api\InstitutionApiController;

// Public authentication routes
Route::get('/institutions', [AuthController::class, 'getInstitutions']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Session profile routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Student & General Public routes
    Route::get('/student/dashboard', [StudentApiController::class, 'getDashboard']);
    Route::put('/student/interests-hobbies', [StudentApiController::class, 'updateInterestsAndHobbies']);
    Route::post('/student/achievements', [StudentApiController::class, 'uploadAchievement']);
    Route::delete('/student/achievements/{id}', [StudentApiController::class, 'deleteAchievement']);
    Route::post('/student/achievements/bulk-delete', [StudentApiController::class, 'bulkDeleteAchievements']);
    Route::get('/student/test', [StudentApiController::class, 'getRiasecTest']);
    Route::post('/student/test/submit', [StudentApiController::class, 'submitTestAnswers']);
    Route::post('/student/test/reset', [StudentApiController::class, 'resetRiasecTest']);
    Route::post('/student/analyze', [StudentApiController::class, 'analyzeTalent']);
    Route::post('/student/analyze/reset', [StudentApiController::class, 'resetAiAnalysis']);
    Route::post('/student/grades', [StudentApiController::class, 'saveIndependentGrade']);
    Route::delete('/student/grades/{id}', [StudentApiController::class, 'deleteIndependentGrade']);
    Route::post('/student/grades/bulk-delete', [StudentApiController::class, 'bulkDeleteIndependentGrades']);
    Route::get('/student/announcements', [StudentApiController::class, 'getAnnouncements']);

    // Teacher routes
    Route::get('/teacher/students', [TeacherApiController::class, 'getStudentsList']);
    Route::post('/teacher/grades', [TeacherApiController::class, 'inputStudentGrades']);
    Route::post('/teacher/achievements/{id}/verify', [TeacherApiController::class, 'verifyAchievement']);
    Route::post('/teacher/notes', [TeacherApiController::class, 'inputTeacherNote']);

    // Institution routes
    Route::get('/institution/stats', [InstitutionApiController::class, 'getStats']);
    Route::post('/institution/teachers', [InstitutionApiController::class, 'addTeacher']);
    Route::get('/institution/classrooms', [InstitutionApiController::class, 'getClassrooms']);
    Route::get('/institution/announcements', [InstitutionApiController::class, 'getAnnouncements']);
    Route::post('/institution/announcements', [InstitutionApiController::class, 'storeAnnouncement']);
    Route::delete('/institution/announcements/{id}', [InstitutionApiController::class, 'deleteAnnouncement']);
});
