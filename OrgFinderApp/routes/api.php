<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\OrganizationApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\RecommendationApiController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/auth/login', [AuthApiController::class, 'login']);

// Protected (requires Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/user', [AuthApiController::class, 'user']);

    Route::post('/profile/complete', [ProfileApiController::class, 'complete']);
    Route::put('/profile', [ProfileApiController::class, 'update']);
    Route::post('/profile/photo', [ProfileApiController::class, 'uploadPhoto']);

    Route::get('/organizations', [OrganizationApiController::class, 'index']);
    Route::get('/organizations/{id}', [OrganizationApiController::class, 'show']);

    Route::get('/events/upcoming', [EventApiController::class, 'upcoming']);
    Route::get('/events/{id}', [EventApiController::class, 'show']);

    Route::get('/recommendations', [RecommendationApiController::class, 'index']);
});
