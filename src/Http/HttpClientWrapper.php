<?php

namespace RadioAPI\Http;

use RadioAPI\Exceptions\RadioAPIException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * HTTP client wrapper for RadioAPI
 * 
 * Handles common HTTP patterns, response parsing, and error handling
 * for all RadioAPI endpoints.
 */
class HttpClientWrapper
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        private ?string $apiKey,
        private array $defaultOptions = []
    ) {
        $this->baseUrl = rtrim($this->baseUrl, '/');
    }

    /**
     * Perform a GET request to the specified endpoint
     *
     * @param string $endpoint The API endpoint path
     * @param array $params Query parameters to include in the request
     * @return array The parsed response data
     * @throws RadioAPIException When API returns error response or request fails
     */
    public function get(string $endpoint, array $params = []): array
    {
        // Build the full URL
        $url = $this->buildUrl($endpoint, $params);
        
        try {
            $response = $this->httpClient->request('GET', $url, $this->defaultOptions);
            return $this->handleResponse($response);
        } catch (ExceptionInterface $e) {
            // Convert HTTP client exceptions to RadioAPIException
            throw new RadioAPIException(
                'HTTP request failed: ' . $e->getMessage(),
                0,
                [],
                ['url' => $url, 'endpoint' => $endpoint],
                $e
            );
        }
    }

    /**
     * Build the complete URL for a request
     *
     * @param string $endpoint The API endpoint path
     * @param array $params Query parameters
     * @return string The complete URL
     */
    private function buildUrl(string $endpoint, array $params = []): string
    {
        // Add API key to parameters if available
        if ($this->apiKey !== null) {
            $params['api_key'] = $this->apiKey;
        }

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Handle the HTTP response and parse it to an array
     *
     * @param ResponseInterface $response The HTTP response
     * @return array The parsed response data
     * @throws RadioAPIException When response contains errors
     */
    private function handleResponse(ResponseInterface $response): array
    {
        try {
            // Parse response to array (false = don't throw on HTTP errors)
            $data = $response->toArray(false);
            $statusCode = $response->getStatusCode();

            // Check for API errors in the response
            $this->validateApiResponse($data, $statusCode, $response->getInfo('url'));

            return $data;
        } catch (ExceptionInterface $e) {
            throw new RadioAPIException(
                'Failed to parse response: ' . $e->getMessage(),
                $response->getStatusCode(),
                [],
                ['url' => $response->getInfo('url')],
                $e
            );
        }
    }

    /**
     * Validate API response and throw appropriate exceptions for error responses
     *
     * @param array $data The response data
     * @param int $statusCode The HTTP status code
     * @param string $url The request URL for context
     * @throws RadioAPIException When API returns error response
     */
    private function validateApiResponse(array $data, int $statusCode, string $url): void
    {
        // Check for HTTP error status codes
        if ($statusCode >= 400) {
            throw $this->createException($data, $statusCode, $url);
        }

        // Check for explicit error field in response
        if (isset($data['error'])) {
            throw $this->createException($data, $statusCode, $url);
        }

        // Check for error status codes in response data
        if (isset($data['status']) && $data['status'] >= 400) {
            throw $this->createException($data, $data['status'], $url);
        }
    }

    /**
     * Create a RadioAPIException based on error data and status code
     *
     * @param array $errorData The error response data
     * @param int $statusCode The HTTP status code
     * @param string $url The request URL for context
     * @return RadioAPIException The created exception
     */
    private function createException(array $errorData, int $statusCode, string $url): RadioAPIException
    {
        // Extract error message from various possible fields
        $message = $errorData['error'] 
            ?? $errorData['message'] 
            ?? $errorData['detail'] 
            ?? 'Unknown API error';

        // Build context information
        $context = [
            'url' => $url,
            'status_code' => $statusCode,
        ];

        return new RadioAPIException($message, $statusCode, $errorData, $context);
    }
}