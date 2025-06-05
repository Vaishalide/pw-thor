<?php
// Retrieve quality video URLs and title from query string
$video720 = isset($_GET['videoUrl'])  ? $_GET['videoUrl']  : '';
$video480 = isset($_GET['videoUrl1']) ? $_GET['videoUrl1'] : '';
$video360 = isset($_GET['videoUrl2']) ? $_GET['videoUrl2'] : '';
$video240 = isset($_GET['videoUrl3']) ? $_GET['videoUrl3'] : '';
$title    = isset($_GET['title'])     ? $_GET['title']     : 'Video Player';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($title); ?></title>
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <style>
    :root {
      --bg: #000;
      --fg: #fff;
      --accent: #2f8eed;
      --seek-bg: #444;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html, body {
      width: 100%;
      height: 100%;
      background: var(--bg);
      overflow: hidden;
      font-family: Arial, sans-serif;
    }

    .player {
      width: 100vw;
      height: 100vh;
      position: relative;
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

    .top-quality {
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 10;
      background: rgba(0, 0, 0, 0.6);
      padding: 4px;
      border-radius: 4px;
    }

    .top-quality select {
      background: var(--bg);
      color: var(--fg);
      border: 1px solid var(--accent);
      border-radius: 4px;
      padding: 4px;
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
      background: var(--seek-bg);
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
  </style>
</head>
<body>
  <div class="player" id="player">
    <video id="video" autoplay playsinline webkit-playsinline></video>

    <!-- Quality Selector -->
    <div class="top-quality">
      <select id="topQualitySelect">
        <option value="<?php echo htmlspecialchars($video720); ?>">720p</option>
        <option value="<?php echo htmlspecialchars($video480); ?>">480p</option>
        <option value="<?php echo htmlspecialchars($video360); ?>">360p</option>
        <option value="<?php echo htmlspecialchars($video240); ?>">240p</option>
      </select>
    </div>

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
  </div>

  <script>
    const video = document.getElementById("video");
    const player = document.getElementById("player");
    const controls = document.getElementById("controls");
    const playPause = document.getElementById("playPause");
    const muteBtn = document.getElementById("mute");
    const volumeSlider = document.getElementById("volume");
    const fullscreenBtn = document.getElementById("fullscreen");
    const seekBar = document.getElementById("seek");
    const currentTimeElem = document.getElementById("currentTime");
    const durationElem = document.getElementById("duration");
    const topQualitySelect = document.getElementById("topQualitySelect");

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
        alert("Your browser does not support HLS");
      }
      video.play();
    }

    topQualitySelect.onchange = () => {
      loadStream(topQualitySelect.value);
    };

    function formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    video.addEventListener("loadedmetadata", () => {
      durationElem.textContent = formatTime(video.duration);
    });

    video.addEventListener("timeupdate", () => {
      currentTimeElem.textContent = formatTime(video.currentTime);
      seekBar.value = (video.currentTime / video.duration) * 100;
    });

    seekBar.addEventListener("input", () => {
      video.currentTime = (seekBar.value / 100) * video.duration;
    });

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
        player.requestFullscreen();
      } else {
        document.exitFullscreen();
      }
    };

    document.addEventListener("DOMContentLoaded", () => {
      loadStream(topQualitySelect.value);
    });
  </script>
</body>
</html>
