<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root to feed (authenticated) or login
Route::get('/', function () {
    return session('api_token') ? redirect()->route('feed.index') : redirect()->route('login');
});

Route::middleware(['check.api.token'])->group(function () {

    // Feed / Timeline
    Route::get('/feed', [PostController::class, 'index'])->name('feed.index');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Comments
    Route::post('/posts/{id}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Likes
    Route::post('/posts/{id}/like', [LikeController::class, 'toggle'])->name('posts.like');

    // Follow
    Route::post('/users/{id}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/@{username}', [ProfileController::class, 'show'])->name('profile.show');
});

require __DIR__ . '/auth.php';

