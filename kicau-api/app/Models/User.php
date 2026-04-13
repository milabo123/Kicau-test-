<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model User
 * Otentikasi Sentral sistem Kicau, mengatur segala kredensial login, dan relasi utama pengguna
 * dengan apa yang mereka miliki (kicauan, komentar, jaringan pertemanan).
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ───────────────────── Relationships ─────────────────────

    /** Relasi: Satu pengguna dapat menulis banyak kicauan */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /** Relasi: Satu pengguna dapat menulis banyak komentar */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /** Relasi: Banyak rekam jejak Like yang ditebar pengguna pada berbagai pos */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /** Users who follow this user */
    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    /** Users this user follows */
    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /** Semua notifikasi yang diterima oleh pengguna ini */
    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    // ───────────────────── Helpers ─────────────────────

    /**
     * Memeriksa apakah saat ini pengguna (User class instans API kita) mengkiuti target akun profil
     * 
     * @param User $user Target Akun
     * @return bool True jika kita telah follow dia.
     */
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Mutator Accessor: Menyediakan absolute Avatar URL.
     * Jika pengguna belum mengunggah foto profil, sistem otomatis men-generate placeholder avatar (ui-avatars).
     * 
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return url('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6C63FF&color=fff&size=128';
    }
}
