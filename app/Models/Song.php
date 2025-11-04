<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    public $timestamps = false;

    function album()
    {
        return $this->belongsTo(Album::class);
    }

    protected $fillable = [
        'name',
        'songwriter',
        'lyrics',
        'album_id'
    ];
}
