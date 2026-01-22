<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function members()
    {
        return $this->belongsToMany(Member::class, 'artists_members');
    }


    function albums()
    {
        return $this->hasMany(Album::class);
    }

    protected static function booted()
    {
        static::deleting(function ($artist) {

            // Delete albums (albums will delete songs)
            $artist->album()->each(function ($album) {
                $album->delete();
            });

            // Delete members
            $artist->member()->delete();
        });
    }

    protected $fillable = [
        'name',
        'nationality',
        'image',
        'description',
        'is_band'
    ];
}
