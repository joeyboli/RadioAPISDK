# RadioAPI PHP Client

A modern, fluent PHP client for the RadioAPI service. Get radio stream metadata, search music across streaming platforms, and extract dominant colors from images with a clean, developer-friendly API.

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## Features

- 🎵 **Stream Metadata** - Get current track information from radio streams
- 🔍 **Music Search** - Search across Spotify, Deezer, Apple Music, and more
- 🎨 **Color Extraction** - Extract dominant colors from album artwork
- ⚡ **Fluent API** - Clean, chainable method calls
- 🛡️ **Type Safe** - Full PHP 8.1+ type declarations
- 📦 **Zero Config** - Works out of the box
- 🔄 **Built on ElliePHP** - Leverages the powerful ElliePHP HttpClient

## Requirements

- PHP 8.1 or higher
- ElliePHP HttpClient

## Installation

Install via Composer:

```bash
composer require radioapi/radioapi-php
```

## Quick Start

```php
<?php

use RadioAPI\RadioAPI;

// Create a client instance
$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key')
    ->language('en')
    ->service(RadioAPI::SPOTIFY)
    ->withHistory();

// Get current track from a radio stream
$stream = $api->getStreamTitle('https://stream.example.com/radio');

if ($stream->isSuccess()) {
    $track = $stream->getCurrentTrack();
    echo "{$track['artist']} - {$track['song']}\n";
    
    // Get history if available
    foreach ($stream->getHistory() as $historical) {
        echo "Previously: {$historical['artist']} - {$historical['song']}\n";
    }
}

// Search for music
$search = $api->searchMusic('The Beatles - Hey Jude');

if ($search->hasResults()) {
    $firstTrack = $search->getFirstTrack();
    echo "Found: {$firstTrack['artist']} - {$firstTrack['title']}\n";
}

// Extract colors from album artwork
$colors = $api->getImageColors('https://example.com/album-art.jpg');

if ($colors->isSuccess()) {
    echo "Dominant color: " . $colors->getDominantColorHex() . "\n";
    echo "Text color: " . $colors->getTextColorHex() . "\n";
}
```

## Configuration

### Basic Configuration

```php
use RadioAPI\RadioAPI;

// Simple instantiation
$api = new RadioAPI('https://api.radioapi.io', 'your-api-key');

// Fluent instantiation with configuration
$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key')
    ->language('en')           // Set response language
    ->service(RadioAPI::SPOTIFY)  // Set default service
    ->withHistory()            // Enable track history
    ->timeout(60);             // Set request timeout
```

### Available Configuration Methods

| Method | Description | Default |
|--------|-------------|---------|
| `language(string $code)` | Set language for responses (ISO 639-1) | `'en'` |
| `service(string $service)` | Set default service/platform | `null` |
| `withHistory(bool $enabled)` | Enable/disable track history | `true` |
| `withoutHistory()` | Disable track history | - |
| `timeout(int $seconds)` | Set request timeout | `30` |

### Service Constants

Music streaming services:

```php
RadioAPI::SPOTIFY         // Spotify
RadioAPI::DEEZER          // Deezer
RadioAPI::APPLE_MUSIC     // Apple Music / iTunes
RadioAPI::YOUTUBE_MUSIC   // YouTube Music
RadioAPI::FLO_MUSIC       // FLO Music
RadioAPI::LINE_MUSIC      // LINE Music
RadioAPI::KKBOX_MUSIC     // KKBOX
RadioAPI::AUTO            // Auto-detect
```

Radio platforms:

```php
RadioAPI::AZURACAST       // AzuraCast
RadioAPI::LIVE365         // Live365
RadioAPI::RADIOKING       // RadioKing
```

## API Methods

### Get Stream Title

Retrieve current track information from a radio stream.

```php
public function getStreamTitle(
    string $streamUrl,
    ?string $service = null,
    ?bool $withHistory = null
): StreamTitleResponse
```

**Parameters:**
- `$streamUrl` - URL of the radio stream
- `$service` - Optional service override (use class constants)
- `$withHistory` - Optional history override

**Example:**

```php
// Basic usage
$response = $api->getStreamTitle('https://stream.example.com/radio');

// With service override
$response = $api->getStreamTitle(
    'https://stream.example.com/radio',
    RadioAPI::SPOTIFY
);

// Disable history for this request
$response = $api->getStreamTitle(
    'https://stream.example.com/radio',
    null,
    false
);
```

### Search Music

Search for music tracks across streaming services.

```php
public function searchMusic(
    string $query,
    ?string $service = null,
    ?string $language = null
): MusicSearchResponse
```

**Parameters:**
- `$query` - Search query (artist, track, album, etc.)
- `$service` - Optional service to search
- `$language` - Optional language override

**Example:**

```php
// Basic search
$response = $api->searchMusic('The Beatles - Hey Jude');

// Search specific service
$response = $api->searchMusic(
    'The Beatles - Hey Jude',
    RadioAPI::SPOTIFY
);

// Search with language override
$response = $api->searchMusic(
    'The Beatles - Hey Jude',
    RadioAPI::SPOTIFY,
    'fr'
);
```

### Get Image Colors

Extract dominant colors from an image URL.

```php
public function getImageColors(string $imageUrl): ColorResponse
```

**Parameters:**
- `$imageUrl` - URL of the image to analyze

**Example:**

```php
$response = $api->getImageColors('https://example.com/album-art.jpg');

if ($response->isSuccess()) {
    $hex = $response->getDominantColorHex();
    $rgb = $response->getDominantColorRgb();
    $css = $response->getDominantColorCss();
}
```

## Response Objects

### StreamTitleResponse

Methods for accessing stream metadata:

```php
// Check success
$response->isSuccess(): bool
$response->getError(): ?string

// Current track
$response->getCurrentTrack(): ?array
$response->getArtist(): ?string
$response->getTitle(): ?string
$response->getAlbum(): ?string

// History
$response->getHistory(): array
$response->hasHistory(): bool
$response->getHistoryCount(): int
$response->getLastTrack(): ?array

// Stream info
$response->getStreamInfo(): array

// Raw data
$response->getRawData(): array
```

**Example:**

```php
$response = $api->getStreamTitle('https://stream.example.com/radio');

if ($response->isSuccess()) {
    // Get current track details
    $artist = $response->getArtist();
    $title = $response->getTitle();
    $album = $response->getAlbum();
    
    echo "$artist - $title";
    if ($album) {
        echo " (from $album)";
    }
    
    // Check history
    if ($response->hasHistory()) {
        echo "\nPreviously played:\n";
        foreach ($response->getHistory() as $track) {
            echo "- {$track['artist']} - {$track['song']}\n";
        }
    }
} else {
    echo "Error: " . $response->getError();
}
```

### MusicSearchResponse

Methods for accessing search results:

```php
// Check success
$response->isSuccess(): bool
$response->getError(): ?string
$response->hasResults(): bool

// Get tracks
$response->getTracks(): array
$response->getFirstTrack(): ?array
$response->getResultCount(): int

// Filter and transform
$response->getTracksByService(string $service): array
$response->filter(callable $callback): array
$response->map(callable $callback): array

// Metadata
$response->getQuery(): ?string
$response->getService(): ?string

// Raw data
$response->getRawData(): array
```

**Example:**

```php
$response = $api->searchMusic('The Beatles - Hey Jude');

if ($response->hasResults()) {
    // Get all tracks
    foreach ($response->getTracks() as $track) {
        echo "{$track['artist']} - {$track['title']}\n";
    }
    
    // Get only first result
    $first = $response->getFirstTrack();
    
    // Filter results
    $explicit = $response->filter(fn($t) => $t['explicit'] ?? false);
    
    // Transform results
    $simplified = $response->map(fn($t) => [
        'artist' => $t['artist'],
        'title' => $t['title']
    ]);
    
    // Get tracks from specific service
    $spotifyTracks = $response->getTracksByService('spotify');
}
```

### ColorResponse

Methods for accessing color data:

```php
// Check success
$response->isSuccess(): bool
$response->getError(): ?string

// Hex colors
$response->getDominantColorHex(): ?string
$response->getTextColorHex(): ?string

// RGB colors
$response->getDominantColorRgb(): ?array
$response->getTextColorRgb(): ?array

// CSS colors
$response->getDominantColorCss(): ?string
$response->getTextColorCss(): ?string

// Flutter colors
$response->getDominantColorFlutterHex(): ?string
$response->getTextColorFlutterHex(): ?string

// Palette
$response->getPalette(): array
$response->getPaletteColor(int $index): ?array
$response->getPaletteCount(): int

// Raw data
$response->getRawData(): array
```

**Example:**

```php
$response = $api->getImageColors('https://example.com/album-art.jpg');

if ($response->isSuccess()) {
    // Get colors in different formats
    $hex = $response->getDominantColorHex();        // "#FF5733"
    $rgb = $response->getDominantColorRgb();        // ['r' => 255, 'g' => 87, 'b' => 51]
    $css = $response->getDominantColorCss();        // "rgb(255, 87, 51)"
    $flutter = $response->getDominantColorFlutterHex(); // "0xFFFF5733"
    
    // Use in HTML
    echo "<div style='background-color: $css;'>";
    echo "  <span style='color: " . $response->getTextColorCss() . ";'>";
    echo "    Content with good contrast";
    echo "  </span>";
    echo "</div>";
    
    // Get full palette
    $palette = $response->getPalette();
    foreach ($palette as $color) {
        echo "Color: {$color['hex']}\n";
    }
}
```

## Error Handling

### RadioAPIException

All API errors throw a `RadioAPIException` with detailed information:

```php
use RadioAPI\Exceptions\RadioAPIException;

try {
    $response = $api->getStreamTitle('https://invalid-url.example.com');
} catch (RadioAPIException $e) {
    // Get error details
    echo $e->getMessage();              // Human-readable message
    echo $e->getStatusCode();           // HTTP status code
    echo $e->getDetailedMessage();      // Full details with context
    
    // Check error type
    if ($e->isClientError()) {
        // 4xx error - problem with request
    }
    if ($e->isServerError()) {
        // 5xx error - problem with API server
    }
    if ($e->isNetworkError()) {
        // Connection/network error
    }
    
    // Specific status checks
    if ($e->isUnauthorized()) {
        // 401 - Invalid API key
    }
    if ($e->isRateLimited()) {
        // 429 - Too many requests
    }
    if ($e->isNotFound()) {
        // 404 - Resource not found
    }
    
    // Get additional data
    $errorData = $e->getErrorData();    // API error response
    $context = $e->getContext();        // Request context
    
    // Check specific error field
    if ($e->hasErrorField('detail')) {
        $detail = $e->getErrorField('detail');
    }
}
```

### Exception Methods

| Method | Description | Returns |
|--------|-------------|---------|
| `getMessage()` | Get error message | `string` |
| `getStatusCode()` | Get HTTP status code | `int` |
| `getDetailedMessage()` | Get detailed message with context | `string` |
| `getErrorData()` | Get API error response data | `array` |
| `getContext()` | Get request context | `array` |
| `isClientError()` | Check if 4xx error | `bool` |
| `isServerError()` | Check if 5xx error | `bool` |
| `isNetworkError()` | Check if network error | `bool` |
| `isUnauthorized()` | Check if 401 | `bool` |
| `isForbidden()` | Check if 403 | `bool` |
| `isNotFound()` | Check if 404 | `bool` |
| `isRateLimited()` | Check if 429 | `bool` |
| `hasErrorField(string)` | Check if error field exists | `bool` |
| `getErrorField(string, mixed)` | Get error field value | `mixed` |

## Complete Examples

### Example 1: Radio Station Monitor

```php
<?php

use RadioAPI\RadioAPI;
use RadioAPI\Exceptions\RadioAPIException;

$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key')
    ->language('en')
    ->service(RadioAPI::SPOTIFY)
    ->withHistory();

$streamUrl = 'https://stream.example.com/radio';

try {
    $response = $api->getStreamTitle($streamUrl);
    
    if ($response->isSuccess()) {
        $track = $response->getCurrentTrack();
        
        echo "Now Playing:\n";
        echo "Artist: {$track['artist']}\n";
        echo "Song: {$track['song']}\n";
        
        if (isset($track['album'])) {
            echo "Album: {$track['album']}\n";
        }
        
        if ($response->hasHistory()) {
            echo "\nRecently Played:\n";
            foreach ($response->getHistory() as $idx => $historical) {
                echo ($idx + 1) . ". {$historical['artist']} - {$historical['song']}\n";
            }
        }
    }
} catch (RadioAPIException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    if ($e->isRateLimited()) {
        echo "Rate limit exceeded. Please try again later.\n";
    }
}
```

### Example 2: Music Search with Multiple Services

```php
<?php

use RadioAPI\RadioAPI;

$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key');

$query = 'Billie Eilish - bad guy';
$services = [
    RadioAPI::SPOTIFY,
    RadioAPI::DEEZER,
    RadioAPI::APPLE_MUSIC,
];

foreach ($services as $service) {
    $response = $api->searchMusic($query, $service);
    
    if ($response->hasResults()) {
        $track = $response->getFirstTrack();
        echo "Found on " . ucfirst($service) . ":\n";
        echo "  {$track['artist']} - {$track['title']}\n";
        if (isset($track['url'])) {
            echo "  URL: {$track['url']}\n";
        }
        echo "\n";
    }
}
```

### Example 3: Album Art Color Theming

```php
<?php

use RadioAPI\RadioAPI;

$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key');

$albumArtUrl = 'https://example.com/album-art.jpg';
$response = $api->getImageColors($albumArtUrl);

if ($response->isSuccess()) {
    $bgColor = $response->getDominantColorCss();
    $textColor = $response->getTextColorCss();
    
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "  <style>\n";
    echo "    body {\n";
    echo "      background-color: $bgColor;\n";
    echo "      color: $textColor;\n";
    echo "    }\n";
    echo "  </style>\n";
    echo "</head>\n";
    echo "<body>\n";
    echo "  <h1>Album Theme</h1>\n";
    echo "  <p>This page is themed with colors from the album art.</p>\n";
    
    // Show palette
    echo "  <h2>Color Palette:</h2>\n";
    echo "  <div style='display: flex; gap: 10px;'>\n";
    foreach ($response->getPalette() as $color) {
        $hex = $color['hex'] ?? '#000';
        echo "    <div style='width: 50px; height: 50px; background-color: $hex;'></div>\n";
    }
    echo "  </div>\n";
    echo "</body>\n";
    echo "</html>\n";
}
```

### Example 4: Combined Workflow

```php
<?php

use RadioAPI\RadioAPI;
use RadioAPI\Exceptions\RadioAPIException;

// Initialize client
$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key')
    ->language('en')
    ->service(RadioAPI::SPOTIFY)
    ->timeout(60);

try {
    // 1. Get current track from stream
    $streamUrl = 'https://stream.example.com/radio';
    $stream = $api->getStreamTitle($streamUrl);
    
    if ($stream->isSuccess()) {
        $currentTrack = $stream->getCurrentTrack();
        $artist = $currentTrack['artist'];
        $song = $currentTrack['song'];
        
        echo "Now Playing: $artist - $song\n\n";
        
        // 2. Search for the track to get more details
        $search = $api->searchMusic("$artist - $song");
        
        if ($search->hasResults()) {
            $trackDetails = $search->getFirstTrack();
            
            // 3. Extract colors from album artwork
            if (isset($trackDetails['artwork'])) {
                $colors = $api->getImageColors($trackDetails['artwork']);
                
                if ($colors->isSuccess()) {
                    echo "Album Colors:\n";
                    echo "- Dominant: " . $colors->getDominantColorHex() . "\n";
                    echo "- Text: " . $colors->getTextColorHex() . "\n";
                }
            }
            
            // Display full track info
            echo "\nTrack Details:\n";
            echo "Artist: " . ($trackDetails['artist'] ?? 'N/A') . "\n";
            echo "Title: " . ($trackDetails['title'] ?? 'N/A') . "\n";
            echo "Album: " . ($trackDetails['album'] ?? 'N/A') . "\n";
            echo "Year: " . ($trackDetails['year'] ?? 'N/A') . "\n";
            
            if (isset($trackDetails['url'])) {
                echo "Listen: {$trackDetails['url']}\n";
            }
        }
    }
} catch (RadioAPIException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Handle specific errors
    if ($e->isUnauthorized()) {
        echo "Please check your API key.\n";
    } elseif ($e->isRateLimited()) {
        echo "Rate limit exceeded. Please wait before retrying.\n";
    } elseif ($e->isServerError()) {
        echo "API server error. Please try again later.\n";
    }
}
```

## Best Practices

### 1. Reuse Client Instances

Create one client instance and reuse it for multiple requests:

```php
// Good ✓
$api = RadioAPI::make('https://api.radioapi.io', 'key');
$stream1 = $api->getStreamTitle($url1);
$stream2 = $api->getStreamTitle($url2);

// Avoid ✗
$stream1 = RadioAPI::make('https://api.radioapi.io', 'key')->getStreamTitle($url1);
$stream2 = RadioAPI::make('https://api.radioapi.io', 'key')->getStreamTitle($url2);
```

### 2. Always Handle Errors

Wrap API calls in try-catch blocks:

```php
try {
    $response = $api->getStreamTitle($url);
} catch (RadioAPIException $e) {
    // Handle error appropriately
    error_log($e->getDetailedMessage());
}
```

### 3. Check Response Success

Always verify responses before accessing data:

```php
$response = $api->getStreamTitle($url);

if ($response->isSuccess()) {
    $track = $response->getCurrentTrack();
    // Use track data
} else {
    echo "Error: " . $response->getError();
}
```

### 4. Use Configuration Methods

Take advantage of the fluent configuration:

```php
$api = RadioAPI::make('https://api.radioapi.io', 'key')
    ->language('fr')
    ->service(RadioAPI::DEEZER)
    ->withHistory()
    ->timeout(45);
```

### 5. Leverage Response Helpers

Use built-in helper methods instead of accessing raw data:

```php
// Good ✓
$artist = $response->getArtist();
$tracks = $response->getTracks();

// Avoid ✗
$artist = $response->getRawData()['artist'] ?? null;
$tracks = $response->getRawData()['tracks'] ?? [];
```

## Testing

Run the test suite:

```bash
composer test
```

Run static analysis:

```bash
composer phpstan
```

Check code style:

```bash
composer cs-check
```

Fix code style:

```bash
composer cs-fix
```

## License

This library is open-sourced software licensed under the [MIT license](LICENSE).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Credits

Built with:
- [ElliePHP HttpClient](https://github.com/elliephp/httpclient) - Modern HTTP client for PHP

## Changelog

### 2.0.0 (2024-02-01)

- Complete rewrite using ElliePHP HttpClient
- Added fluent configuration API
- Improved type safety with PHP 8.1+
- Enhanced error handling
- Added comprehensive documentation
- New helper methods in response objects