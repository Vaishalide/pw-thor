<?php
// ------------------------------
// video_player.php
// ------------------------------
//
// A complete PHP/HTML/CSS/JS file that displays an HLS‐powered video
// with fully custom controls (no native browser controls).
// Simply replace the placeholder URLs below with your actual HLS stream URLs.
// ------------------------------

/**
 * CONFIGURE THESE VARIABLES:
 * -------------------------
 * - $title:       The title of the video (displayed in the header).
 * - $video720:    URL to your 720p HLS playlist (e.g., “.m3u8” file).
 * - $video480:    URL to your 480p HLS playlist.
 * - $video360:    URL to your 360p HLS playlist.
 * - $video240:    URL to your 240p HLS playlist.
 *
 * If you are pulling these from a database or GET parameters, adjust as needed.
 */
$title     = "My Sample Video";  
$video720  = "https://example.com/path/to/stream_720p.m3u8";
$video480  = "https://example.com/path/to/stream_480p.m3u8";
$video360  = "https://example.com/path/to/stream_360p.m3u8";
$video240  = "https://example.com/path/to/stream_240p.m3u8";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta 
    name="viewport" 
    content="width=device-width, initial-scale=1, user-scalable=no" 
  />
  <title><?php echo htmlspecialchars($title); ?></title>

  <!-- HLS.js for playing .m3u8 streams in browsers that don’t support native HLS -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

  <style>
    /* === COLOR & THEME VARIABLES === */
    :root {
      --bg:        #111;    /* dark background */
      --fg:        #fff;    /* light foreground */
      --accent:    #2f8eed; /* blue accent */
      --seek-bg:   #444;    /* scrub‐bar background */
    }
    [data-theme="light"] {
      --bg:      #f9f9f9;
      --fg:      #111;
      --accent:  #0066cc;
      --seek-bg: #ccc;
    }

    /* === RESET & LAYOUT === */
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

    /* === HEADER === */
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

    /* === VIDEO PLAYER CONTAINER === */
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

    /* === CUSTOM CONTROLS BAR === */
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
    /* FORCE-SHOW on hover so desktop always sees them */
    .player:hover .controls {
      opacity: 1 !important;
      pointer-events: all !important;
    }

    /* === BUTTON & INPUT STYLING === */
    .btn {
      background: none;
      border: none;
      color: var(--fg);
      font-size: 1.2em;
      cursor: pointer;
      padding: 4px 6px;
      transition: color 0.2s;
    }
    .btn:hover {
      color: var(--accent);
    }
    .volume {
      width: 100px;
    }
    /* Seek bar (range input) styling */
    .seek {
      flex: 1;
      appearance: none;
      height: 4px;
      background: var(--seek-bg);
      border-radius: 2px;
      margin: 0 8px;
      cursor: pointer;
    }
    .seek::-webkit-slider-thumb {
      appearance: none;
      width: 12px;
      height: 12px;
      background: var(--accent);
      border-radius: 50%;
      cursor: pointer;
    }
    .seek::-moz-range-thumb {
      width: 12px;
      height: 12px;
      background: var(--accent);
      border: none;
      border-radius: 50%;
      cursor: pointer;
    }

    /* === SETTINGS PANEL === */
    .settings-panel {
      position: absolute;
      top: 60px;
      right: 20px;
      background: rgba(0, 0, 0, 0.9);
      border: 1px solid var(--fg);
      padding: 12px;
      border-radius: 4px;
      display: none;
      flex-direction: column;
      gap: 8px;
      z-index: 100;
    }
    .settings-panel.active {
      display: flex;
    }
    .settings-panel label {
      display: flex;
      flex-direction: column;
      font-size: 0.9em;
      gap: 4px;
    }
    .settings-panel select {
      padding: 4px;
      background: var(--bg);
      color: var(--fg);
      border: 1px solid var(--fg);
      border-radius: 3px;
    }
    .settings-panel .close-btn {
      align-self: flex-end;
      background: var(--accent);
      border: none;
      color: #fff;
      padding: 6px 10px;
      font-size: 0.9em;
      border-radius: 3px;
      cursor: pointer;
    }
    .settings-panel .close-btn:hover {
      background: #287ac9;
    }
  </style>
</head>

<body data-theme="dark">
  <!-- === HEADER BAR === -->
  <div class="header">
    <a class="back" href="javascript:history.back()">← Back</a>
    <div class="title"><?php echo htmlspecialchars($title); ?></div>
    <!-- (Optional theme toggle)
    <button id="themeToggle" class="btn">☀️</button>
    -->
  </div>

  <!-- === PLAYER AREA === -->
  <div class="player" id="player">
    <!-- NO native “controls” attribute here! -->
    <video
      id="video"
      playsinline
      webkit-playsinline
      autoplay
      muted
    ></video>

    <!-- START WITH “hide” so JS can fade them in/out after hover or mousemove -->
    <div class="controls hide" id="controls">
      <!-- Play / Pause -->
      <button id="playPause" class="btn">►</button>

      <!-- Mute / Unmute -->
      <button id="mute" class="btn">🔊</button>

      <!-- Volume Slider -->
      <input
        id="volume"
        type="range"
        class="volume"
        min="0"
        max="1"
        step="0.01"
        value="1"
      />

      <!-- Seek Bar -->
      <input
        id="seekBar"
        type="range"
        class="seek"
        min="0"
        max="100"
        value="0"
      />

      <!-- Current Time / Duration Display -->
      <span id="timeDisplay" style="font-size: 0.9em; margin-left: 4px; width: 60px; text-align: right;">
        00:00 / 00:00
      </span>

      <!-- Settings Button -->
      <button id="settingsBtn" class="btn">⚙️</button>

      <!-- Fullscreen Button -->
      <button id="fullscreenBtn" class="btn">⛶</button>
    </div>
  </div>

  <!-- === SETTINGS PANEL (hidden until clicking the “⚙️” button) === -->
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
        <option value="1" selected>1×</option>
        <option value="1.5">1.5×</option>
        <option value="2">2×</option>
      </select>
    </label>

    <button id="closeSettings" class="close-btn">Close</button>
  </div>

  <!-- === JAVASCRIPT FOR CONTROLS & HLS LOADING === -->
  <script>
    // Grab elements by ID
    const player          = document.getElementById("player");
    const video           = document.getElementById("video");
    const controls        = document.getElementById("controls");
    const playPauseBtn    = document.getElementById("playPause");
    const muteBtn         = document.getElementById("mute");
    const volumeSlider    = document.getElementById("volume");
    const seekBar         = document.getElementById("seekBar");
    const timeDisplay     = document.getElementById("timeDisplay");
    const settingsBtn     = document.getElementById("settingsBtn");
    const settingsPanel   = document.getElementById("settingsPanel");
    const closeSettings   = document.getElementById("closeSettings");
    const qualitySelect   = document.getElementById("qualitySelect");
    const speedSelect     = document.getElementById("speedSelect");
    const fullscreenBtn   = document.getElementById("fullscreenBtn");

    let hideControlsTimeout = null;
    let hlsInstance        = null;
    let isSeeking          = false;

    /**
     * formatTime(seconds)
     * Converts a time in seconds to "MM:SS" format (e.g., 125 → "02:05").
     */
    function formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return (
        String(mins).padStart(2, "0") +
        ":" +
        String(secs).padStart(2, "0")
      );
    }

    /**
     * updateTimeDisplay()
     * Updates the timeDisplay text and the seek bar’s position.
     */
    function updateTimeDisplay() {
      const current = video.currentTime;
      const duration = video.duration || 0;
      timeDisplay.textContent = `${formatTime(current)} / ${formatTime(duration)}`;

      if (!isSeeking) {
        // Update the seek bar only when not actively dragging
        const percent = (current / duration) * 100;
        seekBar.value = isNaN(percent) ? 0 : percent;
      }
    }

    /**
     * showControls()
     * Removes “hide” from the controls container, then sets a timeout
     * to re‐add “hide” after 3 seconds (unless settings panel is open).
     */
    function showControls() {
      controls.classList.remove("hide");
      clearTimeout(hideControlsTimeout);

      hideControlsTimeout = setTimeout(() => {
        const isSettingsOpen = settingsPanel.classList.contains("active");
        if (!isSettingsOpen) {
          controls.classList.add("hide");
        }
      }, 3000);
    }

    /**
     * loadStream(url)
     * Destroys any previous Hls instance, then uses Hls.js to load a new stream URL.
     */
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
      } else if (video.canPlayType("application/vnd.apple.mpegurl")) {
        // Native HLS playback (Safari)
        video.src = url;
      } else {
        console.error("HLS not supported in this browser.");
      }
      video.play();
    }

    /**
     * togglePlayPause()
     * Starts or pauses the video, updating the play/pause button’s icon.
     */
    function togglePlayPause() {
      if (video.paused) {
        video.play();
      } else {
        video.pause();
      }
    }

    /**
     * toggleMute()
     * Mutes/unmutes the video and updates the mute button’s icon.
     */
    function toggleMute() {
      video.muted = !video.muted;
      muteBtn.textContent = video.muted ? "🔇" : "🔊";
    }

    /**
     * openSettings()
     * Shows the settings panel and keeps the controls visible.
     */
    function openSettings() {
      settingsPanel.classList.add("active");
      controls.classList.remove("hide");
      clearTimeout(hideControlsTimeout);
    }

    /**
     * closeSettingsPanel()
     * Hides the settings panel and restarts the control‐hide timeout.
     */
    function closeSettingsPanel() {
      settingsPanel.classList.remove("active");
      showControls();
    }

    /**
     * toggleFullScreen()
     * Puts the .player container into fullscreen (if supported).
     */
    function toggleFullScreen() {
      if (!document.fullscreenElement) {
        if (player.requestFullscreen) {
          player.requestFullscreen();
        } else if (player.webkitRequestFullscreen) {
          player.webkitRequestFullscreen();
        } else if (player.msRequestFullscreen) {
          player.msRequestFullscreen();
        }
      } else {
        if (document.exitFullscreen) {
          document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
          document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
          document.msExitFullscreen();
        }
      }
    }

    /**
     * initialize()
     * Called on DOMContentLoaded. Sets up event listeners, loads initial stream,
     * and shows controls once.
     */
    function initialize() {
      // 1. Load the default quality from the <select> element:
      loadStream(qualitySelect.value);

      // 2. Set initial playbackRate:
      video.playbackRate = parseFloat(speedSelect.value);

      // 3. Show controls once immediately:
      showControls();

      // 4. Event Listeners for showing/hiding controls:
      player.addEventListener("mousemove", showControls);
      player.addEventListener("touchstart", showControls);

      // 5. Play / Pause:
      playPauseBtn.addEventListener("click", togglePlayPause);
      video.addEventListener("play", () => {
        playPauseBtn.textContent = "❚❚";
      });
      video.addEventListener("pause", () => {
        playPauseBtn.textContent = "►";
      });

      // 6. Mute / Unmute:
      muteBtn.addEventListener("click", toggleMute);

      // 7. Volume Slider:
      volumeSlider.addEventListener("input", (e) => {
        video.volume = parseFloat(e.target.value);
      });

      // 8. Seek Bar:  
      //    - When user starts dragging
      seekBar.addEventListener("mousedown", () => {
        isSeeking = true;
      });
      seekBar.addEventListener("touchstart", () => {
        isSeeking = true;
      });
      //    - While dragging
      seekBar.addEventListener("input", (e) => {
        const percent = parseFloat(e.target.value);
        const newTime = (percent / 100) * video.duration;
        video.currentTime = isNaN(newTime) ? 0 : newTime;
      });
      //    - When user releases mouse/touch
      seekBar.addEventListener("mouseup", () => {
        isSeeking = false;
      });
      seekBar.addEventListener("touchend", () => {
        isSeeking = false;
      });
      //    - Update seek bar & time display as video plays
      video.addEventListener("timeupdate", updateTimeDisplay);
      video.addEventListener("loadedmetadata", updateTimeDisplay);

      // 9. Quality Change:
      qualitySelect.addEventListener("change", () => {
        // Remember current playback position
        const currentTime = video.currentTime;
        loadStream(qualitySelect.value);
        // After stream loads, restore time after a tiny delay
        video.addEventListener(
          "loadedmetadata",
          function restoreTime() {
            video.currentTime = currentTime;
            video.removeEventListener("loadedmetadata", restoreTime);
          }
        );
      });

      // 10. Playback Speed Change:
      speedSelect.addEventListener("change", () => {
        video.playbackRate = parseFloat(speedSelect.value);
      });

      // 11. Settings Panel Toggle:
      settingsBtn.addEventListener("click", openSettings);
      closeSettings.addEventListener("click", closeSettingsPanel);

      // 12. Fullscreen Toggle:
      fullscreenBtn.addEventListener("click", toggleFullScreen);

      // 13. Hide settings when clicking outside
      document.addEventListener("click", (e) => {
        if (
          settingsPanel.classList.contains("active") &&
          !settingsPanel.contains(e.target) &&
          e.target !== settingsBtn
        ) {
          closeSettingsPanel();
        }
      });
    }

    // Wait until the DOM is fully loaded
    document.addEventListener("DOMContentLoaded", initialize);
  </script>
</body>
</html>
