<?php

namespace RadioAPI\Paths;

use RadioAPI\Exceptions\RadioAPIException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use RuntimeException;

abstract class BasePath
{
    protected HttpClientInterface $httpClient;
    protected string $baseUrl = '';
    protected ?string $apiKey = null;
    protected string $language = 'en';
    protected bool $withHistory = true;
    protected array $data = [];
    protected bool $throwOnApiErrors = true;

    abstract protected function getEndpoint(): string;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function setBaseUrl(?string $baseUrl): void
    {
        $this->baseUrl = $baseUrl ? rtrim($baseUrl, '/') : '';
    }

    public function setApiKey(?string $apiKey): void
    {
        $this->apiKey = $apiKey ?: null;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language ?: 'en';
        return $this;
    }

    public function withHistory(bool $enabled = true): self
    {
        $this->withHistory = $enabled;
        return $this;
    }

    public function setThrowOnApiErrors(bool $enabled): self
    {
        $this->throwOnApiErrors = $enabled;
        return $this;
    }

    protected function buildQuery(array $params = []): string
    {
        $params['language'] = $this->language;
        $params['history'] = $this->withHistory ? 'true' : 'false';

        if (!empty($this->apiKey)) {
            $params['api_key'] = $this->apiKey;
        }

        return http_build_query($params);
    }

    protected function buildRequestUrl(array $params = []): string
    {
        return $this->baseUrl . $this->getEndpoint() . '?' . $this->buildQuery($params);
    }

    /**
     * Perform GET request and return response array.
     *
     * @throws RuntimeException When HTTP request fails
     * @throws RadioAPIException When API returns error response
     */
    protected function fetchArrayFromUrl(string $url): array
    {
        try {
            $this->data = $this->httpClient->request('GET', $url)->toArray(false);
        } catch (ExceptionInterface $e) {
            throw new RuntimeException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }

        // Validate API response for errors if error handling is enabled
        $this->validateApiResponse($this->data, $url);

        return $this->data;
    }

    /**
     * Validate API response and throw appropriate exceptions for error responses
     *
     * @param array $response The API response data
     * @param string $url The request URL for context
     * @throws RadioAPIException When API returns error response
     */
    protected function validateApiResponse(array $response, string $url = ''): void
    {
        if (!$this->throwOnApiErrors) {
            return;
        }

        // Check for explicit error field in response
        if (isset($response['error'])) {
            $this->throwApiError($response, $url);
        }

        // Check for error status codes in response
        if (isset($response['status']) && $response['status'] >= 400) {
            $this->throwApiError($response, $url);

        }
    }

    /**
     * Throw appropriate exception based on error response data
     *
     * @param array $errorResponse The error response data
     * @param string $url The request URL for context
     * @throws RadioAPIException When API returns error response
     */
    protected function throwApiError(array $errorResponse, string $url = ''): void
    {
        $status = $errorResponse['status'] ?? 0;
        $message = $errorResponse['error'] ?? $errorResponse['message'] ?? 'Unknown API error';
        
        // Build context information
        $context = [
            'url' => $url,
            'endpoint' => $this->getEndpoint(),
        ];

        // Use single RadioAPIException - error type can be determined using convenience methods
        throw new RadioAPIException($message, $status, $errorResponse, $context);
    }

    public function hasMetadata(): bool
    {
        return isset($this->data['metadataFound']) && $this->data['metadataFound'] === true;
    }

    public function getData(): array
    {
        return $this->data;
    }
}