<?php

namespace RadioAPI\Paths;

use RadioAPI\Http\HttpClientWrapper;
use RadioAPI\Response\StreamTitleResponse;
use InvalidArgumentException;

/**
 * StreamTitle API Path
 *
 * Handles requests to the /streamtitle endpoint and its mounted variants
 * (e.g., /streamtitle/spotify, /streamtitle/deezer) to retrieve stream
 * metadata and track information.
 *
 * @package RadioAPI\Paths
 */
class StreamTitle
{
    private HttpClientWrapper $httpClient;

    public function __construct(HttpClientWrapper $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get stream metadata and track information
     *
     * @param string $streamUrl The stream URL (e.g., 'https://stream.example.com/radio')
     * @param string|null $service Optional service mount point (e.g., 'spotify', 'deezer')
     * @param array $options Additional options for the request
     * @return StreamTitleResponse The response object containing stream metadata
     * @throws InvalidArgumentException When stream URL is empty
     */
    public function getMetadata(string $streamUrl, ?string $service = null, array $options = []): StreamTitleResponse
    {
        if (empty($streamUrl)) {
            throw new InvalidArgumentException('Stream URL cannot be empty');
        }

        // Build the endpoint path
        $endpoint = '/streamtitle';
        if ($service) {
            $endpoint .= '/' . trim($service, '/');
        }

        // Build parameters for the request
        $params = array_merge([
            'url' => $streamUrl,
        ], $options);

        // Make the API request
        $data = $this->httpClient->get($endpoint, $params);

        return new StreamTitleResponse($data);
    }
}