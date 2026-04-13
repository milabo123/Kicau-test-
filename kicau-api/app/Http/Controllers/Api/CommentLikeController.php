<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentLikeController extends Controller
{
    public function toggle(Comment $comment)
    {
        $user = Auth::user();
        
        $like = CommentLike::where('comment_id', $comment->id)->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            CommentLike::create(['comment_id' => $comment->id, 'user_id' => $user->id]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $comment->likes()->count(),
        ]);
    }
}
