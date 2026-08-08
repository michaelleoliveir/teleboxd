<?php

namespace App\Http\Controllers;

use App\Models\Show;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    public function index(Request $request): View
    {
        $shows = Show::query()
                ->with('actors')
                ->whereNotNull(['poster_path', 'popularity', 'first_air_date'])
                ->orderByRaw('popularity - ((CURRENT_DATE - first_air_date) * 7.5) DESC')
                ->limit(9)
                ->get();

        $actors = $shows->pluck('actors')->flatten()->unique('id')->whereNotNull('profile_path')->take(9);

        return view('catalog', compact('shows', 'actors'));
    }

    public function show(Show $show): View
    {
        $show->load(['genres', 'actors', 'reviews.user']);

        return view('show', compact('show'));
    }
}
