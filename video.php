<?php
// Retrieve all four quality URLs and the title from query parameters
$video720 = isset($_GET['videoUrl'])  ? $_GET['videoUrl']  : '';
$video480 = isset($_GET['videoUrl1']) ? $_GET['videoUrl1'] : '';
$video360 = isset($_GET['videoUrl2']) ? $_GET['videoUrl2'] : '';
$video240 = isset($_GET['videoUrl3']) ? $_GET['videoUrl3'] : '';
$title   = isset($_GET['title'])      ? $_GET['title']      : 'Lecture Video';
?><!DOCTYPE html><html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
  <title><?php echo htmlspecialchars($title); ?></title>
  <!-- HLS.js for streaming -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <style>
    /* Color-theme variables */
    :root {
      --bg: #111;
      --fg: #fff;
      --accent: #2f8eed;
      --seek-bg: #444;
    }
    [data-theme="light"] {
      --bg: #f9f9f9;
      --fg: #111;
      --accent: #0066cc;
      --seek-bg: #ccc;
    }/* Reset & layout */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
body {
  background: var(--bg);
  color: var(--fg);
  font-family: Arial, sans-serif;
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}
.header {
  display: flex;
  align-items: center;
  padding: 10px;
  background: var(--bg);
  border-bottom: 1px solid var(--accent);
}
.back {
  color: var(--accent);
  text-decoration: none;
  font-size: 1.1em;
  margin-right: 10px;
}
.title {
  flex: 1;
  text-align: center;
  font-size: 1.2em;
}

.player {
  position: relative;
  background: #000;
  display: flex;
  justify-content: center;
  align-items: center;
  flex: 1;
  padding: 1rem;
}
video {
  max-width: 100%;
  max-height: 100%; /* limits video height */
  object-fit: contain;
  background: #000;
}

/* Fullscreen adjustments */
.player:-webkit-full-screen video,
.player:fullscreen video {
  width: 100vw;
  height: 100vh;
}

/* Controls bar */
.controls {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  padding: 6px;
  gap: 6px;
}
.btn {
  background: none;
  border: none;
  color: var(--fg);
  font-size: 1.1em;
  cursor: pointer;
  width: 40px;
  text-align: center;
}
.seek,
.volume {
  -webkit-appearance: none;
  background: transparent;
  cursor: pointer;
}
.seek {
  flex: 1;
}
.seek::-webkit-slider-runnable-track {
  height: 6px;
  background: var(--seek-bg);
  border-radius: 3px;
}
.seek::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 12px;
  height: 12px;
  background: var(--accent);
  border-radius: 50%;
  margin-top: -3px;
}
.volume {
  width: 70px;
}
.volume::-webkit-slider-runnable-track {
  height: 4px;
  background: var(--seek-bg);
  border-radius: 2px;
}
.volume::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 10px;
  height: 10px;
  background: var(--accent);
  border-radius: 50%;
  margin-top: -3px;
}

/* Settings button and panel */
.settings-panel {
  position: absolute;
  bottom: calc(100% + 6px);
  right: 10px;
  background: var(--bg);
  border: 1px solid var(--accent);
  border-radius: 4px;
  display: none;
  font-size: 0.95em;
  z-index: 20;
  padding: 8px;
  width: 160px;
}
.settings-panel.active {
  display: block;
}
.settings-panel label {
  display: block;
  margin: 6px 0 2px 0;
  color: var(--accent);
  font-size: 0.95em;
}
.settings-panel select {
  width: 100%;
  padding: 4px;
  margin-bottom: 8px;
  border-radius: 4px;
  border: 1px solid var(--fg);
  background: var(--bg);
  color: var(--fg);
}

/* Top-right quality selector */
.top-quality {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 15;
  background: rgba(0,0,0,0.6);
  padding: 4px;
  border-radius: 4px;
}
.top-quality select {
  background: var(--bg);
  color: var(--fg);
  border: 1px solid var(--acccent);
  border-radius: 4px;
  padding: 2px 4px;
}

  </style>
</head>
<body data-theme="dark">
  <!-- Header with Back link and Title -->
  <div class="header">
    <a class="back" href="javascript:history.back()">← Back</a>
    <div class="title"><?php echo htmlspecialchars($title); ?></div>
  </div>  <!-- Video player container -->  <div class="player" id="player">
    <video id="video" playsinline webkit-playsinline autoplay muted></video><!-- Top-right Quality Selector -->
<div class="top-quality">
  <select id="topQualitySelect">
    <option value="<?php echo htmlspecialchars($video720); ?>">Auto (720p)</option>
    <option value="<?php echo htmlspecialchars($video720); ?>">720p</option>
    <option value="<?php echo htmlspecialchars($video480); ?>">480p</option>
    <option value="<?php echo htmlspecialchars($video360); ?>">360p</option>
    <option value="<?php echo htmlspecialchars($video240); ?>">240p</option>
  </select>
</div>

<!-- Controls bar: play/pause, mute, volume, settings, theme, fullscreen, seek -->
<div class="controls" id="controls">
  <button id="playPause" class="btn">►</button>
  <button id="mute" class="btn">🔊</button>
  <input id="volume" type="range" class="volume" min="0" max="1" step="0.01" value="1">
  <button id="settingsBtn" class="btn">⚙️</button>
  <button id="themeToggle" class="btn">🌙</button>
  <button id="fullscreen" class="btn">⛶</button>
  <div class="seek-container">
    <span id="currentTime" class="time">0:00</span>
    <input id="seek" type="range" class="seek" min="0" max="100" value="0">
    <span id="duration" class="time">0:00</span>
  </div>
</div>

<!-- Settings panel for Quality & Speed -->
<div class="settings-panel" id="settingsPanel">
  <label for="qualitySelect">Quality</label>
  <select id="qualitySelect">
    <option value="<?php echo htmlspecialchars($video720); ?>">Auto (720p)</option>
    <option value="<?php echo htmlspecialchars($video720); ?>">720p</option>
    <option value="<?php echo htmlspecialchars($video480); ?>">480p</option>
    <option value="<?php echo htmlspecialchars($video360); ?>">360p</option>
    <option value="<?php echo htmlspecialchars($video240); ?>">240p</option>
  </select>

  <label for="speedSelect">Playback Speed</label>
  <select id="speedSelect">
    <option value="0.5">0.5×</option>
    <option value="1" selected>1×</option>
    <option value="1.5">1.5×</option>
    <option value="2">2×</option>
  </select>
</div>

  </div>  <script>
    // Retrieve DOM elements
    const video         = document.getElementById('video');
    const player        = document.getElementById('player');
    const controls      = document.getElementById('controls');
    const settingsBtn   = document.getElementById('settingsBtn');
    const themeToggle   = document.getElementById('themeToggle');
    const fullscreenBtn = document.getElementById('fullscreen');
    const playPauseBtn  = document.getElementById('playPause');
    const muteBtn       = document.getElementById('mute');
    const volumeSlider  = document.getElementById('volume');
    const seekSlider    = document.getElementById('seek');
    const currentTimeEl = document.getElementById('currentTime');
    const durationEl    = document.getElementById('duration');
    const settingsPanel = document.getElementById('settingsPanel');
    const qualitySelect = document.getElementById('qualitySelect');
    const speedSelect   = document.getElementById('speedSelect');
    const topQuality    = document.getElementById('topQualitySelect');

    let hideControlsTimeout;
    let hlsInstance;

    // Utility: format seconds → "M:SS"
    function formatTime(seconds) {
      const m = Math.floor(seconds / 60);
      const s = Math.floor(seconds % 60).toString().padStart(2, '0');
      return `${m}:${s}`;
    }

    // Load HLS stream (or native if supported)
    function loadStream(url) {
      if (hlsInstance) {
        hlsInstance.destroy();
        hlsInstance = null;
      }
      if (!url) {
        console.error('Stream URL missing.');
        return;
      }
      if (Hls.isSupported()) {
        hlsInstance = new Hls();
        hlsInstance.loadSource(url);
        hlsInstance.attachMedia(video);
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = url;
      } else {
        console.error('HLS not supported in this browser');
      }
      video.play();
    }

    // Initialize player: load default stream and set up events
    function initPlayer() {
      loadStream(qualitySelect.value);
      video.playbackRate = parseFloat(speedSelect.value);
      setupEventListeners();
      showControls();
    }

    function setupEventListeners() {
      // Show controls on hover
      player.addEventListener('mousemove', showControls);
      player.addEventListener('mouseleave', () => { hideControlsTimeout = setTimeout(hideControls, 2000); });

      // Play / Pause toggle
      playPauseBtn.onclick = () => {
        if (video.paused) video.play();
        else video.pause();
      };
      video.onplay  = () => playPauseBtn.textContent = '❚❚';
      video.onpause = () => playPauseBtn.textContent = '►';

      // Update time display & seek slider
      video.ontimeupdate = () => {
        const current = video.currentTime;
        const total   = video.duration || 0;
        const pct     = total ? (current / total) * 100 : 0;
        seekSlider.value = pct;
        currentTimeEl.textContent = formatTime(current);
      };
      video.onloadedmetadata = () => {
        durationEl.textContent = formatTime(video.duration || 0);
      };

      // Seek input
      seekSlider.oninput = e => {
        const total = video.duration || 0;
        const targetTime = (e.target.value / 100) * total;
        video.currentTime = targetTime;
      };

      // Mute / Unmute toggle
      muteBtn.onclick = () => {
        video.muted = !video.muted;
        muteBtn.textContent = video.muted ? '🔇' : '🔊';
      };

      // Volume adjustment
      volumeSlider.oninput = e => { video.volume = e.target.value; };

      // Settings button toggles panel visibility
      settingsBtn.onclick = (e) => {
        e.stopPropagation();
        settingsPanel.classList.toggle('active');
        showControls(); // keep controls visible when settings open
      };
      // Hide settings panel if clicking outside
      document.addEventListener('click', e => {
        if (!settingsPanel.contains(e.target) && e.target !== settingsBtn) {
          settingsPanel.classList.remove('active');
        }
      });

      // Change quality from settings panel
      qualitySelect.onchange = () => {
        const url = qualitySelect.value;
        topQuality.value = url;
        loadStream(url);
      };
      // Change quality from top-right selector
      topQuality.onchange = () => {
        const url = topQuality.value;
        qualitySelect.value = url;
        loadStream(url);
      };

      // Change playback speed
      speedSelect.onchange = () => { video.playbackRate = parseFloat(speedSelect.value); };

      // Theme toggle (dark / light)
      themeToggle.onclick = (e) => {
        e.stopPropagation();
        const next = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
        document.body.dataset.theme = next;
        themeToggle.textContent = next === 'dark' ? '🌙' : '☀️';
      };

      // Fullscreen toggle
      fullscreenBtn.onclick = () => {
        if (!document.fullscreenElement) {
          player.requestFullscreen().catch(err => console.error(err));
        } else {
          document.exitFullscreen();
        }
      };
    }

    function showControls() {
      clearTimeout(hideControlsTimeout);
      controls.classList.add('active');
      hideControlsTimeout = setTimeout(hideControls, 2000);
    }
    function hideControls() { controls.classList.remove('active'); }

    // Start once DOM is ready
    document.addEventListener('DOMContentLoaded', () => { initPlayer(); });
  </script></body>
</html>
