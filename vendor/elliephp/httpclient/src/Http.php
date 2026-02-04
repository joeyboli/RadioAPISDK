<?php

namespace ElliePHP\Components\HttpClient;

/**
 * Http - Static facade for HttpClient
 * 
 * Provides a convenient static interface to the HttpClient class.
 * All static method calls are forwarded to HttpClient instances.
 * 
 * Example usage:
 * ```php
 * use ElliePHP\Components\HttpClient\Http;
 * 
 * $response = Http::get('https://api.example.com/users');
 * $response = Http::withBaseUrl('https://api.example.com')->get('/users');
 * $response = Http::withToken('token')->get('/api/protected');
 * ```
 * 
 * @method static Response get(string $url, array $query = []) Send a GET request
 * @method static Response post(string $url, array $data = []) Send a POST request
 * @method static Response put(string $url, array $data = []) Send a PUT request
 * @method static Response patch(string $url, array $data = []) Send a PATCH request
 * @method static Response delete(string $url) Send a DELETE request
 * @method static ClientBuilder withBaseUrl(string $baseUrl) Set the base URL for requests
 * @method static ClientBuilder withHeaders(array $headers) Add custom headers to the request
 * @method static ClientBuilder withHeader(string $name, string $value) Set a single header
 * @method static ClientBuilder withUserAgent(string $userAgent) Set the User-Agent header
 * @method static ClientBuilder withContentType(string $contentType) Set the Content-Type header
 * @method static ClientBuilder withAccept(string $accept) Set the Accept header
 * @method static ClientBuilder withToken(string $token) Add Bearer token authentication
 * @method static ClientBuilder withBasicAuth(string $username, string $password) Add Basic authentication
 * @method static ClientBuilder acceptJson() Set Accept header to application/json
 * @method static ClientBuilder asJson() Set Content-Type header to application/json and enable JSON encoding
 * @method static ClientBuilder withTimeout(int $seconds) Set request timeout in seconds
 * @method static ClientBuilder withMaxRedirects(int $maxRedirects) Set maximum number of redirects to follow
 * @method static ClientBuilder withVerify(bool $verify = true) Enable or disable SSL certificate verification
 * @method static ClientBuilder withProxy(string $proxyUrl) Configure proxy settings
 * @method static ClientBuilder withRetry(array $retryConfig) Configure retry behavior for failed requests
 * @method static ClientBuilder withOptions(array $options) Set additional Symfony HttpClient options
 * @method static ClientBuilder attach(string $name, mixed $file) Attach a file to upload with the request (file path or resource)
 */
class Http
{
    /**
     * Forward all static method calls to HttpClient
     * 
     * This allows Http::get(), Http::post(), Http::withBaseUrl(), etc.
     * to work by delegating to HttpClient's static methods.
     * 
     * @param string $method The method name
     * @param array $arguments The method arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return new HttpClient()->$method(...$arguments);
    }
}

