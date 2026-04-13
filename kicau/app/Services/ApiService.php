<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

/**
 * Layanan terpusat untuk komunikasi dengan Kicau REST API (kicau-api).
 *
 * Semua request otomatis membawa:
 *  - Header Accept: application/json
 *  - Bearer token dari session (jika sudah login)
 */
class ApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.kicau_api.base_url', 'http://localhost:8001/api'), '/');
    }

    // ──────────────────────────── Auth ────────────────────────────

    public function login(string $email, string $password): Response
    {
        return $this->publicRequest()->post("{$this->baseUrl}/login", compact('email', 'password'));
    }

    public function register(array $data): Response
    {
        return $this->publicRequest()->post("{$this->baseUrl}/register", $data);
    }

    public function logout(): Response
    {
        return $this->authRequest()->post("{$this->baseUrl}/logout");
    }

    // ──────────────────────────── Feed ────────────────────────────

    /**
     * Mengambil daftar kicauan untuk timeline utama (Feed).
     * 
     * @param int $page Halaman paginasi keberapa yang sedang diminta
     * @return Response Objek respons dari backend API kicau-api
     */
    public function getFeed(int $page = 1): Response
    {
        return $this->authRequest()->get("{$this->baseUrl}/feed", ['page' => $page]);
    }

    /**
     * Mencari profil pengguna dan kicauan berdasarkan keyword atau #hashtag.
     */
    public function search(string $query, int $page = 1): Response
    {
        return $this->authRequest()->get("{$this->baseUrl}/search", [
            'q'    => $query,
            'page' => $page
        ]);
    }

    // ──────────────────────────── Posts ────────────────────────────

    /**
     * Memposting (Store) kicauan baru pengguna.
     * Mengotomasikan deteksi apabila pengguna menyertakan lampiran file multimedia (foto/video).
     * 
     * @param array $data Body JSON array (teks kicauan)
     * @param string|null $mediaPath Root path file yang tersimpan sementara (tmp) dari form
     * @param string|null $mediaOriginalName Nama asli file dari sistem pengguna
     * @param string|null $mediaMime Mime type file (image/jpeg, video/mp4, dsb.)
     * @return Response
     */
    public function createPost(array $data, ?string $mediaPath = null, ?string $mediaOriginalName = null, ?string $mediaMime = null): Response
    {
        $req = $this->authRequest();

        // Jika terdapat file lampiran fisik yang sah dikirim dari browser
        if ($mediaPath && file_exists($mediaPath)) {
            // Gunakan metode multipart Form request (attach) agar file biner terbaca oleh backend
            return $req->attach('media', fopen($mediaPath, 'r'), $mediaOriginalName ?? 'media', ['Content-Type' => $mediaMime])
                ->post("{$this->baseUrl}/posts", $data);
        }

        // Jika sekadar teks tulisan biasa tanpa ekstra file, jalankan POST reguler
        return $req->post("{$this->baseUrl}/posts", $data);
    }

    public function getPost(int $id): Response
    {
        return $this->authRequest()->get("{$this->baseUrl}/posts/{$id}");
    }

    public function updatePost(int $id, string $body): Response
    {
        return $this->authRequest()->put("{$this->baseUrl}/posts/{$id}", compact('body'));
    }

    public function deletePost(int $id): Response
    {
        return $this->authRequest()->delete("{$this->baseUrl}/posts/{$id}");
    }

    // ──────────────────────────── Comments ────────────────────────────

    public function createComment(int $postId, string $body, ?int $parentId = null): Response
    {
        return $this->authRequest()->post("{$this->baseUrl}/posts/{$postId}/comments", [
            'body'      => $body,
            'parent_id' => $parentId,
        ]);
    }

    public function updateComment(int $commentId, string $body): Response
    {
        return $this->authRequest()->put("{$this->baseUrl}/comments/{$commentId}", compact('body'));
    }

    public function deleteComment(int $commentId): Response
    {
        return $this->authRequest()->delete("{$this->baseUrl}/comments/{$commentId}");
    }

    // ──────────────────────────── Likes ────────────────────────────

    public function toggleLike(int $postId): Response
    {
        return $this->authRequest()->post("{$this->baseUrl}/posts/{$postId}/like");
    }

    public function toggleCommentLike(int $commentId): Response
    {
        return $this->authRequest()->post("{$this->baseUrl}/comments/{$commentId}/like");
    }

    // ──────────────────────────── Follow ────────────────────────────

    public function toggleFollow(int $userId): Response
    {
        return $this->authRequest()->post("{$this->baseUrl}/users/{$userId}/follow");
    }

    // ──────────────────────────── Profile ────────────────────────────

    /**
     * Mengambil detail profil spesifik beserta daftar postingan-nya.
     */
    public function getProfile(string $username, string $tab = 'posts', int $page = 1): Response
    {
        return $this->authRequest()->get("{$this->baseUrl}/users/{$username}", [
            'tab'  => $tab,
            'page' => $page,
        ]);
    }

    /**
     * Menyimpan perisakan/update profil pengguna (username, bio, foto avatar).
     */
    public function updateProfile(array $data, ?string $avatarPath = null, ?string $avatarOriginalName = null, ?string $avatarMime = null): Response
    {
        // Standar HTTP mengatur put tidak kompatibel di PHP form-data secara murni, oleh karenanya
        // PUT dengan file lampiran harus pakai multipart POST + method spoofing (_)
        $req = $this->authRequest();

        if ($avatarPath && file_exists($avatarPath)) {
            // Jika merubah photo profile, injeksi token spoofing `_method=PUT`
            return $req->attach('avatar', fopen($avatarPath, 'r'), $avatarOriginalName ?? 'avatar.jpg', ['Content-Type' => $avatarMime])
                ->post("{$this->baseUrl}/profile", array_merge($data, ['_method' => 'PUT']));
        }

        // Jika sekadar merubah informasi teks, PUT langsung didukung oleh Laravel Http Facade
        return $req->put("{$this->baseUrl}/profile", $data);
    }

    // ──────────────────────────── Internal Helpers ────────────────────────────

    /** Request tanpa autentikasi */
    private function publicRequest()
    {
        return Http::acceptJson();
    }

    /** Request dengan Bearer token dari session */
    private function authRequest()
    {
        $token = Session::get('api_token');
        return Http::acceptJson()->withToken($token ?? '');
    }
}
