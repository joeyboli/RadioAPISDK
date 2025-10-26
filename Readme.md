# RadioAPI PHP SDK

A modern PHP SDK for interacting with RadioAPI services to retrieve radio stream metadata and search music tracks across multiple streaming platforms.

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

// Simple configuration
$api = new RadioAPI('https://api.example.com', 'your-api-key');

// Get current stream metadata
$response = $api->getStreamTitle('https://stream.example.com/radio');
echo "Now Playing: " . $response->getCurrentTrack()['artist'] . " - " . $response->getCurrentTrack()['song'];

// Search for music
$response = $api->searchMusic('The Beatles - Hey Jude');
if ($response->hasResults()) {
    $track = $response->getFirstTrack();
    echo "Found: {$track['artist']} - {$track['title']}";
}

// Get image colors
$response = $api->getImageColors('https://example.com/image.jpg');
echo "Dominant color: " . $response->getDominantColorHex();
```

## Configuration

### Constructor-Based Configuration

```php
use RadioAPI\RadioAPI;

// Basic configuration
$api = new RadioAPI('https://your-radioapi-instance.com', 'your-api-key');

// Advanced configuration with options
$api = new RadioAPI('https://your-radioapi-instance.com', 'your-api-key', [
    'throw_on_errors' => true,     // Throw exceptions on API errors (default: true)
    'language' => 'en',            // Response language (ISO 639-1 codes)
    'with_history' => true,        // Include track history in responses (default: true)
    'timeout' => 30,               // HTTP request timeout in seconds (default: 30)
    'user_agent' => 'MyApp/1.0'    // Custom user agent
]);
```

### Configuration Options

- `throw_on_errors` (bool) - Enable/disable exception throwing on API errors (default: true)
- `language` (string) - Set response language using ISO 639-1 codes: 'en', 'fr', 'de', etc.
- `with_history` (bool) - Include/exclude track history in responses (default: true)
- `timeout` (int) - HTTP request timeout in seconds (default: 30)
- `user_agent` (string) - Custom user agent string

## Stream Title API

Retrieve current playing track metadata from radio streams.

### Basic Usage

```php
$response = $api->getStreamTitle('https://stream.example.com/radio');

if ($response->isSuccess()) {
    $currentTrack = $response->getCurrentTrack();
    echo "Now Playing: {$currentTrack['artist']} - {$currentTrack['song']}\n";
    echo "Album: {$currentTrack['album']}\n";
    
    // Access stream info
    $streamInfo = $response->getStreamInfo();
    echo "Stream: {$streamInfo['name']} ({$streamInfo['bitrate']}kbps)\n";
    
    // Get track history
    $history = $response->getHistory();
    foreach ($history as $track) {
        echo "Previous: {$track['artist']} - {$track['song']} ({$track['relative_time']})\n";
    }
}
```

### StreamTitleResponse Methods

- `getCurrentTrack()` - Get current playing track information
- `getStreamInfo()` - Get stream metadata (name, bitrate, format)
- `getHistory()` - Get track history array
- `isSuccess()` - Check if the request was successful
- `getRawData()` - Get the complete raw response data
- `getError()` - Get error message if request failed

### Response Data Structure

The response objects provide convenient methods to access data, but you can also access the raw response data:

**Current Track Information:**
```php
$currentTrack = $response->getCurrentTrack();
// Returns:
[
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
    'explicit' => false
]
```

**Stream Information:**
```php
$streamInfo = $response->getStreamInfo();
// Returns:
[
    'name' => 'Hunter.FM - O Canal K-pop',
    'bitrate' => 256,
    'format' => 'AAC'
]
```

**Track History:**
```php
$history = $response->getHistory();
// Returns array of:
[
    [
        'artist' => 'ENHYPEN',
        'song' => 'Bite Me',
        'timestamp' => '2025-10-12 16:39:25.868785',
        'relative_time' => '4 minutes ago',
        'artwork' => 'https://icdn2.streamafrica.net/stacks/6a615f61aac53844.jpg'
    ]
    // ... more tracks
]
```

## Music Search API

Search for music tracks across various streaming platforms.

### Basic Usage

```php
$response = $api->searchMusic('The Beatles - Hey Jude');

if ($response->hasResults()) {
    $track = $response->getFirstTrack();
    echo "Found: {$track['artist']} - {$track['title']}\n";
    echo "Album: {$track['album']}\n";
    echo "Listen: {$track['stream']}\n";
    
    // Access all tracks
    $tracks = $response->getTracks();
    foreach ($tracks as $track) {
        echo "Track: {$track['artist']} - {$track['title']}\n";
    }
}

// Search with specific service
$response = $api->searchMusic('Radiohead Creep', RadioAPI::SPOTIFY);
```

### MusicSearchResponse Methods

- `getTracks()` - Get array of all found tracks
- `getFirstTrack()` - Get the first/best match track
- `hasResults()` - Check if any tracks were found
- `isSuccess()` - Check if the request was successful
- `getRawData()` - Get the complete raw response data
- `getError()` - Get error message if request failed

### Music Search Response Structure

**Track Information:**
```php
$track = $response->getFirstTrack();
// Returns:
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

## Image Color Analysis API

Extract dominant colors and generate color palettes from images.

### Basic Usage

```php
$response = $api->getImageColors('https://example.com/image.jpg');

if ($response->isSuccess()) {
    echo "Dominant Color: " . $response->getDominantColorHex() . "\n";
    echo "Text Color: " . $response->getTextColorHex() . "\n";
    
    // Get Flutter-compatible hex colors
    echo "Flutter Dominant: " . $response->getDominantColorFlutterHex() . "\n";
    echo "Flutter Text: " . $response->getTextColorFlutterHex() . "\n";
    
    // Get full color palette
    $palette = $response->getPalette();
    foreach ($palette as $color) {
        echo "Color: {$color['hex']} (Population: {$color['population']})\n";
    }
}
```

### ColorResponse Methods

- `getDominantColorHex()` - Get dominant color as hex string (#RRGGBB)
- `getTextColorHex()` - Get recommended text color as hex string
- `getDominantColorFlutterHex()` - Get dominant color in Flutter format (0xFFRRGGBB)
- `getTextColorFlutterHex()` - Get text color in Flutter format
- `getPalette()` - Get complete color palette with population data
- `isSuccess()` - Check if the request was successful
- `getRawData()` - Get the complete raw response data
- `getError()` - Get error message if request failed

## Service Integration

### Music Streaming Services

Enhance metadata with service-specific information by passing the service as a second parameter:

```php
// Spotify integration for stream metadata
$response = $api->getStreamTitle('https://stream.example.com/radio', RadioAPI::SPOTIFY);

// Deezer integration for music search
$response = $api->searchMusic('Radiohead Creep', RadioAPI::DEEZER);

// Auto-detect best service
$response = $api->getStreamTitle('https://stream.example.com/radio', RadioAPI::AUTO);
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
$response = $api->getStreamTitle(
    'https://azuracast.example.com/listen/stationid/mountpoint',
    RadioAPI::AZURACAST
);
```

**Live365:**
```php
$response = $api->getStreamTitle(
    'https://streaming.live365.com/mountid',
    RadioAPI::LIVE365
);
```

## Error Handling

### Exception Handling

The SDK uses a single `RadioAPIException` class for all API errors, with helper methods to categorize error types:

```php
use RadioAPI\Exceptions\RadioAPIException;

try {
    $response = $api->getStreamTitle('https://stream.example.com/radio');
} catch (RadioAPIException $e) {
    if ($e->isClientError()) {
        // Handle 4xx errors (bad request, unauthorized, etc.)
        echo "Client error: {$e->getMessage()}";
    } elseif ($e->isServerError()) {
        // Handle 5xx errors (server issues)
        echo "Server error: {$e->getMessage()}";
    } elseif ($e->isNetworkError()) {
        // Handle network connectivity issues
        echo "Network error: {$e->getMessage()}";
    }
    
    // Access detailed error information
    echo "Status code: {$e->getStatusCode()}";
    echo "Error data: " . json_encode($e->getErrorData());
    echo "Context: " . json_encode($e->getContext());
}
```

### Exception Methods

The `RadioAPIException` class provides:
- `getMessage()` - Get the error message
- `getStatusCode()` - Get the HTTP status code
- `getErrorData()` - Get the original API error response
- `getContext()` - Get additional context information
- `hasErrorField(string $field)` - Check if error data contains a field
- `getErrorField(string $field, $default = null)` - Get specific error field
- `isClientError()` - Check if error is a 4xx client error
- `isServerError()` - Check if error is a 5xx server error
- `isNetworkError()` - Check if error is network-related

### Disable Exception Throwing

```php
// Configure to not throw exceptions
$api = new RadioAPI('https://api.example.com', 'api-key', [
    'throw_on_errors' => false
]);

$response = $api->getStreamTitle('https://stream.example.com/radio');

if (!$response->isSuccess()) {
    echo "Error occurred: " . $response->getError();
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

$streamUrl = 'https://stream.example.com/radio';

foreach ($services as $service) {
    try {
        $response = $api->getStreamTitle($streamUrl, $service);
        
        if ($response->isSuccess() && $response->getCurrentTrack()) {
            echo "Found metadata using: $service\n";
            $track = $response->getCurrentTrack();
            echo "Track: {$track['artist']} - {$track['song']}\n";
            break;
        }
    } catch (RadioAPIException $e) {
        // Log error and continue to next service
        error_log("Service $service failed: " . $e->getMessage());
    }
}
```

### Configuration Options

Configure the client with various options:

```php
// Performance-optimized configuration
$api = new RadioAPI('https://api.example.com', 'api-key', [
    'with_history' => false,    // Disable history for faster responses
    'timeout' => 10,            // Shorter timeout for quick responses
    'language' => 'en'          // Set response language
]);

// Multi-language support
$api = new RadioAPI('https://api.example.com', 'api-key', [
    'language' => 'fr'  // French, German (de), Spanish (es), Japanese (ja), etc.
]);
```

### Response Validation

```php
$response = $api->getStreamTitle('https://stream.example.com/radio');

// Check response status
if ($response->isSuccess()) {
    $currentTrack = $response->getCurrentTrack();
    
    if ($currentTrack) {
        echo "Track: {$currentTrack['artist']} - {$currentTrack['song']}\n";
    } else {
        echo "No current track information available\n";
    }
    
    // Check for history
    $history = $response->getHistory();
    if (!empty($history)) {
        echo "Track history available: " . count($history) . " tracks\n";
    }
} else {
    echo "Request failed: " . $response->getError() . "\n";
}
```

### Raw Response Access

```php
$response = $api->getStreamTitle('https://stream.example.com/radio');

// Get complete raw response data
$rawData = $response->getRawData();
echo json_encode($rawData, JSON_PRETTY_PRINT);

// Access specific fields from raw data
if (isset($rawData['metadataFound']) && $rawData['metadataFound']) {
    echo "Metadata found in raw response\n";
}
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
$api = new RadioAPI($radioApiUrl, $apiKey, ['with_history' => true]);

$response = $api->getStreamTitle($stationStreamUrl, RadioAPI::SPOTIFY);

if ($response->isSuccess()) {
    $currentTrack = $response->getCurrentTrack();
    $streamInfo = $response->getStreamInfo();
    
    if ($currentTrack) {
        echo "Now Playing: {$currentTrack['artist']} - {$currentTrack['song']}\n";
        echo "Album: {$currentTrack['album']}\n";
        echo "Duration: {$currentTrack['time']}\n";
    }
    
    echo "Stream: {$streamInfo['name']} ({$streamInfo['bitrate']}kbps)\n";
    
    $history = $response->getHistory();
    if (!empty($history)) {
        echo "Recently Played:\n";
        foreach (array_slice($history, 0, 5) as $track) {
            echo "- {$track['artist']} - {$track['song']} ({$track['relative_time']})\n";
        }
    }
}
```

### Music Discovery App

```php
$searchQuery = "indie rock 2024";
$response = $api->searchMusic($searchQuery, RadioAPI::SPOTIFY);

if ($response->hasResults()) {
    $track = $response->getFirstTrack();
    echo "Found: {$track['artist']} - {$track['title']}\n";
    echo "Listen on Spotify: {$track['stream']}\n";
    
    if (!empty($track['artwork']['large'])) {
        echo "Artwork: {$track['artwork']['large']}\n";
    }
}
```

### Image Color Analysis

```php
$imageUrl = 'https://example.com/album-cover.jpg';
$response = $api->getImageColors($imageUrl);

if ($response->isSuccess()) {
    echo "Dominant Color: " . $response->getDominantColorHex() . "\n";
    echo "Text Color: " . $response->getTextColorHex() . "\n";
    
    // Use in CSS
    echo "CSS: background-color: " . $response->getDominantColorHex() . "; color: " . $response->getTextColorHex() . ";\n";
    
    // Use in Flutter
    echo "Flutter: Color(" . $response->getDominantColorFlutterHex() . ")\n";
}
```

### Multi-Platform Integration

```php
class RadioMetadataService {
    private RadioAPI $api;
    
    public function __construct(string $baseUrl, string $apiKey) {
        $this->api = new RadioAPI($baseUrl, $apiKey);
    }
    
    public function getCurrentTrack(string $streamUrl, array $preferredServices = []): ?array {
        $services = $preferredServices ?: [
            RadioAPI::SPOTIFY,
            RadioAPI::DEEZER,
            RadioAPI::APPLE_MUSIC
        ];
        
        foreach ($services as $service) {
            try {
                $response = $this->api->getStreamTitle($streamUrl, $service);
                
                if ($response->isSuccess()) {
                    $track = $response->getCurrentTrack();
                    if ($track) {
                        return $track;
                    }
                }
            } catch (RadioAPIException $e) {
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
$api = new RadioAPI('https://api.example.com', 'api-key', [
    'throw_on_errors' => true
]);

try {
    $response = $api->getStreamTitle($url);
} catch (RadioAPIException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Status: " . $e->getStatusCode() . "\n";
    echo "Context: " . json_encode($e->getContext()) . "\n";
    echo "Error Data: " . json_encode($e->getErrorData()) . "\n";
    
    // Check error type
    if ($e->isClientError()) {
        echo "This is a client error (4xx)\n";
    } elseif ($e->isServerError()) {
        echo "This is a server error (5xx)\n";
    }
}
```

## License

Apache-2.0