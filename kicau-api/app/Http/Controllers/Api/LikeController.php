<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class LikeController
 * Menangani endpoint API seputar interaksi "Menyukai" (Like) sebuah postingan.
 */
class LikeController extends Controller
{
    /**
     * POST /api/posts/{post}/like
     * Melakukan toggle aksi "Like" pada sebuah kicauan. Jika user sudah menyukai, maka akan dihapus (unlike).
     * 
     * @param Post $post Model Route binding untuk target kicauan yang akan di-like/unlike.
     * @return \Illuminate\Http\JsonResponse Mengembalikan status JSON berupa hasil toggle `liked` dan jumlah total 'likes_count'.
     */
    public function toggle(Post $post)
    {
        // Mendapatkan instance user saat ini dari token
        $user = Auth::user();
        
        // Memeriksa apakah baris data mengenai relasi 'Like' antara pengguna ini pada postingan ini sudah ada di basis data
        $like = Like::where('post_id', $post->id)->where('user_id', $user->id)->first();

        if ($like) {
            // Jika ada, pengguna menginginkan Unlike (menghapus status 'suka')
            $like->delete();
            $liked = false;
        } else {
            // Jika belum ada, memunculkan instance data Like baru ke database
            Like::create(['post_id' => $post->id, 'user_id' => $user->id]);
            $liked = true;

            // Kirim notifikasi ke pemilik pos (jangan notifikasi diri sendiri)
            if ($post->user_id !== $user->id) {
                \App\Models\Notification::create([
                    'user_id'         => $post->user_id,
                    'actor_id'        => $user->id,
                    'type'            => 'like',
                    'notifiable_type' => Post::class,
                    'notifiable_id'   => $post->id,
                    'message'         => $user->name . ' menyukai kicauan kamu.',
                ]);
            }
        }

        // Mengembalikan respons dengan format array sederhana untuk segera dimuat ulang oleh Javascript/AJAX client
        return response()->json([
            'liked' => $liked,
            'count' => $post->likes()->count(),
        ]);
    }
}
