<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

/**
 * Class CommentController
 * Controller antarmuka (frontend) yang memproses form input komentar dari view 
 * dan meneruskannya ke layanan Kicau API.
 */
class CommentController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /**
     * POST /posts/{id}/comments
     * Mengirimkan data komentar ke backend API.
     * 
     * @param Request $request Payload body komentar dari Form HTML atau fetch AJAX.
     * @param int $id ID dari postingan yang dikomentari.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request, int $id)
    {
        // Validasi secara lokal di sisi form frontend sebelum dikirimkan ke API backend 
        // guna menghemat bandwidth jika isian kosong.
        $request->validate([
            'body' => 'required|string|max:300',
        ]);

        // Teruskan data ke API server melalui ApiService
        $response = $this->api->createComment($id, $request->body);

        if ($response->failed()) {
            // Jika ada indikasi bahwa klien menggunakan JS Fetch (AJAX), kembalikan raw data berbentuk JSON balasan error API
            if ($request->expectsJson()) {
                return response()->json(['error' => $response->json('message', 'Gagal menambahkan komentar.')], 400);
            }
            // Fallback: Jika pengguna tanpa JS (atau fail graceful), kembalikan ke halaman sebelumnya berserta session Flash Error
            return back()->with('error', $response->json('message', 'Gagal menambahkan komentar.'));
        }

        if ($request->expectsJson()) {
            // Untuk skenario AJAX, balas dengan struktur lengkap dari entitas komentar HTTP 201 
            // agar sanggup dirender seketika secara optimistik oleh JS
            return response()->json($response->json());
        }

        // Return standar untuk render server-side
        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    /**
     * DELETE /comments/{id}
     * Menghapus sebuah komentar pengguna dengan mengirimkan token otorisasi ke API.
     * 
     * @param int $id ID Komentar yang akan diterminasi.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $response = $this->api->deleteComment($id);

        if ($response->failed()) {
            return back()->with('error', $response->json('message', 'Gagal menghapus komentar.'));
        }

        return back()->with('success', 'Komentar berhasil dihapus.');
    }
}
