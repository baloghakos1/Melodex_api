<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Album;
use Illuminate\Http\Request;

class ArtistApiController extends Controller
{
    public function index()
    {
        $artists = Artist::all();
        return response()->json(['artists' => $artists]);
    }

    public function single_index($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }
        return response()->json(['artist' => $artist]);
    }

    public function index_album($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'albums' => $artist->albums
        ]);
    }

    public function single_index_album($artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'album' => $album
        ]);
    }

    public function index_song($artist_id,$id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'album' => $album->name,
            'songs' => $album->songs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'image' => 'nullable|string',
            'description' => 'required|string',
        ]);

        $artist = Artist::create($request->all());
        return response()->json(['message' => 'Artist created successfully', 'artist' => $artist], 201);
    }

    public function store_album(Request $request, $id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }


        $request->validate([
            'name' => 'required|string|max:100',
            'cover' => 'nullable|string',
            'year' => 'required|string|size:4',
            'genre' => 'required|string|max:50',
        ]);

        $album = $artist->albums()->create($request->all());

        return response()->json(['message' => 'Album created successfully', 'album' => $album], 201);
    }

    public function store_song(Request $request, $artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }


        $request->validate([
            'name' => 'required|string|max:100',
            'songwriter' => 'required|string|max:100',
            'lyrics' => 'nullable|string',
        ]);

        $song = $album->songs()->create($request->all());

        return response()->json(['message' => 'Song created successfully', 'song' => $song], 201);
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:100',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $artist->update($request->all());

        return response()->json(['message' => 'Artist updated successfully', 'artist' => $artist]);
    }

    public function update_album(Request $request, $artist_id, $id)
    {

        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:100',
            'cover' => 'nullable|string',
            'year' => 'nullable|string|size:4',
            'genre' => 'nullable|string|max:50',
        ]);

        $album->update($request->all());

        return response()->json(['message' => 'Album updated successfully', 'album' => $album]);
    }

    public function update_song(Request $request, $artist_id, $album_id, $id)
    {

        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $song = $album->songs()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:100',
            'songwriter' => 'nullable|string|max:100',
            'lyrics' => 'nullable|string',
        ]);

        $song->update($request->all());

        return response()->json(['message' => 'Song updated successfully', 'song' => $song]);
    }

    public function destroy($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $artist->delete();
        return response()->json(['message' => 'Artist deleted successfully', 'id' => $id], 410);
    }

    public function destroy_album($artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }


        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $album->delete();

        return response()->json(['message' => 'Album deleted successfully', 'id' => $id], 410);
    }

    public function destroy_song($artist_id, $album_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $song = $album->songs()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $song->delete();

        return response()->json(['message' => 'Song deleted successfully', 'id' => $id], 410);
    }

}
