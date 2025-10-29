# RadioAPI PHP SDK

A modern PHP SDK for interacting with RadioAPI services to retrieve radio stream metadata, search music tracks, and analyze image colors.

## Installation

```bash
composer require joeyboli/radioapisdk
```

## Requirements

- PHP 8.3+
- Symfony HTTP Client 7.2+

## Quick Start

```php
use RadioAPI\RadioAPI;

$api = new RadioAPI('https://api.example.com', 'your-api-key');

// Get current stream metadata
$response = $api->getStreamTitle('https://stream.example.com/radio');
if ($response->isSuccess()) {
    $track = $response->getCurrentTrack();
    echo "Now Playing: {$track['artist']} - {$track['song']}";
}

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

```php
$api = new RadioAPI('https://your-radioapi-instance.com', 'your-api-key', [
    'language' => 'en',            // Response language (ISO 639-1)
    'with_history' => true,        // Include track history (default: true)
    'timeout' => 30,               // Request timeout in seconds
    'throw_on_errors' => true,     // Throw exceptions on errors (default: true)
    'user_agent' => 'MyApp/1.0'    // Custom user agent
]);
```

## API Methods

### Stream Title API

Get current track metadata from radio streams:

```php
$response = $api->getStreamTitle('https://stream.example.com/radio', RadioAPI::SPOTIFY);

if ($response->isSuccess()) {
    $track = $response->getCurrentTrack();
    $streamInfo = $response->getStreamInfo();
    $history = $response->getHistory();
}
```

**Response Methods:**
- `getCurrentTrack()` - Current playing track info
- `getStreamInfo()` - Stream metadata (name, bitrate, format)
- `getHistory()` - Track history array
- `isSuccess()` - Check if request succeeded

### Music Search API

Search for tracks across streaming platforms:

```php
$response = $api->searchMusic('Artist - Song', RadioAPI::SPOTIFY);

if ($response->hasResults()) {
    $tracks = $response->getTracks();
    $firstTrack = $response->getFirstTrack();
}
```

**Response Methods:**
- `getTracks()` - All found tracks
- `getFirstTrack()` - Best match track
- `hasResults()` - Check if tracks found

### Image Color Analysis

Extract colors from images:

```php
$response = $api->getImageColors('https://example.com/image.jpg');

if ($response->isSuccess()) {
    $dominantColor = $response->getDominantColorHex();
    $textColor = $response->getTextColorHex();
    $palette = $response->getPalette();
}
```

## Service Constants

**Music Services:**
- `RadioAPI::SPOTIFY` - Spotify
- `RadioAPI::DEEZER` - Deezer
- `RadioAPI::APPLE_MUSIC` - Apple Music
- `RadioAPI::YOUTUBE_MUSIC` - YouTube Music
- `RadioAPI::AUTO` - Auto-detect

**Radio Platforms:**
- `RadioAPI::AZURACAST` - AzuraCast
- `RadioAPI::LIVE365` - Live365

## Error Handling

```php
use RadioAPI\Exceptions\RadioAPIException;

try {
    $response = $api->getStreamTitle($streamUrl);
} catch (RadioAPIException $e) {
    if ($e->isClientError()) {
        echo "Client error: " . $e->getMessage();
    } elseif ($e->isServerError()) {
        echo "Server error: " . $e->getMessage();
    }
    
    echo "Status: " . $e->getStatusCode();
}
```

**Exception Methods:**
- `isClientError()` - 4xx errors
- `isServerError()` - 5xx errors
- `isNetworkError()` - Network issues
- `getStatusCode()` - HTTP status code
- `getErrorData()` - Original error response

## Laravel Integration

### Service Provider

```php
// config/services.php
'radioapi' => [
    'base_url' => env('RADIOAPI_BASE_URL'),
    'api_key' => env('RADIOAPI_API_KEY'),
],

// Service Provider
$this->app->singleton(RadioAPI::class, function ($app) {
    return new RadioAPI(
        config('services.radioapi.base_url'),
        config('services.radioapi.api_key')
    );
});
```

### Controller Usage

```php
class RadioController extends Controller
{
    public function __construct(private RadioAPI $radioApi) {}

    public function getCurrentTrack(Request $request)
    {
        try {
            $response = $this->radioApi->getStreamTitle($request->stream_url);
            
            return response()->json([
                'success' => $response->isSuccess(),
                'current_track' => $response->getCurrentTrack(),
                'history' => $response->getHistory(),
            ]);
        } catch (RadioAPIException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
```

## Common Use Cases

### Radio Dashboard

```php $apiKey, ['with_history' => true]);

$response = $api->getStreamTitle($streamUrl, RadioAPI::SPOTIFY);

if ($response->isSuccess()) {
    $track = $response->getCurrentTrack();
    $streamInfo = $response->getStreamInfo();
    
    echo "Now Playing: {$track['artist']} - {$track['song']}\n";
    echo "Stream: {$streamInfo['name']} ({$streamInfo['bitrate']}kbps)\n";
    
    foreach ($response->getHistory() as $track) {
        echo "Previous: {$track['artist']} - {$track['song']}\n";
    }
}
```

### Music Discovery

```php
$response = $api->searchMusic("indie rock 2024", RadioAPI::SPOTIFY);

if ($response->hasResults()) {
    $track = $response->getFirstTrack();
    echo "Found: {$track['artist']} - {$track['title']}\n";
    echo "Listen: {$track['stream']}\n";
}
```

### Multi-Service Fallback

```php
$services = [RadioAPI::SPOTIFY, RadioAPI::DEEZER, RadioAPI::APPLE_MUSIC];

foreach ($services as $service) {
    try {
        $response = $api->getStreamTitle($streamUrl, $service);
        if ($response->isSuccess() && $response->getCurrentTrack()) {
            echo "Found metadata using: $service\n";
            break;
        }
    } catch (RadioAPIException $e) {
        continue; // Try next service
    }
}
```

## License

Apache-2.0