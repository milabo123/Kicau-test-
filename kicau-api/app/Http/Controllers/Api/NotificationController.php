<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class NotificationController
 * Menangani endpoint API untuk mengambil, membaca, dan mengelola notifikasi pengguna.
 */
class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     * Mengambil daftar notifikasi pengguna yang terautentikasi beserta jumlah yang belum dibaca.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->with('actor:id,name,username,avatar')
            ->latest()
            ->paginate(20);

        // Transformasi data untuk menambahkan avatar_url pada actor
        $notifications->getCollection()->transform(function ($notif) {
            if ($notif->actor) {
                $notif->actor->avatar_url = $notif->actor->avatar_url;
            }
            return $notif;
        });

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => Notification::where('user_id', Auth::id())->unread()->count(),
        ]);
    }

    /**
     * POST /api/notifications/read
     * Menandai semua notifikasi pengguna sebagai sudah dibaca.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Semua notifikasi telah dibaca.']);
    }

    /**
     * POST /api/notifications/{notification}/read
     * Menandai satu notifikasi spesifik sebagai sudah dibaca.
     *
     * @param Notification $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifikasi telah dibaca.']);
    }
}
