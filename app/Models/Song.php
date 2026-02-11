<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'lyrics',
        'album_id',
        'stream_url',
    ];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
