<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Member;
use App\Models\Album;
use Illuminate\Http\Request;

class ArtistApiController extends Controller
{
    /**
     * @api {get} http://localhost:8000/api/artists Get all artists
     * @apiName GetArtists
     * @apiGroup Artist
     *
     * @apiSuccess {Object[]} songs List of artists.
     * @apiSuccess {Number} songs.id Artist ID.
     * @apiSuccess {String} songs.name Artist name.
     * @apiSuccess {String} songs.nationality Artist nationality.
     * @apiSuccess {String} songs.image Artist image URL.
     * @apiSuccess {String} songs.description Artist description.
     * @apiSuccess {String} songs.is_band Indicates if the artist is a band ("yes"/"no").
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "artists": [
     *     {
     *       "id": 1,
     *       "name": "The Beatles",
     *       "nationality": "British",
     *       "image": "beatles.jpg",
     *       "description": "Legendary rock band from Liverpool.",
     *       "is_band": "yes"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $artists = Artist::all();
        return response()->json(['artists' => $artists]);
    }


    public function index_member($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        if ($artist->is_band == "no") {
            return response()->json(['message' => 'Artist is not a band'], 400);
        }

        return response()->json([
            'artist' => $artist->name,
            'members' => $artist->member
        ]);
    }

    public function index_album($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'albums' => $artist->album
        ]);
    }

    public function index_song($artist_id,$id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->album()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found for this artist'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'album' => $album->name,
            'songs' => $album->song
        ]);
    }

    /**
     * @api {post} http://localhost:8000/api/artist Create a new artist
     * @apiName CreateArtist
     * @apiGroup Artist
     *
     * @apiBody {String} name Artist name (required).
     * @apiBody {String} nationality Artist nationality (required).
     * @apiBody {String} [image] Artist image URL (optional).
     * @apiBody {String} description Artist description (required).
     * @apiBody {String} is_band Whether the artist is a band ("yes" or "no") (required).
     *
     * @apiSuccess {Object} product Created artist data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "product": {
     *     "id": 2,
     *     "name": "Adele",
     *     "nationality": "British",
     *     "image": "adele.jpg",
     *     "description": "Pop and soul singer-songwriter.",
     *     "is_band": "no"
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nationality' => 'required|string|max:255',
            'image' => 'nullable|string',
            'description' => 'required|string',
            'is_band' => 'required|string'
        ]);

        $artist = Artist::create($request->all());
        return response()->json(['message' => 'Artist created successfully', 'artist' => $artist], 201);
    }

    public function store_member(Request $request, $id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        if ($artist->is_band == "no") {
            return response()->json(['message' => 'Artist is not a band'], 400);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'instrument' => 'required|string|max:255',
            'year' => 'required|integer',
            'image' => 'nullable|string'
        ]);

        $member = $artist->member()->create($request->all());

        return response()->json(['message' => 'Member created successfully', 'member' => $member], 201);
    }

    public function store_album(Request $request, $id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }


        $request->validate([
            'name' => 'required|string|max:255',
            'cover' => 'nullable|string',
            'year' => 'required|integer',
            'genre' => 'required|string|max:255',
        ]);

        $album = $artist->album()->create($request->all());

        return response()->json(['message' => 'Album created successfully', 'album' => $album], 201);
    }

    public function store_song(Request $request, $artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->album()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found for this artist'], 404);
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
     * @api {put} http://localhost:8000/api/artist/:id Update an artist
     * @apiName UpdateArtist
     * @apiGroup Artist
     *
     * @apiParam {Number} id Artist unique ID.
     *
     * @apiBody {String} [name] Artist name.
     * @apiBody {String} [nationality] Artist nationality.
     * @apiBody {String} [image] Artist image URL.
     * @apiBody {String} [description] Artist description.
     * @apiBody {String} [is_band] Whether the artist is a band ("yes" or "no").
     *
     * @apiSuccess {Object} product Updated artist data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "product": {
     *     "id": 2,
     *     "name": "Adele (Updated)",
     *     "nationality": "British",
     *     "image": "adele_updated.jpg",
     *     "description": "Updated biography...",
     *     "is_band": "no"
     *   }
     * }
     */
    public function update(Request $request, $id)
    {

        $request->validate([
            'name' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'is_band' => 'nullable|string',
        ]);

        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $artist->update($request->all());

        return response()->json(['message' => 'Artist updated successfully', 'artist' => $artist]);
    }

    public function update_member(Request $request, $artist_id, $id)
    {

        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        if ($artist->is_band == "no") {
            return response()->json(['message' => 'Artist is not a band'], 400);
        }

        $member = $artist->member()->find($id);

        if (!$member) {
            return response()->json(['message' => 'Member not found for this artist'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'instrument' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'image' => 'nullable|string'
        ]);

        $member->update($request->all());

        return response()->json(['message' => 'Member updated successfully', 'member' => $member]);
    }

    public function update_album(Request $request, $artist_id, $id)
    {

        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->album()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found for this artist'], 404);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'cover' => 'nullable|string',
            'year' => 'nullable|integer',
            'genre' => 'nullable|string|max:255',
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

        $album = $artist->album()->find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found for this artist'], 404);
        }

        $song = $album->song()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found for this album'], 404);
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
     * @api {delete} http://localhost:8000/api/artist/:id Delete an artist
     * @apiName DeleteArtist
     * @apiGroup Artist
     *
     * @apiParam {Number} id Artist unique ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted artist ID.
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
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $artist->delete();
        return response()->json(['message' => 'Artist deleted successfully', 'id' => $id], 410);
    }

    public function destroy_member($artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        if ($artist->is_band == "no") {
            return response()->json(['message' => 'Artist is not a band'], 400);
        }

        $member = $artist->member()->find($id);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $member->delete();

        return response()->json(['message' => 'Member deleted successfully', 'id' => $id], 410);
    }

    public function destroy_album($artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }


        $album = $artist->album()->find($id);

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

        $album = $artist->album()->find($album_id);

        if (!$album) {
            return response()->json(['message' => 'Album not found for this artist'], 404);
        }

        $song = $album->song()->find($id);

        if (!$song) {
            return response()->json(['message' => 'Song not found for this album'], 404);
        }

        $song->delete();

        return response()->json(['message' => 'Song deleted successfully', 'id' => $id], 410);
    }
}
