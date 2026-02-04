<?php

namespace ElliePHP\Components\HttpClient\Tests\Unit;

use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\ClientBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ConvenienceMethodsTest extends TestCase
{
    public function test_with_user_agent_returns_client_builder(): void
    {
        $client = new HttpClient();
        $builder = $client->withUserAgent('MyApp/1.0');
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_with_user_agent_sets_header(): void
    {
        $client = new HttpClient();
        $builder = $client->withUserAgent('MyApp/1.0');
        
        $headers = $builder->getHeaders();
        $this->assertEquals('MyApp/1.0', $headers['User-Agent']);
    }

    public function test_with_content_type_returns_client_builder(): void
    {
        $client = new HttpClient();
        $builder = $client->withContentType('application/xml');
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_with_content_type_sets_header(): void
    {
        $client = new HttpClient();
        $builder = $client->withContentType('application/xml');
        
        $headers = $builder->getHeaders();
        $this->assertEquals('application/xml', $headers['Content-Type']);
    }

    public function test_with_accept_returns_client_builder(): void
    {
        $client = new HttpClient();
        $builder = $client->withAccept('application/xml');
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_with_accept_sets_header(): void
    {
        $client = new HttpClient();
        $builder = $client->withAccept('application/xml');
        
        $headers = $builder->getHeaders();
        $this->assertEquals('application/xml', $headers['Accept']);
    }

    public function test_with_header_returns_client_builder(): void
    {
        $client = new HttpClient();
        $builder = $client->withHeader('X-Custom', 'value');
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_with_header_sets_header(): void
    {
        $client = new HttpClient();
        $builder = $client->withHeader('X-Custom-Header', 'custom-value');
        
        $headers = $builder->getHeaders();
        $this->assertEquals('custom-value', $headers['X-Custom-Header']);
    }

    public function test_with_max_redirects_returns_client_builder(): void
    {
        $client = new HttpClient();
        $builder = $client->withMaxRedirects(5);
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_with_max_redirects_sets_option(): void
    {
        $client = new HttpClient();
        $builder = $client->withMaxRedirects(5);
        
        $options = $builder->getOptions();
        $this->assertEquals(5, $options['max_redirects']);
    }

    public function test_with_verify_returns_client_builder(): void
    {
        $client = new HttpClient();
        $builder = $client->withVerify(false);
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_with_verify_sets_options(): void
    {
        $client = new HttpClient();
        $builder = $client->withVerify(false);
        
        $options = $builder->getOptions();
        $this->assertFalse($options['verify_peer']);
        $this->assertFalse($options['verify_host']);
    }

    public function test_with_verify_defaults_to_true(): void
    {
        $client = new HttpClient();
        $builder = $client->withVerify();
        
        $options = $builder->getOptions();
        $this->assertTrue($options['verify_peer']);
        $this->assertTrue($options['verify_host']);
    }

    public function test_with_proxy_returns_client_builder(): void
    {
        $client = new HttpClient();
        $builder = $client->withProxy('http://proxy.example.com:8080');
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_with_proxy_sets_option(): void
    {
        $client = new HttpClient();
        $builder = $client->withProxy('http://proxy.example.com:8080');
        
        $options = $builder->getOptions();
        $this->assertEquals('http://proxy.example.com:8080', $options['proxy']);
    }

    public function test_convenience_methods_can_be_chained(): void
    {
        $client = new HttpClient();
        $builder = $client->withUserAgent('MyApp/1.0')
            ->withContentType('application/json')
            ->withAccept('application/json')
            ->withHeader('X-Custom', 'value')
            ->withMaxRedirects(5);
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
        
        $headers = $builder->getHeaders();
        $this->assertEquals('MyApp/1.0', $headers['User-Agent']);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('application/json', $headers['Accept']);
        $this->assertEquals('value', $headers['X-Custom']);
        
        $options = $builder->getOptions();
        $this->assertEquals(5, $options['max_redirects']);
    }

    public function test_convenience_methods_with_existing_configuration(): void
    {
        $mockResponse = new MockResponse('{"status":"ok"}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        // Set base URL first, then add convenience methods
        $builder = $client->withBaseUrl('https://api.example.com')
            ->withUserAgent('MyApp/1.0')
            ->withToken('token123');
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
        $this->assertEquals('https://api.example.com', $builder->getBaseUrl());
        
        $headers = $builder->getHeaders();
        $this->assertEquals('MyApp/1.0', $headers['User-Agent']);
        $this->assertStringStartsWith('Bearer token123', $headers['Authorization']);
    }
}

