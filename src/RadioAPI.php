<?php

declare(strict_types=1);

namespace RadioAPI;

use ElliePHP\Components\HttpClient\Http;
use ElliePHP\Components\HttpClient\RequestException;
use InvalidArgumentException;
use RadioAPI\Exceptions\RadioAPIException;
use RadioAPI\Response\ColorResponse;
use RadioAPI\Response\MusicSearchResponse;
use RadioAPI\Response\StreamTitleResponse;

/**
 * RadioAPI Client
 *
 * A fluent PHP client for the RadioAPI service that provides easy access to
 * radio stream metadata, music search, and image color extraction.
 */
class RadioAPI
{
    // Service Constants
    public const string SPOTIFY = 'spotify';
    public const string DEEZER = 'deezer';
    public const string APPLE_MUSIC = 'itunes';
    public const string YOUTUBE_MUSIC = 'ytmusic';
    public const string FLO_MUSIC = 'flomusic';
    public const string LINE_MUSIC = 'linemusic';
    public const string KKBOX_MUSIC = 'kkbox';
    public const string AUTO = 'auto';

    // Platform Constants
    public const string AZURACAST = 'azuracast';
    public const string LIVE365 = 'live365';
    public const string RADIOKING = 'radioking';

    private string $baseUrl;
    private ?string $apiKey;
    private string $languageCode = 'en';
    private bool $includeHistory = true;
    private ?string $defaultService = null;
    private int $timeout = 30;

    /**
     * @param string $baseUrl The base URL of the RadioAPI service
     * @param string|null $apiKey The API key for authentication
     */
    public function __construct(string $baseUrl, ?string $apiKey = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public static function make(string $baseUrl, ?string $apiKey = null): self
    {
        return new self($baseUrl, $apiKey);
    }

    /**
     * Set the language code for API responses (ISO 639-1)
     */
    public function language(string $code): self
    {
        if (!preg_match('/^[a-z]{2}$/', $code)) {
            throw new InvalidArgumentException(
                'Language must be a valid ISO 639-1 code (e.g., "en", "fr", "es")'
            );
        }

        $this->languageCode = $code;
        return $this;
    }

    /**
     * Set the default service/platform for requests
     */
    public function service(string $service): self
    {
        $this->defaultService = $service;
        return $this;
    }

    public function withHistory(bool $enabled = true): self
    {
        $this->includeHistory = $enabled;
        return $this;
    }

    public function withoutHistory(): self
    {
        return $this->withHistory(false);
    }

    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Get current track information from a radio stream
     */
    public function getStreamTitle(
        string $streamUrl,
        ?string $service = null,
        ?bool $withHistory = null
    ): StreamTitleResponse {
        $endpoint = $this->buildEndpoint('/streamtitle', $service);

        $params = [
            'url' => $streamUrl,
            'language' => $this->languageCode,
            'history' => ($withHistory ?? $this->includeHistory) ? 'true' : 'false',
        ];

        return new StreamTitleResponse($this->request($endpoint, $params));
    }

    /**
     * Search for music tracks across streaming services
     */
    public function searchMusic(
        string $query,
        ?string $service = null,
        ?string $language = null
    ): MusicSearchResponse {
        $endpoint = $this->buildEndpoint('/musicsearch', $service);

        $params = [
            'query' => $query,
            'language' => $language ?? $this->languageCode,
        ];

        return new MusicSearchResponse($this->request($endpoint, $params));
    }

    /**
     * Extract dominant colors from an image
     */
    public function getImageColors(string $imageUrl): ColorResponse
    {
        // Use buildEndpoint even for colorthief in case global service settings affect it
        $endpoint = $this->buildEndpoint('/colorthief');

        $params = [
            'url' => $imageUrl,
            'language' => $this->languageCode,
        ];

        return new ColorResponse($this->request($endpoint, $params));
    }

    /**
     * Build the full endpoint path
     */
    private function buildEndpoint(string $path, ?string $service = null): string
    {
        $serviceToUse = $service ?? $this->defaultService;
        $path = '/' . ltrim($path, '/');

        if ($serviceToUse) {
            return rtrim($path, '/') . '/' . ltrim($serviceToUse, '/');
        }

        return $path;
    }

    /**
     * Make an HTTP request to the RadioAPI
     *
     * @throws RadioAPIException
     */
    private function request(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;

        try {
            if ($this->apiKey !== null) {
                $params['api_key'] = $this->apiKey;
            }

            $response = Http::withTimeout($this->timeout)
                ->acceptJson()
                ->get($url, $params);

            if (!$response->successful()) {
                $data = $response->json() ?: [];

                throw new RadioAPIException(
                    $data['error'] ?? $data['message'] ?? 'Unknown API error',
                    $response->status(),
                    $data,
                    [
                        'url' => $url,
                        'status_code' => $response->status(),
                    ]
                );
            }

            return $response->json() ?: [];

        } catch (RequestException $e) {
            throw new RadioAPIException(
                "HTTP request failed: {$e->getMessage()}",
                0,
                [],
                ['url' => $url],
                $e
            );
        }
    }
}