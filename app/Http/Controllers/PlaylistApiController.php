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

    public function single_index($id)
    {
        $playlist = Playlist::find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }
        return response()->json(['playlist' => $playlist]);
    }

    public function index_song($id) {
        $playlist = Playlist::find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        return response()->json([
            'playlist' => $playlist->name,
            'songs' => $playlist->songs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'user_id'  => 'required|exists:users,id',
        ]);

        $alreadyExists = Playlist::where('user_id', $request->user_id)
            ->where('name', $request->name)
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Playlist with this name already exists for this user'
            ], 409);
        }

        $playlist = Playlist::create([
            'name' => $request->name,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'message' => 'Playlist created successfully',
            'playlist' => $playlist
        ], 201);
    }

    public function store_song(Request $request, $id)
    {
        $playlist = Playlist::find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $request->validate([
            'song_id' => 'required|exists:songs,id'
        ]);

        $songId = $request->song_id;

        $alreadyExists = $playlist->songs()
            ->where('songs.id', $songId)
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Song already exists in playlist'
            ], 409);
        }

        $playlist->songs()->attach($songId);

        return response()->json([
            'message' => 'Song added successfully'
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $playlist = Playlist::find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $request->validate([
            'name'    => 'nullable|string|max:100',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $newName = $request->name ?? $playlist->name;
        $newUserId = $request->user_id ?? $playlist->user_id;

        $alreadyExists = Playlist::where('user_id', $newUserId)
            ->where('name', $newName)
            ->where('id', '!=', $playlist->id)
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Playlist with this name already exists for this user'
            ], 409);
        }

        $playlist->update($request->only(['name', 'user_id']));

        return response()->json([
            'message' => 'Playlist updated successfully',
            'playlist' => $playlist
        ], 200);
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

    public function destroy_song($playlist_id, $id)
    {
        $playlist = Playlist::find($playlist_id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $pivot = $playlist->songs()
            ->wherePivot('id', $id)
            ->first();

        if (!$pivot) {
            return response()->json(['message' => 'Song not found in playlist'], 404);
        }

        $playlist->songs()->detach($pivot->id);

        return response()->json([
            'message' => 'Song removed from playlist',
            'id' => $id
        ], 200);
    }
}
