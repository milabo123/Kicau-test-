<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
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
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'index'])->name('search.index');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Comments
    Route::post('/posts/{id}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{id}/like', [CommentController::class, 'like'])->name('comments.like');

    // Likes
    Route::post('/posts/{id}/like', [LikeController::class, 'toggle'])->name('posts.like');

    // Follow
    Route::post('/users/{id}/follow', [FollowController::class, 'toggle'])->name('users.follow');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');

    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/@{username}', [ProfileController::class, 'show'])->name('profile.show');
});

require __DIR__ . '/auth.php';

