<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\LearningAdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Superadmin\SuperadminDashboardController;
use App\Http\Controllers\Superadmin\UserApprovalController;
use App\Http\Controllers\User\UserDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth (Landing = Login) + Dashboard Superadmin / Admin / User
|--------------------------------------------------------------------------
*/

// Landing page sekaligus halaman login & registrasi.
Route::get('/', [LoginController::class, 'landing'])->name('home');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1')->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/daftar', [LoginController::class, 'showRegister'])->name('register');
Route::post('/daftar', [LoginController::class, 'register'])->middleware('throttle:5,1')->name('register.store');

// ---------------- SUPERADMIN ----------------
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function (): void {
    Route::get('/', [SuperadminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserApprovalController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserApprovalController::class, 'create'])->name('users.create');
    Route::post('/users/create', [UserApprovalController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/approve', [UserApprovalController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [UserApprovalController::class, 'reject'])->name('users.reject');
    Route::post('/users/{user}/toggle-suspend', [UserApprovalController::class, 'toggleSuspend'])->name('users.suspend');
    Route::post('/users/{user}/role', [UserApprovalController::class, 'changeRole'])->name('users.role');
    Route::post('/users/{user}/reset-password', [UserApprovalController::class, 'resetPassword'])->name('users.password');
    Route::delete('/users/{user}', [UserApprovalController::class, 'destroy'])->name('users.destroy');
});

// ---------------- ADMIN ----------------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.role'])->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/mapel', [LearningAdminController::class, 'subjects'])->name('subjects');
    Route::post('/mapel', [LearningAdminController::class, 'storeSubject'])->name('subjects.store');
    Route::get('/materi', [LearningAdminController::class, 'materials'])->name('materials');
    Route::get('/soal', [LearningAdminController::class, 'questions'])->name('questions');
    Route::post('/soal/{question}/status', [LearningAdminController::class, 'updateQuestionStatus'])->name('questions.status');
    Route::get('/tryout', [LearningAdminController::class, 'tryouts'])->name('tryouts');
    Route::post('/tryout/{tryout}/status', [LearningAdminController::class, 'updateTryoutStatus'])->name('tryouts.status');
    Route::get('/ai-soal', [LearningAdminController::class, 'aiQuestions'])->name('ai');
    Route::post('/ai-soal/{id}/review/{decision}', [LearningAdminController::class, 'reviewAiQuestion'])->name('ai.review');
    Route::get('/siswa', [LearningAdminController::class, 'students'])->name('students');
});

// ---------------- USER (siswa) ----------------
Route::prefix('user')->name('user.')->middleware(['auth', 'active.user'])->group(function (): void {
    Route::get('/', [UserDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/mapel', [UserDashboardController::class, 'subjects'])->name('subjects');
    Route::get('/tryout', [UserDashboardController::class, 'tryouts'])->name('tryouts');
    Route::get('/bookmark', [UserDashboardController::class, 'bookmarks'])->name('bookmarks');
    Route::get('/catatan', [UserDashboardController::class, 'notes'])->name('notes');
    Route::post('/catatan', [UserDashboardController::class, 'storeNote'])->name('notes.store');
    Route::delete('/catatan/{note}', [UserDashboardController::class, 'destroyNote'])->name('notes.destroy');
    Route::get('/profil', [UserDashboardController::class, 'profile'])->name('profile');
    Route::put('/profil', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
});
