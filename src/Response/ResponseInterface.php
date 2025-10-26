<?php

namespace RadioAPI\Response;

/**
 * Common interface for all RadioAPI response objects
 *
 * Provides standard methods that all response types must implement
 * for consistent handling of API responses across different endpoints.
 *
 * @package RadioAPI\Response
 */
interface ResponseInterface
{
    /**
     * Get the raw response data from the API
     *
     * @return array The complete, unprocessed response data
     */
    public function getRawData(): array;

    /**
     * Check if the API request was successful
     *
     * @return bool True if the request succeeded, false otherwise
     */
    public function isSuccess(): bool;

    /**
     * Get the error message if the request failed
     *
     * @return string|null The error message, or null if no error occurred
     */
    public function getError(): ?string;
}