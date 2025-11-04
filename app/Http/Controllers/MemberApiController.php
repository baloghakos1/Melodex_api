<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberApiController extends Controller
{
    /**
     * @api {get} http://localhost:8000/api/members Get all members
     * @apiName GetMembers
     * @apiGroup Member
     *
     * @apiSuccess {Object[]} songs List of members.
     * @apiSuccess {Number} songs.id Member ID.
     * @apiSuccess {String} songs.name Member name.
     * @apiSuccess {String} songs.instrument Member instrument.
     * @apiSuccess {Number} songs.year Year joined.
     * @apiSuccess {Number} songs.artist_id Associated artist ID.
     * @apiSuccess {String} songs.image Member image URL.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "songs": [
     *     {
     *       "id": 1,
     *       "name": "John Lennon",
     *       "instrument": "Guitar",
     *       "year": 1960,
     *       "artist_id": 1,
     *       "image": "lennon.jpg"
     *     }
     *   ]
     * }
     */
    public function index() {
        $songs = Member::all();
        return response()->json(['songs' => $songs]);
    }

    /**
     * @api {post} http://localhost:8000/api/member Create a new member
     * @apiName CreateMember
     * @apiGroup Member
     *
     * @apiBody {String} name Member name (required).
     * @apiBody {String} instrument Member instrument (required).
     * @apiBody {Number} year Year joined (required).
     * @apiBody {Number} artist_id Associated artist ID (required, must exist in artists table).
     * @apiBody {String} [image] Member image URL (optional).
     *
     * @apiSuccess {Object} product Created member data.
     * @apiSuccess {Number} product.id Member ID.
     * @apiSuccess {String} product.name Member name.
     * @apiSuccess {String} product.instrument Member instrument.
     * @apiSuccess {Number} product.year Year joined.
     * @apiSuccess {Number} product.artist_id Associated artist ID.
     * @apiSuccess {String} product.image Member image URL.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "product": {
     *     "id": 2,
     *     "name": "Paul McCartney",
     *     "instrument": "Bass",
     *     "year": 1960,
     *     "artist_id": 1,
     *     "image": "paul.jpg"
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'instrument' => 'required|string|max:255',
            'year' => 'required|integer',
            'artist_id' => 'required|exists:artists,id',
            'image' => 'nullable|string'
        ]);

        $product = Member::create($request->all());
        return response()->json(['product' => $product], 201);
    }

    /**
     * @api {put} http://localhost:8000/api/member/:id Update a member
     * @apiName UpdateMember
     * @apiGroup Member
     *
     * @apiParam {Number} id Member unique ID.
     *
     * @apiBody {String} [name] Member name.
     * @apiBody {String} [instrument] Member instrument.
     * @apiBody {Number} [year] Year joined.
     * @apiBody {Number} [artist_id] Associated artist ID.
     * @apiBody {String} [image] Member image URL.
     *
     * @apiSuccess {Object} product Updated member data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "product": {
     *     "id": 2,
     *     "name": "Paul McCartney",
     *     "instrument": "Bass & Piano",
     *     "year": 1960,
     *     "artist_id": 1,
     *     "image": "paul_updated.jpg"
     *   }
     * }
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'instrument' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'artist_id' => 'nullable|exists:artists,id',
            'image' => 'nullable|string'
        ]);

        $member = Member::find($id);

        if (!$member) {
            return response()->json(['message' => 'Not found!'], 404);
        }

        $member->update($request->all());
        return response()->json(['product' => $member]);
    }

    /**
     * @api {delete} http://localhost:8000/api/member/:id Delete a member
     * @apiName DeleteMember
     * @apiGroup Member
     *
     * @apiParam {Number} id Member unique ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted member ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Member deleted successfully",
     *   "id": 2
     * }
     */
    public function destroy($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json(['message' => 'Not found!'], 404);
        }

        $member->delete();
        return response()->json(['message' => 'Member deleted successfully', 'id' => $id], 410);
    }
}
