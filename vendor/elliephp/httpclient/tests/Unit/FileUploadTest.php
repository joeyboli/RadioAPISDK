<?php

namespace ElliePHP\Components\HttpClient\Tests\Unit;

use ElliePHP\Components\HttpClient\HttpClient;
use ElliePHP\Components\HttpClient\ClientBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class FileUploadTest extends TestCase
{
    private string $testFile;

    protected function setUp(): void
    {
        // Create a temporary test file
        $this->testFile = sys_get_temp_dir() . '/httpclient_test_' . uniqid() . '.txt';
        file_put_contents($this->testFile, 'Test file content');
    }

    protected function tearDown(): void
    {
        // Clean up test file
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    public function test_attach_returns_client_builder(): void
    {
        $client = new HttpClient();
        $builder = $client->attach('file', $this->testFile);
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_attach_with_file_path(): void
    {
        $client = new HttpClient();
        $builder = $client->attach('file', $this->testFile);
        
        // Verify the file is stored for attachment
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }

    public function test_attach_with_file_resource(): void
    {
        $file = fopen($this->testFile, 'r');
        
        $client = new HttpClient();
        $builder = $client->attach('file', $file);
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
        
        fclose($file);
    }

    public function test_attach_throws_exception_for_nonexistent_file(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');
        
        $client = new HttpClient();
        $client->attach('file', '/nonexistent/file/path.txt');
    }

    public function test_attach_throws_exception_for_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File must be a file path');
        
        $client = new HttpClient();
        $client->attach('file', ['not', 'a', 'file']);
    }

    public function test_attach_multiple_files(): void
    {
        $testFile2 = sys_get_temp_dir() . '/httpclient_test2_' . uniqid() . '.txt';
        file_put_contents($testFile2, 'Test file 2 content');
        
        try {
            $client = new HttpClient();
            $builder = $client->attach('file1', $this->testFile)
                ->attach('file2', $testFile2);
            
            $this->assertInstanceOf(ClientBuilder::class, $builder);
        } finally {
            if (file_exists($testFile2)) {
                unlink($testFile2);
            }
        }
    }

    public function test_post_with_attached_file(): void
    {
        $mockResponse = new MockResponse('{"uploaded":true}', [
            'http_code' => 200,
            'response_headers' => ['content-type' => 'application/json']
        ]);
        
        $mockClient = new MockHttpClient($mockResponse);
        $client = new HttpClient($mockClient);
        
        $response = $client->attach('file', $this->testFile)
            ->post('https://api.example.com/upload', ['description' => 'Test upload']);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['uploaded' => true], $response->json());
    }

    public function test_attach_can_be_chained_with_other_methods(): void
    {
        $client = new HttpClient();
        $builder = $client->withBaseUrl('https://api.example.com')
            ->withToken('token')
            ->attach('file', $this->testFile);
        
        $this->assertInstanceOf(ClientBuilder::class, $builder);
    }
}

