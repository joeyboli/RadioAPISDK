<?php

namespace ElliePHP\Components\HttpClient;

use InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * ClientBuilder - Fluent builder for configuring HTTP requests
 * 
 * Accumulates request configuration before execution.
 */
class ClientBuilder
{
    private ?string $baseUrl = null;
    private array $headers = [];
    private array $options = [];
    private bool $jsonMode = false;

    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    /**
     * Set the base URL for requests
     * 
     * Configures a base URL that will be prepended to all relative paths.
     * Absolute URLs (starting with http:// or https://) will not be modified.
     * Trailing slashes are automatically removed from the base URL.
     * 
     * Example:
     * ```php
     * $builder->withBaseUrl('https://api.example.com')
     *         ->get('/users'); // Requests https://api.example.com/users
     * ```
     * 
     * @param string $baseUrl The base URL to prepend to relative paths
     * @return self Returns this builder for method chaining
     */
    public function withBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    /**
     * Add custom headers to the request
     * 
     * Adds multiple headers to the request. Headers are merged with any
     * existing headers, with new headers taking precedence for duplicate keys.
     * 
     * Example:
     * ```php
     * $builder->withHeaders([
     *     'X-API-Key' => 'secret',
     *     'User-Agent' => 'MyApp/1.0'
     * ]);
     * ```
     * 
     * @param array $headers Associative array of header name => value pairs
     * @return self Returns this builder for method chaining
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Add Bearer token authentication
     * 
     * Sets the Authorization header with a Bearer token for API authentication.
     * This is commonly used for OAuth 2.0 and JWT authentication.
     * 
     * Example:
     * ```php
     * $builder->withToken('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...');
     * // Sets: Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
     * ```
     * 
     * @param string $token The bearer token (without "Bearer " prefix)
     * @return self Returns this builder for method chaining
     */
    public function withToken(string $token): self
    {
        $this->headers['Authorization'] = 'Bearer ' . $token;
        return $this;
    }

    /**
     * Add Basic authentication
     * 
     * Sets the Authorization header with Basic authentication credentials.
     * The username and password are automatically Base64-encoded.
     * 
     * Example:
     * ```php
     * $builder->withBasicAuth('user', 'password');
     * // Sets: Authorization: Basic dXNlcjpwYXNzd29yZA==
     * ```
     * 
     * @param string $username The username for authentication
     * @param string $password The password for authentication
     * @return self Returns this builder for method chaining
     */
    public function withBasicAuth(string $username, string $password): self
    {
        $credentials = base64_encode($username . ':' . $password);
        $this->headers['Authorization'] = 'Basic ' . $credentials;
        return $this;
    }

    /**
     * Set Accept header to application/json
     * 
     * Configures the request to indicate that JSON responses are preferred.
     * This sets the Accept header to "application/json".
     * 
     * Example:
     * ```php
     * $builder->acceptJson()->get('/api/users');
     * // Sets: Accept: application/json
     * ```
     * 
     * @return self Returns this builder for method chaining
     */
    public function acceptJson(): self
    {
        $this->headers['Accept'] = 'application/json';
        return $this;
    }

    /**
     * Set Content-Type header to application/json and enable JSON encoding
     * 
     * Configures the request to send JSON data. This sets the Content-Type
     * header to "application/json" and automatically JSON-encodes the request
     * body for POST, PUT, and PATCH requests.
     * 
     * Example:
     * ```php
     * $builder->asJson()->post('/api/users', ['name' => 'John']);
     * // Sends: {"name":"John"} with Content-Type: application/json
     * ```
     * 
     * @return self Returns this builder for method chaining
     */
    public function asJson(): self
    {
        $this->headers['Content-Type'] = 'application/json';
        $this->jsonMode = true;
        return $this;
    }

    /**
     * Set request timeout in seconds
     * 
     * Configures the maximum time to wait for a response before aborting
     * the request. If the timeout is exceeded, a RequestException will be thrown.
     * 
     * Example:
     * ```php
     * $builder->withTimeout(30)->get('/api/slow-endpoint');
     * // Request will timeout after 30 seconds
     * ```
     * 
     * @param int $seconds Timeout duration in seconds
     * @return self Returns this builder for method chaining
     */
    public function withTimeout(int $seconds): self
    {
        $this->options['timeout'] = $seconds;
        return $this;
    }

    /**
     * Configure retry behavior for failed requests
     * 
     * Symfony HttpClient supports automatic retries for failed requests.
     * This method allows you to configure the retry strategy.
     * 
     * Example usage:
     * ```php
     * $client->withRetry([
     *     'max_retries' => 3,              // Maximum number of retry attempts
     *     'delay' => 1000,                 // Initial delay in milliseconds
     *     'multiplier' => 2,               // Delay multiplier for exponential backoff
     *     'max_delay' => 10000,            // Maximum delay in milliseconds
     *     'jitter' => 0.1,                 // Random jitter factor (0-1)
     *     'http_codes' => [423, 425, 429, 500, 502, 503, 504, 507, 510] // HTTP codes to retry
     * ]);
     * ```
     * 
     * Common retry strategies:
     * - Exponential backoff: Set multiplier > 1 (e.g., 2) to double delay between retries
     * - Fixed delay: Set multiplier = 1 to use constant delay
     * - Jitter: Add randomness to prevent thundering herd (0.1 = ±10% variation)
     * 
     * @param array $retryConfig Retry configuration options
     * @return self
     */
    public function withRetry(array $retryConfig): self
    {
        // Symfony uses 'retry_failed' option for retry configuration
        // The retry_failed option accepts a configuration array or a callable
        $this->options['retry_failed'] = $retryConfig;
        return $this;
    }

    /**
     * Set the User-Agent header
     * 
     * Sets the User-Agent header for the request.
     * 
     * Example:
     * ```php
     * $builder->withUserAgent('MyApp/1.0')->get('/api/data');
     * ```
     * 
     * @param string $userAgent The User-Agent string
     * @return self Returns this builder for method chaining
     */
    public function withUserAgent(string $userAgent): self
    {
        $this->headers['User-Agent'] = $userAgent;
        return $this;
    }

    /**
     * Set the Content-Type header
     * 
     * Sets the Content-Type header for the request.
     * 
     * Example:
     * ```php
     * $builder->withContentType('application/xml')->post('/api/data', $xmlData);
     * ```
     * 
     * @param string $contentType The Content-Type value
     * @return self Returns this builder for method chaining
     */
    public function withContentType(string $contentType): self
    {
        $this->headers['Content-Type'] = $contentType;
        return $this;
    }

    /**
     * Set the Accept header
     * 
     * Sets the Accept header to specify the media types that are acceptable
     * for the response.
     * 
     * Example:
     * ```php
     * $builder->withAccept('application/xml')->get('/api/data');
     * ```
     * 
     * @param string $accept The Accept header value
     * @return self Returns this builder for method chaining
     */
    public function withAccept(string $accept): self
    {
        $this->headers['Accept'] = $accept;
        return $this;
    }

    /**
     * Set a single header
     * 
     * Sets a single header by name and value.
     * 
     * Example:
     * ```php
     * $builder->withHeader('X-Custom-Header', 'value')->get('/api/data');
     * ```
     * 
     * @param string $name The header name
     * @param string $value The header value
     * @return self Returns this builder for method chaining
     */
    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set the maximum number of redirects to follow
     * 
     * Configures the maximum number of redirects that will be followed
     * before throwing an exception.
     * 
     * Example:
     * ```php
     * $builder->withMaxRedirects(5)->get('/api/data');
     * ```
     * 
     * @param int $maxRedirects Maximum number of redirects (0 to disable)
     * @return self Returns this builder for method chaining
     */
    public function withMaxRedirects(int $maxRedirects): self
    {
        $this->options['max_redirects'] = $maxRedirects;
        return $this;
    }

    /**
     * Enable or disable SSL certificate verification
     * 
     * Controls whether SSL certificates should be verified. Disabling
     * verification is not recommended for production use.
     * 
     * Example:
     * ```php
     * $builder->withVerify(false)->get('https://self-signed-cert.example.com');
     * ```
     * 
     * @param bool $verify Whether to verify SSL certificates (default: true)
     * @return self Returns this builder for method chaining
     */
    public function withVerify(bool $verify = true): self
    {
        $this->options['verify_peer'] = $verify;
        $this->options['verify_host'] = $verify;
        return $this;
    }

    /**
     * Configure proxy settings
     * 
     * Sets the proxy URL for requests.
     * 
     * Example:
     * ```php
     * $builder->withProxy('http://proxy.example.com:8080')->get('https://api.example.com/data');
     * ```
     * 
     * @param string $proxyUrl The proxy URL (e.g., 'http://proxy.example.com:8080')
     * @return self Returns this builder for method chaining
     */
    public function withProxy(string $proxyUrl): self
    {
        $this->options['proxy'] = $proxyUrl;
        return $this;
    }

    /**
     * Set additional Symfony HttpClient options
     * 
     * This method allows you to pass any Symfony HttpClient options directly.
     * For retry configuration, prefer using withRetry() for better documentation.
     * 
     * Available options include:
     * - timeout: Request timeout in seconds
     * - max_redirects: Maximum number of redirects to follow
     * - headers: Additional headers
     * - body: Request body
     * - query: Query parameters
     * - auth_basic: Basic authentication [username, password]
     * - auth_bearer: Bearer token
     * - retry_failed: Retry configuration (use withRetry() instead)
     * - and more...
     * 
     * @see https://symfony.com/doc/current/http_client.html#configuration
     * 
     * @param array $options Symfony HttpClient options
     * @return self
     */
    public function withOptions(array $options): self
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * Get the configured base URL
     * 
     * Returns the base URL that will be prepended to relative paths,
     * or null if no base URL has been configured.
     * 
     * @return string|null The base URL, or null if not set
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * Get the configured headers
     * 
     * Returns an associative array of all headers that have been
     * configured on this builder instance.
     * 
     * @return array Associative array of header name => value pairs
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get the configured options
     * 
     * Returns an array of all Symfony HttpClient options that have
     * been configured on this builder instance.
     * 
     * @return array Array of Symfony HttpClient options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Check if JSON mode is enabled
     * 
     * Returns true if asJson() has been called, which enables automatic
     * JSON encoding of request bodies and sets the Content-Type header.
     * 
     * @return bool True if JSON mode is enabled, false otherwise
     */
    public function isJsonMode(): bool
    {
        return $this->jsonMode;
    }

    /**
     * Get the underlying Symfony HttpClient
     * 
     * Returns the Symfony HttpClientInterface instance that will be
     * used to make the actual HTTP requests.
     * 
     * @return HttpClientInterface The Symfony HTTP client instance
     */
    public function getClient(): HttpClientInterface
    {
        return $this->client;
    }

    /**
     * Send a GET request with the configured settings
     * 
     * Sends an HTTP GET request using all configured settings (base URL,
     * headers, timeout, etc.). Query parameters are automatically URL-encoded.
     * 
     * Example:
     * ```php
     * $response = $builder->get('/users', ['page' => 1, 'limit' => 10]);
     * ```
     * 
     * @param string $url The URL or path to request
     * @param array $query Optional query parameters as key => value pairs
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function get(string $url, array $query = []): Response
    {
        return $this->request('GET', $url, ['query' => $query]);
    }

    /**
     * Attach a file to the request
     * 
     * Attaches a file to be uploaded with the request. The file will be sent
     * as multipart/form-data. You can attach multiple files by calling this
     * method multiple times or by passing multiple files in the data array.
     * 
     * The file can be provided as:
     * - A file path (string): The file at the given path will be opened
     * - A file resource: An already opened file resource
     * 
     * Example:
     * ```php
     * // Attach a single file
     * $response = $builder->attach('file', '/path/to/file.jpg')
     *     ->post('/upload');
     * 
     * // Attach multiple files
     * $response = $builder->attach('avatar', '/path/to/avatar.jpg')
     *     ->attach('document', '/path/to/document.pdf')
     *     ->post('/upload');
     * 
     * // Attach file with other form data
     * $response = $builder->post('/upload', [
     *     'name' => 'John',
     *     'file' => fopen('/path/to/file.jpg', 'r')
     * ]);
     * ```
     * 
     * @param string $name The form field name for the file
     * @param string|resource $file The file path or file resource to upload
     * @return self Returns this builder for method chaining
     * @throws InvalidArgumentException If the file path doesn't exist or resource is invalid
     */
    public function attach(string $name, mixed $file): self
    {
        // If it's a string path, open it as a resource
        if (is_string($file)) {
            if (!file_exists($file)) {
                throw new InvalidArgumentException("File not found: {$file}");
            }
            if (!is_readable($file)) {
                throw new InvalidArgumentException("File is not readable: {$file}");
            }
            $file = fopen($file, 'rb');
        }
        
        // Validate it's a resource
        if (!is_resource($file)) {
            throw new InvalidArgumentException("File must be a file path (string) or file resource");
        }
        
        // Store attached files to be merged with body data during request
        if (!isset($this->options['_attached_files'])) {
            $this->options['_attached_files'] = [];
        }
        
        $this->options['_attached_files'][$name] = $file;
        
        return $this;
    }

    /**
     * Send a POST request with the configured settings
     * 
     * Sends an HTTP POST request with the provided data. If asJson() was called,
     * the data will be automatically JSON-encoded. Otherwise, it's sent as form data.
     * 
     * If files have been attached using attach(), they will be included in the request
     * as multipart/form-data. You can also pass file resources directly in the data array.
     * 
     * Example:
     * ```php
     * $response = $builder->asJson()->post('/users', ['name' => 'John', 'email' => 'john@example.com']);
     * 
     * // With file upload
     * $response = $builder->attach('file', '/path/to/file.jpg')
     *     ->post('/upload', ['description' => 'My file']);
     * ```
     * 
     * @param string $url The URL or path to request
     * @param array $data The request body data as key => value pairs
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function post(string $url, array $data = []): Response
    {
        // Merge attached files with data
        if (isset($this->options['_attached_files'])) {
            $data = array_merge($data, $this->options['_attached_files']);
            unset($this->options['_attached_files']);
        }
        
        return $this->request('POST', $url, ['body' => $data]);
    }

    /**
     * Send a PUT request with the configured settings
     * 
     * Sends an HTTP PUT request with the provided data. If asJson() was called,
     * the data will be automatically JSON-encoded. Otherwise, it's sent as form data.
     * 
     * If files have been attached using attach(), they will be included in the request
     * as multipart/form-data.
     * 
     * Example:
     * ```php
     * $response = $builder->asJson()->put('/users/123', ['name' => 'John Updated']);
     * ```
     * 
     * @param string $url The URL or path to request
     * @param array $data The request body data as key => value pairs
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function put(string $url, array $data = []): Response
    {
        // Merge attached files with data
        if (isset($this->options['_attached_files'])) {
            $data = array_merge($data, $this->options['_attached_files']);
            unset($this->options['_attached_files']);
        }
        
        return $this->request('PUT', $url, ['body' => $data]);
    }

    /**
     * Send a PATCH request with the configured settings
     * 
     * Sends an HTTP PATCH request with the provided data. If asJson() was called,
     * the data will be automatically JSON-encoded. Otherwise, it's sent as form data.
     * 
     * If files have been attached using attach(), they will be included in the request
     * as multipart/form-data.
     * 
     * Example:
     * ```php
     * $response = $builder->asJson()->patch('/users/123', ['email' => 'newemail@example.com']);
     * ```
     * 
     * @param string $url The URL or path to request
     * @param array $data The request body data as key => value pairs
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function patch(string $url, array $data = []): Response
    {
        // Merge attached files with data
        if (isset($this->options['_attached_files'])) {
            $data = array_merge($data, $this->options['_attached_files']);
            unset($this->options['_attached_files']);
        }
        
        return $this->request('PATCH', $url, ['body' => $data]);
    }

    /**
     * Send a DELETE request with the configured settings
     * 
     * Sends an HTTP DELETE request using all configured settings.
     * 
     * Example:
     * ```php
     * $response = $builder->delete('/users/123');
     * ```
     * 
     * @param string $url The URL or path to request
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function delete(string $url): Response
    {
        return $this->request('DELETE', $url);
    }

    /**
     * Send an HTTP request with the configured settings
     * 
     * @param string $method The HTTP method
     * @param string $url The URL to request
     * @param array $additionalOptions Additional request options
     * @return Response
     * @throws RequestException
     */
    private function request(string $method, string $url, array $additionalOptions = []): Response
    {
        try {
            // Build the full URL
            $fullUrl = $this->buildUrl($url);
            
            // Prepare request options
            $requestOptions = $this->options;
            
            // Add headers
            if (!empty($this->headers)) {
                $requestOptions['headers'] = array_merge(
                    $requestOptions['headers'] ?? [],
                    $this->headers
                );
            }
            
            // Handle JSON mode
            if ($this->jsonMode && isset($additionalOptions['body'])) {
                $additionalOptions['json'] = $additionalOptions['body'];
                unset($additionalOptions['body']);
            }
            
            // Merge additional options
            $requestOptions = array_merge($requestOptions, $additionalOptions);
            
            // Make the request
            $response = $this->client->request($method, $fullUrl, $requestOptions);
            
            // Return Response wrapper
            return new Response($response);
        } catch (TimeoutExceptionInterface $e) {
            // Wrap timeout exceptions with specific timeout information
            $timeout = $requestOptions['timeout'] ?? 'unknown';
            throw new RequestException(
                "Request timed out after {$timeout} seconds: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        } catch (TransportExceptionInterface $e) {
            // Wrap all other transport exceptions
            throw new RequestException(
                "HTTP request failed: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Build the full URL by prepending base URL if configured
     * 
     * @param string $url The URL or path
     * @return string The full URL
     */
    private function buildUrl(string $url): string
    {
        if ($this->baseUrl === null) {
            return $url;
        }
        
        // If URL is already absolute, return as-is
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }
        
        // Prepend base URL
        $url = ltrim($url, '/');
        return "{$this->baseUrl}/{$url}";
    }
}

