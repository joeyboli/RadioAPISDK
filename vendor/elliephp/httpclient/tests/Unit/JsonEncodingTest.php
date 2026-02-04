<?php

namespace ElliePHP\Components\HttpClient\Tests\Unit;

use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class JsonEncodingTest extends TestCase
{
    public function test_as_json_sets_content_type_header(): void
    {
        $mockResponse = new MockResponse('{"success":true}', [
            'http_code' => 200,
        ]);
        
        $mockClient = new MockHttpClient(function ($method, $url, $options) use ($mockResponse) {
            // Verify Content-Type header is set
            $this->assertArrayHasKey('headers', $options);
            $this->assertContains('Content-Type: application/json', $options['headers']);
            return $mockResponse;
        });
        
        $client = new HttpClient($mockClient);
        $response = $client->asJson()->post('https://api.example.com/data', ['key' => 'value']);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_as_json_encodes_body_as_json(): void
    {
        $mockResponse = new MockResponse('{"success":true}', [
            'http_code' => 200,
        ]);
        
        $testData = ['name' => 'Test', 'value' => 123, 'nested' => ['key' => 'value']];
        
        $mockClient = new MockHttpClient(function ($method, $url, $options) use ($mockResponse, $testData) {
            // Verify body is JSON-encoded string
            $this->assertArrayHasKey('body', $options);
            $this->assertIsString($options['body']);
            $decoded = json_decode($options['body'], true);
            $this->assertEquals($testData, $decoded);
            return $mockResponse;
        });
        
        $client = new HttpClient($mockClient);
        $response = $client->asJson()->post('https://api.example.com/data', $testData);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_as_json_works_with_put_request(): void
    {
        $mockResponse = new MockResponse('{"updated":true}', [
            'http_code' => 200,
        ]);
        
        $testData = ['status' => 'active'];
        
        $mockClient = new MockHttpClient(function ($method, $url, $options) use ($mockResponse, $testData) {
            $this->assertEquals('PUT', $method);
            $this->assertArrayHasKey('body', $options);
            $this->assertIsString($options['body']);
            $decoded = json_decode($options['body'], true);
            $this->assertEquals($testData, $decoded);
            return $mockResponse;
        });
        
        $client = new HttpClient($mockClient);
        $response = $client->asJson()->put('https://api.example.com/items/1', $testData);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_as_json_works_with_patch_request(): void
    {
        $mockResponse = new MockResponse('{"patched":true}', [
            'http_code' => 200,
        ]);
        
        $testData = ['field' => 'updated'];
        
        $mockClient = new MockHttpClient(function ($method, $url, $options) use ($mockResponse, $testData) {
            $this->assertEquals('PATCH', $method);
            $this->assertArrayHasKey('body', $options);
            $this->assertIsString($options['body']);
            $decoded = json_decode($options['body'], true);
            $this->assertEquals($testData, $decoded);
            return $mockResponse;
        });
        
        $client = new HttpClient($mockClient);
        $response = $client->asJson()->patch('https://api.example.com/items/1', $testData);
        
        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_request_without_as_json_uses_body_parameter(): void
    {
        $mockResponse = new MockResponse('{"success":true}', [
            'http_code' => 200,
        ]);
        
        $testData = ['key' => 'value'];
        
        $mockClient = new MockHttpClient(function ($method, $url, $options) use ($mockResponse) {
            // Without asJson(), body should be passed as array (Symfony will encode it as form data)
            $this->assertArrayHasKey('body', $options);
            // Symfony converts array body to query string format
            $this->assertIsString($options['body']);
            $this->assertEquals('key=value', $options['body']);
            return $mockResponse;
        });
        
        $client = new HttpClient($mockClient);
        $response = $client->post('https://api.example.com/data', $testData);
        
        $this->assertInstanceOf(Response::class, $response);
    }
}
