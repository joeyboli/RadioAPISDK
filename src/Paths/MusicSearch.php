<?php

namespace RadioAPI\Paths;

use RadioAPI\Http\HttpClientWrapper;
use RadioAPI\Response\MusicSearchResponse;
use InvalidArgumentException;

/**
 * MusicSearch API Path
 *
 * Handles requests to the /musicsearch endpoint and its mounted variants
 * (e.g., /musicsearch/spotify, /musicsearch/deezer) to search for music
 * tracks and metadata.
 *
 * @package RadioAPI\Paths
 */
class MusicSearch
{
    private HttpClientWrapper $httpClient;

    public function __construct(HttpClientWrapper $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Search for music tracks
     *
     * @param string $query The search query (e.g., 'artist - song title')
     * @param string|null $service Optional service mount point (e.g., 'spotify', 'deezer')
     * @param array $options Additional options for the request
     * @return MusicSearchResponse The response object containing search results
     * @throws InvalidArgumentException When query is empty
     */
    public function search(string $query, ?string $service = null, array $options = []): MusicSearchResponse
    {
        if (empty($query)) {
            throw new InvalidArgumentException('Search query cannot be empty');
        }

        // Build the endpoint path
        $endpoint = '/musicsearch';
        if ($service) {
            $endpoint .= '/' . trim($service, '/');
        }

        // Build parameters for the request
        $params = array_merge([
            'query' => $query,
        ], $options);

        // Make the API request
        $data = $this->httpClient->get($endpoint, $params);

        return new MusicSearchResponse($data);
    }
}