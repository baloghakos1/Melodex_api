<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongApiController extends Controller
{
    public function index()
    {
        $songs = Song::all();
        return response()->json(['songs' => $songs]);
    }

    public function single_index($id)
    {
        $song = Song::find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        return response()->json(['song' => $song]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'songwriter' => 'required|string|max:100',
            'lyrics'     => 'nullable|string',
            'album_id'   => 'required|exists:albums,id',
        ]);

        $song = Song::create($request->all());

        return response()->json([
            'message' => 'Song created successfully',
            'song'    => $song
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $song = Song::find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $request->validate([
            'name'       => 'nullable|string|max:100',
            'songwriter' => 'nullable|string|max:100',
            'lyrics'     => 'nullable|string',
            'album_id'   => 'nullable|exists:albums,id',
        ]);

        $song->update($request->all());

        return response()->json([
            'message' => 'Song updated successfully',
            'song'    => $song
        ]);
    }

    public function destroy($id)
    {
        $song = Song::find($id);

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
