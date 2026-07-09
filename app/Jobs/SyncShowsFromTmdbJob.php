<?php

namespace App\Jobs;

use App\Models\Genre;
use App\Models\Show;
use App\Services\TmdbService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Log;

#[Timeout(300)]
#[Tries(3)]
class SyncShowsFromTmdbJob implements ShouldQueue
{
    use Queueable;

    private const TOTAL_PAGES = 10;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(TmdbService $tmdb): void
    {
        $genresMap = $this->syncGenres($tmdb->getGenres());

        for ($page = 1; $page <= self::TOTAL_PAGES; $page++) {
            try {
                $this->syncPage($tmdb->getShows($page), $genresMap);
            } catch (\Throwable $th) {
                Log::error("Error syncing shows on page {$page}: " . $th->getMessage());
            }
        }
    }

    private function syncGenres(array $genres): array
    {
        foreach ($genres as $genre) {
            Genre::updateOrCreate(
                ['tmdb_id' => $genre['id']],
                ['name' => $genre['name']]
            );
        }

        return Genre::pluck('id', 'tmdb_id')->toArray();
    }

    private function syncPage(array $shows, array $genresMap): void
    {
        foreach ($shows as $show) {
            $this->syncShows($show, $genresMap);
        }
    }

    private function syncShows(array $show, array $genresMap): void
    {
        $model = Show::updateOrCreate(
            ['tmdb_id' => $show['id']],
            [
                'name' => $show['name'],
                'overview' => $show['overview'],
                'poster_path' => $show['poster_path'],
                'first_air_date' => $show['first_air_date'] ?? null,
                'synced_at' => now(),
            ]
        );

        $model->genres()->sync($this->mapGenreIds($show['genre_ids'], $genresMap));
    }

    private function mapGenreIds(array $tmdbGenreIds, array $genresMap): array
    {
        return collect($tmdbGenreIds)
            ->map(fn($tmdbId) => $genresMap[$tmdbId] ?? null)
            ->filter()
            ->values()
            ->toArray();
    }
}
