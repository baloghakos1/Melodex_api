<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongApiController extends Controller
{
    /**
     * @api {get} http://localhost:8000/api/songs Get all songs
     * @apiName GetSongs
     * @apiGroup Song
     *
     * @apiSuccess {Object[]} songs List of songs.
     * @apiSuccess {Number} songs.id Song ID.
     * @apiSuccess {String} songs.name Song title.
     * @apiSuccess {String} songs.songwriter Songwriter name.
     * @apiSuccess {String} songs.lyrics Song lyrics.
     * @apiSuccess {Number} songs.album_id Associated album ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "songs": [
     *     {
     *       "id": 1,
     *       "name": "Hey Jude",
     *       "songwriter": "Paul McCartney",
     *       "lyrics": "Hey Jude, don't make it bad...",
     *       "album_id": 3
     *     }
     *   ]
     * }
     */
    public function index() {
        $songs = Song::all();
        return response()->json([
            'songs' => $songs,
        ]);
    }

    /**
     * @api {post} http://localhost:8000/api/song Create a new song
     * @apiName CreateSong
     * @apiGroup Song
     *
     * @apiBody {String} name Song title (required).
     * @apiBody {String} songwriter Songwriter name (required).
     * @apiBody {String} [lyrics] Song lyrics (optional).
     * @apiBody {Number} album_id Associated album ID (required, must exist in albums table).
     *
     * @apiSuccess {Object} product Created song data.
     * @apiSuccess {Number} product.id Song ID.
     * @apiSuccess {String} product.name Song title.
     * @apiSuccess {String} product.songwriter Songwriter name.
     * @apiSuccess {String} product.lyrics Song lyrics.
     * @apiSuccess {Number} product.album_id Associated album ID.
     *
     * @apiError (422) ValidationError Some required fields are missing or invalid.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "product": {
     *     "id": 1,
     *     "name": "Hey Jude",
     *     "songwriter": "Paul McCartney",
     *     "lyrics": "Hey Jude, don't make it bad...",
     *     "album_id": 3
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'songwriter' => 'required|string|max:255',
            'lyrics' => 'nullable|string',
            'album_id' => 'required|exists:albums,id',
        ]);
        $song = Song::create($request->all());

        return response()->json(['message' => 'Song created successfully', 'Song' => $song], 201);
    }

    /**
     * @api {put} http://localhost:8000/api/song/:id Update a song
     * @apiName UpdateSong
     * @apiGroup Song
     *
     * @apiParam {Number} id Song unique ID.
     *
     * @apiBody {String} [name] Song title.
     * @apiBody {String} [songwriter] Songwriter name.
     * @apiBody {String} [lyrics] Song lyrics.
     * @apiBody {Number} [album_id] Associated album ID.
     *
     * @apiSuccess {Object} product Updated song data.
     *
     * @apiError (404) NotFound Song not found.
     * @apiError (422) ValidationError Some fields are invalid.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "product": {
     *     "id": 1,
     *     "name": "Hey Jude (Remastered)",
     *     "songwriter": "Paul McCartney",
     *     "lyrics": "Updated lyrics...",
     *     "album_id": 3
     *   }
     * }
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'songwriter' => 'nullable|string|max:255',
            'lyrics' => 'nullable|string',
            'album_id' => 'nullable|exists:albums,id',
        ]);
        $song = Song::find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }
        $song->update($request->all());

        return response()->json(['message' => 'Song updated successfully', 'Song' => $song]);
    }

    /**
     * @api {delete} http://localhost:8000/api/song/:id Delete a song
     * @apiName DeleteSong
     * @apiGroup Song
     *
     * @apiParam {Number} id Song unique ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted song ID.
     *
     * @apiError (404) NotFound Song not found.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Product deleted successfully",
     *   "id": 1
     * }
     */
    public function destroy($id)
    {
        $song = Song::find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $song->delete();
        return response()->json([
            'message' => 'Song deleted successfully',
            'id' => $id
        ], 410);
    }
}
