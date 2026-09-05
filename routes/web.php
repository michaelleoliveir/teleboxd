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
    Route::get('/home', [ShowController::class, 'index'])->name('shows.index');
    Route::get('/shows/{show}', [ShowController::class, 'show'])->name('shows.show');

    Route::livewire('/shows', 'pages::shows.catalog')->name('shows.catalog');
    Route::livewire('/u/{user}', 'pages::users.profile')->name('users.profile');
});

require __DIR__ . '/settings.php';
