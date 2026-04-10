<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model Post
 * Entitas utama yang mewakili satu baris kicauan / timeline post dari pengguna.
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'body',
        'media_path',
        'media_type',
    ];

    /** Relasi: Siapa pemilik/penulis kicauan ini */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Relasi: Kumpulan banyak komentar yang bertengger di kicauan ini */
    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /** Relasi: Relasi Like dari berbagai pengguna */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Memeriksa apakah kicauan/pos ini pernah dilike oleh User spesifik.
     * Menggunakan exists() langsung ke query builder tanpa memuat seluruh data.
     * 
     * @param User $user Objek user yang sedang bertindak
     * @return bool
     */
    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Mutator bawaan Laravel: Accessor `media_url` untuk konversi relative path 
     * di database mejadi Absolute public URL agar dapat dibaca frontend.
     * 
     * @return string|null
     */
    public function getMediaUrlAttribute(): ?string
    {
        return $this->media_path ? url('storage/' . $this->media_path) : null;
    }
}
