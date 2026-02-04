<?php

namespace ElliePHP\Components\HttpClient;

use RuntimeException;
use Throwable;

class RequestException extends RuntimeException
{
    public function __construct(
        string                     $message,
        private readonly int       $statusCode,
        private readonly ?string   $body = null,
        private readonly ?Response $response = null,
        ?Throwable                 $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int { return $this->statusCode; }
    public function getBody(): ?string { return $this->body; }
    public function getResponse(): ?Response { return $this->response; }
}