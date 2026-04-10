<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /**
     * Tampilkan halaman registrasi.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi via kicau-api, simpan token ke session.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'username'              => ['required', 'string', 'max:30', 'alpha_dash'],
            'email'                 => ['required', 'string', 'email', 'max:255'],
            'password'              => ['required', 'confirmed', 'min:8'],
        ]);

        $response = $this->api->register([
            'name'                  => $request->name,
            'username'              => $request->username,
            'email'                 => $request->email,
            'password'              => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ]);

        if ($response->failed()) {
            $errors = $response->json('errors', []);
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors($errors ?: ['email' => $response->json('message', 'Registrasi gagal.')]);
        }

        $data = $response->json();

        // Simpan token & data user ke session
        $request->session()->put('api_token', $data['token']);
        $request->session()->put('user', $data['user']);
        $request->session()->regenerate();

        return redirect()->route('feed.index');
    }
}
