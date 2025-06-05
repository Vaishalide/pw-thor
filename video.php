<?php
// Retrieve all four quality URLs and the title from query parameters
$video720 = isset($_GET['videoUrl'])  ? $_GET['videoUrl']  : '';
$video480 = isset($_GET['videoUrl1']) ? $_GET['videoUrl1'] : '';
$video360 = isset($_GET['videoUrl2']) ? $_GET['videoUrl2'] : '';
$video240 = isset($_GET['videoUrl3']) ? $_GET['videoUrl3'] : '';
$title   = isset($_GET['title'])      ? $_GET['title']      : 'Lecture Video';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
  <title><?php echo htmlspecialchars($title); ?></title>
  <!-- HLS.js for streaming -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

  <style>
    /* Color‐theme variables */
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
    }

    /* Reset & layout */
    body {
      margin: 0;
      background: var(--bg);
      color: var(--fg);
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: column;
      height: 100vh;
    }
    .header {
      background: #1a1b2f;
      padding: 1rem;
      display: flex;
      align-items: center;
      color: gold;
    }
    .back {
      color: gold;
      text-decoration: none;
      font-size: 1.5rem;
      margin-right: 1rem;
    }
    .title {
      font-size: 1.2rem;
      font-weight: bold;
    }

    /* Player container */
    .player {
      position: relative;
      flex: 1;
      background: #000;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }
    video {
      width: 100%;
      height: 100%;
      object-fit: contain;
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
      transition: opacity 0.3s;
    }
    .controls.hide {
      opacity: 0;
      pointer-events: none;
    }
    .btn {
      background: none;
      border: none;
      color: var(--fg);
      font-size: 1.2em;
      padding: 4px;
      cursor: pointer;
    }
    .seek-container {
      flex: 1 1 100%;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .time {
      font-size: 0.75em;
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

    /* Settings panel (Quality & Speed) */
    .settings-panel {
      position: absolute;
      bottom: calc(100% + 6px);
      right: 10px;
      background: var(--bg);
      border: 1px solid var(--accent);
      border-radius: 4px;
      display: none;
      font-size: 0.9em;
      z-index: 10;
      padding: 6px;
    }
    .settings-panel.active {
      display: block;
    }
    .settings-panel strong {
      display: block;
      margin: 4px 0 2px 0;
      color: var(--accent);
    }
    .settings-panel button {
      background: none;
      border: 1px solid var(--fg);
      color: var(--fg);
      padding: 4px 8px;
      margin: 4px 2px 0 2px;
      cursor: pointer;
      border-radius: 3px;
      font-size: 0.9em;
    }
    .settings-panel button.active {
      background: var(--accent);
      border-color: var(--accent);
      color: var(--bg);
    }
  </style>
</head>
<body data-theme="dark">

  <!-- Header with Back link and Title -->
  <div class="header">
    <a class="back" href="javascript:history.back()">← Back</a>
    <div class="title"><?php echo htmlspecialchars($title); ?></div>
  </div>

  <!-- Video player container -->
  <div class="player" id="player">
    <video id="video" playsinline webkit-playsinline autoplay muted></video>

    <!-- Controls bar: play/pause, mute, volume, settings, theme, fullscreen, seek -->
    <div class="controls hide" id="controls">
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
      <strong>Quality</strong>
      <div id="levels"></div>
      <strong>Speed</strong>
      <div id="speeds"></div>
    </div>
  </div>

  <script>
    // Map of labels → URLs for each quality
    const qualityMap = {
      "Auto": "<?php echo htmlspecialchars($video720); ?>",
      "720p": "<?php echo htmlspecialchars($video720); ?>",
      "480p": "<?php echo htmlspecialchars($video480); ?>",
      "360p": "<?php echo htmlspecialchars($video360); ?>",
      "240p": "<?php echo htmlspecialchars($video240); ?>"
    };
    const speedOptions = [0.5, 1, 1.5, 2]; // playback speeds

    const video       = document.getElementById('video');
    const player      = document.getElementById('player');
    const controls    = document.getElementById('controls');
    const levels      = document.getElementById('levels');
    const speedsPanel = document.getElementById('speeds');
    const settingsBtn = document.getElementById('settingsBtn');
    const themeToggle = document.getElementById('themeToggle');
    const fullscreenBtn = document.getElementById('fullscreen');
    const playPauseBtn  = document.getElementById('playPause');
    const muteBtn       = document.getElementById('mute');
    const volumeSlider  = document.getElementById('volume');
    const seekSlider    = document.getElementById('seek');
    const currentTimeEl = document.getElementById('currentTime');
    const durationEl    = document.getElementById('duration');
    const settingsPanel = document.getElementById('settingsPanel');

    let currentUrl = qualityMap["Auto"];
    let hideControlsTimeout;
    let hlsInstance;

    // Utility: format seconds → "M:SS"
    function formatTime(seconds) {
      const m = Math.floor(seconds / 60);
      const s = Math.floor(seconds % 60).toString().padStart(2, '0');
      return `${m}:${s}`;
    }

    // Show controls when user interacts, then hide after a short delay
    function showControls() {
      controls.classList.remove('hide');
      clearTimeout(hideControlsTimeout);
      hideControlsTimeout = setTimeout(() => {
        controls.classList.add('hide');
      }, 4000);
    }

    // Initialize player: load stream, build UI, wire events
    function initPlayer() {
      loadStream(currentUrl);
      buildQualityButtons();
      buildSpeedButtons();
      setupEventListeners();
      showControls();
    }

    // Load an HLS stream (or direct if supported natively)
    function loadStream(url) {
      if (hlsInstance) {
        hlsInstance.destroy();
        hlsInstance = null;
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
    }

    // Build Quality buttons inside the #levels container
    function buildQualityButtons() {
      levels.innerHTML = '';
      Object.keys(qualityMap).forEach(label => {
        const btn = document.createElement('button');
        btn.textContent = label;
        btn.dataset.url = qualityMap[label];
        btn.onclick = () => {
          currentUrl = btn.dataset.url;
          loadStream(currentUrl);
          highlightActiveQuality();
        };
        levels.appendChild(btn);
      });
      highlightActiveQuality();
    }

    // Highlight the active quality button
    function highlightActiveQuality() {
      Array.from(levels.children).forEach(btn => {
        btn.classList.toggle('active', btn.dataset.url === currentUrl);
      });
    }

    // Build Speed buttons inside the #speeds container
    function buildSpeedButtons() {
      speedsPanel.innerHTML = '';
      speedOptions.forEach(s => {
        const btn = document.createElement('button');
        btn.textContent = s + 'x';
        btn.dataset.rate = s;
        btn.onclick = () => {
          video.playbackRate = s;
          highlightActiveSpeed();
        };
        speedsPanel.appendChild(btn);
      });
      highlightActiveSpeed();
    }

    // Highlight the active speed button
    function highlightActiveSpeed() {
      Array.from(speedsPanel.children).forEach(btn => {
        btn.classList.toggle('active', parseFloat(btn.dataset.rate) === video.playbackRate);
      });
    }

    // Set up all event listeners for controls
    function setupEventListeners() {
      // Show controls on mouse or touch
      player.addEventListener('mousemove', showControls);
      player.addEventListener('touchstart', showControls);

      // Play / Pause toggle
      playPauseBtn.onclick = () => {
        if (video.paused) video.play();
        else video.pause();
      };
      video.onplay  = () => playPauseBtn.textContent = '❚❚';
      video.onpause = () => playPauseBtn.textContent = '►';

      // Update time display & seek slider as video plays
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

      // Seek slider input
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

      // Volume slider
      volumeSlider.oninput = e => {
        video.volume = e.target.value;
        video.muted = (e.target.value == 0);
      };

      // Fullscreen toggle + orientation lock on mobile
      fullscreenBtn.onclick = () => {
        if (document.fullscreenElement) {
          document.exitFullscreen();
        } else {
          player.requestFullscreen();
        }
      };
      document.addEventListener('fullscreenchange', () => {
        if (document.fullscreenElement) {
          // Attempt landscape lock on mobile
          if (screen.orientation && screen.orientation.lock) {
            screen.orientation.lock('landscape').catch(() => {});
          }
        } else {
          if (screen.orientation && screen.orientation.unlock) {
            screen.orientation.unlock();
          }
        }
      });

      // Settings button toggles panel
      settingsBtn.onclick = () => {
        settingsPanel.classList.toggle('active');
      };
      // Hide settings panel if clicking outside
      document.addEventListener('click', e => {
        if (!settingsPanel.contains(e.target) && e.target !== settingsBtn) {
          settingsPanel.classList.remove('active');
        }
      });

      // Theme toggle (dark / light)
      themeToggle.onclick = () => {
        const next = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
        document.body.dataset.theme = next;
        themeToggle.textContent = next === 'dark' ? '🌙' : '☀️';
      };
    }

    // Start everything once DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
      initPlayer();
    });
  </script>
</body>
</html>
