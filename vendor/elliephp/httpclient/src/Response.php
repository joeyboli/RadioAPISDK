<?php

namespace ElliePHP\Components\HttpClient;

use JsonException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Throwable;

/**
 * Response - Robust wrapper around Symfony ResponseInterface
 */
class Response
{
    private ?string $cachedContent = null;
    private mixed $cachedJson = null;
    private bool $jsonCached = false;

    public function __construct(
        private readonly ResponseInterface $response
    ) {
    }

    /**
     * Get the response body as a string.
     * Throws RequestException on network failure (timeout, DNS, etc).
     *
     * @throws RequestException
     */
    public function body(): string
    {
        if ($this->cachedContent !== null) {
            return $this->cachedContent;
        }

        try {
            // 'false' prevents Symfony from throwing on 4xx/5xx,
            // but it WILL still throw on network transport errors.
            $this->cachedContent = $this->response->getContent(false);
            return $this->cachedContent;
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            throw new RequestException('Network error reading body: ' . $e->getMessage(), 0, null, $this, $e);
        }
    }

    /**
     * Get the HTTP status code.
     * Throws RequestException on network failure.
     *
     * @throws RequestException
     */
    public function status(): int
    {
        try {
            return $this->response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new RequestException('Network error reading status: ' . $e->getMessage(), 0, null, $this, $e);
        }
    }

    /**
     * Get all response headers.
     *
     * @throws RequestException
     */
    public function headers(): array
    {
        try {
            return $this->response->getHeaders(false);
        } catch (TransportExceptionInterface|ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            throw new RequestException('Network error reading headers: ' . $e->getMessage(), 0, null, $this, $e);
        }
    }

    /**
     * Check if the response has a successful status code (2xx)
     */
    public function successful(): bool
    {
        try {
            $status = $this->status();
            return $status >= 200 && $status < 300;
        } catch (RequestException) {
            return false;
        }
    }

    /**
     * Alias for successful()
     */
    public function success(): bool
    {
        return $this->successful();
    }

    /**
     * Check if the response has a failed status code (4xx or 5xx)
     */
    public function failed(): bool
    {
        try {
            $status = $this->status();
            return $status >= 400 && $status < 600;
        } catch (RequestException) {
            return true;
        }
    }

    /**
     * Alias for failed()
     */
    public function isError(): bool
    {
        return $this->failed();
    }

    /**
     * Check if the response has a client error status code (4xx)
     */
    public function isClientError(): bool
    {
        try {
            $status = $this->status();
            return $status >= 400 && $status < 500;
        } catch (RequestException) {
            return false;
        }
    }

    /**
     * Check if the response has a server error status code (5xx)
     */
    public function isServerError(): bool
    {
        try {
            $status = $this->status();
            return $status >= 500 && $status < 600;
        } catch (RequestException) {
            return false;
        }
    }

    /**
     * Check if the response has a redirect status code (3xx)
     */
    public function isRedirect(): bool
    {
        try {
            $status = $this->status();
            return $status >= 300 && $status < 400;
        } catch (RequestException) {
            return false;
        }
    }

    // Specific status code helpers (Safe versions)
    public function isOk(): bool { return $this->checkStatus(200); }
    public function isCreated(): bool { return $this->checkStatus(201); }
    public function isAccepted(): bool { return $this->checkStatus(202); }
    public function isNoContent(): bool { return $this->checkStatus(204); }
    public function isBadRequest(): bool { return $this->checkStatus(400); }
    public function isUnauthorized(): bool { return $this->checkStatus(401); }
    public function isForbidden(): bool { return $this->checkStatus(403); }
    public function isNotFound(): bool { return $this->checkStatus(404); }
    public function isUnprocessableEntity(): bool { return $this->checkStatus(422); }
    public function isTooManyRequests(): bool { return $this->checkStatus(429); }
    public function isInternalServerError(): bool { return $this->checkStatus(500); }
    public function isServiceUnavailable(): bool { return $this->checkStatus(503); }

    private function checkStatus(int $code): bool
    {
        try {
            return $this->status() === $code;
        } catch (RequestException) {
            return false;
        }
    }

    /**
     * Get a specific response header (case-insensitive)
     */
    public function header(string $name): ?string
    {
        try {
            $headers = $this->headers();
            $lowerName = strtolower($name);

            foreach ($headers as $key => $values) {
                if (strtolower($key) === $lowerName) {
                    return is_array($values) ? ($values[0] ?? null) : $values;
                }
            }
        } catch (RequestException) {
            return null;
        }

        return null;
    }

    /**
     * Check if a header exists
     */
    public function hasHeader(string $name): bool
    {
        return $this->header($name) !== null;
    }

    /**
     * Get all values for a specific header
     */
    public function headerValues(string $name): array
    {
        try {
            $headers = $this->headers();
            $lowerName = strtolower($name);

            foreach ($headers as $key => $values) {
                if (strtolower($key) === $lowerName) {
                    return is_array($values) ? $values : [$values];
                }
            }
        } catch (RequestException) {
            return [];
        }

        return [];
    }

    /**
     * Decode JSON response with flexible key access.
     * Safely handles both Invalid JSON AND Network errors.
     */
    public function json(?string $key = null, mixed $default = null, bool $isAssoc = true): mixed
    {
        if (!$this->jsonCached) {
            try {
                $content = $this->body();

                if ($content === '') {
                    $this->cachedJson = null;
                } else {
                    $this->cachedJson = json_decode($content, $isAssoc, 512, JSON_THROW_ON_ERROR);
                }
                $this->jsonCached = true;
            } catch (JsonException) {
                $this->cachedJson = null;
                $this->jsonCached = true;
                return $default;
            } catch (RequestException) {
                // If network failed, we can't parse JSON. Return default.
                return $default;
            }
        }

        if ($key === null) {
            return $this->cachedJson ?? $default;
        }

        // Support dot notation for nested keys
        $value = $this->cachedJson;
        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } elseif (is_object($value) && isset($value->$segment)) {
                $value = $value->$segment;
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Get JSON response or throw an exception if the request failed
     */
    public function jsonOrFail(?string $key = null, mixed $default = null): mixed
    {
        $this->throw();
        return $this->json($key, $default);
    }

    /**
     * Get response as object
     */
    public function object(?string $key = null, mixed $default = null): mixed
    {
        return $this->json($key, $default, false);
    }

    /**
     * Collect response as a collection-like array with helper methods
     */
    public function collect(?string $key = null): ResponseCollection
    {
        $data = $this->json($key, []);
        return new ResponseCollection(is_array($data) ? $data : []);
    }

    /**
     * Throw an exception if the response indicates a failure
     */
    public function throw(?callable $callback = null): self
    {
        if ($this->failed()) {
            try {
                $status = $this->status();
                // Attempt to read body for error details
                $body = $this->body();
            } catch (RequestException $e) {
                // If we can't even read the status/body, re-throw the network exception
                if ($callback) {
                    $callback($e, $this);
                }
                throw $e;
            }

            // Try to extract error message safely
            $message = "HTTP request returned status code $status";

            // Use safe json method to prevent double-faulting
            $json = $this->json(null, []);

            if (is_array($json)) {
                $message = $json['message'] ?? $json['error'] ?? $json['error_description'] ?? $message;
            }

            $exception = new RequestException($message, $status, $body, $this);

            if ($callback) {
                $callback($exception, $this);
            }

            throw $exception;
        }

        return $this;
    }

    /**
     * Throw an exception if a condition is true
     */
    public function throwIf(bool|callable $condition, ?string $message = null): self
    {
        $shouldThrow = is_callable($condition) ? $condition($this) : $condition;

        if ($shouldThrow) {
            $message = $message ?? "HTTP request condition failed";
            try {
                $status = $this->status();
            } catch (RequestException) {
                $status = 0;
            }
            throw new RequestException($message, $status, null, $this);
        }

        return $this;
    }

    /**
     * Throw an exception unless a condition is true
     */
    public function throwUnless(bool|callable $condition, ?string $message = null): self
    {
        $shouldNotThrow = is_callable($condition) ? $condition($this) : $condition;
        return $this->throwIf(!$shouldNotThrow, $message);
    }

    /**
     * Execute callback if response is successful
     */
    public function onSuccess(callable $callback): self
    {
        if ($this->successful()) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Execute callback if response failed
     */
    public function onError(callable $callback): self
    {
        if ($this->failed()) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Get response info (timing, headers size, etc.)
     */
    public function info(?string $key = null): mixed
    {
        try {
            $info = $this->response->getInfo();
            return $key ? ($info[$key] ?? null) : $info;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Get the effective URL (after redirects)
     */
    public function effectiveUrl(): ?string
    {
        return $this->info('url');
    }

    /**
     * Get the total time taken for the request
     */
    public function totalTime(): ?float
    {
        return $this->info('total_time');
    }

    /**
     * Get the underlying Symfony response
     */
    public function toSymfonyResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Convert response to array
     */
    public function toArray(): array
    {
        $data = $this->json();
        return is_array($data) ? $data : [];
    }

    /**
     * Get response body or default value
     */
    public function bodyOr(string $default): string
    {
        try {
            $body = $this->body();
            return empty($body) ? $default : $body;
        } catch (RequestException) {
            return $default;
        }
    }

    /**
     * Check if response body is empty
     */
    public function isEmpty(): bool
    {
        try {
            return empty($this->body());
        } catch (RequestException) {
            return true;
        }
    }

    /**
     * Get response cookies
     */
    public function cookies(): array
    {
        $cookies = [];
        $setCookieHeaders = $this->headerValues('Set-Cookie');

        foreach ($setCookieHeaders as $header) {
            if (preg_match('/^([^=]+)=([^;]+)/', $header, $matches)) {
                $cookies[$matches[1]] = $matches[2];
            }
        }

        return $cookies;
    }

    /**
     * Get a specific cookie value
     */
    public function cookie(string $name): ?string
    {
        return $this->cookies()[$name] ?? null;
    }

    /**
     * Dump the response and continue
     */
    public function dd(): never
    {
        $status = 'Error';
        $headers = [];
        $body = 'Error reading body';

        try { $status = $this->status(); } catch (RequestException) {}
        try { $headers = $this->headers(); } catch (RequestException) {}
        try { $body = $this->body(); } catch (RequestException) {}

        dd([
            'status' => $status,
            'headers' => $headers,
            'body' => $body,
        ]);
    }

    /**
     * Dump the response and continue
     */
    public function dump(): self
    {
        $status = 'Error';
        $headers = [];
        $body = 'Error reading body';

        try { $status = $this->status(); } catch (RequestException) {}
        try { $headers = $this->headers(); } catch (RequestException) {}
        try { $body = $this->body(); } catch (RequestException) {}

        dump([
            'status' => $status,
            'headers' => $headers,
            'body' => $body,
        ]);
        return $this;
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        try {
            return $this->body();
        } catch (RequestException) {
            return '';
        }
    }
}