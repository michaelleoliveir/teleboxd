<?php

use App\Models\Show;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'shows' => Show::query()->whereNotNull('poster_path')->latest('synced_at')->limit(40)->get(),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
