<?php

namespace RadioAPI\Paths;

use RuntimeException;

/**
 * StreamTitle API Path
 *
 * Handles requests to the /streamtitle endpoint and its mounted variants
 * (e.g., /streamtitle/spotify, /streamtitle/deezer) to retrieve stream
 * metadata and track information.
 *
 * @package RadioAPI\Paths
 */
class StreamTitle extends BasePath
{

    /**
     * The URL of the radio stream to fetch metadata from
     *
     * @var string|null
     */
    private ?string $streamUrl = null;

    /**
     * Optional mount point for specialized endpoints (e.g., 'spotify', 'deezer')
     *
     * @var string|null
     */
    protected ?string $mount = null;

    /**
     * Get the API endpoint path
     *
     * Constructs the endpoint URL, appending the mount point if set.
     * Examples: '/streamtitle' or '/streamtitle/spotify'
     *
     * @return string The endpoint path
     */
    protected function getEndpoint(): string
    {
        $endpoint = '/streamtitle';

        if ($this->mount) {
            $endpoint .= '/' . trim($this->mount, '/');
        }

        return $endpoint;
    }

    /**
     * Set the radio stream URL to fetch metadata from
     *
     * @param string|null $streamUrl The stream URL (e.g., 'https://stream.example.com/radio')
     * @return self
     */
    public function setStreamUrl(?string $streamUrl): self
    {
        $this->streamUrl = $streamUrl;
        return $this;
    }

    /**
     * Set the mount point for specialized endpoints
     *
     * Mount points provide access to service-specific metadata endpoints.
     * Common values include 'spotify', 'deezer', 'apple', etc.
     *
     * @param string|null $serviceName The mount point name (e.g., 'spotify')
     * @return self
     */
    public function withService(?string $serviceName): self
    {
        $this->mount = $serviceName;
        return $this;
    }

    /**
     * Fetch stream metadata as an array
     *
     * Makes a request to the configured endpoint and returns the metadata
     * response. Returns an empty array if baseUrl or streamUrl is not set.
     *
     * @return array The metadata response array, or empty array if configuration is incomplete
     *               Successful responses include 'metadataFound' key
     *               Error responses include 'metadataFound' => false and 'error' key
     * @throws RuntimeException
     */
    public function fetchArray(): array
    {
        if (empty($this->baseUrl) || empty($this->streamUrl)) {
            return [];
        }

        $url = $this->buildRequestUrl([
            'url' => $this->streamUrl,
        ]);

        return $this->fetchArrayFromUrl($url);
    }

}