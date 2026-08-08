<?php

use App\Http\Controllers\ShowController;
use App\Models\Show;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'shows' => Show::query()->whereNotNull('poster_path')->latest('synced_at')->limit(40)->get(),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/shows', [ShowController::class, 'index'])->name('shows.index');
    Route::get('/shows/{show}', [ShowController::class, 'show'])->name('shows.show');
});

require __DIR__.'/settings.php';
