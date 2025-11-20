<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    public $timestamps = false;

    public function artists()
{
    return $this->belongsToMany(Artist::class, 'artists_members');
}

    protected $fillable = [
        'name',
        'instrument',
        'year',
        'image'
    ];
}
