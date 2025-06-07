<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <link rel="stylesheet" href="https://cdn.plyr.io/3.6.12/plyr.css" />
  <script src="https://cdn.plyr.io/3.6.12/plyr.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/hls.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dashjs/3.1.3/dash.all.min.js"></script>
  <link href="https://fonts.googleapis.com/css?family=Poppins|Quattrocento+Sans" rel="stylesheet"/>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  <title>BeastX Player</title>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      background: #000;
      color: #fff;
      font-family: Poppins, sans-serif;
    }
    .loading {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #000;
      z-index: 9999;
    }
    .circle {
      width: 20px;
      height: 20px;
      margin: 10px;
      border-radius: 50%;
      animation: loader-animation 0.75s ease infinite;
    }
    .circle:nth-child(1) { background-color: #D90429; animation-delay: 0s; }
    .circle:nth-child(2) { background-color: #FFA300; animation-delay: 0.15s; }
    .circle:nth-child(3) { background-color: #048BA8; animation-delay: 0.3s; }
    @keyframes loader-animation {
      0% { transform: scale(0); opacity: 0.7; }
      100% { transform: scale(1); opacity: 0; }
    }

    .top-quality {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(0,0,0,0.6);
      padding: 6px;
      border-radius: 4px;
    }
    .top-quality select {
      background: #000;
      color: #fff;
      border: 1px solid #1ac266;
      padding: 4px 8px;
      border-radius: 4px;
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
      color: #fff;
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
      appearance: none;
      cursor: pointer;
    }
    .seek::-webkit-slider-thumb {
      width: 16px;
      height: 16px;
      background: #2f8eed;
      border-radius: 50%;
      margin-top: -4px;
    }
    .time {
      color: #fff;
      font-size: 0.9em;
    }

    /* Playback Speed Selector */
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
      background: #000;
      color: #fff;
      border: 1px solid #2f8eed;
      padding: 4px;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <div id="loading" class="loading">
    <div class="circle"></div>
    <div class="circle"></div>
    <div class="circle"></div>
  </div>

  <!-- Quality Selector -->
  <div class="top-quality">
    <select id="qualitySelect">
      <option value="{{ video720 }}">720p</option>
      <option value="{{ video480 }}">480p</option>
      <option value="{{ video360 }}">360p</option>
      <option value="{{ video240 }}">240p</option>
    </select>
  </div>

  <!-- Video Element -->
  <video id="player" class="plyr" playsinline controls crossorigin>
    <source src="{{ video720 }}" type="application/vnd.apple.mpegurl">
  </video>

  <!-- Controls Bar -->
  <div class="controls">
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

  <!-- Playback-Speed Selector -->
  <div class="playback-speed">
    <label for="speed" style="margin-right:4px;">Speed:</label>
    <select id="speed">
      <option value="0.5">0.5x</option>
      <option value="0.75">0.75x</option>
      <option value="1" selected>1x</option>
      <option value="1.25">1.25x</option>
      <option value="1.5">1.5x</option>
      <option value="2">2x</option>
    </select>
  </div>

  <script>
    const videoEl       = document.getElementById('player');
    const qualitySelect = document.getElementById('qualitySelect');
    let hls;

    function loadStream(url) {
      if (hls) { hls.destroy(); hls = null; }
      if (Hls.isSupported()) {
        hls = new Hls();
        hls.loadSource(url);
        hls.attachMedia(videoEl);
        hls.on(Hls.Events.MANIFEST_PARSED, () => videoEl.play());
      } else if (videoEl.canPlayType('application/vnd.apple.mpegurl')) {
        videoEl.src = url;
        videoEl.addEventListener('loadedmetadata', () => videoEl.play());
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      // Hide loader
      document.getElementById('loading').style.display = 'none';

      // Initialize Plyr
      const player = new Plyr(videoEl, { quality: { forced: true } });
      window.player = player;

      // Load default quality
      loadStream(qualitySelect.value);

      // Change quality
      qualitySelect.addEventListener('change', () => {
        loadStream(qualitySelect.value);
      });

      // Play/Pause button
      document.getElementById("playPause").addEventListener("click", () => {
        if (videoEl.paused) {
          videoEl.play();
        } else {
          videoEl.pause();
        }
      });

      // Mute/Unmute button
      document.getElementById("mute").addEventListener("click", () => {
        videoEl.muted = !videoEl.muted;
      });

      // Volume slider
      document.getElementById("volume").addEventListener("input", () => {
        videoEl.volume = document.getElementById("volume").value;
      });

      // Fullscreen button
      document.getElementById("fullscreen").addEventListener("click", () => {
        if (videoEl.requestFullscreen) {
          videoEl.requestFullscreen();
        } else if (videoEl.webkitRequestFullscreen) {
          videoEl.webkitRequestFullscreen();
        }
      });

      // Seek bar
      document.getElementById("seek").addEventListener("input", () => {
        videoEl.currentTime = (document.getElementById("seek").value / 100) * videoEl.duration;
      });

      // Speed selector
      document.getElementById("speed").addEventListener("change", (event) => {
        videoEl.playbackRate = parseFloat(event.target.value);
      });

      // Time update
      videoEl.addEventListener('timeupdate', () => {
        document.getElementById("currentTime").textContent = formatTime(videoEl.currentTime);
        document.getElementById("seek").value = (videoEl.currentTime / videoEl.duration) * 100;
        document.getElementById("duration").textContent = formatTime(videoEl.duration);
      });
    });

    function formatTime(seconds) {
      const minutes = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }
  </script>
</body>
</html>
