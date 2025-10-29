<?php

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
     *
     * @var array
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
        return !isset($this->data['error']);
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
     * Get the current track information
     *
     * @return array|null The current track data, or null if not available
     */
    public function getCurrentTrack(): ?array
    {
        // Check if metadata was found
        if (!($this->data['metadataFound'] ?? false)) {
            return null;
        }

        // Extract track information from the response
        $track = [];
        
        // Basic track info (always available when metadata is found)
        if (isset($this->data['artist'])) $track['artist'] = $this->data['artist'];
        if (isset($this->data['song'])) $track['song'] = $this->data['song'];
        
        // Extended track info (available with service integration)
        if (isset($this->data['album'])) $track['album'] = $this->data['album'];
        if (isset($this->data['genre'])) $track['genre'] = $this->data['genre'];
        if (isset($this->data['artwork'])) $track['artwork'] = $this->data['artwork'];
        if (isset($this->data['year'])) $track['year'] = $this->data['year'];
        if (isset($this->data['duration'])) $track['duration'] = $this->data['duration'];
        if (isset($this->data['elapsed'])) $track['elapsed'] = $this->data['elapsed'];
        if (isset($this->data['remaining'])) $track['remaining'] = $this->data['remaining'];
        if (isset($this->data['time'])) $track['time'] = $this->data['time'];
        if (isset($this->data['stream'])) $track['stream'] = $this->data['stream'];
        if (isset($this->data['lyrics'])) $track['lyrics'] = $this->data['lyrics'];
        if (isset($this->data['explicit'])) $track['explicit'] = $this->data['explicit'];
        
        return !empty($track) ? $track : null;
    }

    /**
     * Get the track history
     *
     * Returns an array of previously played tracks if history is enabled.
     *
     * @return array Array of historical track information, or empty array if not available
     */
    public function getHistory(): array
    {
        return $this->data['history'] ?? [];
    }

    /**
     * Get general stream information
     *
     * @return array Stream metadata and configuration information
     */
    public function getStreamInfo(): array
    {
        $streamInfo = [];
        
        // Extract stream information from the response
        if (isset($this->data['name'])) $streamInfo['name'] = $this->data['name'];
        if (isset($this->data['bitrate'])) $streamInfo['bitrate'] = $this->data['bitrate'];
        if (isset($this->data['format'])) $streamInfo['format'] = $this->data['format'];
        
        return $streamInfo;
    }

    /**
     * Get the current track title
     *
     * @return string|null The title of the currently playing track, or null if not available
     */
    public function getCurrentTitle(): ?string
    {
        $track = $this->getCurrentTrack();
        return $track['song'] ?? $track['title'] ?? null;
    }

    /**
     * Get the current track artist
     *
     * @return string|null The artist of the currently playing track, or null if not available
     */
    public function getCurrentArtist(): ?string
    {
        $track = $this->getCurrentTrack();
        return $track['artist'] ?? null;
    }

    /**
     * Get the current track album
     *
     * @return string|null The album of the currently playing track, or null if not available
     */
    public function getCurrentAlbum(): ?string
    {
        $track = $this->getCurrentTrack();
        return $track['album'] ?? null;
    }

    /**
     * Get the stream URL that was analyzed
     *
     * @return string|null The stream URL, or null if not available
     */
    public function getStreamUrl(): ?string
    {
        return $this->data['stream_url'] ?? null;
    }

    /**
     * Get the service/platform information
     *
     * @return string|null The service or platform name, or null if not available
     */
    public function getService(): ?string
    {
        return $this->data['service'] ?? null;
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
}