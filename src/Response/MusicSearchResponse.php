<?php

declare(strict_types=1);

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
     * @inheritDoc
     */
    public function getRawData(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function isSuccess(): bool
    {
        return !isset($this->data['error']);
    }

    /**
     * @inheritDoc
     */
    public function getError(): ?string
    {
        return $this->data['error'] ?? null;
    }

    /**
     * Get all search result tracks
     *
     * @return array Array of track information
     *
     * @example
     * ```php
     * foreach ($response->getTracks() as $track) {
     *     echo $track['artist'] . ' - ' . $track['title'] . "\n";
     * }
     * ```
     */
    public function getTracks(): array
    {
        // Handle multiple tracks in results/tracks array
        if (isset($this->data['tracks']) || isset($this->data['results'])) {
            return $this->data['tracks'] ?? $this->data['results'] ?? [];
        }
        
        // Handle single track result (wrap in array)
        if ($this->hasResults()) {
            return [$this->data];
        }
        
        return [];
    }

    /**
     * Get the first track from search results
     *
     * Useful when you only need the most relevant search result.
     *
     * @return array|null The first track data, or null if no results
     *
     * @example
     * ```php
     * $track = $response->getFirstTrack();
     * if ($track) {
     *     echo "Found: " . $track['artist'] . " - " . $track['title'];
     * }
     * ```
     */
    public function getFirstTrack(): ?array
    {
        // Handle multiple tracks
        if (isset($this->data['tracks']) || isset($this->data['results'])) {
            $tracks = $this->data['tracks'] ?? $this->data['results'] ?? [];
            return !empty($tracks) ? $tracks[0] : null;
        }
        
        // Handle single track result
        if ($this->hasResults()) {
            return $this->data;
        }
        
        return null;
    }

    /**
     * Check if the search returned any results
     *
     * @return bool True if results were found, false otherwise
     */
    public function hasResults(): bool
    {
        if (isset($this->data['error']) || empty($this->data)) {
            return false;
        }
        
        // Check for tracks/results array
        if (isset($this->data['tracks']) || isset($this->data['results'])) {
            $tracks = $this->data['tracks'] ?? $this->data['results'] ?? [];
            return !empty($tracks);
        }
        
        // Check if response contains track fields (single result)
        return isset($this->data['artist']) 
            || isset($this->data['title']) 
            || isset($this->data['song']);
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
     *
     * @example
     * ```php
     * $spotifyTracks = $response->getTracksByService('spotify');
     * ```
     */
    public function getTracksByService(string $service): array
    {
        return $this->filter(fn($track) => 
            isset($track['service']) && $track['service'] === $service
        );
    }

    /**
     * Filter tracks using a custom callback
     *
     * @param callable $callback Filter function that receives a track and returns bool
     * @return array Filtered tracks
     *
     * @example
     * ```php
     * // Get only explicit tracks
     * $explicit = $response->filter(fn($t) => $t['explicit'] ?? false);
     * ```
     */
    public function filter(callable $callback): array
    {
        return array_values(array_filter($this->getTracks(), $callback));
    }

    /**
     * Map tracks to a new structure using a callback
     *
     * @param callable $callback Map function that receives a track and returns transformed data
     * @return array Mapped tracks
     *
     * @example
     * ```php
     * // Get only artist and title
     * $simplified = $response->map(fn($t) => [
     *     'artist' => $t['artist'],
     *     'title' => $t['title']
     * ]);
     * ```
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->getTracks());
    }

    /**
     * Get the search query that was used
     *
     * @return string|null The original search query
     */
    public function getQuery(): ?string
    {
        return $this->data['query'] ?? null;
    }

    /**
     * Get the service that was searched
     *
     * @return string|null The service name that was searched
     */
    public function getService(): ?string
    {
        return $this->data['service'] ?? null;
    }
}
