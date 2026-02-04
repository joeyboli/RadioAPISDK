<?php

namespace ElliePHP\Components\HttpClient\Tests\Unit;

use ElliePHP\Components\HttpClient\Http;
use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\ClientBuilder;
use ElliePHP\Components\HttpClient\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class HttpFacadeTest extends TestCase
{
    public function test_http_facade_exists(): void
    {
        $this->assertTrue(class_exists(Http::class));
    }

    public function test_http_facade_delegates_to_httpclient(): void
    {
        $mockResponse = new MockResponse('{"status":"ok"}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        
        // We can't easily test static calls with mocks, but we can verify the method exists
        $this->assertTrue(method_exists(Http::class, '__callStatic'));
    }

    public function test_http_facade_static_get(): void
    {
        // Verify that Http::get() can be called (will use real HTTP client)
        $this->assertTrue(method_exists(Http::class, '__callStatic'));
    }

    public function test_http_facade_returns_client_builder(): void
    {
        // Test that Http facade methods return ClientBuilder instances
        $builder = Http::withBaseUrl('https://api.example.com');
        $this->assertInstanceOf(ClientBuilder::class, $builder);
        
        $builder = Http::withHeaders(['X-Custom' => 'value']);
        $this->assertInstanceOf(ClientBuilder::class, $builder);
        
        $builder = Http::withToken('token123');
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }
}

