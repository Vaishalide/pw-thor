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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
  <title><?php echo htmlspecialchars($title); ?></title>
  <!-- HLS.js for streaming -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  
<style>
body, html {
  margin: 0;
  padding: 0;
  background: var(--bg);
  color: var(--fg);
  font-family: Arial, sans-serif;
  height: 100%;
  width: 100%;
}

.video-wrapper {
  width: 100%;
  max-width: 100%;
  aspect-ratio: 16 / 9;
  background: black;
}

video {
  width: 100%;
  height: 100%;
  display: block;
  background-color: black;
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
    <div class="video-wrapper">
  <video id="video" controls playsinline></video></div>

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
      loadStream(qualitySelect.value);
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
