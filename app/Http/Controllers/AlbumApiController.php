<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class AlbumApiController extends Controller
{
    public function index()
    {
        $albums = Album::all();
        return response()->json(['albums' => $albums]);
    }

    public function single_index($id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json(['album' => $album]);
    }

    public function index_song($id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json([
            'album' => $album->name,
            'songs' => $album->songs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'cover'     => 'nullable|string',
            'year'      => 'required|string|size:4',
            'genre'     => 'required|string|max:50',
            'artist_id' => 'required|exists:artists,id'
        ]);

        $album = Album::create($request->all());

        return response()->json([
            'message' => 'Album created successfully',
            'album'   => $album
        ], 201);
    }

    public function store_song(Request $request, $id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $request->validate([
            'name'       => 'required|string|max:100',
            'songwriter' => 'required|string|max:100',
            'lyrics'     => 'nullable|string',
        ]);

        $song = $album->songs()->create($request->all());

        return response()->json([
            'message' => 'Song created successfully',
            'song'    => $song
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $request->validate([
            'name'      => 'nullable|string|max:100',
            'cover'     => 'nullable|string',
            'year'      => 'nullable|string|size:4',
            'genre'     => 'nullable|string|max:50',
            'artist_id' => 'nullable|exists:artists,id'
        ]);

        $album->update($request->all());

        return response()->json([
            'message' => 'Album updated successfully',
            'album'   => $album
        ]);
    }

    public function update_song(Request $request, $album_id, $id)
    {
        $album = Album::find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $song = $album->songs()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $request->validate([
            'name'       => 'nullable|string|max:100',
            'songwriter' => 'nullable|string|max:100',
            'lyrics'     => 'nullable|string',
        ]);

        $song->update($request->all());

        return response()->json([
            'message' => 'Song updated successfully',
            'song'    => $song
        ]);
    }

    public function destroy($id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $album->delete();

        return response()->json([
            'message' => 'Album deleted successfully',
            'id'      => $id
        ], 410);
    }

    public function destroy_song($album_id, $id)
    {
        $album = Album::find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $song = $album->songs()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $song->delete();

        return response()->json([
            'message' => 'Song deleted successfully',
            'id'      => $id
        ], 410);
    }
}
