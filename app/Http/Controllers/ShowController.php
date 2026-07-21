<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Show;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    public function index(Request $request): View
    {
        $shows = Show::query()
                ->whereNotNull('poster_path')
                ->when($request->filled('genre'), fn($q) => 
                    $q->whereHas('genres', fn($q) => 
                        $q->where('genres.id', $request->integer('genre'))))
                ->orderByDesc('first_air_date')
                ->paginate(24)
                ->withQueryString();

        $genres = Genre::query()
                ->orderBy('name')
                ->get();

        return view('catalog', compact('shows', 'genres'));
    }
}
