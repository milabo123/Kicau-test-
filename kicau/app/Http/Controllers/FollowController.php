<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

/**
 * Class FollowController
 * Penghubung frontend dengan service API untuk fitur mengikuti profil (toggle follow) pengguna lain.
 */
class FollowController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /**
     * POST /users/{id}/follow
     * Menjalankan fungsi Toggle Follow (jika sudah follow jadi unfollow, jika belum jadi follow)
     * via jembatan API.
     * 
     * @param int $id User ID target
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(int $id)
    {
        // Lakukan pemanggilan cURL ke layanan API Kicau backend
        $response = $this->api->toggleFollow($id);

        if ($response->failed()) {
            // Tampilkan SweetAlert error jika user gagal di-follow (contohnya mem-follow diri sendiri)
            return back()->with('error', $response->json('message', 'Gagal memproses follow.'));
        }

        // Jika sukses tambahkan notifikasi yang akan dibaca oleh app.blade.php
        return back()->with('success', $response->json('message'));
    }
}
