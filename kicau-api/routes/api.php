<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Kicau Social Media
|--------------------------------------------------------------------------
*/

// ─── Public routes (tidak butuh login) ───────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Protected routes (butuh token Sanctum) ──────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Feed & Search
    Route::get('/feed', [PostController::class, 'feed']);
    Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'index']);

    // Posts
    Route::post('/posts',        [PostController::class, 'store']);
    Route::get('/posts/{post}',  [PostController::class, 'show']);
    Route::put('/posts/{post}',  [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Comments
    Route::post('/posts/{post}/comments',   [CommentController::class, 'store']);
    Route::put('/comments/{comment}',       [CommentController::class, 'update']);
    Route::delete('/comments/{comment}',    [CommentController::class, 'destroy']);

    // Likes
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle']);
    Route::post('/comments/{comment}/like', [\App\Http\Controllers\Api\CommentLikeController::class, 'toggle']);

    // Follow
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle']);

    // Profile
    Route::get('/users/{username}', [ProfileController::class, 'show'])->where('username', '[a-zA-Z0-9_-]+');
    Route::put('/profile',          [ProfileController::class, 'update']);

});
