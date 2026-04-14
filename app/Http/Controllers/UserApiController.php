<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserApiController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $password ? $user->password : '')) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401); // Unauthorized
        }

        //revoke old tokens
        $user->tokens()->delete();

        $user->token = $user->createToken('access')->plainTextToken;
        // ablities can be set https://laravel.com/docs/11.x/sanctum#token-abilities
        // $token = $user->createToken('access', ['server:update']);

        return response()->json([
            'user' => $user,
        ]);

    }

    public function index(Request $request)
    {
        $users = User::all();
        return response()->json([
            'users' => $users,
        ]);
    }

    public function index_playlist(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'user' => $user->name,
            'playlists' => $user->playlists
        ]);

    }

    public function single_index_playlist($user_id, $id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $playlist = $user->playlists()->find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }
        return response()->json([
            'user' => $user->name,
            'playlist' => $playlist
        ]);
    }

    public function index_playlist_song(Request $request, $user_id, $id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $playlist = $user->playlists()
            ->with('songs.album.artist')
            ->find($id);

        if (!$playlist) {
            return response()->json([
                'message' => 'Playlist not found'
            ], 404);
        }

        return response()->json([
            'user' => $user->name,
            'playlist' => $playlist->name,
            'songs' => $playlist->songs
        ]);
    }


    public function store_playlist(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $existing = $user->playlists()
            ->where('name', $request->name)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Playlist with this name already exists'
            ], 409);
        }

        $playlist = $user->playlists()->create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Playlist created successfully',
            'playlist' => $playlist
        ], 201);
    }

    public function store_song_playlists(Request $request, $user_id, $song_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'playlists' => 'array',
            'playlists.*' => 'exists:playlists,id'
        ]);

        $playlistIds = $request->playlists ?? [];

        $validPlaylistIds = $user->playlists()
            ->whereIn('id', $playlistIds)
            ->pluck('id')
            ->toArray();

        foreach ($user->playlists as $playlist) {
            $playlist->songs()->detach($song_id);
        }

        foreach ($validPlaylistIds as $playlistId) {
            $playlist = $user->playlists()->find($playlistId);
            $playlist->songs()->attach($song_id);
        }

        return response()->json([
            'message' => 'Song added successfully'
        ]);
    }


    public function update_playlist(Request $request, $user_id, $id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $playlist = $user->playlists()->find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:100',
        ]);

        $newName = $request->name ?? $playlist->name;

        $alreadyExists = $user->playlists()
            ->where('name', $newName)
            ->where('id', '!=', $playlist->id)
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'message' => 'Playlist with this name already exists'
            ], 409);
        }

        $playlist->update([
            'name' => $newName
        ]);

        return response()->json([
            'message' => 'Playlist updated successfully',
            'playlist' => $playlist
        ], 200);
    }

    public function destroy_playlist($user_id, $id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        $playlist = $user->playlists()->find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $playlist->delete();

        return response()->json(['message' => 'Playlist deleted successfully', 'id' => $id], 410);
    }

    public function destroy_playlist_song($user_id, $playlist_id, $id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        $playlist = $user->playlists()->find($playlist_id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
        }

        $pivot = $playlist->songs()
            ->wherePivot('song_id', $id)
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

    public function song_playlists($user_id, $song_id)
    {
        $user = User::find($user_id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $playlists = $user->playlists()
            ->whereHas('songs', function ($query) use ($song_id) {
                $query->where('songs.id', $song_id);
            })
            ->get(['id', 'name']);

        return response()->json([
            'song_id' => $song_id,
            'playlists' => $playlists
        ]);
    }
}
