<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class AlbumApiController extends Controller
{
    /**
     * @api {get} http://localhost:8000/api/albums Get all albums
     * @apiName GetAlbums
     * @apiGroup Album
     *
     * @apiSuccess {Object[]} songs List of albums.
     * @apiSuccess {Number} songs.id Album ID.
     * @apiSuccess {String} songs.name Album name.
     * @apiSuccess {String} songs.cover Album cover URL.
     * @apiSuccess {Number} songs.year Release year.
     * @apiSuccess {String} songs.genre Album genre.
     * @apiSuccess {Number} songs.artist_id Artist ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "songs": [
     *     {
     *       "id": 1,
     *       "name": "Abbey Road",
     *       "cover": "abbey_road.jpg",
     *       "year": 1969,
     *       "genre": "Rock",
     *       "artist_id": 1
     *     }
     *   ]
     * }
     */
    public function index() {
        $songs = Album::all();
        return response()->json(['songs' => $songs]);
    }

    /**
     * @api {post} http://localhost:8000/api/album Create a new album
     * @apiName CreateAlbum
     * @apiGroup Album
     *
     * @apiBody {String} name Album name (required).
     * @apiBody {String} [cover] Album cover URL.
     * @apiBody {Number} year Release year (required).
     * @apiBody {String} genre Album genre (required).
     * @apiBody {Number} artist_id Artist ID (required, must exist in artists table).
     *
     * @apiSuccess {Object} product Created album data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "product": {
     *     "id": 2,
     *     "name": "Let It Be",
     *     "cover": "let_it_be.jpg",
     *     "year": 1970,
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

        $product = Album::create($request->all());
        return response()->json(['product' => $product], 201);
    }

    /**
     * @api {put} http://localhost:8000/api/album/:id Update an album
     * @apiName UpdateAlbum
     * @apiGroup Album
     *
     * @apiParam {Number} id Album unique ID.
     * @apiBody {String} [name] Album name.
     * @apiBody {String} [cover] Album cover URL.
     * @apiBody {Number} [year] Release year.
     * @apiBody {String} [genre] Album genre.
     * @apiBody {Number} [artist_id] Artist ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "product": {
     *     "id": 2,
     *     "name": "Let It Be (Remastered)",
     *     "cover": "let_it_be_new.jpg",
     *     "year": 1970,
     *     "genre": "Classic Rock",
     *     "artist_id": 1
     *   }
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
        return response()->json(['message' => 'Not found!'], 404);
        }

        $album->update($request->all());
        return response()->json(['product' => $album]);
    }

    /**
     * @api {delete} http://localhost:8000/api/album/:id Delete an album
     * @apiName DeleteAlbum
     * @apiGroup Album
     *
     * @apiParam {Number} id Album unique ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Product deleted successfully",
     *   "id": 2
     * }
     */
    public function destroy($id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json(['message' => 'Not found!'], 404);
        }

        $album->delete();
        return response()->json(['message' => 'Album deleted successfully', 'id' => $id], 410);
    }
}
