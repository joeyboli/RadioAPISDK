# RadioAPI PHP SDK

A comprehensive PHP SDK for interacting with RadioAPI services to retrieve radio stream metadata and search music tracks across multiple streaming platforms.

## Installation

Install via Composer:

```bash
composer require joeyboli/radioapisdk
```

## Requirements

- PHP 8.3 or higher
- Symfony HTTP Client 7.2+

## Quick Start

```php
use RadioAPI\RadioAPI;

$api = new RadioAPI();
$api->setBaseUrl('https://api.example.com')
    ->setApiKey('your-api-key');

// Get current stream metadata
$metadata = $api->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio')
    ->fetchArray();

// Search for music
$results = $api->musicSearch()
    ->search('The Beatles - Hey Jude')
    ->fetchArray();
```

## Configuration

### Basic Setup

```php
$api = new RadioAPI();
$api->setBaseUrl('https://your-radioapi-instance.com')
    ->setApiKey('your-api-key')
    ->setLanguage('en')
    ->withHistory(true)
    ->setThrowOnApiErrors(true);
```

### Configuration Methods

- `setBaseUrl(string $url)` - Set the RadioAPI service base URL
- `setApiKey(string $key)` - Set your API key for authentication
- `setLanguage(string $lang)` - Set response language (ISO 639-1 codes: 'en', 'fr', 'de', etc.)
- `withHistory(bool $enabled)` - Include/exclude track history in responses (default: true)
- `setThrowOnApiErrors(bool $enabled)` - Enable/disable exception throwing on API errors (default: true)
- `withService(string $mount)` - Set service-specific mount point for enhanced metadata

## StreamTitle API

Retrieve current playing track metadata from radio streams.

### Basic Usage

```php
$stream = $api->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio')
    ->fetchArray();

if ($stream['metadataFound']) {
    echo "Now Playing: {$stream['artist']} - {$stream['song']}\n";
    echo "Album: {$stream['album']}\n";
}
```

### StreamTitle Methods

- `setStreamUrl(string $url)` - Set the radio stream URL to fetch metadata from
- `withService(string $mount)` - Set mount point for service-specific endpoints
- `fetchArray()` - Execute the request and return response as array
- `hasMetadata()` - Check if metadata was successfully retrieved
- `getData()` - Get the raw response data

### Response Structure (Basic)

```php
[
    'name' => 'Hunter.FM - O Canal K-pop',
    'bitrate' => 256,
    'format' => 'AAC',
    'elapsed' => 143,
    'artist' => 'RESCENE',
    'song' => 'LOVE ATTACK',
    'metadataFound' => true,
    'history' => [
        [
            'artist' => 'NewJeans',
            'song' => 'OMG',
            'timestamp' => '2025-10-12 11:44:06.353021',
            'relative_time' => '5 hours ago'
        ]
    ]
]
```

### Response Structure (With Service Integration)

```php
[
    'name' => 'Hunter.FM - O Canal K-pop',
    'bitrate' => 0,
    'format' => 'AAC',
    'artist' => 'RESCENE',
    'song' => 'LOVE ATTACK',
    'album' => 'SCENEDROME',
    'genre' => 'Asiatische Musik',
    'artwork' => 'https://icdn2.streamafrica.net/stacks/e24820c67fecb4c0.jpg',
    'year' => 2024,
    'duration' => 181,
    'elapsed' => 111,
    'remaining' => 70,
    'time' => '03:01',
    'stream' => 'https://song.link/d/2966352091',
    'explicit' => false,
    'songinfoFound' => true,
    'metadataFound' => true,
    'history' => [
        [
            'artist' => 'ENHYPEN',
            'song' => 'Bite Me',
            'timestamp' => '2025-10-12 16:39:25.868785',
            'relative_time' => '4 minutes ago',
            'artwork' => 'https://icdn2.streamafrica.net/stacks/6a615f61aac53844.jpg'
        ]
    ]
]
```

## MusicSearch API

Search for music tracks across various streaming platforms.

### Basic Usage

```php
$results = $api->musicSearch()
    ->search('The Beatles - Hey Jude')
    ->fetchArray();
```

### MusicSearch Methods

- `search(string $query)` - Set the search query for music tracks
- `withService(string $mount)` - Set mount point for service-specific search
- `fetchArray()` - Execute the search and return results as array
- `hasMetadata()` - Check if search results were found
- `getData()` - Get the raw response data

### Response Structure

```php
[
    'artist' => 'SKKST',
    'title' => 'Dance With My Hands',
    'album' => 'Dance With My Hands',
    'genre' => 'Dance',
    'artwork' => [
        'small' => 'https://cdn-images.dzcdn.net/images/cover/.../56x56-000000-80-0-0.jpg',
        'medium' => 'https://cdn-images.dzcdn.net/images/cover/.../250x250-000000-80-0-0.jpg',
        'large' => 'https://cdn-images.dzcdn.net/images/cover/.../500x500-000000-80-0-0.jpg',
        'xl' => 'https://cdn-images.dzcdn.net/images/cover/.../1000x1000-000000-80-0-0.jpg'
    ],
    'artist_artwork' => 'https://cdn-images.dzcdn.net/images/artist/.../1000x1000-000000-80-0-0.jpg',
    'duration' => 124,
    'stream' => 'https://www.deezer.com/track/2109711027',
    'explicit' => false,
    'year' => 2023
]
```

## Service Integration

### Music Streaming Services

Enhance metadata with service-specific information:

```php
// Spotify integration
$spotify = $api->withService(RadioAPI::SPOTIFY)
    ->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio')
    ->fetchArray();

// Deezer integration
$deezer = $api->withService(RadioAPI::DEEZER)
    ->musicSearch()
    ->search('Radiohead Creep')
    ->fetchArray();
```

### Available Service Constants

**For StreamTitle and MusicSearch:**
- `RadioAPI::SPOTIFY` - Spotify
- `RadioAPI::DEEZER` - Deezer  
- `RadioAPI::APPLE_MUSIC` - Apple Music (iTunes)
- `RadioAPI::YOUTUBE_MUSIC` - YouTube Music
- `RadioAPI::FLO_MUSIC` - FLO Music
- `RadioAPI::LINE_MUSIC` - LINE Music
- `RadioAPI::AUTO` - Auto-detect service

**For StreamTitle only (Radio Platforms):**
- `RadioAPI::AZURACAST` - AzuraCast platform
- `RadioAPI::LIVE365` - Live365 platform

### Radio Platform Integration

For radio platforms, use specific URL formats:

**AzuraCast:**
```php
$api->withService(RadioAPI::AZURACAST)
    ->streamTitle()
    ->setStreamUrl('https://azuracast.example.com/listen/stationid/mountpoint')
    ->fetchArray();
```

**Live365:**
```php
$api->withService(RadioAPI::LIVE365)
    ->streamTitle()
    ->setStreamUrl('https://streaming.live365.com/mountid')
    ->fetchArray();
```

## Error Handling

### Exception Types

The SDK provides three types of exceptions:

- `ClientErrorException` - 4xx HTTP status codes (client errors)
- `ServerErrorException` - 5xx HTTP status codes (server errors)  
- `ApiErrorException` - Generic API errors

### Exception Handling

```php
use RadioAPI\Exceptions\ApiErrorException;
use RadioAPI\Exceptions\ClientErrorException;
use RadioAPI\Exceptions\ServerErrorException;

try {
    $data = $api->streamTitle()
        ->setStreamUrl('https://stream.example.com/radio')
        ->fetchArray();
} catch (ClientErrorException $e) {
    // Handle 4xx errors
    echo "Client error: {$e->getMessage()}";
    echo "Status code: {$e->getStatusCode()}";
    echo "Error data: " . json_encode($e->getErrorData());
} catch (ServerErrorException $e) {
    // Handle 5xx errors
    echo "Server error: {$e->getMessage()}";
} catch (ApiErrorException $e) {
    // Handle other API errors
    echo "API error: {$e->getMessage()}";
}
```

### Exception Methods

All exception classes provide:
- `getMessage()` - Get the error message
- `getStatusCode()` - Get the HTTP status code
- `getErrorData()` - Get the original API error response
- `getContext()` - Get additional context information
- `hasErrorField(string $field)` - Check if error data contains a field
- `getErrorField(string $field, $default = null)` - Get specific error field

### Disable Exception Throwing

```php
$api->setThrowOnApiErrors(false);

$data = $api->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio')
    ->fetchArray();

if (isset($data['error'])) {
    echo "Error occurred: {$data['error']}";
}
```

## Advanced Usage

### Multi-Service Lookup

```php
$services = [
    RadioAPI::SPOTIFY,
    RadioAPI::DEEZER,
    RadioAPI::APPLE_MUSIC,
];

foreach ($services as $service) {
    $data = $api->withService($service)
        ->streamTitle()
        ->setStreamUrl($streamUrl)
        ->fetchArray();
    
    if (!empty($data['results']) || $data['metadataFound']) {
        echo "Found metadata using: $service\n";
        break;
    }
}
```

### Language Configuration

Set response language for international metadata:

```php
$api->setLanguage('fr'); // French
$api->setLanguage('de'); // German
$api->setLanguage('es'); // Spanish
$api->setLanguage('ja'); // Japanese
```

### History Control

Control track history inclusion for performance:

```php
// Disable history for faster responses
$api->withHistory(false);

// Re-enable history
$api->withHistory(true);
```

### Metadata Validation

```php
$stream = $api->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio')
    ->fetchArray();

// Check if metadata was found
if ($stream['metadataFound']) {
    echo "Track: {$stream['artist']} - {$stream['song']}\n";
}

// Alternative using hasMetadata() method
$streamObj = $api->streamTitle();
$streamObj->setStreamUrl('https://stream.example.com/radio');
$data = $streamObj->fetchArray();

if ($streamObj->hasMetadata()) {
    echo "Metadata available\n";
}
```

### Raw Response Access

```php
$streamObj = $api->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio');

$response = $streamObj->fetchArray();
$rawData = $streamObj->getData(); // Same as $response
```

## Best Practices

### Performance Optimization

1. **Disable history when not needed:**
   ```php
   $api->withHistory(false);
   ```

2. **Use specific services instead of auto-detection:**
   ```php
   $api->withService(RadioAPI::SPOTIFY); // Better than RadioAPI::AUTO
   ```

3. **Handle empty responses gracefully:**
   ```php
   if (empty($api->streamTitle()->setStreamUrl($url)->fetchArray())) {
       // Handle empty response
   }
   ```

### Error Handling Strategy

1. **Always validate configuration:**
   ```php
   if (empty($baseUrl) || empty($apiKey)) {
       throw new InvalidArgumentException('Base URL and API key required');
   }
   ```

2. **Use appropriate exception handling:**
   ```php
   try {
       $data = $api->streamTitle()->setStreamUrl($url)->fetchArray();
   } catch (ClientErrorException $e) {
       // Log and handle client errors (bad request, unauthorized, etc.)
   } catch (ServerErrorException $e) {
       // Retry logic for server errors
   }
   ```

### Service Selection

1. **Choose services based on your audience:**
   - Use `SPOTIFY` for global audiences
   - Use `DEEZER` for European audiences
   - Use `APPLE_MUSIC` for iOS-focused applications

2. **Implement fallback chains:**
   ```php
   $services = [RadioAPI::SPOTIFY, RadioAPI::DEEZER, RadioAPI::APPLE_MUSIC];
   foreach ($services as $service) {
       $data = $api->withService($service)->streamTitle()->setStreamUrl($url)->fetchArray();
       if ($data['metadataFound']) break;
   }
   ```

## Common Use Cases

### Radio Station Dashboard

```php
$api = new RadioAPI();
$api->setBaseUrl($radioApiUrl)
    ->setApiKey($apiKey)
    ->withHistory(true);

$currentTrack = $api->withService(RadioAPI::SPOTIFY)
    ->streamTitle()
    ->setStreamUrl($stationStreamUrl)
    ->fetchArray();

if ($currentTrack['metadataFound']) {
    echo "Now Playing: {$currentTrack['artist']} - {$currentTrack['song']}\n";
    echo "Album: {$currentTrack['album']}\n";
    echo "Duration: {$currentTrack['time']}\n";
    
    if (!empty($currentTrack['history'])) {
        echo "Recently Played:\n";
        foreach (array_slice($currentTrack['history'], 0, 5) as $track) {
            echo "- {$track['artist']} - {$track['song']} ({$track['relative_time']})\n";
        }
    }
}
```

### Music Discovery App

```php
$searchQuery = "indie rock 2024";
$results = $api->withService(RadioAPI::SPOTIFY)
    ->musicSearch()
    ->search($searchQuery)
    ->fetchArray();

if (!empty($results)) {
    echo "Found: {$results['artist']} - {$results['title']}\n";
    echo "Listen on Spotify: {$results['stream']}\n";
    
    if (!empty($results['artwork']['large'])) {
        echo "Artwork: {$results['artwork']['large']}\n";
    }
}
```

### Multi-Platform Integration

```php
class RadioMetadataService {
    private RadioAPI $api;
    
    public function __construct(string $baseUrl, string $apiKey) {
        $this->api = new RadioAPI();
        $this->api->setBaseUrl($baseUrl)->setApiKey($apiKey);
    }
    
    public function getCurrentTrack(string $streamUrl, array $preferredServices = []): ?array {
        $services = $preferredServices ?: [
            RadioAPI::SPOTIFY,
            RadioAPI::DEEZER,
            RadioAPI::APPLE_MUSIC
        ];
        
        foreach ($services as $service) {
            try {
                $data = $this->api->withService($service)
                    ->streamTitle()
                    ->setStreamUrl($streamUrl)
                    ->fetchArray();
                    
                if ($data['metadataFound']) {
                    return $data;
                }
            } catch (Exception $e) {
                // Log error and continue to next service
                error_log("Service $service failed: " . $e->getMessage());
            }
        }
        
        return null;
    }
}
```

## Troubleshooting

### Common Issues

1. **Empty responses:**
   - Verify base URL and API key are set
   - Check if stream URL is accessible
   - Ensure the stream contains metadata

2. **Authentication errors:**
   - Verify API key is correct
   - Check if API key has required permissions

3. **Service-specific errors:**
   - Some services may not have metadata for all tracks
   - Try different service mount points
   - Use fallback to basic endpoint without service

### Debug Information

Enable detailed error information:

```php
$api->setThrowOnApiErrors(true);

try {
    $data = $api->streamTitle()->setStreamUrl($url)->fetchArray();
} catch (ApiErrorException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Status: " . $e->getStatusCode() . "\n";
    echo "Context: " . json_encode($e->getContext()) . "\n";
    echo "Error Data: " . json_encode($e->getErrorData()) . "\n";
}
```

## License

Apache-2.0