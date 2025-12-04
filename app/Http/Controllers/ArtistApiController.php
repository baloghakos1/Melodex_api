<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Member;
use App\Models\Album;
use Illuminate\Http\Request;

class ArtistApiController extends Controller
{
    /**
     * @api {get} /api/artists Get all artists
     * @apiName GetArtists
     * @apiGroup Artist
     *
     * @apiSuccess {Object[]} artists List of artists.
     * @apiSuccess {Number} artists.id Artist ID.
     * @apiSuccess {String} artists.name Artist name.
     * @apiSuccess {String} artists.nationality Artist nationality.
     * @apiSuccess {String} artists.image Artist image URL.
     * @apiSuccess {String} artists.description Artist description.
     * @apiSuccess {String} artists.is_band Indicates if the artist is a band ("yes"/"no").
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

    public function single_index($id)
    {
        $artist = Artist::find($id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }
        return response()->json(['artist' => $artist]);
    }
    /**
     * @api {get} /api/artists/:id/members Get members of a band
     * @apiName GetArtistMembers
     * @apiGroup Artist
     *
     * @apiParam {Number} id Artist unique ID.
     *
     * @apiSuccess {String} artist Name of the band.
     * @apiSuccess {Object[]} members List of band members.
     * @apiSuccess {Number} members.id Member ID.
     * @apiSuccess {String} members.name Member name.
     * @apiSuccess {String} members.instrument Instrument played by the member.
     * @apiSuccess {String} members.year Year the member joined the band.
     * @apiSuccess {Number} members.artist_id ID of the associated artist.
     * @apiSuccess {String} [members.image] Member image URL (optional).
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "artist": "The Beatles",
     *   "members": [
     *     {
     *       "id": 1,
     *       "name": "John Lennon",
     *       "instrument": "Guitar",
     *       "year": "1960",
     *       "artist_id": 1,
     *       "image": "john_lennon.jpg"
     *     },
     *     {
     *       "id": 2,
     *       "name": "Paul McCartney",
     *       "instrument": "Bass",
     *       "year": "1960",
     *       "artist_id": 1,
     *       "image": "paul_mccartney.jpg"
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
     *
     * @apiErrorExample {json} Artist Not a Band:
     * HTTP/1.1 400 Bad Request
     * {
     *   "message": "Artist is not a band"
     * }
     */
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

        return response()->json([
            'artist' => $artist->name,
            'albums' => $artist->album
        ]);
    }

    public function single_index_album($artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->album()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'album' => $album
        ]);
    }

    /**
     * @api {get} /api/artists/:artist_id/albums/:id/songs Get songs of an album
     * @apiName GetAlbumSongs
     * @apiGroup Artist
     *
     * @apiParam {Number} artist_id Artist unique ID.
     * @apiParam {Number} id Album unique ID.
     *
     * @apiSuccess {String} artist Name of the artist.
     * @apiSuccess {String} album Name of the album.
     * @apiSuccess {Object[]} songs List of songs in the album.
     * @apiSuccess {Number} songs.id Song ID.
     * @apiSuccess {String} songs.name Song name.
     * @apiSuccess {String} [songs.lyrics] Song lyrics (optional).
     * @apiSuccess {String} songs.songwriter Songwriter of the song.
     * @apiSuccess {Number} songs.album_id ID of the associated album.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "artist": "The Beatles",
     *   "album": "Abbey Road",
     *   "songs": [
     *     {
     *       "id": 1,
     *       "name": "Come Together",
     *       "lyrics": "Here come old flat top...",
     *       "songwriter": "John Lennon/Paul McCartney",
     *       "album_id": 1
     *     },
     *     {
     *       "id": 2,
     *       "name": "Something",
     *       "lyrics": "Something in the way she moves...",
     *       "songwriter": "George Harrison",
     *       "album_id": 1
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
     *
     * @apiErrorExample {json} Album Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Album not found for this artist"
     * }
     */
    public function index_song($artist_id,$id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->album()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        return response()->json([
            'artist' => $artist->name,
            'album' => $album->name,
            'songs' => $album->song
        ]);
    }

    /**
     * @api {post} /api/artist Create a new artist
     * @apiName CreateArtist
     * @apiGroup Artist
     *
     * @apiBody {String} name Artist name (required).
     * @apiBody {String} nationality Artist nationality (required).
     * @apiBody {String} [image] Artist image URL (optional).
     * @apiBody {String} description Artist description (required).
     * @apiBody {String="yes","no"} is_band Whether the artist is a band ("yes" or "no") (required).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} artist Created artist data.
     * @apiSuccess {Number} artist.id Artist ID.
     * @apiSuccess {String} artist.name Artist name.
     * @apiSuccess {String} artist.nationality Artist nationality.
     * @apiSuccess {String} [artist.image] Artist image URL (optional).
     * @apiSuccess {String} artist.description Artist description.
     * @apiSuccess {String} artist.is_band Indicates if the artist is a band ("yes" or "no").
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "message": "Artist created successfully",
     *   "artist": {
     *     "id": 2,
     *     "name": "Adele",
     *     "nationality": "British",
     *     "image": "adele.jpg",
     *     "description": "Pop and soul singer-songwriter.",
     *     "is_band": "no"
     *   }
     * }
     *
     * @apiError {String} message Error message.
     *
     * @apiErrorExample {json} Validation Error:
     * HTTP/1.1 422 Unprocessable Entity
     * {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "nationality": ["The nationality field is required."]
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
    /**
     * @api {post} /api/artists/:id/members Add a new member to a band
     * @apiName CreateMember
     * @apiGroup Member
     *
     * @apiParam {Number} id Artist unique ID.
     *
     * @apiBody {String} name Member name (required).
     * @apiBody {String} instrument Instrument played by the member (required).
     * @apiBody {Number} year Year the member joined the band (required).
     * @apiBody {String} [image] Member image URL (optional).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} member Created member data.
     * @apiSuccess {Number} member.id Member ID.
     * @apiSuccess {String} member.name Member name.
     * @apiSuccess {String} member.instrument Instrument played by the member.
     * @apiSuccess {Number} member.year Year the member joined.
     * @apiSuccess {Number} member.artist_id ID of the associated artist.
     * @apiSuccess {String} [member.image] Member image URL (optional).
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "message": "Member created successfully",
     *   "member": {
     *     "id": 1,
     *     "name": "John Lennon",
     *     "instrument": "Guitar",
     *     "year": 1960,
     *     "artist_id": 1,
     *     "image": "john_lennon.jpg"
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
     * @apiErrorExample {json} Artist Not a Band:
     * HTTP/1.1 400 Bad Request
     * {
     *   "message": "Artist is not a band"
     * }
     *
     * @apiErrorExample {json} Validation Error:
     * HTTP/1.1 422 Unprocessable Entity
     * {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "instrument": ["The instrument field is required."],
     *     "year": ["The year field is required."]
     *   }
     * }
     */
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
            'name' => 'required|string|max:255',
            'cover' => 'nullable|string',
            'year' => 'required|integer',
            'genre' => 'required|string|max:255',
        ]);

        $album = $artist->album()->create($request->all());

        return response()->json(['message' => 'Album created successfully', 'album' => $album], 201);
    }
    /**
     * @api {post} /api/artists/:artist_id/albums/:id/songs Add a new song to an album
     * @apiName CreateSong
     * @apiGroup Song
     *
     * @apiParam {Number} artist_id Artist unique ID.
     * @apiParam {Number} id Album unique ID.
     *
     * @apiBody {String} name Song name (required).
     * @apiBody {String} songwriter Songwriter of the song (required).
     * @apiBody {String} [lyrics] Song lyrics (optional).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} song Created song data.
     * @apiSuccess {Number} song.id Song ID.
     * @apiSuccess {String} song.name Song name.
     * @apiSuccess {String} song.songwriter Songwriter of the song.
     * @apiSuccess {String} [song.lyrics] Song lyrics (optional).
     * @apiSuccess {Number} song.album_id ID of the associated album.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "message": "Song created successfully",
     *   "song": {
     *     "id": 1,
     *     "name": "Come Together",
     *     "songwriter": "John Lennon/Paul McCartney",
     *     "lyrics": "Here come old flat top...",
     *     "album_id": 1
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
     *     "name": ["The name field is required."],
     *     "songwriter": ["The songwriter field is required."]
     *   }
     * }
     */
    public function store_song(Request $request, $artist_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->album()->find($id);

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
     * @api {put} /api/artist/:id Update an artist
     * @apiName UpdateArtist
     * @apiGroup Artist
     *
     * @apiParam {Number} id Artist unique ID.
     *
     * @apiBody {String} [name] Artist name.
     * @apiBody {String} [nationality] Artist nationality.
     * @apiBody {String} [image] Artist image URL.
     * @apiBody {String} [description] Artist description.
     * @apiBody {String="yes","no"} [is_band] Whether the artist is a band ("yes" or "no").
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} artist Updated artist data.
     * @apiSuccess {Number} artist.id Artist ID.
     * @apiSuccess {String} artist.name Artist name.
     * @apiSuccess {String} artist.nationality Artist nationality.
     * @apiSuccess {String} [artist.image] Artist image URL (optional).
     * @apiSuccess {String} artist.description Artist description.
     * @apiSuccess {String} artist.is_band Indicates if the artist is a band ("yes" or "no").
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Artist updated successfully",
     *   "artist": {
     *     "id": 2,
     *     "name": "Adele (Updated)",
     *     "nationality": "British",
     *     "image": "adele_updated.jpg",
     *     "description": "Updated biography...",
     *     "is_band": "no"
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
     *     "name": ["The name must be a string."],
     *     "is_band": ["The selected is_band is invalid."]
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
    /**
     * @api {put} /api/artists/:artist_id/members/:id Update a member of a band
     * @apiName UpdateMember
     * @apiGroup Member
     *
     * @apiParam {Number} artist_id Artist unique ID.
     * @apiParam {Number} id Member unique ID.
     *
     * @apiBody {String} [name] Member name.
     * @apiBody {String} [instrument] Member instrument.
     * @apiBody {Number} [year] Year the member joined or relevant year.
     * @apiBody {String} [image] Member image URL.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} member Updated member data.
     * @apiSuccess {Number} member.id Member ID.
     * @apiSuccess {String} member.name Member name.
     * @apiSuccess {String} member.instrument Member instrument.
     * @apiSuccess {Number} member.year Year associated with the member.
     * @apiSuccess {String} [member.image] Member image URL (optional).
     * @apiSuccess {Number} member.artist_id ID of the associated artist.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Member updated successfully",
     *   "member": {
     *     "id": 1,
     *     "name": "John Lennon",
     *     "instrument": "Guitar",
     *     "year": 1960,
     *     "image": "john_lennon.jpg",
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
     * @apiErrorExample {json} Artist Not a Band:
     * HTTP/1.1 400 Bad Request
     * {
     *   "message": "Artist is not a band"
     * }
     *
     * @apiErrorExample {json} Member Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Member not found for this artist"
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
            return response()->json(['message' => 'Member not found'], 404);
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

        $album = $artist->album()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
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
    /**
     * @api {put} /api/artists/:artist_id/albums/:album_id/songs/:id Update a song of an album
     * @apiName UpdateSong
     * @apiGroup Song
     *
     * @apiParam {Number} artist_id Artist unique ID.
     * @apiParam {Number} album_id Album unique ID.
     * @apiParam {Number} id Song unique ID.
     *
     * @apiBody {String} [name] Song name.
     * @apiBody {String} [songwriter] Songwriter name.
     * @apiBody {String} [lyrics] Song lyrics.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} song Updated song data.
     * @apiSuccess {Number} song.id Song ID.
     * @apiSuccess {String} song.name Song name.
     * @apiSuccess {String} song.songwriter Songwriter name.
     * @apiSuccess {String} [song.lyrics] Song lyrics (optional).
     * @apiSuccess {Number} song.album_id ID of the associated album.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Song updated successfully",
     *   "song": {
     *     "id": 1,
     *     "name": "Come Together (Updated)",
     *     "songwriter": "Lennon/McCartney",
     *     "lyrics": "Here come old flat top...",
     *     "album_id": 1
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
     * @apiErrorExample {json} Song Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Song not found for this album"
     * }
     *
     * @apiErrorExample {json} Validation Error:
     * HTTP/1.1 422 Unprocessable Entity
     * {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name must be a string."],
     *     "songwriter": ["The songwriter must be a string."]
     *   }
     * }
     */
    public function update_song(Request $request, $artist_id, $album_id, $id)
    {

        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->album()->find($album_id);

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
     * @api {delete} /api/artists/:id Delete an artist
     * @apiName DeleteArtist
     * @apiGroup Artist
     *
     * @apiParam {Number} id Artist unique ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted artist ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 410 Gone
     * {
     *   "message": "Artist deleted successfully",
     *   "id": 2
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
     * @api {delete} /api/artists/:artist_id/members/:id Delete a member of a band
     * @apiName DeleteMember
     * @apiGroup Member
     *
     * @apiParam {Number} artist_id Artist unique ID.
     * @apiParam {Number} id Member unique ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted member ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 410 Gone
     * {
     *   "message": "Member deleted successfully",
     *   "id": 3
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
     * @apiErrorExample {json} Artist Not a Band:
     * HTTP/1.1 400 Bad Request
     * {
     *   "message": "Artist is not a band"
     * }
     *
     * @apiErrorExample {json} Member Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Member not found"
     * }
     */
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


        $album = $artist->album()->find($id);

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        $album->delete();

        return response()->json(['message' => 'Album deleted successfully', 'id' => $id], 410);
    }
    /**
     * @api {delete} /api/artists/:artist_id/albums/:album_id/songs/:id Delete a song from an album
     * @apiName DeleteSong
     * @apiGroup Song
     *
     * @apiParam {Number} artist_id Artist unique ID.
     * @apiParam {Number} album_id Album unique ID.
     * @apiParam {Number} id Song unique ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted song ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 410 Gone
     * {
     *   "message": "Song deleted successfully",
     *   "id": 8
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
     * @apiErrorExample {json} Song Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Song not found for this album"
     * }
     */
    public function destroy_song($artist_id, $album_id, $id)
    {
        $artist = Artist::find($artist_id);

        if (!$artist) {
            return response()->json(['message' => 'Artist not found'], 404);
        }

        $album = $artist->album()->find($album_id);

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
