<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

/**
 * Class LikeController
 * Menangani permintaan frontend untuk menekan tombol "Suka" (Like / Heart) pada suatu kicauan.
 */
class LikeController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /**
     * POST /posts/{id}/like
     * Melakukan aksi toggle like/unlike dengan menembak route internal ApiService.
     * 
     * @param Request $request Memerlukan deteksi tipe konteks klien (AJAX vs traditional form submission)
     * @param int $id ID postingan bersangkutan
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request, int $id)
    {
        $response = $this->api->toggleLike($id);

        if ($response->failed()) {
            return back()->with('error', 'Gagal memproses like.');
        }

        // Jika request bersumber dari script JS Fetch/AJAX, tolak redirect, dan mutlak kembalikan object JSON
        // sehingga frontend tidak termuat ulang (no full reload request).
        if ($request->expectsJson()) {
            return response()->json($response->json());
        }

        // Fallback untuk aksi like saat JS dinonaktifkan di sisi antarmuka pengguna
        return back();
    }
}
