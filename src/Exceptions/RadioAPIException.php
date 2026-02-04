<?php

declare(strict_types=1);

namespace RadioAPI\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception class for RadioAPI errors
 *
 * Provides detailed error information including status codes, error data,
 * and context about the failed request.
 *
 * @package RadioAPI\Exceptions
 */
class RadioAPIException extends RuntimeException
{
    /**
     * Original API error response data
     */
    private array $errorData;

    /**
     * HTTP status code
     */
    private int $statusCode;

    /**
     * Additional context information
     */
    private array $context;

    /**
     * Create a new RadioAPIException instance
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errorData Original API error response data
     * @param array $context Additional context information
     * @param Throwable|null $previous Previous exception in the chain
     */
    public function __construct(
        string $message = '',
        int $statusCode = 0,
        array $errorData = [],
        array $context = [],
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        
        $this->statusCode = $statusCode;
        $this->errorData = $errorData;
        $this->context = $context;
    }

    /**
     * Get the original API error response data
     *
     * @return array The error data returned by the API
     *
     * @example
     * ```php
     * try {
     *     $api->getStreamTitle('invalid-url');
     * } catch (RadioAPIException $e) {
     *     $errorData = $e->getErrorData();
     *     print_r($errorData);
     * }
     * ```
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Get the HTTP status code
     *
     * @return int The HTTP status code (e.g., 404, 500)
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get additional context information
     *
     * Context may include URL, endpoint, and other request details.
     *
     * @return array Additional context about the error
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Check if the error data contains a specific field
     *
     * @param string $field Field name to check
     * @return bool True if the field exists in error data
     */
    public function hasErrorField(string $field): bool
    {
        return isset($this->errorData[$field]);
    }

    /**
     * Get a specific field from the error data
     *
     * @param string $field Field name to retrieve
     * @param mixed $default Default value if field doesn't exist
     * @return mixed The field value or default
     */
    public function getErrorField(string $field, mixed $default = null): mixed
    {
        return $this->errorData[$field] ?? $default;
    }

    /**
     * Check if this is a client error (4xx status code)
     *
     * Client errors indicate problems with the request itself.
     *
     * @return bool True if status code is 4xx
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if this is a server error (5xx status code)
     *
     * Server errors indicate problems on the API server side.
     *
     * @return bool True if status code is 5xx
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Check if this is a network/connection error
     *
     * Network errors have no status code or invalid status codes.
     *
     * @return bool True if this is a network error
     */
    public function isNetworkError(): bool
    {
        return $this->statusCode === 0 || $this->statusCode < 100;
    }

    /**
     * Check if this is an unauthorized error (401)
     *
     * @return bool True if status code is 401
     */
    public function isUnauthorized(): bool
    {
        return $this->statusCode === 401;
    }

    /**
     * Check if this is a forbidden error (403)
     *
     * @return bool True if status code is 403
     */
    public function isForbidden(): bool
    {
        return $this->statusCode === 403;
    }

    /**
     * Check if this is a not found error (404)
     *
     * @return bool True if status code is 404
     */
    public function isNotFound(): bool
    {
        return $this->statusCode === 404;
    }

    /**
     * Check if this is a rate limit error (429)
     *
     * @return bool True if status code is 429
     */
    public function isRateLimited(): bool
    {
        return $this->statusCode === 429;
    }

    /**
     * Get a detailed error message including context
     *
     * @return string Detailed error message with context
     *
     * @example
     * ```php
     * try {
     *     $api->searchMusic('query');
     * } catch (RadioAPIException $e) {
     *     error_log($e->getDetailedMessage());
     * }
     * ```
     */
    public function getDetailedMessage(): string
    {
        $message = $this->getMessage();
        
        if ($this->statusCode > 0) {
            $message .= " (HTTP {$this->statusCode})";
        }

        if (!empty($this->context)) {
            $contextStr = json_encode($this->context, JSON_PRETTY_PRINT);
            $message .= "\nContext: {$contextStr}";
        }

        if (!empty($this->errorData)) {
            $errorStr = json_encode($this->errorData, JSON_PRETTY_PRINT);
            $message .= "\nError Data: {$errorStr}";
        }

        return $message;
    }
}
