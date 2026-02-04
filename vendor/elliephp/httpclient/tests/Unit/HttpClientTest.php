<?php

namespace ElliePHP\Components\HttpClient\Tests\Unit;

use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\ClientBuilder;
use ElliePHP\Components\HttpClient\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class HttpClientTest extends TestCase
{
    public function test_can_create_http_client_instance(): void
    {
        $client = new HttpClient();
        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function test_configuration_methods_return_client_builder(): void
    {
        $client = new HttpClient();
        
        $this->assertInstanceOf(ClientBuilder::class, $client->withBaseUrl('https://api.example.com'));
        $this->assertInstanceOf(ClientBuilder::class, $client->withHeaders(['X-Custom' => 'value']));
        $this->assertInstanceOf(ClientBuilder::class, $client->withToken('token123'));
        $this->assertInstanceOf(ClientBuilder::class, $client->withBasicAuth('user', 'pass'));
        $this->assertInstanceOf(ClientBuilder::class, $client->acceptJson());
        $this->assertInstanceOf(ClientBuilder::class, $client->asJson());
        $this->assertInstanceOf(ClientBuilder::class, $client->withTimeout(30));
        $this->assertInstanceOf(ClientBuilder::class, $client->withOptions(['verify_peer' => false]));
    }

    public function test_get_request_returns_response(): void
    {
        $mockResponse = new MockResponse('{"status":"ok"}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        $response = $client->get('https://api.example.com/test');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['status' => 'ok'], $response->json());
    }

    public function test_post_request_returns_response(): void
    {
        $mockResponse = new MockResponse('{"created":true}', [
            'http_code' => 201,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        $response = $client->post('https://api.example.com/items', ['name' => 'Test']);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(201, $response->status());
        $this->assertEquals(['created' => true], $response->json());
    }

    public function test_put_request_returns_response(): void
    {
        $mockResponse = new MockResponse('{"updated":true}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        $response = $client->put('https://api.example.com/items/1', ['name' => 'Updated']);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['updated' => true], $response->json());
    }

    public function test_patch_request_returns_response(): void
    {
        $mockResponse = new MockResponse('{"patched":true}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        $response = $client->patch('https://api.example.com/items/1', ['status' => 'active']);
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['patched' => true], $response->json());
    }

    public function test_delete_request_returns_response(): void
    {
        $mockResponse = new MockResponse('', [
            'http_code' => 204,
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        $response = $client->delete('https://api.example.com/items/1');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(204, $response->status());
    }

    public function test_get_request_handles_query_parameters(): void
    {
        $mockResponse = new MockResponse('{"results":[]}', [
            'http_code' => 200,
        ]);
        
        $mockClient = new MockHttpClient(function ($method, $url) use ($mockResponse) {
            // Verify query parameters are in the URL
            $this->assertStringContainsString('page=1', $url);
            $this->assertStringContainsString('limit=10', $url);
            return $mockResponse;
        });
        
        $client = new HttpClient($mockClient);
        $response = $client->get('https://api.example.com/items', ['page' => 1, 'limit' => 10]);
        
        $this->assertInstanceOf(Response::class, $response);
    }
}
