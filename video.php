<?php
$video720 = $_GET['videoUrl']  ?? '';
$video480 = $_GET['videoUrl1'] ?? '';
$video360 = $_GET['videoUrl2'] ?? '';
$video240 = $_GET['videoUrl3'] ?? '';
$title    = $_GET['title']     ?? 'Video Player';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($title); ?></title>
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <style>
    :root {
      --bg: #000;
      --fg: #fff;
      --accent: #2f8eed;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      height: 100%;
      background: var(--bg);
      color: var(--fg);
      font-family: Arial, sans-serif;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: rgba(0, 0, 0, 0.7);
      padding: 10px 20px;
      position: absolute;
      top: 0;
      width: 100%;
      z-index: 10;
    }

    .back-btn {
      color: var(--fg);
      text-decoration: none;
      font-size: 18px;
      background: rgba(255,255,255,0.1);
      padding: 5px 10px;
      border-radius: 4px;
    }

    .title {
      flex: 1;
      text-align: center;
      font-size: 18px;
      font-weight: bold;
      color: var(--fg);
    }

    .top-quality {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 10;
    }

    .top-quality select {
      background: var(--bg);
      color: var(--fg);
      border: 1px solid var(--accent);
      padding: 5px;
      border-radius: 4px;
    }

    .player {
      width: 100vw;
      height: 100vh;
      position: relative;
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

    .controls {
      position: absolute;
      bottom: 0;
      width: 100%;
      display: flex;
      align-items: center;
      background: rgba(0, 0, 0, 0.6);
      padding: 10px;
      gap: 10px;
      z-index: 10;
    }

    .btn {
      background: none;
      border: none;
      color: var(--fg);
      font-size: 1.5em;
      cursor: pointer;
      width: 40px;
      height: 40px;
    }

    .volume {
      width: 100px;
    }

    .seek-container {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .seek {
      width: 100%;
      height: 8px;
      background: #444;
      border-radius: 4px;
      -webkit-appearance: none;
      appearance: none;
      cursor: pointer;
    }

    .seek::-webkit-slider-thumb {
      -webkit-appearance: none;
      width: 16px;
      height: 16px;
      background: var(--accent);
      border-radius: 50%;
      margin-top: -4px;
    }

    .seek::-moz-range-thumb {
      width: 16px;
      height: 16px;
      background: var(--accent);
      border-radius: 50%;
    }

    .time {
      color: var(--fg);
      font-size: 0.9em;
      min-width: 40px;
    }

    .playback-speed {
      position: absolute;
      bottom: 60px;
      right: 20px;
      z-index: 10;
      background: rgba(0, 0, 0, 0.6);
      padding: 6px;
      border-radius: 6px;
    }

    .playback-speed select {
      background: var(--bg);
      color: var(--fg);
      border: 1px solid var(--accent);
      padding: 4px;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <div class="player" id="player">
    <!-- Top Controls -->
    <div class="header">
      <a href="javascript:history.back()" class="back-btn">← Back</a>
      <div class="title"><?php echo htmlspecialchars($title); ?></div>
    </div>

    <!-- Quality Select -->
    <div class="top-quality">
      <select id="topQualitySelect">
        <option value="<?php echo htmlspecialchars($video720); ?>">720p</option>
        <option value="<?php echo htmlspecialchars($video480); ?>">480p</option>
        <option value="<?php echo htmlspecialchars($video360); ?>">360p</option>
        <option value="<?php echo htmlspecialchars($video240); ?>">240p</option>
      </select>
    </div>

    <!-- Video Element -->
    <video id="video" autoplay playsinline webkit-playsinline></video>

    <!-- Controls -->
    <div class="controls" id="controls">
      <button id="playPause" class="btn">►</button>
      <button id="mute" class="btn">🔊</button>
      <input id="volume" type="range" class="volume" min="0" max="1" step="0.01" value="1" />
      <button id="fullscreen" class="btn">⛶</button>
      <div class="seek-container">
        <span id="currentTime" class="time">0:00</span>
        <input id="seek" type="range" class="seek" min="0" max="100" value="0" />
        <span id="duration" class="time">0:00</span>
      </div>
    </div>

    <!-- Playback Speed -->
    <div class="playback-speed">
      <label for="speed">Speed:</label>
      <select id="speed">
        <option value="0.5">0.5x</option>
        <option value="0.75">0.75x</option>
        <option value="1" selected>1x</option>
        <option value="1.25">1.25x</option>
        <option value="1.5">1.5x</option>
        <option value="2">2x</option>
      </select>
    </div>
  </div>

  <script>
    const video = document.getElementById("video");
    const playPause = document.getElementById("playPause");
    const muteBtn = document.getElementById("mute");
    const volumeSlider = document.getElementById("volume");
    const fullscreenBtn = document.getElementById("fullscreen");
    const seekBar = document.getElementById("seek");
    const currentTimeElem = document.getElementById("currentTime");
    const durationElem = document.getElementById("duration");
    const topQualitySelect = document.getElementById("topQualitySelect");
    const speedSelect = document.getElementById("speed");

    let hlsInstance = null;

    function loadStream(url) {
      if (hlsInstance) {
        hlsInstance.destroy();
        hlsInstance = null;
      }

      if (Hls.isSupported()) {
        hlsInstance = new Hls();
        hlsInstance.loadSource(url);
        hlsInstance.attachMedia(video);
      } else if (video.canPlayType("application/vnd.apple.mpegurl")) {
        video.src = url;
      } else {
        alert("HLS not supported in this browser.");
      }

      video.play();
    }

    topQualitySelect.onchange = () => {
      loadStream(topQualitySelect.value);
    };

    playPause.onclick = () => {
      if (video.paused) {
        video.play();
        playPause.textContent = "❚❚";
      } else {
        video.pause();
        playPause.textContent = "►";
      }
    };

    muteBtn.onclick = () => {
      video.muted = !video.muted;
      muteBtn.textContent = video.muted ? "🔇" : "🔊";
    };

    volumeSlider.oninput = () => {
      video.volume = volumeSlider.value;
      if (video.volume > 0) {
        video.muted = false;
        muteBtn.textContent = "🔊";
      }
    };

    fullscreenBtn.onclick = () => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
      } else {
        document.exitFullscreen();
      }
    };

    seekBar.addEventListener("input", () => {
      video.currentTime = (seekBar.value / 100) * video.duration;
    });

    video.addEventListener("timeupdate", () => {
      currentTimeElem.textContent = formatTime(video.currentTime);
      seekBar.value = (video.currentTime / video.duration) * 100;
    });

    video.addEventListener("loadedmetadata", () => {
      durationElem.textContent = formatTime(video.duration);
    });

    speedSelect.onchange = () => {
      video.playbackRate = parseFloat(speedSelect.value);
    };

    function formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    document.addEventListener("DOMContentLoaded", () => {
      loadStream(topQualitySelect.value);
    });
  </script>
</body>
</html>
