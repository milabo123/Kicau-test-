<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Class PostController
 * Mengelola fitur inti dari manipulasi data Postingan/Kicauan termasuk menampilkan feed linimasa (timeline), 
 * membuat postingan dengan media, mengambil satu pos spesifik, dan menghapus postingan.
 */
class PostController extends Controller
{
    /**
     * GET /api/feed
     * Menghasilkan Timeline linimasa (feed), berisi gabungan pos dari user yang akunnya ia ikuti 
     * serta kicauannya sendiri secara kronologis dari yang terbaru.
     * 
     * @param Request $request Detail HTTP request dan auth payload.
     * @return \Illuminate\Http\JsonResponse Hasil respons yang telah dipaginasi
     */
    public function feed(Request $request)
    {
        $user = $request->user();

        // Mengambil daftar ID teman-teman/user lain yang mana akun kita menjadi follower-nya
        $followingIds   = $user->following()->pluck('following_id')->toArray();
        // Menambahkan diri sendiri ke daftar ID tersebut sehingga kicau kita dapat terlihat di timeline sendiri
        $followingIds[] = $user->id;

        // Eloquent query memfilter post mana saja berdasarkan himpunan followingIds 
        // dengan eager-loading relasi dan menerapkan sistem paginasi tiap 15 rekaman per-halaman.
        $posts = Post::with(['user', 'comments.user', 'comments.likes', 'comments.replies.user', 'comments.replies.likes', 'likes'])
            ->whereIn('user_id', $followingIds)
            ->latest() // Mengurutkan dari terbaru (kolom created_at DESC)
            ->paginate(15);

        return response()->json($this->transformPaginate($posts, $user));
    }

    /**
     * POST /api/posts
     * Membuat serta mempublikasikan kicauan (post) baru yang mengizinkan juga lampiran unggahan gambar/video.
     * 
     * @param Request $request The incoming form payload with multipart form-data untuk file media.
     * @return \Illuminate\Http\JsonResponse Objek JSON pos tersebut dengan status sukses
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'body'  => 'nullable|string|max:500',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm|max:102400',
        ]);

        if (!$request->body && !$request->hasFile('media')) {
            // Harus ada minimal salah satu dari `teks konten` atau `media unggahan` untuk membentuk pesan yang sah
            return response()->json(['message' => 'Post harus berisi teks atau media.'], 422);
        }

        $mediaPath = null;
        $mediaType = null;

        if ($request->hasFile('media')) {
            $file      = $request->file('media');
            $mime      = $file->getMimeType();
            // Analisis awal prefix mimetype menentukan pemutaran browser (tipe video atau image fallback)
            $mediaType = str_starts_with($mime, 'video/') ? 'video' : 'image';
            // Unggah file menuju direktori Storage lokal/S3 `posts/{user_id}`
            $mediaPath = $file->store('posts/' . Auth::id(), 'public');
        }

        // Persisten instans model ke basis data SQL
        $post = Post::create([
            'user_id'    => Auth::id(),
            'body'       => $request->body,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
        ]);

        // Eager-loading kembali semua relasi yang ada sebelum di-return di response API 
        $post->load(['user', 'comments.user', 'comments.likes', 'comments.replies.user', 'comments.replies.likes', 'likes']);

        return response()->json([
            'message' => 'Kicauan berhasil dikirim! 🐦',
            'post'    => $this->transformPost($post, Auth::user()),
        ], 201);
    }

    /**
     * GET /api/posts/{post}
     * Menampilkan keseluruhan isi sebuah pesan atau kicauan secara mendetail (berserta komentar di dalam)
     * 
     * @param Request $request
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Post $post)
    {
        $post->load(['user', 'comments.user', 'comments.likes', 'comments.replies.user', 'comments.replies.likes', 'likes']);
        return response()->json($this->transformPost($post, $request->user()));
    }

    /**
     * DELETE /api/posts/{post}
     * Menghapus Post/Kicauan berserta file medianya ke Storage, apabila user tersebut memiliki otorisasi kepemilikan.
     * 
     * @param Request $request
     * @param Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan mengedit kicauan ini.'], 403);
        }

        $request->validate([
            'body' => 'nullable|string|max:500',
        ]);

        if (!$request->body && !$post->media_path) {
            return response()->json(['message' => 'Post harus berisi teks atau media.'], 422);
        }

        $post->body = $request->body;
        $post->save();

        $post->load(['user', 'comments.user', 'comments.likes', 'comments.replies.user', 'comments.replies.likes', 'likes']);

        return response()->json([
            'message' => 'Kicauan berhasil diperbarui.',
            'post'    => $this->transformPost($post, Auth::user()),
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan menghapus kicauan ini.'], 403);
        }

        // Hapus juga file statis yang ada di disk sistem untuk mencegah memory/disk bloating
        if ($post->media_path) {
            Storage::disk('public')->delete($post->media_path);
        }

        $post->delete();

        return response()->json(['message' => 'Kicauan berhasil dihapus.']);
    }

    // ─────────────────── Helpers ───────────────────

    /**
     * Fungsi helper perantara yang men-sanitasi objek Post dan relasinya ke mode map standar API Kicau.
     * Menghindari properti rahasia ikut ter-ekspos ke antarmuka aplikasi.
     * 
     * @param Post $post Data entri Pos
     * @param object|null $authUser Referensi obj user API yang sedang berkunjung agar tahu parameter `is_liked` personal.
     * @return array Custom hash map standar
     */
    private function transformPost(Post $post, ?object $authUser): array
    {
        return [
            'id'           => $post->id,
            'body'         => $post->body,
            'media_url'    => $post->media_url,
            'media_type'   => $post->media_type,
            'created_at'   => $post->created_at,
            'is_liked'     => $authUser ? $post->isLikedBy($authUser) : false,
            'likes_count'  => $post->likes->count(),
            'comments_count' => $post->comments->count(),
            'user' => [
                'id'         => $post->user->id,
                'name'       => $post->user->name,
                'username'   => $post->user->username,
                'avatar_url' => $post->user->avatar_url,
            ],
            'comments' => $post->comments->whereNull('parent_id')->map(function($c) use ($authUser) {
                return $this->transformComment($c, $authUser);
            })->values(),
        ];
    }

    private function transformComment($comment, $authUser): array
    {
        return [
            'id'           => $comment->id,
            'parent_id'    => $comment->parent_id,
            'body'         => $comment->body,
            'created_at'   => $comment->created_at,
            'is_liked'     => $authUser ? $comment->isLikedBy($authUser) : false,
            'likes_count'  => $comment->likes ? $comment->likes->count() : 0,
            'user' => [
                'id'         => $comment->user->id,
                'name'       => $comment->user->name,
                'username'   => $comment->user->username,
                'avatar_url' => $comment->user->avatar_url,
            ],
            'replies' => $comment->replies ? $comment->replies->map(function($r) use ($authUser) {
                return $this->transformComment($r, $authUser);
            })->values() : [],
        ];
    }

    /**
     * Fungsi Helper mengubah Format `LengthAwarePaginator` standar Eloquent
     * sehingga payload response API formatnya dapat ditentukan mandiri.
     * 
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginator Objek paginator Laravel 
     * @param object|null $authUser Referensi objek authorisasi
     * @return array
     */
    private function transformPaginate($paginator, ?object $authUser): array
    {
        return [
            'data'         => $paginator->getCollection()->map(fn($p) => $this->transformPost($p, $authUser))->values(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];
    }
}
