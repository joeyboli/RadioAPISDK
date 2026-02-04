<?php
require_once __DIR__ . '/vendor/autoload.php';

use RadioAPI\RadioAPI;
use RadioAPI\Exceptions\RadioAPIException;

$streamUrl = $_POST['stream_url'] ?? null;
$error = null;
$response = null;
$accentColor = '#f97316'; // Default solid orange

if ($streamUrl) {
    try {
        // We set the service manually here to track what we used
        $requestedService = RadioAPI::SPOTIFY;

        $api = RadioAPI::make('https://coreapi.streamafrica.cloud/v2')
                ->language('en')
                ->service($requestedService);

        $response = $api->getStreamTitle($streamUrl);

        if ($response->isSuccess()) {
            $track = $response->getCurrentTrack();
            // Dynamic Color extraction (Flat design, no glow)
            if (!empty($track['artwork'])) {
                try {
                    $colorData = $api->getImageColors($track['artwork']);
                    $accentColor = $colorData->getDominantColorHex();
                } catch (\Exception $e) {}
            }
        }
    } catch (RadioAPIException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stream Studio | Metadata Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        :root { --accent: <?php echo $accentColor; ?>; }
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .accent-text { color: var(--accent); }
        .accent-bg { background-color: var(--accent); }
        .accent-border { border-color: var(--accent); }

        /* Remove all glows/shadows for a flat hardware look */
        .card-flat { background: #0a0a0a; border: 1px solid #1a1a1a; }
        input:focus { border-color: var(--accent) !important; outline: none; }
    </style>
</head>
<body class="min-h-screen p-4 md:p-12">

<div class="max-w-6xl mx-auto">
    <!-- Input Section -->
    <header class="mb-16">
        <div class="flex flex-col md:flex-row items-end gap-6">
            <div class="flex-1 w-full">
                <label class="mono text-[10px] uppercase tracking-[0.3em] text-zinc-500 mb-3 block">Network Stream Source</label>
                <form method="POST" class="flex flex-col md:flex-row gap-2">
                    <input type="url" name="stream_url" required
                           placeholder="https://server.com/live"
                           value="<?php echo htmlspecialchars($streamUrl ?? ''); ?>"
                           class="flex-1 bg-zinc-900/50 border border-zinc-800 px-6 py-4 text-sm mono focus:bg-zinc-900 transition-all">
                    <button type="submit" class="accent-bg text-black font-black uppercase text-[10px] tracking-widest px-10 py-4 hover:brightness-110 active:scale-95 transition-all">
                        Analyze
                    </button>
                </form>
            </div>
            <?php if ($streamUrl): ?>
                <div class="w-full md:w-auto">
                    <audio controls class="h-10 w-full md:w-64 opacity-60 hover:opacity-100 transition-opacity">
                        <source src="<?php echo htmlspecialchars($streamUrl); ?>" type="audio/mpeg">
                    </audio>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($error): ?>
        <div class="mb-12 p-6 border-l-2 border-red-500 bg-red-500/5 mono text-xs text-red-400">
            <span class="font-bold mr-2">[FAULT]</span> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($response && $response->isSuccess()):
        $track = $response->getCurrentTrack();
        $artwork = $track['artwork'] ?? '';
        ?>
        <main class="grid grid-cols-1 lg:grid-cols-12 gap-12">

            <!-- Left: Sharp Cover -->
            <div class="lg:col-span-5">
                <?php if ($artwork): ?>
                    <img src="<?php echo $artwork; ?>" class="w-full aspect-square object-cover border border-zinc-800 grayscale hover:grayscale-0 transition-all duration-700">
                <?php else: ?>
                    <div class="w-full aspect-square bg-zinc-900 flex items-center justify-center border border-zinc-800">
                        <span class="mono text-zinc-700 uppercase tracking-widest">No Artwork Data</span>
                    </div>
                <?php endif; ?>

                <div class="mt-6 flex justify-between items-center border-t border-zinc-800 pt-6">
                    <div class="mono text-[10px] text-zinc-500 uppercase">Signal Strength</div>
                    <div class="flex gap-1">
                        <div class="w-1 h-3 accent-bg"></div>
                        <div class="w-1 h-3 accent-bg"></div>
                        <div class="w-1 h-3 accent-bg"></div>
                        <div class="w-1 h-3 bg-zinc-800"></div>
                    </div>
                </div>
            </div>

            <!-- Right: Metadata -->
            <div class="lg:col-span-7">
                <div class="mb-12">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="accent-text mono text-xs font-bold uppercase tracking-widest">Live Metadata</span>
                        <div class="h-[1px] flex-1 bg-zinc-800"></div>
                    </div>

                    <h1 class="text-6xl md:text-8xl font-black tracking-tighter uppercase leading-[0.85] mb-6">
                        <?php echo htmlspecialchars($track['song']); ?>
                    </h1>
                    <p class="text-2xl md:text-3xl font-bold text-zinc-400 mb-10">
                        <?php echo htmlspecialchars($track['artist']); ?>
                    </p>

                    <!-- Social/Search Tools -->
                    <div class="flex flex-wrap gap-2">
                        <a href="https://open.spotify.com/search/<?php echo urlencode($track['artist'] . ' ' . $track['song']); ?>" target="_blank"
                           class="border border-zinc-800 hover:border-zinc-600 px-5 py-2 mono text-[10px] uppercase transition-all">
                            Spotify
                        </a>
                        <a href="https://music.apple.com/search?term=<?php echo urlencode($track['artist'] . ' ' . $track['song']); ?>" target="_blank"
                           class="border border-zinc-800 hover:border-zinc-600 px-5 py-2 mono text-[10px] uppercase transition-all">
                            Apple Music
                        </a>
                        <button onclick="navigator.clipboard.writeText('<?php echo addslashes($track['artist'] . ' - ' . $track['song']); ?>')"
                                class="bg-zinc-100 text-black px-5 py-2 mono text-[10px] font-bold uppercase hover:bg-white transition-all">
                            Copy Text
                        </button>
                    </div>
                </div>

                <!-- History (Horizontal Flat List) -->
                <div class="mt-20">
                    <h3 class="mono text-[10px] text-zinc-600 uppercase tracking-[0.4em] mb-8">Previous Broadcasts</h3>
                    <div class="space-y-1">
                        <?php foreach (array_slice($response->getHistory(), 0, 4) as $index => $prev): ?>
                            <div class="flex items-center justify-between py-3 border-b border-zinc-900 group">
                                <div class="flex items-center gap-6">
                                    <span class="mono text-zinc-800 text-xs">0<?php echo $index + 1; ?></span>
                                    <div class="min-w-0">
                                            <span class="block text-sm font-bold text-zinc-300 group-hover:text-white transition-colors truncate">
                                                <?php echo htmlspecialchars($prev['song']); ?>
                                            </span>
                                        <span class="block text-[10px] mono uppercase text-zinc-600">
                                                <?php echo htmlspecialchars($prev['artist']); ?>
                                            </span>
                                    </div>
                                </div>
                                <div class="hidden md:block">
                                    <span class="mono text-[9px] text-zinc-700 italic uppercase">Log Verified</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    <?php endif; ?>
</div>

<footer class="max-w-6xl mx-auto mt-24 pt-12 border-t border-zinc-900 flex flex-col md:flex-row justify-between gap-6">
    <div class="mono text-[9px] text-zinc-600 uppercase tracking-widest">
        Broadcast Metadata Processor // <?php echo date('Y'); ?>
    </div>
    <div class="flex gap-8">
        <div class="mono text-[9px] text-zinc-600 uppercase tracking-widest">
            <span class="text-zinc-400">Service:</span> Cloud V2
        </div>
        <div class="mono text-[9px] text-zinc-600 uppercase tracking-widest">
            <span class="text-zinc-400">Mode:</span> Intelligent Extraction
        </div>
    </div>
</footer>

</body>
</html>