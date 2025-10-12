# RadioAPI PHP SDK

A simple PHP client for fetching radio stream metadata and searching music tracks across streaming platforms.

## Installation

```bash
composer require joeyboli/radioapi-php
```

## Quick Start

```php
use RadioAPI\RadioAPI;

$api = new RadioAPI();
$api->setBaseUrl('https://api.example.com')
    ->setApiKey('your-api-key');

$metadata = $api->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio')
    ->fetchArray();

echo "{$metadata['artist']} - {$metadata['song']}";
```

## Response Examples

### StreamTitle (without mount)

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

### StreamTitle (with mount/service)

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
        ],
        // ... more history items
    ]
]
```

### MusicSearch

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

## Basic Usage

### Getting Stream Metadata

The most common use case is grabbing what's currently playing on a radio stream:

```php
$stream = $api->streamTitle();
$stream->setStreamUrl('https://stream.example.com/radio');
$data = $stream->fetchArray();

if ($stream->hasMetadata()) {
    echo $data['song'];
    echo $data['artist'];
    echo $data['album'];
}
```

### Searching for Music

Need to search for a track? Here's how:

```php
$result = $api->musicSearch()
    ->search('Radiohead Creep')
    ->fetchArray();

echo "{$result['artist']} - {$result['title']}\n";
echo "Album: {$result['album']}\n";
echo "Stream: {$result['stream']}\n";
```

## Music Service Integration

Want metadata from Spotify, Deezer, or other services? Just set a mount point:

```php
// Spotify
$api->withService(RadioAPI::SPOTIFY);

// Deezer
$api->withService(RadioAPI::DEEZER);

// Apple Music
$api->withService(RadioAPI::APPLE_MUSIC);

// YouTube Music
$api->withService(RadioAPI::YOUTUBE_MUSIC);
```

Full example:

```php
$spotify = $api->withService(RadioAPI::SPOTIFY)
    ->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio')
    ->fetchArray();

// Now you have Spotify-specific data
$trackId = $spotify['spotify']['id'];
$spotifyUrl = $spotify['spotify']['external_urls']['spotify'];
```

### Radio Platform Integration (StreamTitle only)

For radio platforms like AzuraCast and Live365, use their specific mount points with the correct URL format:

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

### Available Services

**For StreamTitle and MusicSearch:**
- `RadioAPI::SPOTIFY` - Spotify
- `RadioAPI::DEEZER` - Deezer
- `RadioAPI::APPLE_MUSIC` - Apple Music (iTunes)
- `RadioAPI::YOUTUBE_MUSIC` - YouTube Music
- `RadioAPI::FLO_MUSIC` - FLO Music
- `RadioAPI::LINE_MUSIC` - LINE Music
- `RadioAPI::AUTO` - Auto-detect

**For StreamTitle only:**
- `RadioAPI::AZURACAST` - AzuraCast (use with `https://azuracast.example.com/listen/stationid/mountpoint`)
- `RadioAPI::LIVE365` - Live365 (use with `https://streaming.live365.com/mountid`)

## Configuration Options

### Language

Set the language for metadata responses:

```php
$api->setLanguage('fr'); // French
$api->setLanguage('de'); // German
$api->setLanguage('es'); // Spanish
```

### History

By default, the API includes track history. Turn it off if you don't need it:

```php
$api->withHistory(false);
```

### Error Handling

The SDK throws exceptions by default when something goes wrong. If you prefer to handle errors manually:

```php
$api->setThrowOnApiErrors(false);

$data = $api->streamTitle()
    ->setStreamUrl('https://stream.example.com/radio')
    ->fetchArray();

if (isset($data['error'])) {
    echo "Something went wrong: {$data['error']}";
}
```

Or catch exceptions:

```php
use RadioAPI\Exceptions\ApiErrorException;
use RadioAPI\Exceptions\ClientErrorException;
use RadioAPI\Exceptions\ServerErrorException;

try {
    $data = $api->streamTitle()
        ->setStreamUrl('https://stream.example.com/radio')
        ->fetchArray();
} catch (ClientErrorException $e) {
    // 4xx errors
    echo "Client error: {$e->getMessage()}";
} catch (ServerErrorException $e) {
    // 5xx errors
    echo "Server error: {$e->getMessage()}";
} catch (ApiErrorException $e) {
    // Other API errors
    echo "API error: {$e->getMessage()}";
}
```

## Working with Response Data

Check if metadata was found:

```php
$stream = $api->streamTitle();
$stream->setStreamUrl('https://stream.example.com/radio');
$stream->fetchArray();

if ($stream->hasMetadata()) {
    // Do something with the data
}
```

Get the raw response:

```php
$rawData = $stream->getData();
```

## Common Patterns

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
    
    if (!empty($data['results'])) {
        // Found it!
        break;
    }
}
```

## Tips

- Always set your base URL and API key before making requests
- Use mount points when you need service-specific metadata
- The `withHistory(false)` option can speed up responses if you don't need historical data
- Check `hasMetadata()` before accessing metadata fields to avoid errors
- Use static helpers for quick one-off requests

## Requirements

- PHP 8.1 or higher

## License

MIT