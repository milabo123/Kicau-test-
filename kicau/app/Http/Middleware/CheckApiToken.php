<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pengganti 'auth' bawaan Laravel.
 * Mengecek keberadaan api_token di session (hasil login via kicau-api).
 * Jika tidak ada, redirect ke halaman login.
 */
class CheckApiToken
    /**
     * Memproses Request yang masuk.
     * 
     * @param Request $request Data request yang melintas
     * @param Closure $next Pipa/jalur request selanjutnya
     * @return Response Objek respons ke browser
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Menyensor jalur (routing) jika variabel 'api_token' belum dilekatkan di Session Cache (belum ter-otentikasi)
        if (!$request->session()->has('api_token')) {
            // Alihkan mereka kembali menuju pintu gerbang (login) bila persyaratan gagal terpenuhi
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Lolos dari sensor: Teruskan Request menuju Controller Destinasi 
        return $next($request);
    }
}
