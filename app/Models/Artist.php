<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Artist extends Model implements Searchable
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'nationality',
        'image',
        'description',
        'audius_id',
    ];

    function albums()
    {
        return $this->hasMany(Album::class);
    }

    protected static function booted()
    {
        static::deleting(function ($artist) {
            $artist->albums()->each(function ($album) {
                $album->delete();
            });
        });
    }

    public function getSearchResult(): SearchResult
    {
        return new SearchResult($this, $this->name); 
    }
}
