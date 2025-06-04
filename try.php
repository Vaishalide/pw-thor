<?php
// Determine video URL and title from POST (preferred) or fallback to GET (optional).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $videoUrl = isset($_POST['videoUrl']) ? $_POST['videoUrl'] : '';
    $title    = isset($_POST['title'])    ? $_POST['title']    : '';
} else {
    // Fallback only if someone tries to hit video.php directly with query parameters.
    // If you don’t want any GET‐based fallback, you can remove this entire else block.
    $videoUrl = isset($_GET['videoUrl']) ? $_GET['videoUrl'] : '';
    $title    = isset($_GET['title'])    ? $_GET['title']    : '';
}

if (empty($videoUrl)) {
    // No video URL provided—redirect back to index or show an error.
    header('Location: my.html');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?php echo htmlspecialchars($title ?: "Video Player"); ?></title>
  <style>
    :root {
      --bg: #111;
      --fg: #eee;
      --highlight: #0066cc;
      --accent: #ff6600;
      --seek-bg: rgba(255, 255, 255, 0.2);
    }
    [data-theme="light"] {
      --bg: #f5f5f5;
      --fg: #111;
      --highlight: #0066cc;
      --accent: #ff6600;
      --seek-bg: rgba(0, 0, 0, 0.1);
    }
    body {
      margin: 0;
      background: var(--bg);
      color: var(--fg);
      font-family: Arial, sans-serif;
    }
    .player {
      position: relative;
      max-width: 800px;
      margin: 40px auto;
      background: #000;
    }
    video {
      width: 100%;
      height: auto;
      background: #000;
    }
    .controls {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      display: flex;
      align-items: center;
      padding: 4px;
      gap: 4px;
      background: rgba(0, 0, 0, 0.5);
      transition: opacity .3s;
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
      gap: 4px;
    }
    .time {
      font-size: .75em;
      width: 34px;
      text-align: center;
    }
    .seek, .volume {
      -webkit-appearance: none;
      background: transparent;
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
      margin-top: -4px;
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
    .settings-panel {
      position: absolute;
      bottom: calc(100% + 8px);
      right: 8px;
      background: var(--bg);
      border: 1px solid var(--seek-bg);
      border-radius: 4px;
      display: none;
      font-size: .75em;
      z-index: 10;
    }
    .settings-panel.active {
      display: block;
    }
    .settings-panel button {
      background: none;
      border: 1px solid var(--seek-bg);
      color: var(--fg);
      padding: 2px 4px;
      margin: 2px 1px;
      cursor: pointer;
      border-radius: 3px;
    }
    /* Overlay controls: 10s Backward, Center Play/Pause, 10s Forward */
    .overlay-controls {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: none;
      transition: opacity .3s;
    }
    .overlay-controls.hide {
      opacity: 0;
      pointer-events: none;
    }
    .overlay-controls button {
      background: rgba(0, 0, 0, 0.6);
      border: none;
      color: #fff;
      font-size: 1.5em;
      width: 50px;
      height: 50px;
      margin: 0 10px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: all;
      transition: background 0.2s;
    }
    .overlay-controls button:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    .overlay-backward::before { content: "⏪"; }
    .overlay-forward::after  { content: "⏩"; }
    .overlay-backward .seconds,
    .overlay-forward .seconds {
      position: absolute;
      font-size: 0.75em;
      top: 30px;
      color: #fff;
    }
  </style>
</head>
<body data-theme="dark">
  <div class="player" id="player">
    <video id="video" playsinline webkit-playsinline></video>
    <div class="overlay-controls hide" id="overlayControls">
      <button id="btnBackward" class="overlay-backward"><span class="seconds">10s</span></button>
      <button id="btnPlayPauseOverlay" class="overlay-play-pause"><span id="overlayPlayPauseIcon">►</span></button>
      <button id="btnForward" class="overlay-forward"><span class="seconds">10s</span></button>
    </div>
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
      <div id="settingsPanel" class="settings-panel">
        <div><strong>Quality</strong></div>
        <div id="levels"></div>
        <div><strong>Speed</strong></div>
        <div id="speeds"></div>
      </div>
    </div>
  </div>
  <script>
    const originalUrl = "<?php echo htmlspecialchars($videoUrl); ?>";
    let currentUrl = originalUrl;

    const video = document.getElementById('video');
    const controls = document.getElementById('controls');
    const playPause = document.getElementById('playPause');
    const muteBtn = document.getElementById('mute');
    const volumeSlider = document.getElementById('volume');
    const seek = document.getElementById('seek');
    const currentTimeElem = document.getElementById('currentTime');
    const durationElem = document.getElementById('duration');
    const settingsBtn = document.getElementById('settingsBtn');
    const settingsPanel = document.getElementById('settingsPanel');
    const levels = document.getElementById('levels');
    const speedsContainer = document.getElementById('speeds');
    const themeToggle = document.getElementById('themeToggle');
    const fullscreenBtn = document.getElementById('fullscreen');
    const player = document.getElementById('player');

    let hideTimeout;
    let isSeeking = false;
    const qualities = [1080, 720, 480, 360];
    const speeds = [0.5, 1, 1.5, 2];

    function showControls() {
      controls.classList.remove('hide');
      clearTimeout(hideTimeout);
      hideTimeout = setTimeout(() => controls.classList.add('hide'), 4000);
    }

    function initPlayer() {
      function formatTime(s) {
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60).toString().padStart(2, '0');
        return m + ':' + sec;
      }

      video.addEventListener('loadedmetadata', () => {
        durationElem.textContent = formatTime(video.duration);
        seek.max = Math.floor(video.duration);
      });

      video.addEventListener('timeupdate', () => {
        if (!isSeeking) {
          seek.value = Math.floor(video.currentTime);
          currentTimeElem.textContent = formatTime(video.currentTime);
        }
      });

      playPause.addEventListener('click', () => {
        if (video.paused) video.play();
        else video.pause();
      });

      video.addEventListener('play', () => {
        playPause.textContent = '❚❚';
      });
      video.addEventListener('pause', () => {
        playPause.textContent = '►';
      });
      video.addEventListener('ended', () => {
        playPause.textContent = '►';
      });

      muteBtn.addEventListener('click', () => {
        video.muted = !video.muted;
        muteBtn.textContent = video.muted ? '🔇' : '🔊';
      });

      volumeSlider.addEventListener('input', () => {
        video.volume = volumeSlider.value;
        video.muted = volumeSlider.value == 0;
        muteBtn.textContent = video.muted ? '🔇' : '🔊';
      });

      seek.addEventListener('input', () => {
        isSeeking = true;
        currentTimeElem.textContent = formatTime(seek.value);
      });
      seek.addEventListener('change', () => {
        video.currentTime = seek.value;
        isSeeking = false;
      });

      fullscreenBtn.addEventListener('click', () => {
        if (!document.fullscreenElement) {
          player.requestFullscreen();
        } else {
          document.exitFullscreen();
        }
      });

      settingsBtn.onclick = () => {
        settingsPanel.classList.toggle('active');
      };
      document.addEventListener('click', e => {
        if (!settingsPanel.contains(e.target)) {
          settingsPanel.classList.remove('active');
        }
      });
      themeToggle.onclick = () => {
        const next = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
        document.body.dataset.theme = next;
        themeToggle.textContent = next === 'dark' ? '🌙' : '☀️';
      };

      function addLevelButton(label, url) {
        const btn = document.createElement('button');
        btn.textContent = label;
        btn.onclick = () => {
          currentUrl = url;
          loadStream(currentUrl);
          highlightActive();
        };
        levels.appendChild(btn);
      }

      function addSpeedButton(s) {
        const btn = document.createElement('button');
        btn.textContent = s + 'x';
        btn.onclick = () => {
          video.playbackRate = s;
          highlightActiveSpeed();
        };
        speedsContainer.appendChild(btn);
      }

      function highlightActive() {
        Array.from(levels.children).forEach(btn => {
          const isActive = btn.textContent === 'Auto'
            ? currentUrl === originalUrl
            : btn.textContent.replace('p', '') + '/main.m3u8' === currentUrl.replace(/^.*\/(\d+)\/main\.m3u8$/, '$1/main.m3u8');
          btn.classList.toggle('active', isActive);
        });
      }

      function highlightActiveSpeed() {
        Array.from(speedsContainer.children).forEach(btn => {
          btn.classList.toggle('active', btn.textContent === video.playbackRate + 'x');
        });
      }

      function buildLevels() {
        levels.innerHTML = '';
        addLevelButton('Auto', originalUrl);
        qualities.forEach(q => {
          addLevelButton(q + 'p', originalUrl.replace(/\/(\d+)\/main\.m3u8$/, '/' + q + '/main.m3u8'));
        });
        highlightActive();
      }

      function buildSpeeds() {
        speedsContainer.innerHTML = '';
        speeds.forEach(s => {
          addSpeedButton(s);
        });
        highlightActiveSpeed();
      }

      function loadStream(url) {
        if (Hls.isSupported()) {
          if (window.hls) window.hls.destroy();
          window.hls = new Hls({ capLevelToPlayerSize: true });
          window.hls.loadSource(url);
          window.hls.attachMedia(video);
          window.hls.on(Hls.Events.MANIFEST_PARSED, () => {
            video.currentTime = 14;
            video.play();
          });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
          video.src = url;
          video.addEventListener('loadedmetadata', () => {
            video.currentTime = 14;
            video.play();
          });
        }
      }

      buildLevels();
      buildSpeeds();
      loadStream(currentUrl);
    }

    document.addEventListener('DOMContentLoaded', () => { 
      initPlayer(); 
      showControls(); 
    });
    player.addEventListener('mousemove', showControls);
    player.addEventListener('touchstart', showControls);

    // ----- Overlay 10s Back / Play-Pause / 10s Forward -----
    const overlayControls = document.getElementById('overlayControls');
    const btnBackward = document.getElementById('btnBackward');
    const btnForward = document.getElementById('btnForward');
    const btnPlayPauseOverlay = document.getElementById('btnPlayPauseOverlay');
    const overlayIcon = document.getElementById('overlayPlayPauseIcon');
    let overlayHideTimeout;

    // Update overlay play/pause icon based on video state
    function updateOverlayIcon() {
      overlayIcon.textContent = (video.paused || video.ended) ? '►' : '❚❚';
    }

    btnBackward.addEventListener('click', e => {
      e.stopPropagation();
      video.currentTime = Math.max(0, video.currentTime - 10);
    });
    btnForward.addEventListener('click', e => {
      e.stopPropagation();
      video.currentTime = Math.min(video.duration, video.currentTime + 10);
    });
    btnPlayPauseOverlay.addEventListener('click', e => {
      e.stopPropagation();
      if (video.paused || video.ended) {
        video.play();
      } else {
        video.pause();
      }
      updateOverlayIcon();
    });

    video.addEventListener('play', updateOverlayIcon);
    video.addEventListener('pause', updateOverlayIcon);
    video.addEventListener('ended', updateOverlayIcon);

    // Integrate overlay show/hide with existing controls hide/show
    const originalShowControls = showControls;
    function showAllControls() {
      originalShowControls();
      overlayControls.classList.remove('hide');
      clearTimeout(overlayHideTimeout);
      overlayHideTimeout = setTimeout(() => overlayControls.classList.add('hide'), 4000);
    }
    // Override showControls function
    showControls = showAllControls;
    // Initially show overlay controls
    document.addEventListener('DOMContentLoaded', () => {
      overlayControls.classList.remove('hide');
    });

    function fmt(s) { const m=Math.floor(s/60), sec=Math.floor(s%60).toString().padStart(2,'0'); return m+':'+sec; }
  </script>
</body>
</html>
