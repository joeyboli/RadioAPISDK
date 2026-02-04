<?php

namespace ElliePHP\Components\HttpClient\Tests\Unit;

use ElliePHP\Components\HttpClient\RequestException;
use ElliePHP\Components\HttpClient\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ResponseHelperMethodsTest extends TestCase
{
    public function test_success_is_alias_for_successful(): void
    {
        $mockResponse = new MockResponse('{"status":"ok"}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $this->assertTrue($response->success());
        $this->assertTrue($response->successful());
        $this->assertEquals($response->success(), $response->successful());
    }

    public function test_throw_does_not_throw_for_successful_response(): void
    {
        $mockResponse = new MockResponse('{"status":"ok"}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        // Should not throw
        $result = $response->throw();
        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame($response, $result);
    }

    public function test_throw_throws_for_4xx_response(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(404);
        
        $mockResponse = new MockResponse('{"error":"Not found"}', [
            'http_code' => 404,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $response->throw();
    }

    public function test_throw_throws_for_5xx_response(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(500);
        
        $mockResponse = new MockResponse('{"error":"Server error"}', [
            'http_code' => 500,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $response->throw();
    }

    public function test_throw_includes_json_message_if_available(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('User not found');
        
        $mockResponse = new MockResponse('{"message":"User not found"}', [
            'http_code' => 404,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $response->throw();
    }

    public function test_throw_includes_json_error_if_available(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Invalid request');
        
        $mockResponse = new MockResponse('{"error":"Invalid request"}', [
            'http_code' => 400,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $response->throw();
    }

    public function test_json_or_fail_returns_json_for_successful_response(): void
    {
        $mockResponse = new MockResponse('{"status":"ok","data":"test"}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $data = $response->jsonOrFail();
        $this->assertEquals(['status' => 'ok', 'data' => 'test'], $data);
        
        $status = $response->jsonOrFail('status');
        $this->assertEquals('ok', $status);
    }

    public function test_json_or_fail_throws_for_failed_response(): void
    {
        $this->expectException(RequestException::class);
        
        $mockResponse = new MockResponse('{"error":"Not found"}', [
            'http_code' => 404,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $response = new Response($mockClient->request('GET', 'https://api.example.com'));
        
        $response->jsonOrFail();
    }
}

