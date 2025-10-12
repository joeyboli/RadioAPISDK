<?php

namespace RadioAPI\Exceptions;

/**
 * Exception thrown for generic API errors without specific status codes
 */
class ApiErrorException extends RadioAPICoreException
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
}