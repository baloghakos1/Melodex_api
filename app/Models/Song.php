<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Song extends Model implements Searchable
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

    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'songs_playlists', 'song_id', 'playlist_id');
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult($this, $this->name); 
    }
}
