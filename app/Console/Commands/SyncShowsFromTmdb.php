<?php

namespace App\Console\Commands;

use App\Jobs\SyncShowsFromTmdbJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tmdb:sync')]
#[Description('Sync shows from TMDB')]
class SyncShowsFromTmdb extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        SyncShowsFromTmdbJob::dispatch();
        $this->info('Sync job dispatched.');
    }
}
