<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;

class PlaylistApiController extends Controller
{
    public function index()
    {
        $playlists = Playlist::all();
        return response()->json(['playlists' => $playlists]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'user_id'   => 'required|exists:users,id',
        ]);

        $playlist = Playlist::create($request->all());

        return response()->json([
            'message' => 'Playlist created successfully',
            'playlist'    => $playlist
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $playlist = Playlist::find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $request->validate([
            'name'       => 'nullable|string|max:100',
            'user_id'   => 'nullable|exists:users,id',
        ]);

        $playlist->update($request->all());

        return response()->json([
            'message' => 'Playlist updated successfully',
            'playlist'    => $playlist
        ]);
    }

    public function destroy($id)
    {
        $playlist = Playlist::find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $playlist->delete();

        return response()->json([
            'message' => 'Playlist deleted successfully',
            'id'      => $id
        ], 410);
    }
}
