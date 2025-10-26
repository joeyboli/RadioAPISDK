<?php

namespace RadioAPI\Paths;

use RadioAPI\Http\HttpClientWrapper;
use RadioAPI\Response\ColorResponse;
use InvalidArgumentException;

class ColorThief
{
    private HttpClientWrapper $httpClient;

    public function __construct(HttpClientWrapper $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get color palette from an image URL
     *
     * @param string $imageUrl The remote image URL to analyze
     * @param array $options Additional options for the request
     * @return ColorResponse The response object containing color data
     * @throws InvalidArgumentException When image URL is empty
     */
    public function getColors(string $imageUrl, array $options = []): ColorResponse
    {
        if (empty($imageUrl)) {
            throw new InvalidArgumentException('Image URL cannot be empty');
        }

        // Build parameters for the request
        $params = array_merge([
            'url' => $imageUrl,
        ], $options);

        // Make the API request
        $data = $this->httpClient->get('/colorthief', $params);

        return new ColorResponse($data);
    }
}