<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected ApiService $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Tampilkan halaman hasil pencarian.
     */
    public function index(Request $request)
    {
        $q = $request->query('q', '');
        $page = $request->query('page', 1);

        $users = [];
        $postsData = ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0];

        if (!empty(trim($q))) {
            $response = $this->api->search($q, $page);

            if ($response->successful()) {
                $data = $response->json();
                $users = $data['users'] ?? [];
                $postsData = $data['posts'] ?? $postsData;
            } else {
                return back()->with('error', 'Gagal memuat hasil pencarian.');
            }
        }

        return view('search.index', [
            'title' => 'Pencarian',
            'q' => $q,
            'users' => $users,
            'postsData' => $postsData,
        ]);
    }
}
