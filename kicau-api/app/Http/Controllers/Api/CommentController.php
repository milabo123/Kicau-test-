<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class CommentController
 * Mengelola endpoint API untuk menulis dan menghapus komentar pada postingan/kicauan pengguna.
 */
class CommentController extends Controller
{
    /**
     * POST /api/posts/{post}/comments
     * Membuat komentar baru yang berelasi dengan sebuah Post/Kicauan.
     * 
     * @param Request $request Detail request data komentar (parameter `body`).
     * @param Post $post Model Route Binding otomatis untuk mencari postingan berdasarkan ID.
     * @return \Illuminate\Http\JsonResponse Status keberhasilan berserta object balasan berisikan komentar dan informasi pengguna.
     */
    public function store(Request $request, Post $post)
    {
        // Validasi panjang dan format teks body untuk komentar
        $request->validate([
            'body' => 'required|string|max:300',
        ]);

        // Simpan komentar di basis data terikat pada ID Post tersebut dan ID User dari token saat ini
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'body'    => $request->body,
        ]);

        // Gunakan eager loading untuk segera mengambil data pengguna terkait yang membuat komentar ini
        // hal ini mencegah N+1 problem ketika dirender kembali dalam antarmuka UI
        $comment->load('user');

        return response()->json([
            'message' => 'Komentar berhasil ditambahkan.',
            'comment' => [
                'id'         => $comment->id,
                'body'       => $comment->body,
                'created_at' => $comment->created_at,
                'user' => [
                    'id'         => $comment->user->id,
                    'name'       => $comment->user->name,
                    'username'   => $comment->user->username,
                    'avatar_url' => $comment->user->avatar_url,
                ],
            ],
        ], 201);
    }

    /**
     * DELETE /api/comments/{comment}
     * Menghapus sebuah komentar dari basis data. Hanya pemilik dari komentar yang diberikan izin hapus.
     * 
     * @param Comment $comment Model Route Binding untuk mencari Model Comment terkait.
     * @return \Illuminate\Http\JsonResponse JSON response success message.
     */
    public function destroy(Comment $comment)
    {
        // Verifikasi bahwa user yang diotentikasi saat ini adalah pengguna yang benar-benar menciptakan komentar ini
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan menghapus komentar ini.'], 403);
        }

        // Hapus records entri komentar dari table `comments`
        $comment->delete();

        return response()->json(['message' => 'Komentar berhasil dihapus.']);
    }
}
