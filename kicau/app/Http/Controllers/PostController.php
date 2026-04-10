<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

/**
 * Class PostController
 * Controller antarmuka (frontend) utama untuk halam pakan timeline (beranda kicau), 
 * antarmuka pembuatan postingan, detail postingan, dan penghapusannya.
 */
class PostController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /**
     * GET /feed
     * Halaman beranda utama yang menyajikan postingan dalam bentuk timeline.
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $page     = $request->get('page', 1);
        
        // Panggil endpoint getFeed HTTP Request ke server API (kicau-api) melalui service internal kita
        $response = $this->api->getFeed($page);

        // Apabila token invalid atau terhapus (expired token / 401 Unauthorized logiknya), 
        // fallback dan lempar user kembali mendarat di halaman gerbang masuk
        if ($response->failed()) {
            return redirect()->route('login')->with('error', 'Sesi habis, silakan login kembali.');
        }

        // Parsing JSON mentah dari HTTP Body Content API response 
        $data = $response->json();

        // Render blade view dengan lemparan kumpulan Data (Array) Post & Pagination
        return view('feed.index', [
            'postsData' => $data,
        ]);
    }

    /**
     * POST /posts
     * Pengolahan form input upload Kicauan baru dari pengguna untuk direlay (diteruskan) ke layanan API backend.
     * 
     * @param Request $request Payload berupa form submission text beserta kemungkinan attachment Mime-type
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Standar validasi file ukuran maks 100MB di lapisan UI sebelum menjejali bandwidth server API
        $request->validate([
            'body'  => 'nullable|string|max:500',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm|max:102400',
        ]);

        // Pastikan tidak mengirim blank data secara tidak sengaja
        if (!$request->body && !$request->hasFile('media')) {
            return back()->withErrors(['body' => 'Post harus berisi teks atau media.'])->withInput();
        }

        $mediaPath         = null;
        $mediaOriginalName = null;
        $mediaMime         = null;

        if ($request->hasFile('media')) {
            $file              = $request->file('media');
            // Dapatkan property fisik untuk mensimulasikan ulang upload stream multipart form-data di belakang layar (server to server)
            $mediaPath         = $file->getPathname();
            $mediaOriginalName = $file->getClientOriginalName();
            $mediaMime         = $file->getMimeType();
        }

        // Jalankan perintah post HTTP perantara yang telah distandardisasi pada class ApiService
        $response = $this->api->createPost(
            ['body' => $request->body],
            $mediaPath,
            $mediaOriginalName,
            $mediaMime
        );

        if ($response->failed()) {
            return back()->withErrors(['body' => $response->json('message', 'Gagal mengirim kicauan.')])->withInput();
        }

        return back();
    }

    /**
     * GET /posts/{id}
     * Menampilkan antarmuka penelusuran satu kicau yang lebih detail.
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        // Minta detail objek post dari sistem backend
        $response = $this->api->getPost($id);

        if ($response->failed()) {
            abort(404);
        }

        // Parsing map attribute JSON lalu pasangkan bersama komponen UI View untuk dibaca
        return view('posts.show', ['post' => $response->json()]);
    }

    /**
     * DELETE /posts/{id}
     * Menghancurkan instance Postingan/Kicau bersangkutan dari basis data platform.
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        // Eksekusi trigger hapus via Http
        $response = $this->api->deletePost($id);

        if ($response->failed()) {
            return back()->with('error', $response->json('message', 'Gagal menghapus kicauan.'));
        }

        return back()->with('success', 'Kicauan berhasil dihapus.');
    }
}
