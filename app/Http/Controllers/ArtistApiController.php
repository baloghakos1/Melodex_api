<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Album;
use Illuminate\Http\Request;

class ArtistApiController extends Controller
{
    public function index()
    {
        $artists = Artist::all();
        return response()->json(['artists' => $artists]);
    }

    public function single_index($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }
        return response()->json(['artist' => $artist]);
    }
    /**
     * @api {get} /api/artists/:id/albums Get albums of an artist
     * @apiName GetArtistAlbums
     * @apiGroup Artist
     *
     * @apiParam {Number} id Artist unique ID.
     *
     * @apiSuccess {String} artist Name of the artist.
     * @apiSuccess {Object[]} albums List of albums by the artist.
     * @apiSuccess {Number} albums.id Album ID.
     * @apiSuccess {String} albums.name Album name.
     * @apiSuccess {String} [albums.cover] Album cover image URL (optional).
     * @apiSuccess {String} albums.year Year the album was released.
     * @apiSuccess {String} albums.genre Album genre.
     * @apiSuccess {Number} albums.artist_id ID of the associated artist.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "artist": "The Beatles",
     *   "albums": [
     *     {
     *       "id": 1,
     *       "name": "Abbey Road",
     *       "cover": "abbey_road.jpg",
     *       "year": "1969",
     *       "genre": "Rock",
     *       "artist_id": 1
     *     },
     *     {
     *       "id": 2,
     *       "name": "Sgt. Pepper's Lonely Hearts Club Band",
     *       "cover": "sgt_pepper.jpg",
     *       "year": "1967",
     *       "genre": "Rock",
     *       "artist_id": 1
     *     }
     *   ]
     * }
     *
     * @apiError {String} message Error message.
     *
     * @apiErrorExample {json} Artist Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Artist not found"
     * }
     */
    public function index_album($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }
        $albums =Album::where('artist_id', $id)->get();

        return response()->json([
            'artist' => $artist->name,
            'albums' => $albums
        ]);
    }

    public function single_index_album($artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'album' => $album
        ]);
    }

    public function index_song($artist_id,$id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'album' => $album->name,
            'songs' => $album->songs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $artist = Artist::create($request->all());
        return response()->json(['message' => 'Artist created successfully', 'artist' => $artist], 201);
    }
    /**
     * @api {post} /api/artists/:id/albums Add a new album for an artist
     * @apiName CreateAlbum
     * @apiGroup Album
     *
     * @apiParam {Number} id Artist unique ID.
     *
     * @apiBody {String} name Album name (required).
     * @apiBody {String} [cover] Album cover image URL (optional).
     * @apiBody {Number} year Year the album was released (required).
     * @apiBody {String} genre Album genre (required).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} album Created album data.
     * @apiSuccess {Number} album.id Album ID.
     * @apiSuccess {String} album.name Album name.
     * @apiSuccess {String} [album.cover] Album cover image URL (optional).
     * @apiSuccess {Number} album.year Year the album was released.
     * @apiSuccess {String} album.genre Album genre.
     * @apiSuccess {Number} album.artist_id ID of the associated artist.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "message": "Album created successfully",
     *   "album": {
     *     "id": 1,
     *     "name": "Abbey Road",
     *     "cover": "abbey_road.jpg",
     *     "year": 1969,
     *     "genre": "Rock",
     *     "artist_id": 1
     *   }
     * }
     *
     * @apiError {String} message Error message.
     *
     * @apiErrorExample {json} Artist Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Artist not found"
     * }
     *
     * @apiErrorExample {json} Validation Error:
     * HTTP/1.1 422 Unprocessable Entity
     * {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "year": ["The year field is required."],
     *     "genre": ["The genre field is required."]
     *   }
     * }
     */
    public function store_album(Request $request, $id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }


        $request->validate([
            'name' => 'required|string|max:100',
            'cover' => 'nullable|string',
            'year' => 'required|string|size:4',
            'genre' => 'required|string|max:50',
        ]);

        $album = $artist->albums()->create($request->all());

        return response()->json(['message' => 'Album created successfully', 'album' => $album], 201);
    }

    public function store_song(Request $request, $artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }


        $request->validate([
            'name' => 'required|string|max:255',
            'lyrics' => 'nullable|string',
            'album_id' => 'required|integer',
            'stream_url' => 'required|string',
        ]);

        $song = $album->songs()->create($request->all());

        return response()->json(['message' => 'Song created successfully', 'song' => $song], 201);
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:100',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $artist->update($request->all());

        return response()->json(['message' => 'Artist updated successfully', 'artist' => $artist]);
    }
    /**
     * @api {put} /api/artists/:artist_id/albums/:id Update an album of an artist
     * @apiName UpdateAlbum
     * @apiGroup Album
     *
     * @apiParam {Number} artist_id Artist unique ID.
     * @apiParam {Number} id Album unique ID.
     *
     * @apiBody {String} [name] Album name.
     * @apiBody {String} [cover] Album cover image URL.
     * @apiBody {Number} [year] Album release year.
     * @apiBody {String} [genre] Album genre.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} album Updated album data.
     * @apiSuccess {Number} album.id Album ID.
     * @apiSuccess {String} album.name Album name.
     * @apiSuccess {String} [album.cover] Album cover image URL (optional).
     * @apiSuccess {Number} album.year Album release year.
     * @apiSuccess {String} album.genre Album genre.
     * @apiSuccess {Number} album.artist_id ID of the associated artist.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Album updated successfully",
     *   "album": {
     *     "id": 1,
     *     "name": "Abbey Road (Updated)",
     *     "cover": "abbey_road_updated.jpg",
     *     "year": 1969,
     *     "genre": "Rock",
     *     "artist_id": 1
     *   }
     * }
     *
     * @apiError {String} message Error message.
     *
     * @apiErrorExample {json} Artist Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Artist not found"
     * }
     *
     * @apiErrorExample {json} Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found for this artist"
     * }
     *
     * @apiErrorExample {json} Validation Error:
     * HTTP/1.1 422 Unprocessable Entity
     * {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name must be a string."],
     *     "year": ["The year must be an integer."]
     *   }
     * }
     */
    public function update_album(Request $request, $artist_id, $id)
    {

        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:100',
            'cover' => 'nullable|string',
            'year' => 'nullable|string|size:4',
            'genre' => 'nullable|string|max:50',
        ]);

        $album->update($request->all());

        return response()->json(['message' => 'Album updated successfully', 'album' => $album]);
    }

    public function update_song(Request $request, $artist_id, $album_id, $id)
    {

        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $song = $album->songs()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'lyrics' => 'nullable|string',
            'album_id' => 'nullable|integer',
            'stream_url' => 'nullable|string',
        ]);

        $song->update($request->all());

        return response()->json(['message' => 'Song updated successfully', 'song' => $song]);
    }

    public function destroy($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $artist->delete();
        return response()->json(['message' => 'Artist deleted successfully', 'id' => $id], 410);
    }
    /**
     * @api {delete} /api/artists/:artist_id/albums/:id Delete an album of an artist
     * @apiName DeleteAlbum
     * @apiGroup Album
     *
     * @apiParam {Number} artist_id Artist unique ID.
     * @apiParam {Number} id Album unique ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted album ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 410 Gone
     * {
     *   "message": "Album deleted successfully",
     *   "id": 5
     * }
     *
     * @apiError {String} message Error message.
     *
     * @apiErrorExample {json} Artist Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Artist not found"
     * }
     *
     * @apiErrorExample {json} Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found"
     * }
     */
    public function destroy_album($artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }


        $album = $artist->albums()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $album->delete();

        return response()->json(['message' => 'Album deleted successfully', 'id' => $id], 410);
    }

    public function destroy_song($artist_id, $album_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->albums()->find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $song = $album->songs()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found'], 404);
        }

        $song->delete();

        return response()->json(['message' => 'Song deleted successfully', 'id' => $id], 410);
    }

}
