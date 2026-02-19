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
            return response()->json(['message' => 'User not found'], 404);
        }

        $playlist = $user->playlists()->find($id);

        if (!$playlist) {
            return response()->json(['message' => 'Playlist not found'], 404);
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
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('playlists')->where(function ($query) use ($id) {
                    return $query->where('user_id', $id);
                }),
            ],
        ]);

        $playlist = $user->playlists()->create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Playlist created successfully',
            'playlist' => $playlist
        ], 201);
    }

    public function store_playlist_song(Request $request, $user_id, $id)
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
            'song_id' => 'required|exists:songs,id'
        ]);

        $playlist->songs()->syncWithoutDetaching($request->all());

        return response()->json(['message' => 'Song added successfully'], 200);
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
            'name' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('playlists')
                    ->where(fn ($query) => $query->where('user_id', $user_id))
                    ->ignore($playlist->id),
            ],
        ]);

        $playlist->update($request->only('name'));

        return response()->json([
            'message' => 'Playlist updated successfully',
            'playlist' => $playlist
        ]);
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
