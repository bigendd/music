<?php
// src/Service/SpotifyApiService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class SpotifyApiService
{
    private $client;
    private $clientId;
    private $clientSecret;
    private $accessToken;

    public function __construct(HttpClientInterface $client, string $clientId, string $clientSecret)
    {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken = $this->getAccessToken();
    }

    /**
     * Méthode pour récupérer et stocker le token d'accès
     */
    private function getAccessToken(): string
    {
        try {
            $response = $this->client->request('POST', 'https://accounts.spotify.com/api/token', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                ],
                'body' => [
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $data = $response->toArray();
            return $data['access_token'];
        } catch (ClientExceptionInterface $e) {
            throw new \Exception('Unable to retrieve Spotify access token: ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour récupérer les informations d'un artiste
     */
    public function getArtistInfo(string $artistId): array
    {
        // 1. Récupérer les infos de l'artiste
        $artistResponse = $this->client->request('GET', "https://api.spotify.com/v1/artists/{$artistId}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
        ]);
        $artistData = $artistResponse->toArray();

        // 2. Récupérer les top tracks de l'artiste
        $tracksResponse = $this->client->request('GET', "https://api.spotify.com/v1/artists/{$artistId}/top-tracks?market=FR", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
        ]);
        $tracksData = $tracksResponse->toArray();
        $artistData['top_tracks'] = $tracksData['tracks'];

        // 3. Récupérer les albums de l'artiste (Albums, Singles, Compilations)
        $albumsResponse = $this->client->request('GET', "https://api.spotify.com/v1/artists/{$artistId}/albums?include_groups=album,single,compilation,appears_on&market=FR", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
        ]);
        $albumsData = $albumsResponse->toArray();
        $artistData['albums'] = $albumsData['items'];

        return $artistData;
    }

    /**
     * Méthode générique pour effectuer des requêtes GET à l'API Spotify
     */
    private function apiRequest(string $url): array
    {
        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
            ]);

            return $response->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new \Exception('API request error: ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour rechercher des artistes via l'API Spotify
     */
    public function searchArtists(string $query): array
    {
        $response = $this->client->request('GET', 'https://api.spotify.com/v1/search', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
            'query' => [
                'q' => $query,
                'type' => 'artist',
                'limit' => 5, // Limite pour l'autocomplétion
            ],
        ]);

        return $response->toArray();
    }
}
