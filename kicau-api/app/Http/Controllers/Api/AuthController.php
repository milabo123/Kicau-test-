<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Class AuthController
 * Menangani proses otentikasi (registrasi, login, logout) untuk API pengguna Kicau.
 */
class AuthController extends Controller
{
    /**
     * POST /api/register
     * Mendaftarkan pengguna baru ke dalam database dan mengembalikan token otentikasi awal.
     * 
     * @param Request $request Data request yang berisi kredensial registrasi pengguna.
     * @return \Illuminate\Http\JsonResponse JSON response berisi pesan, token Sanctum, dan profil user.
     */
    public function register(Request $request)
    {
        // Validasi data input dari request pengguna
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:30|alpha_dash|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        // Simpan data pengguna baru ke database Kicau dengan hashing menggunakan Hash facade untuk keamanan password
        $user = User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Buat access token persisten (Sanctum) untuk autentikasi endpoint API berikutnya
        $token = $user->createToken('kicau_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil!',
            'token'   => $token,
            'user'    => $this->userResource($user),
        ], 201);
    }

    /**
     * POST /api/login
     * Memvalidasi kredensial login dan mengembalikan token otentikasi Sanctum baru.
     * 
     * @param Request $request Data request yang berisi email dan password.
     * @return \Illuminate\Http\JsonResponse JSON response berisi token dan profil user jika kredensial benar.
     * @throws ValidationException Jika email atau password tidak sesuai.
     */
    public function login(Request $request)
    {
        // Validasi input form login
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Autentikasi kredensial pengguna menggunakan Facade Laravel Auth
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Jika tidak cocok, tolak dan kembalikan pesan error yang sesuai
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Ambil instance pengguna yang baru saja diotentikasi dari sistem
        $user  = Auth::user();
        // Buat personal access token (Sanctum) yang akan digunakan di sesi berikutnya
        $token = $user->createToken('kicau_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil!',
            'token'   => $token,
            'user'    => $this->userResource($user),
        ]);
    }

    /**
     * POST /api/logout
     * Menghapus token saat ini untuk mencabut akses API dari perangkat atau sesi ini.
     * 
     * @param Request $request Data request dengan token otentikasi.
     * @return \Illuminate\Http\JsonResponse Pesan sukses logout.
     */
    public function logout(Request $request)
    {
        // Mencabut/menghapus token akses API saat ini yang dikirim bersama request ini
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * GET /api/me
     * Mengambil dan mengembalikan profil pengguna yang saat ini sedang login.
     * 
     * @param Request $request Data request dengan token otentikasi Bearer.
     * @return \Illuminate\Http\JsonResponse JSON response berisi profil lengkap user.
     */
    public function me(Request $request)
    {
        // Panggil metode pembantu \userResource untuk memformat data pengguna agar kompatibel dengan API
        return response()->json($this->userResource($request->user()));
    }

    // ─────────────────── Helper ───────────────────

    /**
     * Helper method internal.
     * Merangkai properti instance pengguna ke format array yang distandarisasi untuk di-serialize ke JSON.
     * 
     * @param User $user Model objek pengguna yang akan diformat.
     * @return array Struktur array dengan properti dari \User.
     */
    private function userResource(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'username'   => $user->username,
            'email'      => $user->email,
            'bio'        => $user->bio,
            'avatar_url' => $user->avatar_url,
            'created_at' => $user->created_at,
        ];
    }
}
