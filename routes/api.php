<?php

use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminModuleController;
use App\Http\Controllers\Api\LandingContentController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
Route::get('/admin/module/{section}/{item}', [AdminModuleController::class, 'show']);
Route::post('/admin/module/{section}/{item}', [AdminModuleController::class, 'store']);
Route::put('/admin/module/{section}/{item}/{entryId}', [AdminModuleController::class, 'update']);
Route::delete('/admin/module/{section}/{item}/{entryId}', [AdminModuleController::class, 'destroy']);
Route::get('/landing/content', [LandingContentController::class, 'index']);
