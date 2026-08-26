<?php

namespace App\Http\Controllers;

use App\Models\Show;
use Illuminate\Contracts\View\View;

class ShowController extends Controller
{
    public function index(): View
    {
        $shows = Show::query()
                ->with('actors')
                ->whereNotNull(['poster_path', 'popularity', 'first_air_date'])
                ->orderByRaw('popularity - ((CURRENT_DATE - first_air_date) * 7.5) DESC')
                ->limit(9)
                ->get();

        $actors = $shows->pluck('actors')->flatten()->unique('id')->whereNotNull('profile_path')->take(9);

        return view('main', compact('shows', 'actors'));
    }

    public function show(Show $show): View
    {
        $show->load(['genres', 'actors', 'reviews.user', 'seasons']);

        return view('show', compact('show'));
    }
}
