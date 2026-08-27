<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Models\Genre;
use App\Models\Season;
use App\Models\Show;
use App\Services\TmdbService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Log;

#[Timeout(600)]
#[Tries(3)]
class SyncShowsFromTmdbJob implements ShouldQueue
{
    use Queueable;

    private const TOTAL_PAGES = 10;
    private int $created = 0;
    private int $updated = 0;

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
                $this->syncPage($tmdb->getShows($page), $genresMap, $tmdb);
            } catch (\Throwable $th) {
                Log::error("Error syncing shows on page {$page}: " . $th->getMessage());
            }
        }

        Log::info("Sync complete: {$this->created} created, {$this->updated} updated.");
    }

    /**
     * @param array<int, array<string, mixed>> $genres
     * @return array<int, int>
     */
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

    /**
     * @param array<int, array<string, mixed>> $shows
     * @param array<int, int> $genresMap
     * @param TmdbService $tmdb
     */
    private function syncPage(array $shows, array $genresMap, TmdbService $tmdb): void
    {
        foreach ($shows as $show) {
            $this->syncShows($show, $genresMap, $tmdb);
        }
    }

    /**
     * @param array<int|string, mixed> $show
     * @param array<int, int> $genresMap
     * @param TmdbService $tmdb
     */
    private function syncShows(array $show, array $genresMap, TmdbService $tmdb): void
    {
        $details = $tmdb->getShowDetails($show['id']);
        $episodeRuntime = null;

        if (!empty($details['episode_run_time'])) {
            $episodeRuntime = (int) collect((array) $details['episode_run_time'])->avg();
        } elseif (!empty($details['last_episode_to_air']['runtime'])) {
            $episodeRuntime = (int) $details['last_episode_to_air']['runtime'];
        }

        $model = Show::updateOrCreate(
            ['tmdb_id' => $show['id']],
            [
                'name' => $show['name'],
                'overview' => $show['overview'],
                'popularity' => $show['popularity'] ?? null,
                'poster_path' => $show['poster_path'],
                'first_air_date' => $show['first_air_date'] ?? null,
                'number_of_seasons' => $details['number_of_seasons'] ?? null,
                'number_of_episodes' => $details['number_of_episodes'] ?? null,
                'episode_run_time' => $episodeRuntime,
                'synced_at' => now(),
            ]
        );

        if ($model->wasRecentlyCreated) {
            $this->created++;
        } else {
            $this->updated++;
        }

        $model->genres()->sync($this->mapGenreIds($show['genre_ids'], $genresMap));
        $this->syncActors($model, $details['credits']['cast'] ?? []);
        $this->syncSeasons($model, $details['seasons'] ?? []);
    }

    /**
     * @param Show $show
     * @param array<int|string, mixed> $cast
     */
    private function syncActors(Show $show, array $cast): void
    {
        $pivotData = [];
        $cast = collect($cast)
            ->sortBy('order')
            ->take(5);

        foreach ($cast as $actor) {
            $model = Actor::updateOrCreate([
                'tmdb_id' => $actor['id']
            ], [
                'name' => $actor['name'],
                'profile_path' => $actor['profile_path']
            ]);

            $pivotData[$model->id] = [
                'character' => $actor['character'],
                'popularity_order' => $actor['order']
            ];
        };

        $show->actors()->sync($pivotData);
    }

    /**
     * @param Show $show
     * @param array<int|string, mixed> $seasons
     */
    private function syncSeasons(Show $show, array $seasons): void
    {
        foreach ($seasons as $season) {
            if ($season['season_number'] !== 0) {
                Season::updateOrCreate(
                    [
                        'show_id' => $show->id,
                        'season_number' => $season['season_number']
                    ],
                    [
                        'name' => $season['name'],
                        'episode_count' => $season['episode_count'],
                        'air_date' => $season['air_date']
                    ]
                );
            }
        }
    }

    /**
     * @param array<int, int> $tmdbGenreIds
     * @param array<int, int> $genresMap
     * @return array<int, int>
     */
    private function mapGenreIds(array $tmdbGenreIds, array $genresMap): array
    {
        return collect($tmdbGenreIds)
            ->map(fn($tmdbId) => $genresMap[$tmdbId] ?? null)
            ->filter()
            ->values()
            ->toArray();
    }
}
