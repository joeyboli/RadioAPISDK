<?php

namespace RadioAPI\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Core exception class for RadioAPI internal errors
 * 
 * Used for internal library errors that are not related to API responses
 */
class RadioAPICoreException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}