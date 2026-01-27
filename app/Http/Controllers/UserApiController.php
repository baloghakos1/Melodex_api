<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function store_playlist(Request $request, $id) 
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $playlist = $user->playlists()->create($request->all());

        return response()->json(['message' => 'Playlist created successfully', 'playlist' => $playlist], 201);
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

        $playlist->update($request->all());

        return response()->json(['message' => 'Playlist updated successfully', 'playlist' => $playlist]);
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
}
