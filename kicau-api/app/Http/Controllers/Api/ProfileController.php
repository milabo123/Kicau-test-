<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Class ProfileController
 * Memungkinkan untuk memuat isi halaman dan postingan spesifik profil seorang pengguna 
 * serta fitur khusus mutasi profil pengguna itu sendiri (seperti ganti identitas, maupun simpan photo bio avatar).
 */
class ProfileController extends Controller
{
    /**
     * GET /api/users/{username}
     * Menunjukkan spesifikasi riwayat profil. 
     * 
     * @param Request $request Req instance.
     * @param string $username Unique handler pengguna '@username'.
     * @return \Illuminate\Http\JsonResponse Output JSON dari user + array Pos Paginasi
     */
    public function show(Request $request, string $username)
    {
        // Temukan pengguna atau gagalkan dengan menghasilkan respons 404
        $user           = User::where('username', $username)->firstOrFail();
        // Muat segala daftar postingan yang telah dilontarkan/dirilis pengguna bersangkutan
        $posts          = $user->posts()->with(['comments', 'likes'])->latest()->paginate(12);
        $isFollowing    = $request->user() ? $request->user()->isFollowing($user) : false;
        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();

        return response()->json([
            'user' => [
                'id'              => $user->id,
                'name'            => $user->name,
                'username'        => $user->username,
                'bio'             => $user->bio,
                'avatar_url'      => $user->avatar_url,
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
                'is_following'    => $isFollowing,
                'created_at'      => $user->created_at,
            ],
            'posts' => [
                'data'         => $posts->getCollection()->map(fn($p) => [
                    'id'             => $p->id,
                    'body'           => $p->body,
                    'media_url'      => $p->media_url,
                    'media_type'     => $p->media_type,
                    'created_at'     => $p->created_at,
                    'likes_count'    => $p->likes->count(),
                    'comments_count' => $p->comments->count(),
                ])->values(),
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'total'        => $posts->total(),
            ],
        ]);
    }

    /**
     * PUT /api/profile
     * Mengelola dan menyetujui mutasi Profil Pengguna terkait (User setting).
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi dan rule `Rule::unique` untuk mencegah perubahan nama user bersitegang dengan akun yg telah eksis
        $request->validate([
            'name'     => 'required|string|max:100',
            'username' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'bio'      => 'nullable|string|max:160',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Mekanisme simpan lampiran foto pengganti avatar
        if ($request->hasFile('avatar')) {
            // Jika ada avatar historis sebelumnya, hapus dahulu secara fisik dari penyimpan agar tidak memenuhi kuota
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            // Letakkan path terkirim ke penyimpanan `avatars/ID_USER/`
            $avatarPath  = $request->file('avatar')->store('avatars/' . $user->id, 'public');
            $user->avatar = $avatarPath;
        }

        $user->name     = $request->name;
        $user->username = $request->username;
        $user->bio      = $request->bio;
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui!',
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'bio'        => $user->bio,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }
}
