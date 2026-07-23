<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class TmdbService
{
    public function __construct(
        protected ?string $token = null,
        protected ?string $baseUrl = null,
    ) {
        $this->token ??= config('services.tmdb.token');
        $this->baseUrl ??= config('services.tmdb.base_url');
    }

    /** @return array<int, array<string, mixed>> */
    public function getShows(int $page = 1): array
    {
        return $this->request()
            ->get('/tv/popular', [
                'page' => $page,
                'language' => 'en',
            ])
            ->json('results', []);
    }

    /** @return array<int, array<string, mixed>> */
    public function getGenres(): array
    {
        return $this->request()
            ->get('/genre/tv/list', [
                'language' => 'en',
            ])
            ->json('genres', []);
    }

    /** @return array<int, array<string, mixed>> */
    public function getCredits(int $showId): array
    {
        return $this->request()
            ->get("/tv/$showId/credits")
            ->json('cast', []);
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->token)
            ->baseUrl($this->baseUrl);
    }
}