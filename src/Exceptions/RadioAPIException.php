<?php

namespace RadioAPI\Exceptions;

use RuntimeException;

/**
 * Single exception class for all RadioAPI errors
 * Replaces ApiErrorException, ClientErrorException, and ServerErrorException
 */
class RadioAPIException extends RuntimeException
{
    private array $errorData;
    private int $statusCode;
    private array $context;

    public function __construct(
        string $message = '',
        int $statusCode = 0,
        array $errorData = [],
        array $context = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        
        $this->statusCode = $statusCode;
        $this->errorData = $errorData;
        $this->context = $context;
    }

    /**
     * Get the original API error response data
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get additional context information
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Check if the error data contains a specific field
     */
    public function hasErrorField(string $field): bool
    {
        return isset($this->errorData[$field]);
    }

    /**
     * Get a specific field from the error data
     */
    public function getErrorField(string $field, mixed $default = null): mixed
    {
        return $this->errorData[$field] ?? $default;
    }

    /**
     * Check if this is a client error (4xx status code)
     */
    public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Check if this is a server error (5xx status code)
     */
    public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Check if this is a network error (no status code or connection issues)
     */
    public function isNetworkError(): bool
    {
        return $this->statusCode === 0 || $this->statusCode < 100;
    }
}