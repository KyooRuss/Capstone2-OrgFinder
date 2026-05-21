<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\OrganizationApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\RecommendationApiController;
use App\Http\Controllers\Api\MembershipRequestApiController;
use App\Http\Controllers\Api\ChatApiController;
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
    Route::get('/organizations/recruiting', [OrganizationApiController::class, 'recruiting']);
    Route::get('/organizations/{id}', [OrganizationApiController::class, 'show']);

    Route::get('/events/upcoming', [EventApiController::class, 'upcoming']);
    Route::get('/events/{id}', [EventApiController::class, 'show']);

    Route::get('/recommendations', [RecommendationApiController::class, 'index']);

    Route::post('/organizations/{id}/apply', [MembershipRequestApiController::class, 'apply']);
    Route::get('/organizations/{id}/my-status', [MembershipRequestApiController::class, 'myStatus']);

    Route::get('/chat/my-chats', [ChatApiController::class, 'myChats']);
    Route::get('/chat/unread', [ChatApiController::class, 'unreadCount']);
    Route::get('/chat/{orgId}/messages', [ChatApiController::class, 'messages']);
    Route::post('/chat/{orgId}/send', [ChatApiController::class, 'send']);
});
