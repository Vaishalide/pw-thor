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
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: sans-serif;
      color: var(--fg);
    }
    body {
      background: var(--bg);
      display: flex;
      flex-direction: column;
      height: 100vh;
    }
    .header {
      display: flex;
      align-items: center;
      padding: 10px;
      background: var(--bg);
      border-bottom: 1px solid var(--fg);
    }
    .header .back {
      color: var(--fg);
      text-decoration: none;
      margin-right: 15px;
      font-size: 1.2em;
    }
    .header .title {
      font-size: 1.2em;
      flex: 1;
    }

    .player {
      position: relative;
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      background: black;
    }
    .player video {
      width: 100%;
      height: 100%;
      object-fit: contain;
      background: black;
    }

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

    /* NEW: show controls on hover */
    .player:hover .controls {
      opacity: 1;
      pointer-events: all;
    }

    /* …rest of your CSS (buttons, sliders, settings panel, etc.) … */
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
    <!-- ADD “controls” HERE for native controls -->
    <video id="video"
           playsinline
           webkit-playsinline
           autoplay
           muted
           controls></video>

    <!-- Controls bar: play/pause, mute, volume, settings, theme, fullscreen, seek -->
    <div class="controls hide" id="controls">
      <button id="playPause" class="btn">►</button>
      <button id="mute"       class="btn">🔊</button>
      <input  id="volume"     type="range" class="volume" min="0" max="1" step="0.01" value="1">
      <!-- … your other control buttons (seek bar, settings dropdown, fullscreen, etc.) … -->
    </div>
  </div>

  <!-- Settings Panel (unchanged) -->
  <div class="settings-panel" id="settingsPanel">
    <label>
      Quality:
      <select id="qualitySelect">
        <option value="<?php echo htmlspecialchars($video720); ?>">720p</option>
        <option value="<?php echo htmlspecialchars($video480); ?>">480p</option>
        <option value="<?php echo htmlspecialchars($video360); ?>">360p</option>
        <option value="<?php echo htmlspecialchars($video240); ?>">240p</option>
      </select>
    </label>
    <label>
      Speed:
      <select id="speedSelect">
        <option value="0.5">0.5×</option>
        <option value="1"  selected>1×</option>
        <option value="1.5">1.5×</option>
        <option value="2">2×</option>
      </select>
    </label>
    <button id="closeSettings" class="btn">Close</button>
  </div>

  <script>
    // Grab elements
    const player         = document.getElementById('player');
    const video          = document.getElementById('video');
    const controls       = document.getElementById('controls');
    const playPauseBtn   = document.getElementById('playPause');
    const muteBtn        = document.getElementById('mute');
    const volumeSlider   = document.getElementById('volume');
    const qualitySelect  = document.getElementById('qualitySelect');
    const speedSelect    = document.getElementById('speedSelect');
    const settingsPanel  = document.getElementById('settingsPanel');
    const closeSettings  = document.getElementById('closeSettings');
    let hideControlsTimeout = null;
    let hlsInstance        = null;

    // Initialize: load stream, set playbackRate, event listeners, show controls briefly
    function initPlayer() {
      loadStream(qualitySelect.value);
      video.playbackRate = parseFloat(speedSelect.value);
      setupEventListeners();
      showControls();
    }

    // Show custom controls (removes “hide”), then auto-hide after 3s
    function showControls() {
      controls.classList.remove('hide');
      clearTimeout(hideControlsTimeout);
      hideControlsTimeout = setTimeout(() => {
        if (!settingsPanel.classList.contains('active')) {
          controls.classList.add('hide');
        }
      }, 3000);
    }

    // Set up all click, mouse, and keyboard events
    function setupEventListeners() {
      // Whenever the mouse moves over the player area, show the controls
      player.addEventListener('mousemove', showControls);
      player.addEventListener('touchstart', showControls);

      // Play / Pause toggle
      playPauseBtn.onclick = () => {
        if (video.paused) video.play();
        else video.pause();
      };
      video.onplay  = () => playPauseBtn.textContent = '❚❚';
      video.onpause = () => playPauseBtn.textContent = '►';

      // Mute / Unmute
      muteBtn.onclick = () => {
        video.muted = !video.muted;
        muteBtn.textContent = video.muted ? '🔇' : '🔊';
      };

      // Volume slider
      volumeSlider.oninput = (e) => video.volume = e.target.value;

      // Quality change
      qualitySelect.onchange = () => loadStream(qualitySelect.value);

      // Playback speed change
      speedSelect.onchange = () => video.playbackRate = parseFloat(speedSelect.value);

      // Settings panel toggle
      // (…your existing settings button / event handlers here…)
      closeSettings.onclick = () => settingsPanel.classList.remove('active');
      
      // Fullscreen toggle (if you have a fullscreen button)
      // (…your existing code…)
    }

    // Load HLS stream (or native if supported)
    function loadStream(url) {
      if (hlsInstance) {
        hlsInstance.destroy();
        hlsInstance = null;
      }
      if (!url) return;
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

    // Theme toggle (dark / light) if you have a theme‐button
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
      themeToggle.onclick = (e) => {
        e.stopPropagation();
        const next = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
        document.body.dataset.theme = next;
        themeToggle.textContent = next === 'dark' ? '🌙' : '☀️';
      };
    }

    document.addEventListener('DOMContentLoaded', () => {
      initPlayer();
    });
  </script>
</body>
</html>
