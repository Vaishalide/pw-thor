<?php
// Determine video URLs and title from POST (preferred) or fallback to GET (optional).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $video720 = isset($_POST['videoUrl'])  ? $_POST['videoUrl']  : '';
    $video480 = isset($_POST['videoUrl1']) ? $_POST['videoUrl1'] : '';
    $video360 = isset($_POST['videoUrl2']) ? $_POST['videoUrl2'] : '';
    $video240 = isset($_POST['videoUrl3']) ? $_POST['videoUrl3'] : '';
    $title    = isset($_POST['title'])     ? $_POST['title']     : '';
} else {
    // Fallback only if someone tries to hit player.php directly with query parameters.
    $video720 = isset($_GET['videoUrl'])  ? $_GET['videoUrl']  : '';
    $video480 = isset($_GET['videoUrl1']) ? $_GET['videoUrl1'] : '';
    $video360 = isset($_GET['videoUrl2']) ? $_GET['videoUrl2'] : '';
    $video240 = isset($_GET['videoUrl3']) ? $_GET['videoUrl3'] : '';
    $title    = isset($_GET['title'])     ? $_GET['title']     : 'Lecture Video';
}

if (empty($video720)) {
    // No primary (720p) video URL provided—redirect back to index or show an error.
    header('Location: index.html');
    exit;
}

// Safely escape for embedding in HTML/JS
$escapedTitle  = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$escaped720    = htmlspecialchars($video720, ENT_QUOTES, 'UTF-8');
$escaped480    = htmlspecialchars($video480, ENT_QUOTES, 'UTF-8');
$escaped360    = htmlspecialchars($video360, ENT_QUOTES, 'UTF-8');
$escaped240    = htmlspecialchars($video240, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
  <title><?php echo $escapedTitle; ?></title>
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
    }
    /* Reset & layout */
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
      background: #1a1b2f;
      padding: 1rem;
      display: flex;
      align-items: center;
      color: gold;
      flex-shrink: 0;
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
    }
    video {
      width: 100%;
      height: 100%;
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
      font-size: 0.95em;
      outline: none;
    }
  </style>
</head>
<body data-theme="dark">
  <!-- Header with Back link and Title -->
  <div class="header">
    <a class="back" href="javascript:history.back()">← Back</a>
    <div class="title"><?php echo $escapedTitle; ?></div>
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
      <label for="qualitySelect">Quality</label>
      <select id="qualitySelect">
        <?php if (!empty($escaped720)): ?>
          <option value="<?php echo $escaped720; ?>">Auto (720p)</option>
          <option value="<?php echo $escaped720; ?>">720p</option>
        <?php endif; ?>
        <?php if (!empty($escaped480)): ?>
          <option value="<?php echo $escaped480; ?>">480p</option>
        <?php endif; ?>
        <?php if (!empty($escaped360)): ?>
          <option value="<?php echo $escaped360; ?>">360p</option>
        <?php endif; ?>
        <?php if (!empty($escaped240)): ?>
          <option value="<?php echo $escaped240; ?>">240p</option>
        <?php endif; ?>
      </select>

      <label for="speedSelect">Playback Speed</label>
      <select id="speedSelect">
        <option value="0.5">0.5×</option>
        <option value="1" selected>1×</option>
        <option value="1.5">1.5×</option>
        <option value="2">2×</option>
      </select>
    </div>
  </div>

  <script>
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

    let hideControlsTimeout;
    let hlsInstance;

    // Utility: format seconds → "M:SS"
    function formatTime(seconds) {
      const m = Math.floor(seconds / 60);
      const s = Math.floor(seconds % 60).toString().padStart(2, '0');
      return `${m}:${s}`;
    }

    // Show controls when user interacts, then hide after 3 seconds (unless settings open)
    function showControls() {
      controls.classList.remove('hide');
      clearTimeout(hideControlsTimeout);
      hideControlsTimeout = setTimeout(() => {
        if (!settingsPanel.classList.contains('active')) {
          controls.classList.add('hide');
        }
      }, 3000);
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
      if (qualitySelect.options.length) {
        loadStream(qualitySelect.value);
      }
      video.playbackRate = parseFloat(speedSelect.value);
      setupEventListeners();
      showControls();
    }

    // Update quality when user changes select
    qualitySelect.onchange = function(e) {
      loadStream(e.target.value);
    };

    // Update playback speed when changed
    speedSelect.onchange = function(e) {
      video.playbackRate = parseFloat(e.target.value);
    };

    // Set up event listeners for controls
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

      // Volume slider
      volumeSlider.oninput = e => {
        video.volume = e.target.value;
        video.muted = (e.target.value == 0);
      };

      // Fullscreen toggle + orientation lock on mobile
      fullscreenBtn.onclick = (e) => {
        e.stopPropagation();
        if (document.fullscreenElement) {
          document.exitFullscreen();
        } else {
          player.requestFullscreen();
        }
      };
      document.addEventListener('fullscreenchange', () => {
        if (document.fullscreenElement) {
          if (screen.orientation && screen.orientation.lock) {
            screen.orientation.lock('landscape').catch(() => {});
          }
        } else {
          if (screen.orientation && screen.orientation.unlock) {
            screen.orientation.unlock();
          }
        }
      });

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

      // Theme toggle (dark / light)
      themeToggle.onclick = (e) => {
        e.stopPropagation();
        const next = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
        document.body.dataset.theme = next;
        themeToggle.textContent = next === 'dark' ? '🌙' : '☀️';
      };
    }

    // Start once DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
      initPlayer();
    });
  </script>
</body>
</html>
