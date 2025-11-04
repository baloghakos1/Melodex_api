<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;
    public $timestamps = false;

    function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    function song()
    {
        return $this->hasMany(Song::class);
    }

    protected $fillable = [
        'name',
        'cover',
        'year',
        'genre',
        'artist_id'
    ];

}
