<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-shows-from-tmdb')]
#[Description('Sync shows from TMDB')]
class SyncShowsFromTmdb extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }

    private function syncGenres(): void
    {
    }
}
