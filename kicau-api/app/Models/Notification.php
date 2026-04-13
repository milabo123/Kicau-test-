<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Notification
 * Menyimpan catatan notifikasi untuk setiap interaksi (like, comment, follow)
 * yang ditujukan kepada seorang pengguna oleh actor tertentu.
 */
class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // ───────────────────── Relationships ─────────────────────

    /** Penerima notifikasi */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Pelaku aksi yang memicu notifikasi */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** Objek terkait (Post, Comment, atau User) secara polimorfik */
    public function notifiable()
    {
        return $this->morphTo();
    }

    // ───────────────────── Scopes ─────────────────────

    /** Hanya notifikasi yang belum dibaca */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
