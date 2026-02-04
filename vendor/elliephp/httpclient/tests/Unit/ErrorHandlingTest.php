<?php

namespace ElliePHP\Components\HttpClient\Tests\Unit;

use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\RequestException;
use ElliePHP\Components\HttpClient\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;

class ErrorHandlingTest extends TestCase
{
    public function test_network_error_throws_request_exception(): void
    {
        $mockClient = new MockHttpClient(function () {
            throw new class('Connection refused') extends \Exception implements TransportExceptionInterface {
            };
        });
        
        $client = new HttpClient($mockClient);
        
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection refused');
        
        $client->get('https://api.example.com/test');
    }

    public function test_timeout_error_throws_request_exception_with_timeout_info(): void
    {
        $mockClient = new MockHttpClient(function () {
            throw new class('Operation timed out') extends \Exception implements TimeoutExceptionInterface {
            };
        });
        
        $client = new HttpClient($mockClient);
        
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageMatches('/timed out/i');
        
        $client->get('https://api.example.com/slow');
    }

    public function test_4xx_response_returns_response_without_throwing(): void
    {
        $mockResponse = new MockResponse('{"error":"Not found"}', [
            'http_code' => 404,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        $response = $client->get('https://api.example.com/missing');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(404, $response->status());
        $this->assertTrue($response->failed());
        $this->assertFalse($response->successful());
        $this->assertEquals(['error' => 'Not found'], $response->json());
    }

    public function test_5xx_response_returns_response_without_throwing(): void
    {
        $mockResponse = new MockResponse('{"error":"Internal server error"}', [
            'http_code' => 500,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        $response = $client->get('https://api.example.com/error');
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->status());
        $this->assertTrue($response->failed());
        $this->assertFalse($response->successful());
        $this->assertEquals(['error' => 'Internal server error'], $response->json());
    }

    public function test_request_exception_preserves_previous_exception(): void
    {
        $originalException = new class('Original error') extends \Exception implements TransportExceptionInterface {
        };
        
        $mockClient = new MockHttpClient(function () use ($originalException) {
            throw $originalException;
        });
        
        $client = new HttpClient($mockClient);
        
        try {
            $client->get('https://api.example.com/test');
            $this->fail('Expected RequestException to be thrown');
        } catch (RequestException $e) {
            $this->assertSame($originalException, $e->getPrevious());
            $this->assertStringContainsString('Original error', $e->getMessage());
        }
    }

    public function test_various_4xx_status_codes_return_response(): void
    {
        $statusCodes = [400, 401, 403, 404, 422, 429];
        
        foreach ($statusCodes as $statusCode) {
            $mockResponse = new MockResponse('{"error":"Error"}', [
                'http_code' => $statusCode,
            ]);
            
            $mockClient = new MockHttpClient($mockResponse);
            $client = new HttpClient($mockClient);
            
            $response = $client->get('https://api.example.com/test');
            
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals($statusCode, $response->status());
            $this->assertTrue($response->failed());
        }
    }

    public function test_various_5xx_status_codes_return_response(): void
    {
        $statusCodes = [500, 502, 503, 504];
        
        foreach ($statusCodes as $statusCode) {
            $mockResponse = new MockResponse('{"error":"Server error"}', [
                'http_code' => $statusCode,
            ]);
            
            $mockClient = new MockHttpClient($mockResponse);
            $client = new HttpClient($mockClient);
            
            $response = $client->get('https://api.example.com/test');
            
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals($statusCode, $response->status());
            $this->assertTrue($response->failed());
        }
    }

    public function test_2xx_status_codes_are_successful(): void
    {
        $statusCodes = [200, 201, 202, 204];
        
        foreach ($statusCodes as $statusCode) {
            $mockResponse = new MockResponse('{"success":true}', [
                'http_code' => $statusCode,
            ]);
            
            $mockClient = new MockHttpClient($mockResponse);
            $client = new HttpClient($mockClient);
            
            $response = $client->get('https://api.example.com/test');
            
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals($statusCode, $response->status());
            $this->assertTrue($response->successful());
            $this->assertFalse($response->failed());
        }
    }
}
