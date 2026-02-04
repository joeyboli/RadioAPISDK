# ElliePHP HttpClient

A simple, Laravel-inspired HTTP client abstraction built on top of Symfony HttpClient. This library provides a fluent, developer-friendly interface for making HTTP requests in PHP applications.

## Features

- Simple and intuitive API with fluent interface for building requests
- Static and instance methods available, use whichever style fits your needs
- Built-in authentication support for Bearer tokens and Basic auth
- Automatic JSON encoding and decoding with convenience methods
- Configurable retry strategies with exponential backoff
- Easy timeout control for requests
- Graceful error handling with custom exceptions
- Convenient response helper methods for checking status and accessing data

## Installation

Install via Composer:

```bash
composer require elliephp/httpclient
```

## Requirements

- PHP 8.4 or higher
- Symfony HttpClient component

## Quick Start

### Http Facade (Recommended)

The `Http` facade provides a clean, static interface for making requests:

```php
use ElliePHP\Components\HttpClient\Http;

// Simple GET request
$response = Http::get('https://api.example.com/users');

// Configured request with method chaining
$response = Http::withBaseUrl('https://api.example.com')
    ->withToken('your-api-token')
    ->withUserAgent('MyApp/1.0')
    ->acceptJson()
    ->get('/users');

// POST request
$response = Http::post('https://api.example.com/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Check response
if ($response->successful()) {
    $data = $response->json();
    echo "User created: " . $data['name'];
}
```

### Static Methods (HttpClient)

For quick, one-off requests, use static methods on `HttpClient`. Note that static methods do not support configuration chaining. They expect full, absolute URLs.

```php
use ElliePHP\Components\HttpClient\HttpClient;

// GET request
$response = HttpClient::get('https://api.example.com/users');

// POST request
$response = HttpClient::post('https://api.example.com/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Check response
if ($response->successful()) {
    $data = $response->json();
    echo "User created: " . $data['name'];
}
```

### Instance Methods (Configured Usage)

For multiple requests with shared configuration, create an instance. When you use configuration methods like `withBaseUrl()`, `withToken()`, etc., they return a `ClientBuilder` instance that properly handles the configuration. The `ClientBuilder` has all the same request methods (get, post, put, patch, delete) and will use your configured settings.

```php
$client = new HttpClient();

// This returns a ClientBuilder with your configuration applied
$response = $client
    ->withBaseUrl('https://api.example.com')
    ->withToken('your-api-token')
    ->acceptJson()
    ->get('/users');  // The ClientBuilder's get() method uses the base URL
```

**Important:** If you call request methods directly on the `HttpClient` instance without any configuration chaining, you must provide full URLs because the configuration is not stored on the `HttpClient` instance itself:

```php
$client = new HttpClient();

// This will NOT work as expected because get() is called directly on HttpClient
// and HttpClient doesn't store the baseUrl configuration
$client->withBaseUrl('https://api.example.com');
$response = $client->get('/users');  // Error: /users is not a valid URL

// This works because the configuration returns ClientBuilder which handles it properly
$response = $client->withBaseUrl('https://api.example.com')->get('/users');
```

## Usage Examples

### Making Requests

#### GET Request

```php
use ElliePHP\Components\HttpClient\Http;

// Simple GET
$response = Http::get('https://api.example.com/users');

// GET with query parameters
$response = Http::get('https://api.example.com/users', [
    'page' => 1,
    'limit' => 10
]);
```

#### POST Request

```php
use ElliePHP\Components\HttpClient\Http;

// POST with form data
$response = Http::post('https://api.example.com/users', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// POST with JSON
$response = Http::asJson()
    ->post('https://api.example.com/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
```

#### PUT Request

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::put('https://api.example.com/users/123', [
    'name' => 'Jane Doe'
]);
```

#### PATCH Request

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::patch('https://api.example.com/users/123', [
    'status' => 'active'
]);
```

#### DELETE Request

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::delete('https://api.example.com/users/123');
```

#### File Uploads

The HTTP client supports file uploads using the `attach()` method. Files are automatically sent as `multipart/form-data`.

**Upload a single file:**

```php
use ElliePHP\Components\HttpClient\Http;

// Upload a file by path
$response = Http::attach('file', '/path/to/image.jpg')
    ->post('https://api.example.com/upload');

// Upload with additional form data
$response = Http::attach('file', '/path/to/image.jpg')
    ->post('https://api.example.com/upload', [
        'description' => 'My uploaded image',
        'category' => 'photos'
    ]);
```

**Upload multiple files:**

```php
// Attach multiple files
$response = Http::attach('avatar', '/path/to/avatar.jpg')
    ->attach('document', '/path/to/document.pdf')
    ->post('https://api.example.com/upload', [
        'user_id' => 123
    ]);
```

**Upload using file resource:**

```php
// Open file and upload
$file = fopen('/path/to/file.jpg', 'r');
$response = Http::attach('file', $file)
    ->post('https://api.example.com/upload');

// Don't forget to close the file if you opened it manually
fclose($file);
```

**Upload with file resource in data array:**

```php
// You can also pass file resources directly in the data array
$response = Http::post('https://api.example.com/upload', [
    'name' => 'John',
    'file' => fopen('/path/to/file.jpg', 'r')
]);
```

**Upload with authentication and configuration:**

```php
$response = Http::withBaseUrl('https://api.example.com')
    ->withToken('your-api-token')
    ->withUserAgent('MyApp/1.0')
    ->attach('file', '/path/to/file.jpg')
    ->post('/upload', [
        'description' => 'Uploaded file'
    ]);
```

**Error Handling:**

The `attach()` method will throw an `InvalidArgumentException` if the file path doesn't exist, the file is not readable, or the provided value is not a file path or resource.

```php
use ElliePHP\Components\HttpClient\Http;
use InvalidArgumentException;

try {
    $response = Http::attach('file', '/nonexistent/file.jpg')
        ->post('https://api.example.com/upload');
} catch (InvalidArgumentException $e) {
    echo "File error: " . $e->getMessage();
}
```

Note that file uploads work with POST, PUT, and PATCH requests. When files are attached, the request is automatically sent as `multipart/form-data`, even if `asJson()` was called earlier.

### Authentication

#### Bearer Token Authentication

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::withToken('your-api-token')
    ->get('https://api.example.com/protected-resource');
```

#### Basic Authentication

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::withBasicAuth('username', 'password')
    ->get('https://api.example.com/protected-resource');
```

### Working with JSON

#### Sending JSON Requests

```php
use ElliePHP\Components\HttpClient\Http;

// asJson() sets Content-Type header and encodes body as JSON
$response = Http::asJson()
    ->post('https://api.example.com/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'metadata' => [
            'role' => 'admin',
            'department' => 'IT'
        ]
    ]);
```

#### Receiving JSON Responses

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::get('https://api.example.com/users/123');

// Get entire JSON response as array
$data = $response->json();
echo $data['name']; // John Doe

// Get specific key from JSON
$name = $response->json('name');
echo $name; // John Doe

// Handle invalid JSON gracefully
$data = $response->json(); // Returns null if JSON is invalid
```

#### Accept JSON Header

```php
use ElliePHP\Components\HttpClient\Http;

// Sets Accept: application/json header
$response = Http::acceptJson()
    ->get('https://api.example.com/users');
```

### Configuration Options

#### Base URL

```php
use ElliePHP\Components\HttpClient\Http;

// Set base URL for all requests
$response = Http::withBaseUrl('https://api.example.com')
    ->get('/users'); // Requests https://api.example.com/users

// Absolute URLs override base URL
$response = Http::withBaseUrl('https://api.example.com')
    ->get('https://other-api.com/data'); // Requests https://other-api.com/data
```

#### Custom Headers

```php
$client = new HttpClient();

// Add multiple headers
$response = $client
    ->withHeaders([
        'X-API-Key' => 'secret-key',
        'User-Agent' => 'MyApp/1.0',
        'X-Custom-Header' => 'value'
    ])
    ->get('https://api.example.com/data');

// Or use convenience methods for common headers
$response = Http::withUserAgent('MyApp/1.0')
    ->withHeader('X-API-Key', 'secret-key')
    ->get('https://api.example.com/data');
```

#### User-Agent Header

```php
// Set User-Agent header
$response = Http::withUserAgent('MyApp/1.0')
    ->get('https://api.example.com/data');
```

#### Content-Type Header

```php
// Set Content-Type header
$response = Http::withContentType('application/xml')
    ->post('https://api.example.com/data', $xmlData);
```

#### Accept Header

```php
// Set Accept header
$response = Http::withAccept('application/xml')
    ->get('https://api.example.com/data');
```

#### Single Header

```php
// Set a single custom header
$response = Http::withHeader('X-Custom-Header', 'value')
    ->get('https://api.example.com/data');
```

#### Maximum Redirects

```php
// Configure maximum number of redirects to follow
$response = Http::withMaxRedirects(5)
    ->get('https://api.example.com/data');
```

#### SSL Verification

```php
// Disable SSL certificate verification (not recommended for production)
$response = Http::withVerify(false)
    ->get('https://self-signed-cert.example.com');

// Enable SSL verification (default)
$response = Http::withVerify(true)
    ->get('https://api.example.com/data');
```

#### Proxy Configuration

```php
// Configure proxy for requests
$response = Http::withProxy('http://proxy.example.com:8080')
    ->get('https://api.example.com/data');
```

#### Timeout

```php
use ElliePHP\Components\HttpClient\Http;

// Set timeout in seconds
$response = Http::withTimeout(30)
    ->get('https://api.example.com/slow-endpoint');
```

### Retry Configuration

Configure automatic retry behavior for failed requests.

#### Exponential Backoff

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::withRetry([
    'max_retries' => 3,      // Retry up to 3 times
    'delay' => 1000,         // Start with 1 second delay (milliseconds)
    'multiplier' => 2,       // Double delay each time: 1s, 2s, 4s
    'max_delay' => 10000,    // Cap delay at 10 seconds
])
    ->get('https://api.example.com/data');
```

#### Fixed Delay

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::withRetry([
    'max_retries' => 5,
    'delay' => 2000,         // 2 second delay
    'multiplier' => 1,       // Keep delay constant
])
    ->get('https://api.example.com/data');
```

#### Retry with Jitter

Add randomness to prevent thundering herd problems:

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::withRetry([
    'max_retries' => 3,
    'delay' => 1000,
    'multiplier' => 2,
    'jitter' => 0.1,         // Add ±10% random variation
])
    ->get('https://api.example.com/data');
```

#### Retry Specific Status Codes

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::withRetry([
    'max_retries' => 3,
    'delay' => 1000,
    'multiplier' => 2,
    'http_codes' => [429, 500, 502, 503, 504], // Only retry these codes
])
    ->get('https://api.example.com/data');
```

### Advanced Configuration

#### Symfony HttpClient Options

Pass any Symfony HttpClient options directly:

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::withOptions([
    'max_redirects' => 5,
    'timeout' => 30,
    'verify_peer' => true,
    'verify_host' => true,
])
    ->get('https://api.example.com/data');
```

For all available options, see the [Symfony HttpClient documentation](https://symfony.com/doc/current/http_client.html#configuration).

### Response Handling

#### Check Response Status

The Response class provides many convenience methods for checking HTTP status codes.

**General Status Checks:**

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::get('https://api.example.com/users');

// Check if successful (2xx status)
if ($response->successful()) {
    echo "Request succeeded!";
}

// Alias for successful() with shorter syntax
if ($response->success()) {
    $data = $response->json();
}

// Check if failed (4xx or 5xx status)
if ($response->failed()) {
    echo "Request failed!";
}

// Check if error (alias for failed())
if ($response->isError()) {
    echo "Request has error!";
}

// Get status code
$status = $response->status(); // e.g., 200, 404, 500
```

**Error Type Checks:**

```php
// Check for client errors (4xx)
if ($response->isClientError()) {
    echo "Client error: " . $response->status();
}

// Check for server errors (5xx)
if ($response->isServerError()) {
    echo "Server error: " . $response->status();
}

// Check for redirects (3xx)
if ($response->isRedirect()) {
    echo "Redirect to: " . $response->header('Location');
}
```

**Specific Status Code Checks:**

```php
// Success codes
$response->isOk();                    // 200 OK
$response->isCreated();               // 201 Created
$response->isNoContent();             // 204 No Content

// Client error codes
$response->isBadRequest();            // 400 Bad Request
$response->isUnauthorized();          // 401 Unauthorized
$response->isForbidden();             // 403 Forbidden
$response->isNotFound();              // 404 Not Found
$response->isUnprocessableEntity();   // 422 Unprocessable Entity
$response->isTooManyRequests();       // 429 Too Many Requests

// Server error codes
$response->isInternalServerError();   // 500 Internal Server Error
```

**Example Usage:**

```php
$response = Http::get('https://api.example.com/users/123');

if ($response->isNotFound()) {
    echo "User not found!";
} elseif ($response->isUnauthorized()) {
    echo "Authentication required!";
} elseif ($response->isServerError()) {
    echo "Server error occurred!";
} elseif ($response->success()) {
    $user = $response->json();
    echo "User: " . $user['name'];
}
```

**Throw on Failure:**

You can throw an exception automatically if the response failed:

```php
use ElliePHP\Components\HttpClient\Http;
use ElliePHP\Components\HttpClient\RequestException;

try {
    // This will throw RequestException if response is 4xx or 5xx
    $response = Http::get('https://api.example.com/users')
        ->throw();
    
    $data = $response->json();
} catch (RequestException $e) {
    echo "Request failed: " . $e->getMessage();
}
```

**Get JSON or Throw:**

For convenience, you can get JSON directly and throw if failed:

```php
use ElliePHP\Components\HttpClient\Http;
use ElliePHP\Components\HttpClient\RequestException;

try {
    // This will throw RequestException if response is not successful
    $data = Http::get('https://api.example.com/users')
        ->jsonOrFail();
    
    // Or get a specific key
    $name = Http::get('https://api.example.com/users/123')
        ->jsonOrFail('name');
} catch (RequestException $e) {
    echo "Request failed: " . $e->getMessage();
}
```

#### Access Response Data

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::get('https://api.example.com/users');

// Get raw body
$body = $response->body();

// Get JSON data
$data = $response->json();

// Get specific JSON key
$name = $response->json('name');
```

#### Access Response Headers

```php
use ElliePHP\Components\HttpClient\Http;

$response = Http::get('https://api.example.com/users');

// Get all headers
$headers = $response->headers();

// Get specific header
$contentType = $response->header('Content-Type');
$rateLimit = $response->header('X-RateLimit-Remaining');
```

### Error Handling

The library throws `RequestException` for network errors and timeouts:

```php
use ElliePHP\Components\HttpClient\Http;
use ElliePHP\Components\HttpClient\RequestException;

try {
    $response = Http::get('https://api.example.com/users');
    
    if ($response->successful()) {
        $data = $response->json();
        // Process data
    } else {
        // Handle 4xx/5xx responses
        echo "HTTP Error: " . $response->status();
    }
} catch (RequestException $e) {
    // Handle network errors, timeouts, etc.
    echo "Request failed: " . $e->getMessage();
    
    // Access original exception if needed
    $previous = $e->getPrevious();
}
```

#### Exception Types

The library handles these types of errors:

- Network Errors: Connection failures, DNS resolution errors, SSL errors
- Timeout Errors: Request exceeds configured timeout
- Transport Errors: Other Symfony transport level errors

Note that 4xx and 5xx HTTP responses do NOT throw exceptions by default. Use `$response->successful()` or `$response->failed()` to check status.

## Method Chaining

All configuration methods return a `ClientBuilder` instance, allowing fluent method chaining:

```php
// Using Http facade
$response = Http::withBaseUrl('https://api.example.com')
    ->withToken('your-api-token')
    ->withUserAgent('MyApp/1.0')
    ->withTimeout(30)
    ->withMaxRedirects(5)
    ->withRetry([
        'max_retries' => 3,
        'delay' => 1000,
        'multiplier' => 2,
    ])
    ->acceptJson()
    ->asJson()
    ->post('/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);

// Or using HttpClient instance
$client = new HttpClient();
$response = $client
    ->withBaseUrl('https://api.example.com')
    ->withToken('your-api-token')
    ->withTimeout(30)
    ->withRetry([
        'max_retries' => 3,
        'delay' => 1000,
        'multiplier' => 2,
    ])
    ->acceptJson()
    ->asJson()
    ->post('/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
```

## Complete Examples

### Example 1: Simple API Client

```php
use ElliePHP\Components\HttpClient\Http;

// Quick one-off requests using Http facade
$users = Http::get('https://api.example.com/users')->json();

foreach ($users as $user) {
    echo $user['name'] . "\n";
}
```

### Example 2: Configured API Client

```php
use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\RequestException;

class ApiClient
{
    private HttpClient $client;
    private string $apiToken;
    
    public function __construct(string $apiToken)
    {
        $this->client = new HttpClient();
        $this->apiToken = $apiToken;
    }
    
    public function getUsers(int $page = 1): array
    {
        try {
            $response = $this->client
                ->withBaseUrl('https://api.example.com')
                ->withToken($this->apiToken)
                ->withUserAgent('MyApp/1.0')
                ->withTimeout(30)
                ->acceptJson()
                ->get('/users', ['page' => $page]);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            throw new \Exception('Failed to fetch users: ' . $response->status());
        } catch (RequestException $e) {
            throw new \Exception('API request failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    public function createUser(array $userData): array
    {
        try {
            $response = $this->client
                ->withBaseUrl('https://api.example.com')
                ->withToken($this->apiToken)
                ->asJson()
                ->post('/users', $userData);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            throw new \Exception('Failed to create user: ' . $response->status());
        } catch (RequestException $e) {
            throw new \Exception('API request failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
```

### Example 3: Resilient API Client with Retries

```php
use ElliePHP\Components\HttpClient\Http;

// Configure for resilient API calls using Http facade
$response = Http::withBaseUrl('https://api.example.com')
    ->withToken('your-api-token')
    ->withUserAgent('MyApp/1.0')
    ->withTimeout(30)
    ->withMaxRedirects(5)
    ->withRetry([
        'max_retries' => 3,
        'delay' => 1000,
        'multiplier' => 2,
        'jitter' => 0.1,
        'http_codes' => [429, 500, 502, 503, 504],
    ])
    ->acceptJson()
    ->asJson()
    ->post('/orders', [
        'product_id' => 123,
        'quantity' => 2,
        'customer_id' => 456
    ]);

if ($response->successful()) {
    $order = $response->json();
    echo "Order created: " . $order['id'];
} else {
    echo "Order failed: " . $response->status();
}
```

### Example 4: Using Convenience Methods

```php
use ElliePHP\Components\HttpClient\Http;

// Chain convenience methods for clean, readable code
$response = Http::withBaseUrl('https://api.example.com')
    ->withUserAgent('MyApp/2.0')
    ->withContentType('application/json')
    ->withAccept('application/json')
    ->withHeader('X-API-Version', 'v2')
    ->withTimeout(30)
    ->withMaxRedirects(3)
    ->withToken('your-api-token')
    ->get('/users');

if ($response->successful()) {
    $users = $response->json();
    // Process users...
}
```

### Example 5: File Upload

```php
use ElliePHP\Components\HttpClient\Http;
use ElliePHP\Components\HttpClient\RequestException;
use InvalidArgumentException;

try {
    // Upload a single file with metadata
    $response = Http::withBaseUrl('https://api.example.com')
        ->withToken('your-api-token')
        ->withUserAgent('MyApp/1.0')
        ->attach('file', '/path/to/document.pdf')
        ->post('/upload', [
            'title' => 'Important Document',
            'category' => 'legal'
        ]);
    
    if ($response->successful()) {
        $result = $response->json();
        echo "File uploaded: " . $result['file_id'];
    }
    
    // Upload multiple files
    $response = Http::withBaseUrl('https://api.example.com')
        ->withToken('your-api-token')
        ->attach('avatar', '/path/to/avatar.jpg')
        ->attach('cover', '/path/to/cover.jpg')
        ->post('/upload', [
            'user_id' => 123
        ]);
    
} catch (InvalidArgumentException $e) {
    echo "File error: " . $e->getMessage();
} catch (RequestException $e) {
    echo "Upload failed: " . $e->getMessage();
}
```

## API Reference

### Http Facade

The `Http` facade provides a static interface that delegates to `HttpClient`. All methods available on `HttpClient` are also available on `Http`.

```php
use ElliePHP\Components\HttpClient\Http;

// All HttpClient methods work with Http facade
Http::get('https://api.example.com/users');
Http::withBaseUrl('https://api.example.com')->get('/users');
Http::withToken('token')->get('/api/protected');
```

### HttpClient

#### Static Methods

These methods are for quick, one-off requests without configuration. They expect full, absolute URLs.

- `HttpClient::get(string $url, array $query = []): Response`
- `HttpClient::post(string $url, array $data = []): Response`
- `HttpClient::put(string $url, array $data = []): Response`
- `HttpClient::patch(string $url, array $data = []): Response`
- `HttpClient::delete(string $url): Response`

#### Configuration Methods

These methods return a `ClientBuilder` instance for method chaining and properly handle configured requests.

- `withBaseUrl(string $baseUrl): ClientBuilder`
- `withHeaders(array $headers): ClientBuilder`
- `withHeader(string $name, string $value): ClientBuilder`
- `withUserAgent(string $userAgent): ClientBuilder`
- `withContentType(string $contentType): ClientBuilder`
- `withAccept(string $accept): ClientBuilder`
- `withToken(string $token): ClientBuilder`
- `withBasicAuth(string $username, string $password): ClientBuilder`
- `acceptJson(): ClientBuilder`
- `asJson(): ClientBuilder`
- `withTimeout(int $seconds): ClientBuilder`
- `withMaxRedirects(int $maxRedirects): ClientBuilder`
- `withVerify(bool $verify = true): ClientBuilder`
- `withProxy(string $proxyUrl): ClientBuilder`
- `withRetry(array $retryConfig): ClientBuilder`
- `withOptions(array $options): ClientBuilder`
- `attach(string $name, string|resource $file): ClientBuilder`

#### Request Methods on Instance

These methods work on the HttpClient instance but require full URLs when called directly without configuration chaining.

- `get(string $url, array $query = []): Response`
- `post(string $url, array $data = []): Response`
- `put(string $url, array $data = []): Response`
- `patch(string $url, array $data = []): Response`
- `delete(string $url): Response`

### Response

#### Status Methods

**General Status Checks:**
- `status(): int`
- `successful(): bool`
- `success(): bool`
- `failed(): bool`
- `isError(): bool`
- `throw(): self`
- `jsonOrFail(?string $key = null): mixed`

**Error Type Checks:**
- `isClientError(): bool`
- `isServerError(): bool`
- `isRedirect(): bool`

**Specific Status Code Checks:**
- `isOk(): bool`
- `isCreated(): bool`
- `isNoContent(): bool`
- `isBadRequest(): bool`
- `isUnauthorized(): bool`
- `isForbidden(): bool`
- `isNotFound(): bool`
- `isUnprocessableEntity(): bool`
- `isTooManyRequests(): bool`
- `isInternalServerError(): bool`

#### Content Methods

- `body(): string`
- `json(?string $key = null): mixed`
- `headers(): array`
- `header(string $name): ?string`

### RequestException

Custom exception thrown for network errors, timeouts, and transport failures.

```php
use ElliePHP\Components\HttpClient\Http;
use ElliePHP\Components\HttpClient\RequestException;

try {
    $response = Http::get('https://api.example.com/data');
} catch (RequestException $e) {
    echo $e->getMessage();
    echo $e->getCode();
    $previous = $e->getPrevious();
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

## License

This library is open-sourced software licensed under the MIT license.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

- Issues: GitHub Issues
- Source: GitHub Repository

## Credits

Built on top of Symfony HttpClient.