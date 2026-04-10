<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /**
     * Tampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses login via kicau-api, simpan token ke session.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $response = $this->api->login($request->email, $request->password);

        if ($response->failed()) {
            $errors = $response->json('errors', []);
            return back()
                ->withInput($request->only('email'))
                ->withErrors($errors ?: ['email' => $response->json('message', 'Login gagal.')]);
        }

        $data = $response->json();

        // Simpan token & data user ke session
        $request->session()->put('api_token', $data['token']);
        $request->session()->put('user', $data['user']);
        $request->session()->regenerate();

        return redirect()->intended(route('feed.index'));
    }

    /**
     * Logout: hapus token di API lalu bersihkan session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->api->logout();

        $request->session()->forget(['api_token', 'user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
