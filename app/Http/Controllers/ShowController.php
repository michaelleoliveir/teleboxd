<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Models\Genre;
use App\Models\Show;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    public function index(Request $request): View
    {
        $shows = Show::query()
                ->with('actors')
                ->whereNotNull(['poster_path', 'popularity'])
                ->orderByDesc('popularity')
                ->limit(9)
                ->get();

        $actors = $shows->pluck('actors')->flatten()->unique('id')->whereNotNull('profile_path')->take(9);

        return view('catalog', compact('shows', 'actors'));
    }
}
