# RadioAPI PHP Client

A clean, modern PHP client for RadioAPI that makes working with radio streams and music data feel effortless. Get real-time stream metadata, search across all the major streaming platforms, and even pull color palettes from album artwork—all with an API that just works.

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## What You Can Do

- **Stream Metadata** - Pull current track info from any radio stream
- **Music Search** - Find tracks across Spotify, Deezer, Apple Music, and more
- **Color Extraction** - Grab dominant colors from album art for theming
- **Fluent API** - Chain methods together naturally
- **Type Safe** - Full PHP 8.1+ type declarations throughout
- **Zero Config** - Just install and go
- **Built on ElliePHP** - Powered by the rock-solid ElliePHP HttpClient

## What You'll Need

- PHP 8.1 or newer
- ElliePHP HttpClient

## Getting Started

Pop this into your terminal:

```bash
composer require joeyboli/radioapisdk
```

## Quick Example

Here's the basic idea:

```php
<?php

use RadioAPI\RadioAPI;

// Set up your client
$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key')
    ->language('en')
    ->service(RadioAPI::SPOTIFY)
    ->withHistory();

// Find out what's playing on a stream
$stream = $api->getStreamTitle('https://stream.example.com/radio');

if ($stream->isSuccess()) {
    $track = $stream->getCurrentTrack();
    echo "{$track['artist']} - {$track['song']}\n";
    
    // Check what played before
    foreach ($stream->getHistory() as $historical) {
        echo "Previously: {$historical['artist']} - {$historical['song']}\n";
    }
}

// Search for a specific track
$search = $api->searchMusic('The Beatles - Hey Jude');

if ($search->hasResults()) {
    $firstTrack = $search->getFirstTrack();
    echo "Found: {$firstTrack['artist']} - {$firstTrack['title']}\n";
}

// Pull colors from album artwork
$colors = $api->getImageColors('https://example.com/album-art.jpg');

if ($colors->isSuccess()) {
    echo "Dominant color: " . $colors->getDominantColorHex() . "\n";
    echo "Text color: " . $colors->getTextColorHex() . "\n";
}
```

## Setting Things Up

### The Basics

You've got a couple ways to create your client:

```php
use RadioAPI\RadioAPI;

// Simple version
$api = new RadioAPI('https://api.radioapi.io', 'your-api-key');

// Or chain some config options
$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key')
    ->language('en')           // Response language
    ->service(RadioAPI::SPOTIFY)  // Default service
    ->withHistory()            // Include track history
    ->timeout(60);             // Request timeout in seconds
```

### Configuration Options

Here's what you can tweak:

| Method | What It Does | Default |
|--------|-------------|---------|
| `language(string $code)` | Set language for responses (ISO 639-1) | `'en'` |
| `service(string $service)` | Choose your default platform | `null` |
| `withHistory(bool $enabled)` | Turn track history on or off | `true` |
| `withoutHistory()` | Quick way to disable history | - |
| `timeout(int $seconds)` | How long to wait for responses | `30` |

### Available Services

Music streaming platforms you can use:

```php
RadioAPI::SPOTIFY         // Spotify
RadioAPI::DEEZER          // Deezer
RadioAPI::APPLE_MUSIC     // Apple Music / iTunes
RadioAPI::YOUTUBE_MUSIC   // YouTube Music
RadioAPI::FLO_MUSIC       // FLO Music
RadioAPI::LINE_MUSIC      // LINE Music
RadioAPI::KKBOX_MUSIC     // KKBOX
RadioAPI::AUTO            // Let the API figure it out
```

Radio platforms:

```php
RadioAPI::AZURACAST       // AzuraCast
RadioAPI::LIVE365         // Live365
RadioAPI::RADIOKING       // RadioKing
```

## What You Can Do With It

### Get Stream Info

Pull the current track from any radio stream:

```php
public function getStreamTitle(
    string $streamUrl,
    ?string $service = null,
    ?bool $withHistory = null
): StreamTitleResponse
```

**What you pass in:**
- `$streamUrl` - The radio stream URL
- `$service` - Optional: override the default service
- `$withHistory` - Optional: override history setting

**Examples:**

```php
// Basic usage
$response = $api->getStreamTitle('https://stream.example.com/radio');

// Force a specific service
$response = $api->getStreamTitle(
    'https://stream.example.com/radio',
    RadioAPI::SPOTIFY
);

// Skip history for this one request
$response = $api->getStreamTitle(
    'https://stream.example.com/radio',
    null,
    false
);
```

### Search for Music

Find tracks across streaming services:

```php
public function searchMusic(
    string $query,
    ?string $service = null,
    ?string $language = null
): MusicSearchResponse
```

**What you pass in:**
- `$query` - What you're looking for
- `$service` - Optional: which platform to search
- `$language` - Optional: override the language

**Examples:**

```php
// Just search
$response = $api->searchMusic('The Beatles - Hey Jude');

// Search on Spotify specifically
$response = $api->searchMusic(
    'The Beatles - Hey Jude',
    RadioAPI::SPOTIFY
);

// Search in French
$response = $api->searchMusic(
    'The Beatles - Hey Jude',
    RadioAPI::SPOTIFY,
    'fr'
);
```

### Extract Colors from Images

Pull color palettes from any image URL:

```php
public function getImageColors(string $imageUrl): ColorResponse
```

**What you pass in:**
- `$imageUrl` - The image URL to analyze

**Example:**

```php
$response = $api->getImageColors('https://example.com/album-art.jpg');

if ($response->isSuccess()) {
    $hex = $response->getDominantColorHex();
    $rgb = $response->getDominantColorRgb();
    $css = $response->getDominantColorCss();
}
```

## Working with Responses

### StreamTitleResponse

Everything you need from stream metadata:

```php
// Check if it worked
$response->isSuccess(): bool
$response->getError(): ?string

// Current track info
$response->getCurrentTrack(): ?array
$response->getArtist(): ?string
$response->getTitle(): ?string
$response->getAlbum(): ?string

// History stuff
$response->getHistory(): array
$response->hasHistory(): bool
$response->getHistoryCount(): int
$response->getLastTrack(): ?array

// Stream details
$response->getStreamInfo(): array

// Raw data if you need it
$response->getRawData(): array
```

**Real example:**

```php
$response = $api->getStreamTitle('https://stream.example.com/radio');

if ($response->isSuccess()) {
    // Get the details
    $artist = $response->getArtist();
    $title = $response->getTitle();
    $album = $response->getAlbum();
    
    echo "$artist - $title";
    if ($album) {
        echo " (from $album)";
    }
    
    // Check what played before
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

All your search result tools:

```php
// Status checks
$response->isSuccess(): bool
$response->getError(): ?string
$response->hasResults(): bool

// Get tracks
$response->getTracks(): array
$response->getFirstTrack(): ?array
$response->getResultCount(): int

// Filter and transform results
$response->getTracksByService(string $service): array
$response->filter(callable $callback): array
$response->map(callable $callback): array

// Info about the search
$response->getQuery(): ?string
$response->getService(): ?string

// Raw data
$response->getRawData(): array
```

**Real example:**

```php
$response = $api->searchMusic('The Beatles - Hey Jude');

if ($response->hasResults()) {
    // Loop through everything
    foreach ($response->getTracks() as $track) {
        echo "{$track['artist']} - {$track['title']}\n";
    }
    
    // Just grab the first one
    $first = $response->getFirstTrack();
    
    // Filter for explicit tracks
    $explicit = $response->filter(fn($t) => $t['explicit'] ?? false);
    
    // Simplify the data
    $simplified = $response->map(fn($t) => [
        'artist' => $t['artist'],
        'title' => $t['title']
    ]);
    
    // Get results from just Spotify
    $spotifyTracks = $response->getTracksByService('spotify');
}
```

### ColorResponse

Color extraction made easy:

```php
// Success check
$response->isSuccess(): bool
$response->getError(): ?string

// Hex format
$response->getDominantColorHex(): ?string
$response->getTextColorHex(): ?string

// RGB format
$response->getDominantColorRgb(): ?array
$response->getTextColorRgb(): ?array

// CSS format
$response->getDominantColorCss(): ?string
$response->getTextColorCss(): ?string

// Flutter format (if you need it)
$response->getDominantColorFlutterHex(): ?string
$response->getTextColorFlutterHex(): ?string

// Full color palette
$response->getPalette(): array
$response->getPaletteColor(int $index): ?array
$response->getPaletteCount(): int

// Raw data
$response->getRawData(): array
```

**Real example:**

```php
$response = $api->getImageColors('https://example.com/album-art.jpg');

if ($response->isSuccess()) {
    // Get colors in whatever format you need
    $hex = $response->getDominantColorHex();        // "#FF5733"
    $rgb = $response->getDominantColorRgb();        // ['r' => 255, 'g' => 87, 'b' => 51]
    $css = $response->getDominantColorCss();        // "rgb(255, 87, 51)"
    $flutter = $response->getDominantColorFlutterHex(); // "0xFFFF5733"
    
    // Use it in your HTML
    echo "<div style='background-color: $css;'>";
    echo "  <span style='color: " . $response->getTextColorCss() . ";'>";
    echo "    Content with good contrast";
    echo "  </span>";
    echo "</div>";
    
    // Or grab the whole palette
    $palette = $response->getPalette();
    foreach ($palette as $color) {
        echo "Color: {$color['hex']}\n";
    }
}
```

## When Things Go Wrong

### RadioAPIException

All errors come through as a `RadioAPIException` with helpful info:

```php
use RadioAPI\Exceptions\RadioAPIException;

try {
    $response = $api->getStreamTitle('https://invalid-url.example.com');
} catch (RadioAPIException $e) {
    // Get the details
    echo $e->getMessage();              // Simple error message
    echo $e->getStatusCode();           // HTTP status code
    echo $e->getDetailedMessage();      // Full context
    
    // Figure out what went wrong
    if ($e->isClientError()) {
        // 4xx - something wrong with your request
    }
    if ($e->isServerError()) {
        // 5xx - API server issue
    }
    if ($e->isNetworkError()) {
        // Connection problems
    }
    
    // Check specific issues
    if ($e->isUnauthorized()) {
        // 401 - bad API key
    }
    if ($e->isRateLimited()) {
        // 429 - slow down
    }
    if ($e->isNotFound()) {
        // 404 - doesn't exist
    }
    
    // Dig deeper if needed
    $errorData = $e->getErrorData();    // API response
    $context = $e->getContext();        // Request context
    
    // Check specific error fields
    if ($e->hasErrorField('detail')) {
        $detail = $e->getErrorField('detail');
    }
}
```

### Exception Methods

| Method | What It Does | Returns |
|--------|-------------|---------|
| `getMessage()` | Basic error message | `string` |
| `getStatusCode()` | HTTP status code | `int` |
| `getDetailedMessage()` | Full details with context | `string` |
| `getErrorData()` | API error response | `array` |
| `getContext()` | Request context | `array` |
| `isClientError()` | Check for 4xx errors | `bool` |
| `isServerError()` | Check for 5xx errors | `bool` |
| `isNetworkError()` | Check for connection issues | `bool` |
| `isUnauthorized()` | Check for 401 | `bool` |
| `isForbidden()` | Check for 403 | `bool` |
| `isNotFound()` | Check for 404 | `bool` |
| `isRateLimited()` | Check for 429 | `bool` |
| `hasErrorField(string)` | Check if error field exists | `bool` |
| `getErrorField(string, mixed)` | Get error field value | `mixed` |

## Real-World Examples

### Radio Station Monitor

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

### Search Across Multiple Platforms

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

### Dynamic Album Art Theming

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
    
    // Show the whole palette
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

### Complete Workflow

```php
<?php

use RadioAPI\RadioAPI;
use RadioAPI\Exceptions\RadioAPIException;

// Set up your client
$api = RadioAPI::make('https://api.radioapi.io', 'your-api-key')
    ->language('en')
    ->service(RadioAPI::SPOTIFY)
    ->timeout(60);

try {
    // Step 1: Get what's currently playing
    $streamUrl = 'https://stream.example.com/radio';
    $stream = $api->getStreamTitle($streamUrl);
    
    if ($stream->isSuccess()) {
        $currentTrack = $stream->getCurrentTrack();
        $artist = $currentTrack['artist'];
        $song = $currentTrack['song'];
        
        echo "Now Playing: $artist - $song\n\n";
        
        // Step 2: Search for more details about the track
        $search = $api->searchMusic("$artist - $song");
        
        if ($search->hasResults()) {
            $trackDetails = $search->getFirstTrack();
            
            // Step 3: Pull colors from the album art
            if (isset($trackDetails['artwork'])) {
                $colors = $api->getImageColors($trackDetails['artwork']);
                
                if ($colors->isSuccess()) {
                    echo "Album Colors:\n";
                    echo "- Dominant: " . $colors->getDominantColorHex() . "\n";
                    echo "- Text: " . $colors->getTextColorHex() . "\n";
                }
            }
            
            // Show everything
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
    
    // Handle specific problems
    if ($e->isUnauthorized()) {
        echo "Please check your API key.\n";
    } elseif ($e->isRateLimited()) {
        echo "Rate limit exceeded. Please wait before retrying.\n";
    } elseif ($e->isServerError()) {
        echo "API server error. Please try again later.\n";
    }
}
```

## Tips and Best Practices

### Reuse Your Client

Don't create a new client for every request—that's wasteful:

```php
// Do this ✓
$api = RadioAPI::make('https://api.radioapi.io', 'key');
$stream1 = $api->getStreamTitle($url1);
$stream2 = $api->getStreamTitle($url2);

// Not this ✗
$stream1 = RadioAPI::make('https://api.radioapi.io', 'key')->getStreamTitle($url1);
$stream2 = RadioAPI::make('https://api.radioapi.io', 'key')->getStreamTitle($url2);
```

### Always Handle Errors

Things can go wrong, so be ready:

```php
try {
    $response = $api->getStreamTitle($url);
} catch (RadioAPIException $e) {
    // Handle it gracefully
    error_log($e->getDetailedMessage());
}
```

### Check Success Before Using Data

Don't assume everything worked:

```php
$response = $api->getStreamTitle($url);

if ($response->isSuccess()) {
    $track = $response->getCurrentTrack();
    // Now you can safely use the data
} else {
    echo "Error: " . $response->getError();
}
```

### Use the Configuration Chain

It's cleaner and reads better:

```php
$api = RadioAPI::make('https://api.radioapi.io', 'key')
    ->language('fr')
    ->service(RadioAPI::DEEZER)
    ->withHistory()
    ->timeout(45);
```

### Use Helper Methods

They're there for a reason:

```php
// Do this ✓
$artist = $response->getArtist();
$tracks = $response->getTracks();

// Not this ✗
$artist = $response->getRawData()['artist'] ?? null;
$tracks = $response->getRawData()['tracks'] ?? [];
```

## License

MIT licensed

## Contributing

Found a bug? Want to add a feature? Pull requests are welcome!

## Built With

- [ElliePHP HttpClient](https://github.com/elliephp/httpclient) - The HTTP client powering everything under the hood

## Changelog

### 1.0.9 (2026-02-04)

- Complete rewrite using ElliePHP HttpClient
- Fluent configuration API
- Better type safety with PHP 8.1+
- Improved error handling
- Comprehensive docs
- More helpful response methods
