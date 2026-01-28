<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['name', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'songs_playlists', 'playlist_id', 'song_id')->withPivot('id');
    }

    protected static function booted()
    {
        static::deleting(function ($playlist) {
            $playlist->songs()->detach();
        });
    }
}
