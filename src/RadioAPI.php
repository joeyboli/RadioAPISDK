<?php

namespace RadioAPI;

use RadioAPI\Paths\MusicSearch;
use RadioAPI\Paths\StreamTitle;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Main RadioAPI client class
 *
 * Provides a convenient interface to access RadioAPI endpoints for retrieving
 * radio stream metadata and searching music tracks across various streaming services.
 *
 * @package RadioAPI
 *
 * @example
 * ```php
 * $api = new RadioAPI();
 * $api->setBaseUrl('https://api.example.com')
 *     ->setApiKey('your-api-key')
 *     ->setMount(RadioAPI::SPOTIFY);
 *
 * $metadata = $api->streamTitle()
 *     ->setStreamUrl('https://stream.example.com/radio')
 *     ->fetchArray();
 * ```
 */
class RadioAPI
{
    /**
     * HTTP client for making API requests
     *
     * @var HttpClientInterface
     */
    private HttpClientInterface $httpClient;

    /**
     * Base URL of the RadioAPI service
     *
     * @var string|null
     */
    private ?string $baseUrl = null;

    /**
     * API key for authentication
     *
     * @var string|null
     */
    private ?string $apiKey = null;

    /**
     * Language code for API responses (ISO 639-1)
     *
     * @var string
     */
    private string $language = 'en';

    /**
     * Whether to include track history in responses
     *
     * @var bool
     */
    private bool $withHistory = true;

    /**
     * Whether to throw exceptions on API errors
     *
     * @var bool
     */
    private bool $throwOnApiErrors = true;

    /**
     * Mount point for specialized endpoints (e.g., 'spotify', 'deezer')
     *
     * @var string|null
     */
    private ?string $mount = null;

    /**
     * Deezer music service mount point
     */
    public const string DEEZER = 'deezer';

    /**
     * Spotify music service mount point
     */
    public const string SPOTIFY = 'spotify';

    /**
     * Apple Music (iTunes) service mount point
     */
    public const string APPLE_MUSIC = 'itunes';

    /**
     * YouTube Music service mount point
     */
    public const string YOUTUBE_MUSIC = 'ytmusic';

    /**
     * FLO Music service mount point
     */
    public const string FLO_MUSIC = 'flomusic';

    /**
     * LINE Music service mount point
     */
    public const string LINE_MUSIC = 'linemusic';

    /**
     * Auto-detect service mount point
     */
    public const string AUTO = 'auto';

    /**
     * AzuraCast radio platform mount point
     */
    public const string AZURACAST = 'azuracast';

    /**
     * Live365 radio platform mount point
     */
    public const string LIVE365 = 'live365';

    /**
     * RadioKing radio platform mount point
     */
    public const string RADIOKING = 'radioking';

    /**
     * Create a new RadioAPI client instance
     *
     */
    public function __construct()
    {
        $this->httpClient = HttpClient::create();
    }

    /**
     * Set the base URL of the RadioAPI service
     *
     * @param string|null $baseUrl The base URL (e.g., 'https://api.example.com')
     * @return self Returns self for method chaining
     */
    public function setBaseUrl(?string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }



    /**
     * Set the API key for authentication
     *
     * @param string|null $apiKey The API key provided by your RadioAPI service
     * @return self Returns self for method chaining
     */
    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Set the language for API responses
     *
     * @param string|null $language ISO 639-1 language code (e.g., 'en', 'fr', 'de').
     *                              Defaults to 'en' if null or empty.
     * @return self Returns self for method chaining
     */
    public function setLanguage(?string $language): self
    {
        $this->language = $language ?: 'en';
        return $this;
    }

    /**
     * Enable or disable track history in API responses
     *
     * @param bool $enabled True to include history, false to exclude it
     * @return self Returns self for method chaining
     */
    public function withHistory(bool $enabled = true): self
    {
        $this->withHistory = $enabled;
        return $this;
    }

    /**
     * Enable or disable exception throwing on API errors
     *
     * When enabled, API errors will throw exceptions. When disabled,
     * errors will be returned in the response array.
     *
     * @param bool $enabled True to throw exceptions, false to return errors in response
     * @return self Returns self for method chaining
     */
    public function setThrowOnApiErrors(bool $enabled): self
    {
        $this->throwOnApiErrors = $enabled;
        return $this;
    }

    /**
     * Set the mount point for specialized service endpoints
     *
     * Mount points direct requests to service-specific endpoints for enhanced
     * metadata retrieval (e.g., Spotify track IDs, Deezer links).
     *
     * @param string|null $mount The mount point name. Use class constants like
     *                           RadioAPI::SPOTIFY, RadioAPI::DEEZER, etc.
     * @return self Returns self for method chaining
     *
     * @see RadioAPI::SPOTIFY
     * @see RadioAPI::DEEZER
     * @see RadioAPI::APPLE_MUSIC
     * @see RadioAPI::YOUTUBE_MUSIC
     */
    public function withService(?string $mount): self
    {
        $this->mount = $mount;
        return $this;
    }

    /**
     * Create a StreamTitle endpoint instance
     *
     * Returns a configured StreamTitle object for retrieving radio stream
     * metadata and current track information.
     *
     * @return StreamTitle Configured StreamTitle instance
     *
     * @example
     * ```php
     * $metadata = $api->streamTitle()
     *     ->setStreamUrl('https://stream.example.com/radio')
     *     ->fetchArray();
     * ```
     */
    public function streamTitle(): StreamTitle
    {
        $streamTitle = new StreamTitle($this->httpClient);
        $this->configureBasePath($streamTitle);
        if ($this->mount) {
            $streamTitle->withService($this->mount);
        }
        return $streamTitle;
    }

    /**
     * Create a MusicSearch endpoint instance
     *
     * Returns a configured MusicSearch object for searching music tracks
     * across various streaming services.
     *
     * @return MusicSearch Configured MusicSearch instance
     *
     * @example
     * ```php
     * $results = $api->musicSearch()
     *     ->search('The Beatles - Hey Jude')
     *     ->fetchArray();
     * ```
     */
    public function musicSearch(): MusicSearch
    {
        $musicSearch = new MusicSearch($this->httpClient);
        $this->configureBasePath($musicSearch);
        if ($this->mount) {
            $musicSearch->withService($this->mount);
        }
        return $musicSearch;
    }

    /**
     * Configure a BasePath instance with current client settings
     *
     * Applies all client configuration (baseUrl, apiKey, language, etc.)
     * to the provided BasePath instance.
     *
     * @param mixed $basePath The BasePath instance to configure
     * @return void
     */
    private function configureBasePath(mixed $basePath): void
    {
        $basePath->setBaseUrl($this->baseUrl);
        $basePath->setApiKey($this->apiKey);
        $basePath->setLanguage($this->language);
        $basePath->withHistory($this->withHistory);
        $basePath->setThrowOnApiErrors($this->throwOnApiErrors);
    }
}