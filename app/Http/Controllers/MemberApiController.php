<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberApiController extends Controller
{
    /**
     * @api {get} /api/members Get all members
     * @apiName GetMembers
     * @apiGroup Member
     *
     * @apiSuccess {Object[]} members List of members.
     * @apiSuccess {Number} members.id Member ID.
     * @apiSuccess {String} members.name Member name.
     * @apiSuccess {String} members.instrument Member instrument.
     * @apiSuccess {String} members.year Year joined.
     * @apiSuccess {Number} members.artist_id Associated artist ID.
     * @apiSuccess {String} members.image Member image URL (nullable).
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "members": [
     *     {
     *       "id": 1,
     *       "name": "John Lennon",
     *       "instrument": "Guitar",
     *       "year": "1960",
     *       "image": "lennon.jpg"
     *     }
     *   ]
     * }
     */
    public function index() {
        $members = Member::with('artists')->get();
        return response()->json([
            'members' => $members
        ]);
    }

    /**
     * @api {post} /api/members Create a new member
     * @apiName CreateMember
     * @apiGroup Member
     *
     * @apiBody {String} name Member name (required).
     * @apiBody {String} instrument Member instrument (required).
     * @apiBody {String} year Year joined (required).
     * @apiBody {Number} artist_id Associated artist ID (required, must exist in artists table).
     * @apiBody {String} [image] Member image URL (nullable).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} member Created member data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 201 Created
     * {
     *   "message": "Member created successfully",
     *   "member": {
     *     "id": 2,
     *     "name": "Paul McCartney",
     *     "instrument": "Bass",
     *     "year": "1960",
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
            'image' => 'nullable|string'
        ]);

        $member = Member::create($request->all());
        return response()->json(['message' => 'Member created successfully', 'member' => $member], 201);
    }

    /**
     * @api {put} /api/members/:id Update a member
     * @apiName UpdateMember
     * @apiGroup Member
     *
     * @apiParam {Number} id Member ID.
     *
     * @apiBody {String} [name] Member name.
     * @apiBody {String} [instrument] Member instrument.
     * @apiBody {String} [year] Year joined.
     * @apiBody {Number} [artist_id] Associated artist ID (must exist in artists table).
     * @apiBody {String} [image] Member image URL (nullable).
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Object} member Updated member data.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 200 OK
     * {
     *   "message": "Member updated successfully",
     *   "member": {
     *     "id": 2,
     *     "name": "Paul McCartney",
     *     "instrument": "Bass & Piano",
     *     "year": "1960",
     *     "artist_id": 1,
     *     "image": "paul_updated.jpg"
     *   }
     * }
     *
     * @apiErrorExample {json} Member Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Member not found"
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
            return response()->json(['message' => 'Member not found'], 404);
        }

        $member->update($request->all());
        return response()->json(['message' => 'Member updated successfully', 'member' => $member]);
    }

    /**
     * @api {delete} /api/members/:id Delete a member
     * @apiName DeleteMember
     * @apiGroup Member
     *
     * @apiParam {Number} id Member ID.
     *
     * @apiSuccess {String} message Success message.
     * @apiSuccess {Number} id Deleted member ID.
     *
     * @apiSuccessExample {json} Success Response:
     * HTTP/1.1 410 Gone
     * {
     *   "message": "Member deleted successfully",
     *   "id": 2
     * }
     *
     * @apiErrorExample {json} Member Not Found:
     * HTTP/1.1 404 Not Found
     * {
     *   "message": "Member not found"
     * }
     */
    public function destroy($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return response()->json(['message' => 'Member not found'], 404);
        }

        $member->delete();
        return response()->json(['message' => 'Member deleted successfully', 'id' => $id], 410);
    }
}
