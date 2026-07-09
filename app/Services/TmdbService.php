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

    public function getShows(int $page = 1): array
    {
        return $this->request()
            ->get('/tv/popular', [
                'page' => $page,
                'language' => 'en',
            ])
            ->json('results', []);
    }

    public function getGenres(): array
    {
        return $this->request()
            ->get('/genre/tv/list', [
                'language' => 'en',
            ])
            ->json('genres', []);
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->token)
            ->baseUrl($this->baseUrl);
    }
}