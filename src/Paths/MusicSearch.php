<?php

namespace RadioAPI\Paths;

use RuntimeException;

/**
 * MusicSearch API Path
 *
 * Handles requests to the /musicsearch endpoint and its mounted variants
 * (e.g., /musicsearch/spotify, /musicsearch/deezer) to search for music
 * tracks and metadata.
 *
 * @package RadioAPI\Paths
 */
class MusicSearch extends BasePath
{


    /**
     * The search query for music tracks
     *
     * @var string|null
     */
    private ?string $query = null;

    /**
     * Optional mount point for specialized endpoints (e.g., 'spotify', 'deezer')
     *
     * @var string|null
     */
    protected ?string $mount = null; // Changed from private to protected

    /**
     * Get the API endpoint path
     *
     * Constructs the endpoint URL, appending the mount point if set.
     * Examples: '/musicsearch' or '/musicsearch/spotify'
     *
     * @return string The endpoint path
     */
    protected function getEndpoint(): string
    {
        $endpoint = '/musicsearch';

        if ($this->mount) {
            $endpoint .= '/' . trim($this->mount, '/');
        }

        return $endpoint;
    }

    /**
     * Set the search query for music tracks
     *
     * @param string $songQuery The search query (e.g., 'artist - song title')
     * @return self Returns self for method chaining
     */
    public function search(string $songQuery): self
    {
        $this->query = $songQuery;
        return $this;
    }

    /**
     * Set the mount point for specialized endpoints
     *
     * Mount points provide access to service-specific search endpoints.
     * Common values include 'spotify', 'deezer', 'itunes', etc.
     *
     * @param string|null $mount The mount point name (e.g., 'spotify')
     * @return self Returns self for method chaining
     */
    public function withService(?string $mount): self
    {
        $this->mount = $mount;
        return $this;
    }

    /**
     * Fetch music search results as an array
     *
     * Makes a request to the configured endpoint and returns the search
     * results. Returns an empty array if baseUrl or query is not set.
     *
     * @return array The search results array, or empty array if configuration is incomplete
     * @throws RuntimeException When HTTP request fails
     */
    public function fetchArray(): array
    {
        if (empty($this->baseUrl) || empty($this->query)) {
            return [];
        }

        $url = $this->buildRequestUrl([
            'query' => $this->query,
        ]);

        return $this->fetchArrayFromUrl($url);
    }
}