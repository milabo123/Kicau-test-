<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * GET /api/search?q={query}
     * Menerima query string dari frontend dan mencari Users dan Posts bersangkutan.
     */
    public function index(Request $request)
    {
        $q = $request->input('q', '');
        
        $users = [];
        $postsData = ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0];

        if (!empty(trim($q))) {
            // Jika pencarian menggunakan #hastag, kita tidak perlu mencari User yang namanya ada #, 
            // fokus ke Post
            $isHashtag = str_starts_with($q, '#');

            if (!$isHashtag) {
                // Cari Users (limit 15 untuk mencegah payload terlalu besar)
                $users = User::where('name', 'LIKE', "%{$q}%")
                             ->orWhere('username', 'LIKE', "%{$q}%")
                             ->limit(15)
                             ->get()
                             ->map(function ($u) {
                                 return [
                                     'id'         => $u->id,
                                     'name'       => $u->name,
                                     'username'   => $u->username,
                                     'avatar_url' => $u->avatar_url,
                                     'bio'        => $u->bio,
                                 ];
                             });
            }

            // Cari Posts di mana body memuat string query (termasuk #hashtag string opsional)
            $postsQuery = Post::with(['user', 'comments.user', 'comments.likes', 'comments.replies.user', 'comments.replies.likes', 'likes'])
                              ->where('body', 'LIKE', "%{$q}%")
                              ->latest();

            $postsPaginator = $postsQuery->paginate(15);
            
            // Re-use fungsi transformPaginate dari PostController untuk konsistensi API
            $postController = app(PostController::class);
            $postsData = $postController->transformPaginate($postsPaginator, $request->user());
        }

        return response()->json([
            'users' => $users,
            'posts' => $postsData,
        ]);
    }
}
