<?php

namespace ElliePHP\Components\HttpClient\Tests\Unit;

use ElliePHP\Components\HttpClient\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ResponseStatusMethodsTest extends TestCase
{
    public function test_is_error_returns_true_for_4xx(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 404]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isError());
        $this->assertTrue($response->failed());
    }

    public function test_is_error_returns_true_for_5xx(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isError());
        $this->assertTrue($response->failed());
    }

    public function test_is_error_returns_false_for_2xx(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 200]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertFalse($response->isError());
        $this->assertTrue($response->successful());
    }

    public function test_is_client_error(): void
    {
        $statusCodes = [400, 401, 403, 404, 422, 429];
        
        foreach ($statusCodes as $statusCode) {
            $mockResponse = new MockResponse('', ['http_code' => $statusCode]);
            $mockClient = new MockHttpClient($mockResponse);
            $response = new Response($mockClient->request('GET', 'https://api.example.com'));
            
            $this->assertTrue($response->isClientError(), "Status code {$statusCode} should be a client error");
            $this->assertFalse($response->isServerError(), "Status code {$statusCode} should not be a server error");
        }
    }

    public function test_is_server_error(): void
    {
        $statusCodes = [500, 502, 503, 504];
        
        foreach ($statusCodes as $statusCode) {
            $mockResponse = new MockResponse('', ['http_code' => $statusCode]);
            $mockClient = new MockHttpClient($mockResponse);
            $response = new Response($mockClient->request('GET', 'https://api.example.com'));
            
            $this->assertTrue($response->isServerError(), "Status code {$statusCode} should be a server error");
            $this->assertFalse($response->isClientError(), "Status code {$statusCode} should not be a client error");
        }
    }

    public function test_is_redirect(): void
    {
        $statusCodes = [301, 302, 303, 307, 308];
        
        foreach ($statusCodes as $statusCode) {
            $mockResponse = new MockResponse('', ['http_code' => $statusCode]);
            $mockClient = new MockHttpClient($mockResponse);
            $response = new Response($mockClient->request('GET', 'https://api.example.com'));
            
            $this->assertTrue($response->isRedirect(), "Status code {$statusCode} should be a redirect");
        }
    }

    public function test_is_ok(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 200]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isOk());
        
        $mockResponse = new MockResponse('', ['http_code' => 201]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertFalse($response->isOk());
    }

    public function test_is_created(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 201]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isCreated());
    }

    public function test_is_no_content(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 204]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isNoContent());
    }

    public function test_is_bad_request(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 400]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isBadRequest());
    }

    public function test_is_unauthorized(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 401]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isUnauthorized());
    }

    public function test_is_forbidden(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 403]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isForbidden());
    }

    public function test_is_not_found(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 404]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isNotFound());
    }

    public function test_is_unprocessable_entity(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 422]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isUnprocessableEntity());
    }

    public function test_is_too_many_requests(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 429]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isTooManyRequests());
    }

    public function test_is_internal_server_error(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->isInternalServerError());
    }
}

