<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

/**
 * Class ProfileController
 * Controller antarmuka (frontend) guna menyajikan profil publik user Kicau maupun fitur
 * pemutakhiran pengaturan profil bagi pemilik sesi akun saat ini.
 */
class ProfileController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /**
     * GET /@{username}
     * Render layout muka profil pengunjung atau personal berisikan timeline statis (milik 1 pengguna).
     * 
     * @param Request $request
     * @param string $username Parameter segment url
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Request $request, string $username)
    {
        $response = $this->api->getProfile($username);

        if ($response->notFound()) {
            abort(404); // Jika JSON API menegaskan 404 (Objek Entitas tidak ditemukan), alihkan user ke UI Laravel 404 Not Found
        }

        if ($response->failed()) {
            return redirect()->route('feed.index')->with('error', 'Gagal memuat profil.');
        }

        // Tembakan sukses, mengekstrak data
        $data = $response->json();

        // Menginjeksikan pecahan data API yang berbeda ke dalam variabel terpisah di struktur Blade UI 
        return view('profile.show', [
            'user'           => $data['user'],
            'postsData'      => $data['posts'],
            'isFollowing'    => $data['user']['is_following'],
            'followersCount' => $data['user']['followers_count'],
            'followingCount' => $data['user']['following_count'],
        ]);
    }

    /**
     * GET /profile/edit
     * Memuat kanvas form HTML yang menampilkan konfigurasi pengaturan akun.
     * Hanya berlaku bagi user yang terotentikasi.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        // Ambil info session token (cache login sementara) dari perangkat internal untuk prepopulasi atribut profil (nama, bio, email)
        return view('profile.edit', ['user' => $request->session()->get('user', [])]);
    }

    /**
     * PUT /profile
     * Prosesor mutasi pengaturan pada Profil di sisi peramban web form-data.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Verifikasi awal pada level view middleware untuk mencegah input asal-asalan yang merusak format form
        $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:30|alpha_dash',
            'bio'      => 'nullable|string|max:160',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $avatarPath         = null;
        $avatarOriginalName = null;
        $avatarMime         = null;

        if ($request->hasFile('avatar')) {
            $file               = $request->file('avatar');
            // Mengemas variabel lampiran File ke dalam bentuk RAW parameter yang disiapkan untuk fungsi spoof HTTP Multipart
            $avatarPath         = $file->getPathname();
            $avatarOriginalName = $file->getClientOriginalName();
            $avatarMime         = $file->getMimeType();
        }

        // Tembak REST API
        $response = $this->api->updateProfile(
            [
                'name'     => $request->name,
                'username' => $request->username,
                'bio'      => $request->bio ?? '',
            ],
            $avatarPath,
            $avatarOriginalName,
            $avatarMime
        );

        if ($response->failed()) {
            // Jika ada field isian yang tidak kompatibel server-side, map pesan API tersebut balik ke Flash 'Errors' Bag 
            $errors = $response->json('errors', []);
            return back()
                ->withInput()
                ->withErrors($errors ?: ['name' => $response->json('message', 'Gagal memperbarui profil.')]);
        }

        // Jika integrasi berhasil, otomatis Update Session User Data agar avatar yang terlihat di navbar aplikasi ikutan berubah (Realtime Feeling) Tanpa Logout
        $updatedUser = $response->json('user');
        $request->session()->put('user', array_merge($request->session()->get('user', []), $updatedUser));

        return back()->with('success', 'Profil berhasil diperbarui!');
    }
}
