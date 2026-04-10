<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Follow
 * Merepresentasikan skema Pivot interaksi 'Mengikuti' atau Follower-Following dari Database.
 */
class Follow extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'follower_id',
        'following_id',
    ];

    /**
     * Relasi Entitas 'Follower': Pengguna SIAPA yang MENGALAMI/melakukan aksi follow.
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * Relasi Entitas 'Following': AKUN APA yang MENERIMA aksi follow / Yang Diikuti.
     */
    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}
