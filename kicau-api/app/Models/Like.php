<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Like
 * Merepresentasikan Interaksi Pivot Likes diantara User dan Kicauan.
 */
class Like extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
    ];

    /**
     * Pemilik tindakan Like
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Target Postingan yang dilike
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
