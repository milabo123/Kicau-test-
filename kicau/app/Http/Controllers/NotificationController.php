<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

/**
 * Class NotificationController
 * Mengelola halaman dan AJAX endpoint notifikasi pada sisi frontend.
 */
class NotificationController extends Controller
{
    protected ApiService $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /notifications
     * Menampilkan halaman daftar semua notifikasi pengguna.
     */
    public function index(Request $request)
    {
        $page     = $request->get('page', 1);
        $response = $this->api->getNotifications($page);
        $data     = $response->json();

        return view('notifications.index', [
            'notifications' => $data['notifications']['data'] ?? [],
            'unreadCount'   => $data['unread_count'] ?? 0,
            'pagination'    => $data['notifications'] ?? [],
            'title'         => 'Notifikasi',
        ]);
    }

    /**
     * POST /notifications/read
     * Menandai semua notifikasi sebagai dibaca, lalu redirect kembali.
     */
    public function markAllRead()
    {
        $this->api->markAllNotificationsRead();
        return back()->with('success', 'Semua notifikasi telah dibaca.');
    }

    /**
     * GET /notifications/unread-count (AJAX)
     * Mengembalikan jumlah notifikasi belum dibaca untuk badge di navbar.
     */
    public function unreadCount()
    {
        $response = $this->api->getNotifications();
        return response()->json([
            'unread_count' => $response->json('unread_count') ?? 0,
        ]);
    }
}
