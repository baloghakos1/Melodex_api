<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class AlbumApiController extends Controller
{
    /**
     * @api {get} /api/albums Get all albums
     * @apiName GetAlbums
     * @apiGroup Album
     *
     * @apiSuccess {Object[]} albums List of albums.
     * @apiSuccess {Number} albums.id Album ID.
     * @apiSuccess {String} albums.name Album name.
     * @apiSuccess {String} albums.cover Album cover URL (nullable).
     * @apiSuccess {String} albums.year Release year.
     * @apiSuccess {String} albums.genre Album genre.
     * @apiSuccess {Number} albums.artist_id Artist ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "albums": [
     *     {
     *       "id": 1,
     *       "name": "Abbey Road",
     *       "cover": "abbey_road.jpg",
     *       "year": "1969",
     *       "genre": "Rock",
     *       "artist_id": 1
     *     }
     *   ]
     * }
     */
    public function index() {
        $albums = Album::all();
        return response()->json(['albums' => $albums]);
    }

    public function single_index($id) {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }
        return response()->json(['Album' => $album]);
    }
    /**
     * @api {get} /api/albums/:id/songs Get songs of an album
     * @apiName GetAlbumSongs
     * @apiGroup Album
     *
     * @apiParam {Number} id Album ID.
     *
     * @apiSuccess {String} album Album name.
     * @apiSuccess {Object[]} songs List of songs in the album.
     * @apiSuccess {Number} songs.id Song ID.
     * @apiSuccess {String} songs.name Song name.
     * @apiSuccess {String} songs.lyrics Song lyrics (nullable).
     * @apiSuccess {String} songs.songwriter Songwriter name.
     * @apiSuccess {Number} songs.album_id Album ID the song belongs to.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "album": "Abbey Road",
     *   "songs": [
     *     {
     *       "id": 1,
     *       "name": "Come Together",
     *       "lyrics": null,
     *       "songwriter": "Lennon–McCartney",
     *       "album_id": 1
     *     }
     *   ]
     * }
     *
     * @apiErrorExample {json} Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found"
     * }
     */
    public function index_song($id) {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json([
            'album' => $album->name,
            'songs' => $album->song
        ]);
    }



    /**
     * @api {post} /api/albums Create a new album
     * @apiName CreateAlbum
     * @apiGroup Album
     *
     * @apiBody {String} name Album name (required).
     * @apiBody {String} [cover] Album cover URL (nullable).
     * @apiBody {String} year Release year (required).
     * @apiBody {String} genre Album genre (required).
     * @apiBody {Number} artist_id Artist ID (required, must exist in artists table).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} album Created album data.
     * @apiSuccess {Number} album.id Album ID.
     * @apiSuccess {String} album.name Album name.
     * @apiSuccess {String} album.cover Album cover URL (nullable).
     * @apiSuccess {String} album.year Release year.
     * @apiSuccess {String} album.genre Album genre.
     * @apiSuccess {Number} album.artist_id Artist ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "message": "Album created successfully",
     *   "album": {
     *     "id": 2,
     *     "name": "Let It Be",
     *     "cover": "let_it_be.jpg",
     *     "year": "1970",
     *     "genre": "Rock",
     *     "artist_id": 1
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cover' => 'nullable|string',
            'year' => 'required|integer',
            'genre' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id'
        ]);

        $album = Album::create($request->all());
        return response()->json(['message' => 'Album created successfully', 'album' => $album], 201);
    }
    /**
     * @api {post} /api/albums/:id/songs Create a new song for an album
     * @apiName CreateSong
     * @apiGroup Song
     *
     * @apiParam {Number} id Album ID.
     *
     * @apiBody {String} name Song name (required).
     * @apiBody {String} songwriter Songwriter name (required).
     * @apiBody {String} [lyrics] Song lyrics (nullable).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} song Created song data.
     * @apiSuccess {Number} song.id Song ID.
     * @apiSuccess {String} song.name Song name.
     * @apiSuccess {String} song.lyrics Song lyrics (nullable).
     * @apiSuccess {String} song.songwriter Songwriter name.
     * @apiSuccess {Number} song.album_id Album ID the song belongs to.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "message": "Song created successfully",
     *   "song": {
     *     "id": 5,
     *     "name": "Something",
     *     "lyrics": null,
     *     "songwriter": "George Harrison",
     *     "album_id": 1
     *   }
     * }
     *
     * @apiErrorExample {json} Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found"
     * }
     */
    public function store_song(Request $request, $id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'songwriter' => 'required|string|max:255',
            'lyrics' => 'nullable|string',
        ]);

        $song = $album->song()->create($request->all());

        return response()->json(['message' => 'Song created successfully', 'song' => $song], 201);
    }

    /**
     * @api {put} /api/albums/:id Update an album
     * @apiName UpdateAlbum
     * @apiGroup Album
     *
     * @apiParam {Number} id Album ID.
     *
     * @apiBody {String} [name] Album name.
     * @apiBody {String} [cover] Album cover URL (nullable).
     * @apiBody {String} [year] Release year.
     * @apiBody {String} [genre] Album genre.
     * @apiBody {Number} [artist_id] Artist ID (must exist in artists table).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} album Updated album data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Album updated successfully",
     *   "album": {
     *     "id": 2,
     *     "name": "Let It Be (Remastered)",
     *     "cover": "let_it_be_new.jpg",
     *     "year": "1970",
     *     "genre": "Classic Rock",
     *     "artist_id": 1
     *   }
     * }
     *
     * @apiErrorExample {json} Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found"
     * }
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'cover' => 'nullable|string',
            'year' => 'nullable|integer',
            'genre' => 'nullable|string|max:255',
            'artist_id' => 'nullable|exists:artists,id'
        ]);

        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $album->update($request->all());
        return response()->json(['message' => 'Album updated successfully', 'album' => $album]);
    }
    /**
     * @api {put} /api/albums/:album_id/songs/:id Update a song in an album
     * @apiName UpdateSong
     * @apiGroup Song
     *
     * @apiParam {Number} album_id Album ID.
     * @apiParam {Number} id Song ID.
     *
     * @apiBody {String} [name] Song name.
     * @apiBody {String} [songwriter] Songwriter name.
     * @apiBody {String} [lyrics] Song lyrics (nullable).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} song Updated song data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Song updated successfully",
     *   "song": {
     *     "id": 3,
     *     "name": "Something (Remastered)",
     *     "lyrics": null,
     *     "songwriter": "George Harrison",
     *     "album_id": 1
     *   }
     * }
     *
     * @apiErrorExample {json} Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found"
     * }
     *
     * @apiErrorExample {json} Song Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Song not found for this album"
     * }
     */
    public function update_song(Request $request, $album_id, $id)
    {

        $album = Album::find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $song = $album->song()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'songwriter' => 'nullable|string|max:255',
            'lyrics' => 'nullable|string',
        ]);

        $song->update($request->all());

        return response()->json(['message' => 'Song updated successfully', 'song' => $song]);
    }

    /**
     * @api {delete} /api/albums/:id Delete an album
     * @apiName DeleteAlbum
     * @apiGroup Album
     *
     * @apiParam {Number} id Album ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id ID of deleted album.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 410 Gone
     * {
     *   "message": "Album deleted successfully",
     *   "id": 2
     * }
     *
     * @apiErrorExample Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found"
     * }
     */
    public function destroy($id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $album->delete();
        return response()->json(['message' => 'Album deleted successfully', 'id' => $id], 410);
    }
    /**
     * @api {delete} /api/albums/:album_id/songs/:id Delete a song from an album
     * @apiName DeleteSong
     * @apiGroup Song
     *
     * @apiParam {Number} album_id Album ID.
     * @apiParam {Number} id Song ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id ID of deleted song.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 410 Gone
     * {
     *   "message": "Song deleted successfully",
     *   "id": 7
     * }
     *
     * @apiErrorExample Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found"
     * }
     *
     * @apiErrorExample Song Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Song not found for this album"
     * }
     */
    public function destroy_song($album_id, $id)
    {
        $album = Album::find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $song = $album->song()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $song->delete();
        return response()->json(['message' => 'Song deleted successfully', 'id' => $id], 410);
    }
}
