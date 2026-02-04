<?php
// 1. Group List Endpoint
$listEndpoint = "https://prod-api.radioapi.me/web/radios/1ceb9727-3e36-4e64-99e7-f776b50c7f4f";
$json = file_get_contents($listEndpoint);
$data = json_decode($json, true);
$radios = $data['data'] ?? [];

// 2. Handle Routing
$requestedId = $_GET['station'] ?? null;
$initialStation = null;

if ($requestedId) {
    foreach ($radios as $radio) {
        if ($radio['id'] === $requestedId) {
            $initialStation = $radio;
            break;
        }
    }
}
if (!$initialStation) { $initialStation = $radios[0] ?? null; }

function optimizeImg($url, $w = 400, $h = 400): string
{
    if (!$url) {
        return "";
    }
    return "https://wsrv.nl/?url=" . urlencode($url) . "&w=$w&h=$h&fit=cover&quality=webp";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>STUDIO-X | <?php echo $initialStation ? htmlspecialchars($initialStation['title']) : 'Broadcast'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/color-thief/2.3.0/color-thief.umd.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; overflow-x: hidden; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        @media (max-width: 1024px) {
            #station-sidebar { position: fixed; top: 64px; left: -100%; width: 100%; height: calc(100vh - 64px - 80px); z-index: 40; transition: 0.3s; }
            #station-sidebar.open { left: 0; }
        }
        #history-panel, #lyrics-panel { position: fixed; top: 0; right: -100%; width: 100%; max-width: 500px; height: 100vh; background: #080808; z-index: 100; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-left: 1px solid #1a1a1a; }
        #history-panel.open, #lyrics-panel.open { right: 0; }
        .station-card.active { border-right: 4px solid #f97316; background: #0f0f0f; }
        .overlay-blur { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); z-index: 90; display: none; }
        .overlay-blur.open { display: block; }
        .v-bar { width: 4px; background: #333; transition: height 0.2s; }
        .playing .v-bar { background: currentColor; animation: v-bounce 0.8s infinite ease-in-out; }
        @keyframes v-bounce { 0%, 100% { height: 6px; } 50% { height: 24px; } }

        #progress-shadow { transition: width 1s linear; background: rgba(0,0,0,0.1); }
        .lyrics-content { white-space: pre-line; scrollbar-width: thin; }

        /* Signal Lock UI Helper */
        .signal-lock { pointer-events: none !important; opacity: 0.5; }
    </style>
</head>
<body class="flex flex-col h-screen max-h-screen">

<div id="blur-bg" class="overlay-blur" onclick="closeAllPanels()"></div>

<nav class="h-16 border-b border-zinc-900 px-4 md:px-8 flex justify-between items-center bg-black z-50">
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 text-zinc-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16m-7 6h7"/></svg>
        </button>
        <h1 class="font-black text-lg tracking-tighter uppercase italic text-zinc-200">Unlimited<span class="text-orange-500">Radio</span></h1>
    </div>
    <div class="hidden sm:block mono text-[10px] text-orange-500 font-bold uppercase tracking-widest">
        <span id="clock">00:00:00</span> // SYSTEM_LIVE
    </div>
</nav>

<div class="flex flex-1 overflow-hidden relative">
    <aside id="station-sidebar" class="lg:w-80 bg-[#050505] border-r border-zinc-900 flex flex-col shrink-0 transition-opacity">
        <div class="p-4 bg-zinc-900/20 border-b border-zinc-900 lg:block hidden text-center">
            <p class="mono text-[10px] uppercase tracking-[0.3em] text-zinc-500">Source Select</p>
        </div>
        <div id="station-list-container" class="overflow-y-auto flex-1 pb-20 lg:pb-0">
            <?php foreach ($radios as $radio): ?>
                <button onclick="switchStation(<?php echo htmlspecialchars(json_encode($radio)); ?>, true)" id="btn-<?php echo $radio['id']; ?>" class="station-card w-full text-left p-4 md:p-5 border-b border-zinc-900/50 flex items-center gap-4 transition-all hover:bg-zinc-900/50">
                    <img src="<?php echo optimizeImg($radio['posterUrl'], 80, 80); ?>" class="w-10 h-10 object-cover bg-zinc-900">
                    <div class="min-w-0">
                        <h4 class="font-bold text-xs md:text-sm truncate uppercase tracking-tighter"><?php echo htmlspecialchars($radio['title']); ?></h4>
                        <p class="mono text-[9px] text-zinc-600 truncate uppercase tracking-tighter"><?php echo htmlspecialchars($radio['genre']); ?></p>
                    </div>
                </button>
            <?php endforeach; ?>
        </div>
    </aside>

    <aside id="lyrics-panel" class="flex flex-col">
        <div class="p-6 border-b border-zinc-900 flex justify-between items-center">
            <div>
                <h3 class="font-black text-xl uppercase italic">Signal<span class="text-orange-500">Lyrics</span></h3>
                <p id="lyrics-title" class="mono text-[9px] text-zinc-500 uppercase tracking-widest truncate">---</p>
            </div>
            <button onclick="closeAllPanels()" class="mono text-xs text-zinc-500 border border-zinc-800 px-4 py-2">CLOSE</button>
        </div>
        <div id="lyrics-body" class="flex-1 overflow-y-auto p-8 mono text-sm leading-relaxed lyrics-content text-zinc-400"></div>
    </aside>

    <aside id="history-panel" class="flex flex-col">
        <div class="p-6 border-b border-zinc-900 flex justify-between items-center">
            <h3 class="font-black text-xl uppercase italic text-orange-500 uppercase tracking-tight">LogBook</h3>
            <button onclick="closeAllPanels()" class="mono text-xs text-zinc-500 border border-zinc-800 px-4 py-2">CLOSE</button>
        </div>
        <div id="full-history-list" class="flex-1 overflow-y-auto p-6 space-y-6 bg-black/20"></div>
    </aside>

    <main class="flex-1 overflow-y-auto bg-black p-4 md:p-8 lg:p-12">
        <div class="max-w-5xl mx-auto flex flex-col lg:grid lg:grid-cols-12 gap-8 lg:gap-16">

            <div class="lg:col-span-5 w-full max-w-[440px] lg:max-w-none mx-auto lg:mx-0">
                <div class="aspect-square bg-zinc-900 border border-zinc-800 relative overflow-hidden group">
                    <img id="main-artwork" crossorigin="anonymous" src="<?php echo optimizeImg($initialStation['posterUrl'], 600, 600); ?>" class="w-full h-full object-cover">
                    <div class="absolute bottom-3 left-3 bg-black/90 px-3 py-1.5 mono text-[9px] text-zinc-300 uppercase tracking-widest border border-white/10 flex gap-3 divide-x divide-zinc-700">
                        <div class="flex gap-1.5"><span class="text-zinc-500">BIT:</span><span id="art-bitrate">---</span></div>
                        <div class="pl-3 flex gap-1.5"><span class="text-zinc-500">FMT:</span><span id="art-format">---</span></div>
                        <div class="pl-3 flex gap-1.5"><span class="text-zinc-500">YEAR:</span><span id="art-year">----</span></div>
                    </div>
                </div>

                <div class="mt-4 relative overflow-hidden h-20 bg-white group">
                    <div id="progress-shadow" class="absolute top-0 left-0 bottom-0 w-0 z-0 border-r border-black/5"></div>
                    <button onclick="togglePlayback()" id="master-play-btn" class="relative z-10 w-full h-full px-8 flex items-center justify-between text-black transition-colors duration-500">
                        <div class="flex items-center gap-5 text-left">
                            <div class="w-8 h-8"><svg id="play-icon" class="w-full h-full fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div>
                            <div class="flex flex-col leading-none">
                                <span id="play-text" class="font-black uppercase italic tracking-tighter text-2xl md:text-3xl">START SIGNAL</span>
                                <span id="progress-text" class="mono text-[9px] uppercase tracking-[0.2em] mt-1 opacity-60">MASTER CONTROL</span>
                            </div>
                        </div>
                        <div id="visualizer" class="flex items-end gap-1.5">
                            <div class="v-bar h-2"></div><div class="v-bar h-2"></div><div class="v-bar h-2"></div><div class="v-bar h-2"></div>
                        </div>
                    </button>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div class="mb-12">
                    <div id="metadata-loader" class="mono text-[9px] text-orange-500 mb-2 hidden tracking-widest animate-pulse font-bold uppercase">POLLING...</div>
                    <h2 id="station-name-label" class="mono text-[11px] uppercase tracking-[0.4em] text-zinc-500 mb-6 block border-l-2 border-orange-500 pl-4 uppercase"><?php echo htmlspecialchars($initialStation['title']); ?></h2>
                    <h1 id="track-name" class="text-4xl md:text-6xl lg:text-7xl font-black tracking-tighter uppercase leading-[0.85] mb-4 italic line-clamp-3 uppercase">STANDBY</h1>
                    <p id="artist-name" class="text-xl md:text-2xl font-bold text-zinc-400 line-clamp-2 uppercase">Hardware Interface</p>
                </div>

                <div id="history-container" class="border-t border-zinc-900 pt-10">
                    <div class="flex justify-between items-end mb-6">
                        <h3 class="mono text-[10px] text-zinc-700 uppercase tracking-[0.5em]">Log Sequence</h3>
                        <div class="flex gap-4">
                            <button id="btn-lyrics-toggle" onclick="openLyrics()" class="hidden mono text-[10px] text-zinc-400 uppercase font-bold hover:text-orange-500 transition-colors">[ LYRICS ]</button>
                            <button onclick="openHistory()" class="mono text-[10px] text-orange-500 uppercase font-bold hover:text-white transition-colors">[ EXPAND_LOG ]</button>
                        </div>
                    </div>
                    <div id="history-list" class="space-y-4"></div>
                </div>
            </div>
        </div>
        <div class="h-32 lg:hidden"></div>
    </main>
</div>

<footer class="h-20 border-t border-zinc-900 bg-[#050505] px-4 md:px-8 flex items-center justify-center z-50 shrink-0">
    <div class="flex items-center gap-3">
        <span id="live-dot" class="w-2.5 h-2.5 rounded-full bg-zinc-800"></span>
        <span id="footer-station-name" class="mono text-[11px] text-zinc-400 uppercase tracking-widest font-bold uppercase"><?php echo htmlspecialchars($initialStation['title']); ?></span>
    </div>
</footer>

<script>
    const allRadios = <?php echo json_encode($radios); ?>;
    const colorThief = new ColorThief();

    let trackProgress = { duration: 0, elapsed: 0, lastUpdated: 0, isActive: false };
    let currentStation = null;
    let fullHistoryData = [];
    let hls = null;
    let metaTimer = null;
    let progressInterval = null;
    let isSwitching = false; // --- NEW: Rapid Switch Preventer ---

    const player = new Audio();
    player.preload = "none";
    const playIcon = document.getElementById('play-icon');
    const playText = document.getElementById('play-text');
    const liveDot = document.getElementById('live-dot');
    const visualizer = document.getElementById('visualizer');
    const masterBtnContainer = document.getElementById('master-play-btn').parentElement;
    const mainArtwork = document.getElementById('main-artwork');

    player.addEventListener('play', updatePlayUI);
    player.addEventListener('pause', updatePlayUI);

    mainArtwork.addEventListener('load', function() {
        try {
            const color = colorThief.getColor(mainArtwork);
            masterBtnContainer.style.backgroundColor = `rgb(${color[0]}, ${color[1]}, ${color[2]})`;
            const brightness = Math.round(((parseInt(color[0]) * 299) + (parseInt(color[1]) * 587) + (parseInt(color[2]) * 114)) / 1000);
            document.getElementById('master-play-btn').style.color = (brightness > 125) ? '#000000' : '#ffffff';
        } catch(e) { masterBtnContainer.style.backgroundColor = '#ffffff'; }
    });

    function formatTime(secs) {
        if (secs < 0) secs = 0;
        const m = Math.floor(secs / 60);
        const s = Math.floor(secs % 60);
        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }

    function startProgressLoop() {
        if (progressInterval) clearInterval(progressInterval);
        progressInterval = setInterval(() => {
            if (!trackProgress.isActive || trackProgress.duration <= 0) return;
            const delta = (Date.now() - trackProgress.lastUpdated) / 1000;
            const currentElapsed = trackProgress.elapsed + delta;
            if (currentElapsed <= trackProgress.duration) {
                document.getElementById('progress-shadow').style.width = ((currentElapsed / trackProgress.duration) * 100) + '%';
                document.getElementById('progress-text').innerText = `${formatTime(currentElapsed)} // ${formatTime(trackProgress.duration - currentElapsed)} LEFT`;
            } else {
                document.getElementById('progress-shadow').style.width = '100%';
                document.getElementById('progress-text').innerText = `NEXT TRACK PENDING...`;
            }
        }, 1000);
    }

    function switchStation(station, shouldPushState = true) {
        // --- FIXED: Ignore clicks if currently tuning ---
        if (isSwitching) return;
        isSwitching = true;

        // Show busy state
        const loader = document.getElementById('metadata-loader');
        loader.innerText = "SIGNAL LOCK [BUSY]";
        loader.classList.remove('hidden');
        document.getElementById('station-sidebar').classList.add('signal-lock');

        currentStation = station;
        if (shouldPushState) {
            const url = new URL(window.location);
            url.searchParams.set('station', station.id);
            window.history.pushState({}, '', url);
        }

        // Reset UI immediately
        trackProgress.isActive = false;
        document.getElementById('progress-shadow').style.width = '0%';
        document.getElementById('progress-text').innerText = 'TUNING SIGNAL...';
        document.getElementById('station-name-label').innerText = station.title;
        document.getElementById('footer-station-name').innerText = station.title;
        document.getElementById('track-name').innerText = "PROBING SIGNAL...";
        document.getElementById('artist-name').innerText = station.genre || "HARDWARE INTERFACE";
        document.getElementById('art-bitrate').innerText = '---';
        document.getElementById('art-format').innerText = '---';
        document.getElementById('art-year').innerText = '----';

        document.getElementById('station-sidebar').classList.remove('open');
        document.querySelectorAll('.station-card').forEach(c => c.classList.remove('active'));
        const activeBtn = document.getElementById(`btn-${station.id}`);
        if (activeBtn) activeBtn.classList.add('active');

        mainArtwork.src = optimizeImg(station.posterUrl, 600, 600);

        if (hls) { hls.destroy(); hls = null; }
        player.pause(); player.src = ""; player.load();

        if (station.streamUrl.includes('.m3u8')) {
            if (Hls.isSupported()) {
                hls = new Hls(); hls.loadSource(station.streamUrl); hls.attachMedia(player);
                hls.on(Hls.Events.MANIFEST_PARSED, () => player.play().catch(() => {}));
            } else if (player.canPlayType('application/vnd.apple.mpegurl')) {
                player.src = station.streamUrl; player.play().catch(() => {});
            }
        } else {
            player.src = station.streamUrl; player.play().catch(() => {});
        }

        fetchMetadata();
        if(metaTimer) clearInterval(metaTimer);
        metaTimer = setInterval(fetchMetadata, 30000);
        startProgressLoop();

        // Release lock after stable time
        setTimeout(() => {
            isSwitching = false;
            document.getElementById('station-sidebar').classList.remove('signal-lock');
            loader.innerText = "POLLING...";
        }, 1500);
    }

    async function fetchMetadata() {
        if(!currentStation) return;
        document.getElementById('metadata-loader').classList.remove('hidden');
        try {
            const response = await fetch(currentStation.api);
            const data = await response.json();
            document.getElementById('track-name').innerText = data.song || 'Live';
            document.getElementById('artist-name').innerText = data.artist || currentStation.title;
            mainArtwork.src = optimizeImg(data.artwork || currentStation.posterUrl, 600, 600);
            document.getElementById('art-bitrate').innerText = `${data.bitrate || '128'}K`;
            document.getElementById('art-format').innerText = data.format || 'MP3';
            document.getElementById('art-year').innerText = data.year || '----';
            if(data.duration > 0) {
                trackProgress = { duration: data.duration, elapsed: data.elapsed, lastUpdated: Date.now(), isActive: true };
            } else {
                trackProgress.isActive = false;
                document.getElementById('progress-shadow').style.width = '0%';
                document.getElementById('progress-text').innerText = 'MASTER CONTROL';
            }
            const lyricsBtn = document.getElementById('btn-lyrics-toggle');
            if(data.lyrics && data.lyrics.trim() !== "") {
                lyricsBtn.classList.remove('hidden');
                document.getElementById('lyrics-body').innerText = data.lyrics;
                document.getElementById('lyrics-title').innerText = `${data.artist} - ${data.song}`;
            } else { lyricsBtn.classList.add('hidden'); }
            fullHistoryData = data.history || [];
            renderCompactHistory();
        } catch (e) { console.error("Metadata fail"); }
        finally { if(!isSwitching) document.getElementById('metadata-loader').classList.add('hidden'); }
    }

    function togglePlayback() { if (player.paused) player.play(); else player.pause(); }
    function toggleSidebar() { document.getElementById('station-sidebar').classList.toggle('open'); }
    function openHistory() { document.getElementById('history-panel').classList.add('open'); document.getElementById('blur-bg').classList.add('open'); renderFullHistory(); }
    function openLyrics() { document.getElementById('lyrics-panel').classList.add('open'); document.getElementById('blur-bg').classList.add('open'); }
    function closeAllPanels() { document.getElementById('history-panel').classList.remove('open'); document.getElementById('lyrics-panel').classList.remove('open'); document.getElementById('blur-bg').classList.remove('open'); }

    function renderCompactHistory() {
        const list = document.getElementById('history-list');
        list.innerHTML = '';
        fullHistoryData.slice(0, 3).forEach(item => {
            const div = document.createElement('div');
            div.className = "flex items-center justify-between text-[11px] border-b border-zinc-900 pb-3";
            div.innerHTML = `<div class="min-w-0 flex-1 pr-4"><span class="font-bold text-zinc-300 block truncate uppercase line-clamp-2">${item.song}</span><span class="mono text-[9px] text-zinc-600 uppercase block line-clamp-1">${item.artist}</span></div>`;
            list.appendChild(div);
        });
    }

    function renderFullHistory() {
        const list = document.getElementById('full-history-list');
        list.innerHTML = '';
        fullHistoryData.forEach((item, idx) => {
            const div = document.createElement('div');
            div.className = "flex gap-5 items-start border-b border-zinc-900/50 pb-6 group";
            div.innerHTML = `<div class="mono text-[10px] text-zinc-800 pt-1 shrink-0">${(idx+1).toString().padStart(2, '0')}</div><img src="${optimizeImg(item.artwork, 150, 150)}" class="w-16 h-16 object-cover border border-zinc-800 shrink-0"><div class="min-w-0 flex-1"><p class="text-zinc-600 mono text-[9px] uppercase tracking-tighter mb-1">${item.relative_time || 'LOGGED'}</p><h4 class="font-bold text-sm text-zinc-200 uppercase leading-tight line-clamp-3">${item.song}</h4><p class="mono text-[10px] text-zinc-500 uppercase line-clamp-2 mt-1">${item.artist}</p></div>`;
            list.appendChild(div);
        });
    }

    function updatePlayUI() {
        if (player.paused) {
            playIcon.innerHTML = '<path d="M8 5v14l11-7z"/>'; playText.innerText = "START SIGNAL";
            liveDot.classList.remove('bg-orange-600', 'animate-pulse'); liveDot.classList.add('bg-zinc-800');
            visualizer.classList.remove('playing');
        } else {
            playIcon.innerHTML = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>'; playText.innerText = "STOP SIGNAL";
            liveDot.classList.remove('bg-zinc-800'); liveDot.classList.add('bg-orange-600', 'animate-pulse');
            visualizer.classList.add('playing');
        }
    }

    function optimizeImg(url, w, h) { return `https://wsrv.nl/?url=${encodeURIComponent(url)}&w=${w}&h=${h}&fit=cover&quality=webp`; }
    setInterval(() => { document.getElementById('clock').innerText = new Date().toTimeString().split(' ')[0]; }, 1000);
    window.onload = () => switchStation(<?php echo json_encode($initialStation); ?>, false);
</script>
</body>
</html>