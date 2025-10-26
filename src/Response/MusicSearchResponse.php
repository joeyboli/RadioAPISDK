<?php

namespace RadioAPI\Response;

/**
 * Response object for MusicSearch API endpoint
 *
 * Provides convenient methods for accessing music search results from various
 * streaming services, including track information, artist details, and service-specific data.
 *
 * @package RadioAPI\Response
 */
class MusicSearchResponse implements ResponseInterface
{
    /**
     * Raw response data from the API
     *
     * @var array
     */
    private array $data;

    /**
     * Create a new MusicSearchResponse instance
     *
     * @param array $data The raw response data from the MusicSearch API
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the raw response data from the API
     *
     * @return array The complete, unprocessed response data
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * Check if the API request was successful
     *
     * @return bool True if the request succeeded, false otherwise
     */
    public function isSuccess(): bool
    {
        return !isset($this->data['error']) && !empty($this->data);
    }

    /**
     * Get the error message if the request failed
     *
     * @return string|null The error message, or null if no error occurred
     */
    public function getError(): ?string
    {
        return $this->data['error'] ?? null;
    }

    /**
     * Get all search result tracks
     *
     * @return array Array of track information, or empty array if no results
     */
    public function getTracks(): array
    {
        return $this->data['tracks'] ?? $this->data['results'] ?? [];
    }

    /**
     * Get the first track from search results
     *
     * Useful when you only need the most relevant search result.
     *
     * @return array|null The first track data, or null if no results
     */
    public function getFirstTrack(): ?array
    {
        $tracks = $this->getTracks();
        return !empty($tracks) ? $tracks[0] : null;
    }

    /**
     * Check if the search returned any results
     *
     * @return bool True if results were found, false otherwise
     */
    public function hasResults(): bool
    {
        return !empty($this->getTracks());
    }

    /**
     * Get the total number of search results
     *
     * @return int The number of tracks found
     */
    public function getResultCount(): int
    {
        return count($this->getTracks());
    }

    /**
     * Get search results filtered by a specific service
     *
     * @param string $service The service name (e.g., 'spotify', 'deezer')
     * @return array Array of tracks from the specified service
     */
    public function getTracksByService(string $service): array
    {
        $tracks = $this->getTracks();
        return array_filter($tracks, function ($track) use ($service) {
            return isset($track['service']) && $track['service'] === $service;
        });
    }

    /**
     * Get the search query that was used
     *
     * @return string|null The original search query, or null if not available
     */
    public function getQuery(): ?string
    {
        return $this->data['query'] ?? null;
    }

    /**
     * Get the service that was searched
     *
     * @return string|null The service name that was searched, or null if not specified
     */
    public function getService(): ?string
    {
        return $this->data['service'] ?? null;
    }
}