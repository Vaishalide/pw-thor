<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <link rel="stylesheet" href="https://cdn.plyr.io/3.6.12/plyr.css" />
  <link href="favicon.ico" rel="icon">
  <script src="https://cdn.plyr.io/3.6.12/plyr.js"></script>
  <link href="https://fonts.googleapis.com/css?family=Poppins|Quattrocento+Sans" rel="stylesheet"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dashjs/3.1.3/dash.all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/hls.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
  <title>VIDEO PLAYER</title>
  <style type="text/css" media="screen">
    html {
      font-family: Poppins;
      background: #0A0909;
      margin: 0;
      padding: 0;
      --plyr-color-main: #1ac266;
    }

    .logo-container {
      position: absolute;
      top: 10px;
      left: 10px;
      width: 60px;
      height: 60px;
    }

    .plyr {
      height: 100%;
      width: 100%;
    }

    #logo {
      position: fixed;
      background-image: url("https://res.cloudinary.com/drlkucdog/image/upload/v1748878974/sl545g6vrorheb1hyzwn.jpg");
      background-size: contain;
      background-position: center;
    }

    .float {
      height: 60px;
      width: 60px;
      z-index: 10;
      border-radius: 50px;
      box-shadow: 2px 2px 3px #999;
    }

    .label-container {
      position: relative;
      top: 5px;
      left: 70px;
      display: table;
      visibility: hidden;
    }

    .label-text {
      color: #FFF;
      background: rgba(51,51,51,0.5);
      display: table-cell;
      vertical-align: middle;
      padding: 10px;
      border-radius: 3px;
    }

    .label-arrow {
      display: table-cell;
      vertical-align: middle;
      color: #333;
      opacity: 0.5;
      transform: scaleX(-1);
    }

    a.float + div.label-container {
      visibility: hidden;
      opacity: 0;
      transition: visibility 0s, opacity 0.5s ease;
    }

    a.float:hover + div.label-container {
      visibility: visible;
      opacity: 1;
    }

    .loading {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #000;
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .circle {
      width: 20px;
      height: 20px;
      margin: 10px;
      border-radius: 50%;
      animation: loader-animation 0.75s ease infinite;
    }

    .circle:nth-child(1) {
      background-color: #D90429;
      animation-delay: 0s;
    }

    .circle:nth-child(2) {
      background-color: #FFA300;
      animation-delay: 0.15s;
    }

    .circle:nth-child(3) {
      background-color: #048BA8;
      animation-delay: 0.3s;
    }

    @keyframes loader-animation {
      0% {
        transform: scale(0);
        opacity: 0.7;
      }
      100% {
        transform: scale(1);
        opacity: 0;
      }
    }

    .top-quality {
      position: absolute;
      top: 10px;
      right: 10px;
      background: rgba(0, 0, 0, 0.6);
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
  <video id="videoContainer" class="plyr" playsinline controls crossorigin>
    <source src="{{ video_url }}">
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
    const video = document.getElementById("videoContainer");
    const qualitySelect = document.getElementById('qualitySelect');
    let hls;

    function loadStream(url) {
      if (hls) { hls.destroy(); hls = null; }
      if (Hls.isSupported()) {
        hls = new Hls();
        hls.loadSource(url);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, () => video.play());
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = url;
        video.addEventListener('loadedmetadata', () => video.play());
      }
    }

    document.addEventListener("DOMContentLoaded", function () {
      // Hide loader
      document.getElementById('loading').style.display = 'none';

      // Initialize Plyr
      const player = new Plyr(video, {
        quality: { forced: true }
      });
      window.player = player;

      // Load the video stream
      loadStream(qualitySelect.value);

      // Change video quality
      qualitySelect.addEventListener('change', () => {
        loadStream(qualitySelect.value);
      });

      // Play/Pause button
      document.getElementById("playPause").addEventListener("click", () => {
        if (video.paused) {
          video.play();
        } else {
          video.pause();
        }
      });

      // Mute/Unmute button
      document.getElementById("mute").addEventListener("click", () => {
        video.muted = !video.muted;
      });

      // Volume control
      document.getElementById("volume").addEventListener("input", () => {
        video.volume = document.getElementById("volume").value;
      });

      // Fullscreen button
      document.getElementById("fullscreen").addEventListener("click", () => {
        if (video.requestFullscreen) {
          video.requestFullscreen();
        } else if (video.webkitRequestFullscreen) {
          video.webkitRequestFullscreen();
        }
      });

      // Seek bar
      document.getElementById("seek").addEventListener("input", () => {
        video.currentTime = (document.getElementById("seek").value / 100) * video.duration;
      });

      // Playback speed
      document.getElementById("speed").addEventListener("change", (event) => {
        video.playbackRate = parseFloat(event.target.value);
      });

      // Update time display
      video.addEventListener('timeupdate', () => {
        document.getElementById("currentTime").textContent = formatTime(video.currentTime);
        document.getElementById("seek").value = (video.currentTime / video.duration) * 100;
        document.getElementById("duration").textContent = formatTime(video.duration);
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
