<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Searchable\Search;
use App\Models\Artist;
use App\Models\Album;
use App\Models\Song;
class SearchController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->input('query');

        $searchResults = (new Search())
            ->registerModel(Artist::class, 'name') 
            ->registerModel(Album::class, 'name','genre')         
            ->registerModel(Song::class, 'name')         
            ->search($term);

        return response()->json($searchResults->groupByType());
    }
}
