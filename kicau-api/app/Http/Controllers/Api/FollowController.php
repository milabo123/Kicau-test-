<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class FollowController
 * Menangani endpoint API seputar interaksi hubungan followers / following antar user di platform.
 */
class FollowController extends Controller
{
    /**
     * POST /api/users/{user}/follow
     * Toggle fitur "Mengikuti": jika belum mengikuti maka ikuti, dan jika sudah ikuti maka putuskan (unfollow).
     * 
     * @param User $user Model Route Binding dari target akun User yang ingin diikuti/unfollow.
     * @return \Illuminate\Http\JsonResponse Menyatakan status saat ini, dan pesan berhasil.
     */
    public function toggle(User $user)
    {
        // Mengidentifikasi ID pengguna yang terotentikasi dan melakukan request API saat ini
        $currentUser = Auth::user();

        // Pencegahan aksi mem-follow akun sendiri
        if ($currentUser->id === $user->id) {
            return response()->json(['message' => 'Kamu tidak bisa mengikuti dirimu sendiri.'], 422);
        }

        // Melakukan kueri untuk mengecek apakan relasi 'mengiutii' ini memang sudah terjadi atau ada di database
        $follow = Follow::where('follower_id', $currentUser->id)
            ->where('following_id', $user->id)
            ->first();

        if ($follow) {
            // Relasi telah ditemukan: Pengguna ingin membatalkan status follow (unfollow)
            $follow->delete();
            $isFollowing = false;
            $message     = 'Kamu berhenti mengikuti @' . $user->username;
        } else {
            // Relasi belum ditemukan: Pengguna ingin menambahkan follower untuk target ini
            Follow::create([
                'follower_id'  => $currentUser->id,
                'following_id' => $user->id,
            ]);
            $isFollowing = true;
            $message     = 'Kamu sekarang mengikuti @' . $user->username . '!';

            // Kirim notifikasi ke pengguna yang di-follow
            \App\Models\Notification::create([
                'user_id'         => $user->id,
                'actor_id'        => $currentUser->id,
                'type'            => 'follow',
                'notifiable_type' => \App\Models\User::class,
                'notifiable_id'   => $currentUser->id,
                'message'         => $currentUser->name . ' mulai mengikuti kamu.',
            ]);
        }

        return response()->json([
            'message'      => $message,
            'is_following' => $isFollowing,
        ]);
    }
}
