<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongApiController extends Controller
{
    /**
     * @api {get} /api/songs Get all songs
     * @apiName GetSongs
     * @apiGroup Song
     *
     * @apiSuccess {Object[]} songs List of songs.
     * @apiSuccess {Number} songs.id Song ID.
     * @apiSuccess {String} songs.name Song title.
     * @apiSuccess {String} songs.songwriter Songwriter name.
     * @apiSuccess {String} songs.lyrics Song lyrics (nullable).
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
        return response()->json(['songs' => $songs]);
    }

    public function single_index($id) {
        $song = Song::find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }
        return response()->json(['Song' => $song]);
    }

    /**
     * @api {post} /api/songs Create a new song
     * @apiName CreateSong
     * @apiGroup Song
     *
     * @apiBody {String} name Song title (required).
     * @apiBody {String} songwriter Songwriter name (required).
     * @apiBody {String} [lyrics] Song lyrics (nullable).
     * @apiBody {Number} album_id Associated album ID (required, must exist in albums table).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} song Created song data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "message": "Song created successfully",
     *   "song": {
     *     "id": 1,
     *     "name": "Hey Jude",
     *     "songwriter": "Paul McCartney",
     *     "lyrics": "Hey Jude, don't make it bad...",
     *     "album_id": 3
     *   }
     * }
     *
     * @apiErrorExample {json} Validation Error:
     * HTTP/1.1 422 Unprocessable Entity
     * {
     *   "message": "The name field is required."
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lyrics' => 'nullable|string',
            'album_id' => 'required|integer',
            'stream_url' => 'required|string',
        ]);
        $song = Song::create($request->all());

        return response()->json(['message' => 'Song created successfully', 'song' => $song], 201);
    }

    /**
     * @api {put} /api/songs/:id Update a song
     * @apiName UpdateSong
     * @apiGroup Song
     *
     * @apiParam {Number} id Song ID.
     *
     * @apiBody {String} [name] Song title.
     * @apiBody {String} [songwriter] Songwriter name.
     * @apiBody {String} [lyrics] Song lyrics (nullable).
     * @apiBody {Number} [album_id] Associated album ID (must exist in albums table).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} song Updated song data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Song updated successfully",
     *   "song": {
     *     "id": 1,
     *     "name": "Hey Jude (Remastered)",
     *     "songwriter": "Paul McCartney",
     *     "lyrics": "Updated lyrics...",
     *     "album_id": 3
     *   }
     * }
     *
     * @apiErrorExample {json} Song Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Song not found"
     * }
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'lyrics' => 'nullable|string',
            'album_id' => 'nullable|integer',
            'stream_url' => 'nullable|string|max:255',
        ]);
        $song = Song::find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }
        $song->update($request->all());

        return response()->json(['message' => 'Song updated successfully', 'song' => $song]);
    }

    /**
     * @api {delete} /api/songs/:id Delete a song
     * @apiName DeleteSong
     * @apiGroup Song
     *
     * @apiParam {Number} id Song ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted song ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 410 Gone
     * {
     *   "message": "Song deleted successfully",
     *   "id": 1
     * }
     *
     * @apiErrorExample {json} Song Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Song not found"
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
