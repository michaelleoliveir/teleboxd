<?php

namespace App\Services;

use App\Models\Show;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WatchStatsService
{
    public function watchedEpisodesCount(User $user, ?Show $show = null): int
    {
        $query = $user->watchedSeasons();

        if ($show) {
            $query->where('show_id', $show->id);
        }

        return (int) $query->sum('last_watched_episode');
    }

    public function watchedSeasonsCount(User $user, ?Show $show = null): int
    {
        $query = $user->watchedSeasons();

        if ($show) {
            $query->where('show_id', $show->id);
        }

        return (int) $query->whereColumn('watched_seasons.last_watched_episode', '>=', 'seasons.episode_count')->count();
    }

    public function watchedSeasonsCountForUsers(Collection $usersIds, Show $show): array
    {
        return DB::table('watched_seasons')
            ->join('seasons', 'seasons.id', '=', 'watched_seasons.season_id')
            ->where('seasons.show_id', $show->id)
            ->whereIn('watched_seasons.user_id', $usersIds)
            ->whereColumn('watched_seasons.last_watched_episode', '>=', 'seasons.episode_count')
            ->groupBy('watched_seasons.user_id')
            ->select('watched_seasons.user_id', DB::raw('count(*) as total'))
            ->pluck('total', 'user_id')
            ->all();
    }

}