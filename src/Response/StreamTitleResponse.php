<?php

declare(strict_types=1);

namespace RadioAPI\Response;

/**
 * Response object for StreamTitle API endpoint
 *
 * Provides convenient methods for accessing radio stream metadata,
 * including current track information, track history, and stream details.
 *
 * @package RadioAPI\Response
 */
class StreamTitleResponse implements ResponseInterface
{
    /**
     * Raw response data from the API
     */
    private array $data;

    /**
     * Create a new StreamTitleResponse instance
     *
     * @param array $data The raw response data from the StreamTitle API
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
     * Get the current track information
     *
     * @return array|null The current track data, or null if not available
     *
     * @example
     * ```php
     * $track = $response->getCurrentTrack();
     * echo $track['artist'] . ' - ' . $track['song'];
     * ```
     */
    public function getCurrentTrack(): ?array
    {
        if (!($this->data['metadataFound'] ?? false)) {
            return null;
        }

        $track = [];
        
        // Basic track info
        if (isset($this->data['artist'])) {
            $track['artist'] = $this->data['artist'];
        }
        if (isset($this->data['song'])) {
            $track['song'] = $this->data['song'];
        }
        
        // Extended track info (available with service integration)
        $extendedFields = [
            'album', 'genre', 'artwork', 'year', 'duration',
            'elapsed', 'remaining', 'time', 'stream', 'lyrics', 'explicit'
        ];

        foreach ($extendedFields as $field) {
            if (isset($this->data[$field])) {
                $track[$field] = $this->data[$field];
            }
        }
        
        return !empty($track) ? $track : null;
    }

    /**
     * Get the current track artist
     *
     * @return string|null The artist of the currently playing track
     */
    public function getArtist(): ?string
    {
        return $this->getCurrentTrack()['artist'] ?? null;
    }

    /**
     * Get the current track title/song name
     *
     * @return string|null The title of the currently playing track
     */
    public function getTitle(): ?string
    {
        $track = $this->getCurrentTrack();
        return $track['song'] ?? $track['title'] ?? null;
    }

    /**
     * Get the current track album
     *
     * @return string|null The album of the currently playing track
     */
    public function getAlbum(): ?string
    {
        return $this->getCurrentTrack()['album'] ?? null;
    }

    /**
     * Get the track history
     *
     * Returns an array of previously played tracks if history is enabled.
     *
     * @return array Array of historical track information
     *
     * @example
     * ```php
     * foreach ($response->getHistory() as $track) {
     *     echo $track['artist'] . ' - ' . $track['song'] . "\n";
     * }
     * ```
     */
    public function getHistory(): array
    {
        return $this->data['history'] ?? [];
    }

    /**
     * Check if track history is available
     *
     * @return bool True if history data is present, false otherwise
     */
    public function hasHistory(): bool
    {
        return !empty($this->getHistory());
    }

    /**
     * Get the number of tracks in history
     *
     * @return int The count of historical tracks
     */
    public function getHistoryCount(): int
    {
        return count($this->getHistory());
    }

    /**
     * Get the most recent track from history
     *
     * @return array|null The most recent historical track, or null if no history
     */
    public function getLastTrack(): ?array
    {
        $history = $this->getHistory();
        return !empty($history) ? $history[0] : null;
    }

    /**
     * Get general stream information
     *
     * @return array Stream metadata and configuration information
     */
    public function getStreamInfo(): array
    {
        $streamInfo = [];
        
        $streamFields = ['name', 'bitrate', 'format', 'stream_url', 'service'];
        
        foreach ($streamFields as $field) {
            if (isset($this->data[$field])) {
                $streamInfo[$field] = $this->data[$field];
            }
        }
        
        return $streamInfo;
    }
}
