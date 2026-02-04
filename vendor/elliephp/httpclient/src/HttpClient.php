<?php

namespace ElliePHP\Components\HttpClient;

use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;

/**
 * HttpClient - Main entry point for making HTTP requests
 * 
 * Provides both static and instance methods for HTTP operations.
 */
class HttpClient
{
    private ?string $baseUrl = null;
    private array $headers = [];
    private array $options = [];

    /**
     * Create a new HttpClient instance
     * 
     * Creates a new HTTP client. If no Symfony HttpClient is provided,
     * a default one will be created automatically when needed.
     * 
     * Example:
     * ```php
     * $client = new HttpClient();
     * $response = $client->get('https://api.example.com/users');
     * ```
     * 
     * @param HttpClientInterface|null $client Optional Symfony HttpClient instance for dependency injection
     */
    public function __construct(
        private readonly ?HttpClientInterface $client = null
    ) {
    }

    /**
     * Handle static method calls by creating a new instance
     * 
     * This allows HttpClient::get(), HttpClient::post(), etc. to work
     * by creating a fresh instance for each static call.
     * 
     * @param string $method The method name
     * @param array $arguments The method arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return new self()->$method(...$arguments);
    }

    /**
     * Get the Symfony HttpClient instance
     * 
     * @return HttpClientInterface
     */
    private function getClient(): HttpClientInterface
    {
        return $this->client ?? SymfonyHttpClient::create();
    }

    /**
     * Set the base URL for requests
     * 
     * Configures a base URL that will be prepended to all relative paths.
     * Returns a ClientBuilder for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withBaseUrl('https://api.example.com')
     *                    ->get('/users');
     * ```
     * 
     * @param string $baseUrl The base URL to prepend to relative paths
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withBaseUrl(string $baseUrl): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        $builder->withBaseUrl($baseUrl);
        
        // Apply existing configuration
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        return $builder;
    }

    /**
     * Add custom headers to the request
     * 
     * Adds multiple headers to the request. Returns a ClientBuilder
     * for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withHeaders([
     *     'X-API-Key' => 'secret',
     *     'User-Agent' => 'MyApp/1.0'
     * ])->get('/api/data');
     * ```
     * 
     * @param array $headers Associative array of header name => value pairs
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withHeaders(array $headers): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        $builder->withHeaders(array_merge($this->headers, $headers));
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        return $builder;
    }

    /**
     * Add Bearer token authentication
     * 
     * Sets the Authorization header with a Bearer token for API authentication.
     * Returns a ClientBuilder for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withToken('your-api-token')
     *                    ->get('/api/protected-resource');
     * ```
     * 
     * @param string $token The bearer token (without "Bearer " prefix)
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withToken(string $token): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withToken($token);
        return $builder;
    }

    /**
     * Add Basic authentication
     * 
     * Sets the Authorization header with Basic authentication credentials.
     * Returns a ClientBuilder for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withBasicAuth('username', 'password')
     *                    ->get('/api/data');
     * ```
     * 
     * @param string $username The username for authentication
     * @param string $password The password for authentication
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withBasicAuth(string $username, string $password): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withBasicAuth($username, $password);
        return $builder;
    }

    /**
     * Set Accept header to application/json
     * 
     * Configures the request to indicate that JSON responses are preferred.
     * Returns a ClientBuilder for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->acceptJson()->get('/api/users');
     * ```
     * 
     * @return ClientBuilder A builder instance for method chaining
     */
    public function acceptJson(): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->acceptJson();
        return $builder;
    }

    /**
     * Set Content-Type header to application/json and enable JSON encoding
     * 
     * Configures the request to send JSON data. Request bodies will be
     * automatically JSON-encoded. Returns a ClientBuilder for further
     * configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->asJson()->post('/api/users', ['name' => 'John']);
     * ```
     * 
     * @return ClientBuilder A builder instance for method chaining
     */
    public function asJson(): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->asJson();
        return $builder;
    }

    /**
     * Set request timeout in seconds
     * 
     * Configures the maximum time to wait for a response. If exceeded,
     * a RequestException will be thrown. Returns a ClientBuilder for
     * further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withTimeout(30)->get('/api/slow-endpoint');
     * ```
     * 
     * @param int $seconds Timeout duration in seconds
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withTimeout(int $seconds): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withTimeout($seconds);
        return $builder;
    }

    /**
     * Configure retry behavior for failed requests
     * 
     * Symfony HttpClient supports automatic retries for failed requests.
     * This method allows you to configure the retry strategy.
     * 
     * Example usage:
     * ```php
     * $client = new HttpClient();
     * $response = $client->withRetry([
     *     'max_retries' => 3,              // Maximum number of retry attempts
     *     'delay' => 1000,                 // Initial delay in milliseconds
     *     'multiplier' => 2,               // Delay multiplier for exponential backoff
     *     'max_delay' => 10000,            // Maximum delay in milliseconds
     *     'jitter' => 0.1,                 // Random jitter factor (0-1)
     *     'http_codes' => [423, 425, 429, 500, 502, 503, 504, 507, 510] // HTTP codes to retry
     * ])->get('https://api.example.com/data');
     * ```
     * 
     * Common retry strategies:
     * - Exponential backoff: Set multiplier > 1 (e.g., 2) to double delay between retries
     * - Fixed delay: Set multiplier = 1 to use constant delay
     * - Jitter: Add randomness to prevent thundering herd (0.1 = ±10% variation)
     * 
     * @param array $retryConfig Retry configuration options
     * @return ClientBuilder
     */
    public function withRetry(array $retryConfig): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withRetry($retryConfig);
        return $builder;
    }

    /**
     * Set the User-Agent header
     * 
     * Sets the User-Agent header for the request. Returns a ClientBuilder
     * for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withUserAgent('MyApp/1.0')
     *                    ->get('https://api.example.com/data');
     * ```
     * 
     * @param string $userAgent The User-Agent string
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withUserAgent(string $userAgent): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withUserAgent($userAgent);
        return $builder;
    }

    /**
     * Set the Content-Type header
     * 
     * Sets the Content-Type header for the request. Returns a ClientBuilder
     * for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withContentType('application/xml')
     *                    ->post('/api/data', $xmlData);
     * ```
     * 
     * @param string $contentType The Content-Type value
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withContentType(string $contentType): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withContentType($contentType);
        return $builder;
    }

    /**
     * Set the Accept header
     * 
     * Sets the Accept header to specify the media types that are acceptable
     * for the response. Returns a ClientBuilder for further configuration
     * and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withAccept('application/xml')
     *                    ->get('/api/data');
     * ```
     * 
     * @param string $accept The Accept header value
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withAccept(string $accept): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withAccept($accept);
        return $builder;
    }

    /**
     * Set a single header
     * 
     * Sets a single header by name and value. Returns a ClientBuilder
     * for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withHeader('X-Custom-Header', 'value')
     *                    ->get('/api/data');
     * ```
     * 
     * @param string $name The header name
     * @param string $value The header value
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withHeader(string $name, string $value): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withHeader($name, $value);
        return $builder;
    }

    /**
     * Set the maximum number of redirects to follow
     * 
     * Configures the maximum number of redirects that will be followed
     * before throwing an exception. Returns a ClientBuilder for further
     * configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withMaxRedirects(5)
     *                    ->get('/api/data');
     * ```
     * 
     * @param int $maxRedirects Maximum number of redirects (0 to disable)
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withMaxRedirects(int $maxRedirects): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withMaxRedirects($maxRedirects);
        return $builder;
    }

    /**
     * Enable or disable SSL certificate verification
     * 
     * Controls whether SSL certificates should be verified. Disabling
     * verification is not recommended for production use. Returns a
     * ClientBuilder for further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withVerify(false)
     *                    ->get('https://self-signed-cert.example.com');
     * ```
     * 
     * @param bool $verify Whether to verify SSL certificates (default: true)
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withVerify(bool $verify = true): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withVerify($verify);
        return $builder;
    }

    /**
     * Configure proxy settings
     * 
     * Sets the proxy URL for requests. Returns a ClientBuilder for
     * further configuration and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withProxy('http://proxy.example.com:8080')
     *                    ->get('https://api.example.com/data');
     * ```
     * 
     * @param string $proxyUrl The proxy URL (e.g., 'http://proxy.example.com:8080')
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withProxy(string $proxyUrl): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->withProxy($proxyUrl);
        return $builder;
    }

    /**
     * Attach a file to the request
     * 
     * Attaches a file to be uploaded with the request. The file will be sent
     * as multipart/form-data. You can attach multiple files by calling this
     * method multiple times.
     * 
     * The file can be provided as:
     * - A file path (string): The file at the given path will be opened
     * - A file resource: An already opened file resource
     * 
     * Example:
     * ```php
     * // Attach a single file
     * $response = $client->attach('file', '/path/to/file.jpg')
     *     ->post('/upload');
     * 
     * // Attach multiple files
     * $response = $client->attach('avatar', '/path/to/avatar.jpg')
     *     ->attach('document', '/path/to/document.pdf')
     *     ->post('/upload');
     * ```
     * 
     * @param string $name The form field name for the file
     * @param string|resource $file The file path or file resource to upload
     * @return ClientBuilder A builder instance for method chaining
     */
    public function attach(string $name, mixed $file): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        if (!empty($this->options)) {
            $builder->withOptions($this->options);
        }
        
        $builder->attach($name, $file);
        return $builder;
    }

    /**
     * Set additional Symfony HttpClient options
     * 
     * Allows passing any Symfony HttpClient options directly for advanced
     * configuration. Returns a ClientBuilder for further configuration
     * and request execution.
     * 
     * Example:
     * ```php
     * $response = $client->withOptions(['max_redirects' => 5])
     *                    ->get('/api/data');
     * ```
     * 
     * @see https://symfony.com/doc/current/http_client.html#configuration
     * @param array $options Symfony HttpClient options
     * @return ClientBuilder A builder instance for method chaining
     */
    public function withOptions(array $options): ClientBuilder
    {
        $builder = new ClientBuilder($this->getClient());
        
        // Apply existing configuration
        if ($this->baseUrl !== null) {
            $builder->withBaseUrl($this->baseUrl);
        }
        if (!empty($this->headers)) {
            $builder->withHeaders($this->headers);
        }
        $builder->withOptions(array_merge($this->options, $options));
        
        return $builder;
    }

    /**
     * Send a GET request
     * 
     * Sends an HTTP GET request to the specified URL. Can be called statically
     * or on an instance. Query parameters are automatically URL-encoded.
     * 
     * Example (static):
     * ```php
     * $response = HttpClient::get('https://api.example.com/users', ['page' => 1]);
     * ```
     * 
     * Example (instance):
     * ```php
     * $client = new HttpClient();
     * $response = $client->get('https://api.example.com/users');
     * ```
     * 
     * @param string $url The URL to request
     * @param array $query Optional query parameters as key => value pairs
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function get(string $url, array $query = []): Response
    {
        return $this->request('GET', $url, ['query' => $query]);
    }

    /**
     * Send a POST request
     * 
     * Sends an HTTP POST request with the provided data. Can be called
     * statically or on an instance. Data is sent as form data by default.
     * 
     * Example (static):
     * ```php
     * $response = HttpClient::post('https://api.example.com/users', [
     *     'name' => 'John',
     *     'email' => 'john@example.com'
     * ]);
     * ```
     * 
     * @param string $url The URL to request
     * @param array $data The request body data as key => value pairs
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function post(string $url, array $data = []): Response
    {
        return $this->request('POST', $url, ['body' => $data]);
    }

    /**
     * Send a PUT request
     * 
     * Sends an HTTP PUT request with the provided data. Can be called
     * statically or on an instance. Data is sent as form data by default.
     * 
     * Example (static):
     * ```php
     * $response = HttpClient::put('https://api.example.com/users/123', [
     *     'name' => 'John Updated'
     * ]);
     * ```
     * 
     * @param string $url The URL to request
     * @param array $data The request body data as key => value pairs
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function put(string $url, array $data = []): Response
    {
        return $this->request('PUT', $url, ['body' => $data]);
    }

    /**
     * Send a PATCH request
     * 
     * Sends an HTTP PATCH request with the provided data. Can be called
     * statically or on an instance. Data is sent as form data by default.
     * 
     * Example (static):
     * ```php
     * $response = HttpClient::patch('https://api.example.com/users/123', [
     *     'email' => 'newemail@example.com'
     * ]);
     * ```
     * 
     * @param string $url The URL to request
     * @param array $data The request body data as key => value pairs
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function patch(string $url, array $data = []): Response
    {
        return $this->request('PATCH', $url, ['body' => $data]);
    }

    /**
     * Send a DELETE request
     * 
     * Sends an HTTP DELETE request. Can be called statically or on an instance.
     * 
     * Example (static):
     * ```php
     * $response = HttpClient::delete('https://api.example.com/users/123');
     * ```
     * 
     * Example (instance):
     * ```php
     * $client = new HttpClient();
     * $response = $client->delete('https://api.example.com/users/123');
     * ```
     * 
     * @param string $url The URL to request
     * @return Response The response wrapper object
     * @throws RequestException If the request fails due to network or timeout errors
     */
    public function delete(string $url): Response
    {
        return $this->request('DELETE', $url);
    }

    /**
     * Send an HTTP request
     * 
     * @param string $method The HTTP method
     * @param string $url The URL to request
     * @param array $options Additional request options
     * @return Response
     * @throws RequestException
     */
    private function request(string $method, string $url, array $options = []): Response
    {
        try {
            // Build the full URL
            $fullUrl = $this->buildUrl($url);
            
            // Merge headers
            $requestOptions = $this->options;
            if (!empty($this->headers)) {
                $requestOptions['headers'] = array_merge(
                    $requestOptions['headers'] ?? [],
                    $this->headers
                );
            }
            
            // Merge additional options
            $requestOptions = array_merge($requestOptions, $options);
            
            // Make the request
            $response = $this->getClient()->request($method, $fullUrl, $requestOptions);
            
            // Return Response wrapper - 4xx/5xx responses are returned without throwing
            // The Response class provides successful() and failed() methods to check status
            return new Response($response);
        } catch (TimeoutExceptionInterface $e) {
            // Wrap timeout exceptions with specific timeout information
            $timeout = $requestOptions['timeout'] ?? 'unknown';
            throw new RequestException(
                "Request timed out after $timeout seconds: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        } catch (TransportExceptionInterface $e) {
            // Wrap all other transport exceptions (network errors, connection failures, etc.)
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
        return "$this->baseUrl/$url";
    }
}
