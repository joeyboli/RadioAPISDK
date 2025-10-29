<?php

namespace RadioAPI;

use RadioAPI\Exceptions\RadioAPIException;
use RadioAPI\Http\HttpClientWrapper;
use RadioAPI\Response\ColorResponse;
use RadioAPI\Response\MusicSearchResponse;
use RadioAPI\Response\StreamTitleResponse;
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
 * $api = new RadioAPI('https://api.example.com', 'your-api-key', [
 *     'service' => RadioAPI::SPOTIFY,
 *     'language' => 'en',
 *     'with_history' => true
 * ]);
 *
 * $response = $api->getStreamTitle('https://stream.example.com/radio');
 * $currentTrack = $response->getCurrentTrack();
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
     * HTTP client wrapper for handling common patterns
     *
     * @var HttpClientWrapper
     */
    private HttpClientWrapper $httpWrapper;

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
     * KKBOX Music service mount point
     */
    public const string KKBOX_MUSIC = 'kkbox';


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
     * @param string $baseUrl The base URL of the RadioAPI service
     * @param string|null $apiKey The API key for authentication (optional)
     * @param array $options Configuration options array
     * @throws \InvalidArgumentException When required parameters are invalid
     */
    public function __construct(
        string $baseUrl,
        ?string $apiKey = null,
        array $options = []
    ) {
        // Validate required parameters
        if (empty(trim($baseUrl))) {
            throw new \InvalidArgumentException('Base URL cannot be empty');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;

        // Set configuration options with defaults
        $this->language = $options['language'] ?? 'en';
        $this->withHistory = $options['with_history'] ?? true;
        $this->throwOnApiErrors = $options['throw_on_errors'] ?? true;
        $this->mount = $options['service'] ?? null;

        // Validate language code format (basic validation)
        if (!preg_match('/^[a-z]{2}$/', $this->language)) {
            throw new \InvalidArgumentException('Language must be a valid ISO 639-1 code (e.g., "en", "fr")');
        }

        // Create HTTP client with default options
        $httpClientOptions = [
            'timeout' => $options['timeout'] ?? 30,
            'headers' => [
                'User-Agent' => $options['user_agent'] ?? 'RadioAPI-PHP/2.0',
                'Accept' => 'application/json',
            ],
        ];

        $this->httpClient = HttpClient::create($httpClientOptions);
        
        // Initialize HTTP wrapper with configuration
        $this->httpWrapper = new HttpClientWrapper(
            $this->httpClient,
            $this->baseUrl,
            $this->apiKey,
            $httpClientOptions
        );
    }



    /**
     * Get image colors from a remote image URL
     *
     * Extracts dominant colors and generates a color palette from the specified image.
     * Returns a ColorResponse object with convenient methods for accessing color data.
     *
     * @param string $imageUrl The URL of the image to analyze
     * @return ColorResponse Response object containing color information
     * @throws RadioAPIException When the API request fails or returns an error
     *
     * @example
     * ```php
     * $response = $api->getImageColors('https://example.com/album-art.jpg');
     * $dominantColor = $response->getDominantColorHex();
     * $palette = $response->getPalette();
     * ```
     */
    public function getImageColors(string $imageUrl): ColorResponse
    {
        $params = [
            'url' => $imageUrl,  // Fixed: API expects 'url' not 'image_url'
            'language' => $this->language,
        ];

        $data = $this->httpWrapper->get('/colorthief', $params);
        return new ColorResponse($data);
    }

    /**
     * Search for music tracks across streaming services
     *
     * Searches for music tracks using the specified query string and optional service.
     * Returns a MusicSearchResponse object with methods for accessing search results.
     *
     * @param string $query The search query (artist, track, album, etc.)
     * @param string|null $service Optional service to search (use class constants like RadioAPI::SPOTIFY)
     * @param string|null $language Override language setting for this request (null = use default)
     * @return MusicSearchResponse Response object containing search results
     * @throws RadioAPIException When the API request fails or returns an error
     *
     * @example
     * ```php
     * $response = $api->searchMusic('The Beatles - Hey Jude', RadioAPI::SPOTIFY);
     * $tracks = $response->getTracks();
     * $firstTrack = $response->getFirstTrack();
     * 
     * // Search with specific language override
     * $response = $api->searchMusic('The Beatles - Hey Jude', RadioAPI::SPOTIFY, 'fr');
     * ```
     */
    public function searchMusic(string $query, ?string $service = null, ?string $language = null): MusicSearchResponse
    {
        // Use override parameter if provided, otherwise use instance setting
        $languageToUse = $language ?? $this->language;
        
        $params = [
            'query' => $query,
            'language' => $languageToUse,
        ];

        // Build the endpoint path with service
        $serviceToUse = $service ?? $this->mount;
        $endpoint = '/musicsearch';
        
        if ($serviceToUse) {
            $endpoint = "/musicsearch/{$serviceToUse}";
        }

        $data = $this->httpWrapper->get($endpoint, $params);
        return new MusicSearchResponse($data);
    }

    /**
     * Get current track information from a radio stream
     *
     * Retrieves metadata about the currently playing track from the specified stream URL.
     * Returns a StreamTitleResponse object with methods for accessing track and stream information.
     *
     * @param string $streamUrl The URL of the radio stream to analyze
     * @param string|null $service Optional service/platform hint (use class constants like RadioAPI::AZURACAST)
     * @param bool|null $withHistory Override history setting for this request (null = use default)
     * @return StreamTitleResponse Response object containing stream metadata
     * @throws RadioAPIException When the API request fails or returns an error
     *
     * @example
     * ```php
     * $response = $api->getStreamTitle('https://stream.example.com/radio');
     * $currentTrack = $response->getCurrentTrack();
     * $history = $response->getHistory();
     * 
     * // Disable history for this specific request
     * $response = $api->getStreamTitle('https://stream.example.com/radio', null, false);
     * 
     * // Force enable history for this specific request
     * $response = $api->getStreamTitle('https://stream.example.com/radio', RadioAPI::SPOTIFY, true);
     * ```
     */
    public function getStreamTitle(string $streamUrl, ?string $service = null, ?bool $withHistory = null): StreamTitleResponse
    {
        // Use override parameter if provided, otherwise use instance setting
        $historyEnabled = $withHistory ?? $this->withHistory;
        
        $params = [
            'url' => $streamUrl,  // Fixed: API expects 'url' not 'stream_url'
            'language' => $this->language,
        ];
        
        // Add history parameter - API expects 'history' not 'with_history'
        $params['history'] = $historyEnabled ? 'true' : 'false';

        // Build the endpoint path with service
        $serviceToUse = $service ?? $this->mount;
        $endpoint = '/streamtitle';
        
        if ($serviceToUse) {
            $endpoint = "/streamtitle/{$serviceToUse}";
        }

        $data = $this->httpWrapper->get($endpoint, $params);
        return new StreamTitleResponse($data);
    }


}