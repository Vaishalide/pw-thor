<?php
// Determine video URL and title from POST (preferred) or fallback to GET (optional).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $videoUrl = isset($_POST['videoUrl']) ? $_POST['videoUrl'] : '';
    $title    = isset($_POST['title'])    ? $_POST['title']    : '';
} else {
    // Fallback only if someone tries to hit video.php directly with query parameters.
    // If you don’t want any GET-based fallback, you can remove this entire else block.
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
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($title ?: 'Video Player'); ?></title>
  <style>
    /* Basic Reset */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; background: #000; color: #fff; font-family: sans-serif; }
    #player { position: relative; width: 100%; max-width: 800px; margin: 20px auto; background: #111; }
    video { width: 100%; height: auto; background: #000; }
    #controls {
      position: absolute; bottom: 0; left: 0; width: 100%;
      display: flex; flex-wrap: wrap; align-items: center;
      background: rgba(0,0,0,0.6); padding: 8px;
    }
    #controls button, #controls input[type="range"] {
      margin-right: 8px; background: none; border: none; color: #fff; font-size: 16px; cursor: pointer;
    }
    #controls input[type="range"] { width: 100px; }
    #levels button, #speedsContainer button {
      margin-right: 4px; padding: 4px 8px; background: #222; border: 1px solid #444; border-radius: 4px; color: #fff; cursor: pointer;
    }
    #levels button.active, #speedsContainer button.active {
      background: #fff; color: #000; border-color: #888;
    }
    #settingsBtn { margin-left: auto; }
    #settingsPanel {
      position: absolute; top: 40px; right: 20px;
      background: #222; padding: 10px; border: 1px solid #444; border-radius: 4px;
      display: none; z-index: 10;
    }
    #settingsPanel.active { display: block; }
    #themeToggle { margin-top: 8px; cursor: pointer; }
  </style>
  <!-- HLS.js from CDN -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
</head>
<body data-theme="dark">
  <div id="player">
    <video id="video" controls muted autoplay></video>
    <div id="controls">
      <button id="playPause">❚❚</button>
      <div>
        <span id="currentTime">0:00</span> / <span id="duration">0:00</span>
      </div>
      <input type="range" id="seek" min="0" max="100" value="0" />
      <button id="mute">🔇</button>
      <input type="range" id="volume" min="0" max="1" step="0.01" value="1" />
      <button id="fullscreen">⛶</button>
      <div id="levels" style="display: flex; align-items: center; margin-left: 16px;"></div>
      <div id="speedsContainer" style="display: flex; align-items: center; margin-left: 16px;"></div>
      <button id="settingsBtn">⚙️</button>
    </div>
    <div id="settingsPanel">
      <div id="themeToggle">☀️</div>
    </div>
  </div>

  <script>
    // Grab PHP-generated URL and title
    const originalUrl = "<?php echo htmlspecialchars($videoUrl); ?>"; 
    let currentUrl = originalUrl;
    const qualities = [144, 360, 480, 720];
    const speeds = [0.5, 1, 1.5, 2];
    const video = document.getElementById('video');
    const levels = document.getElementById('levels');
    const speedsContainer = document.getElementById('speedsContainer');
    const playPause = document.getElementById('playPause');

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
      initPlayer();
    });

    function initPlayer() {
      loadStream(currentUrl);
      buildLevels();
      buildSpeeds();
      setupEvents();
    }

    function loadStream(url) {
      if (Hls.isSupported()) {
        if (window.hls) window.hls.destroy();
        window.hls = new Hls({ capLevelToPlayerSize: true });
        window.hls.loadSource(url);
        window.hls.attachMedia(video);
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = url;
      }
      video.play();
    }

    // ===== Updated buildLevels =====
    function buildLevels() {
      levels.innerHTML = '';
      // Auto (master) still points to the proxy root
      addLevelButton('Auto', originalUrl);
      qualities.forEach(q => {
        // Build “https://…/video/⟨token⟩/⟨q⟩/main.m3u8”
        const qualityUrl = originalUrl + '/' + q + '/main.m3u8';
        addLevelButton(q + 'p', qualityUrl);
      });
      highlightActive();
    }

    // ===== Updated highlightActive =====
    function highlightActive() {
      Array.from(levels.children).forEach(btn => {
        const label = btn.textContent;
        if (label === 'Auto') {
          btn.classList.toggle('active', currentUrl === originalUrl);
        } else {
          // e.g. “480p” → detect “/480/main.m3u8”
          const qStr = label.replace('p', '');
          btn.classList.toggle('active', currentUrl.includes('/' + qStr + '/main.m3u8'));
        }
      });
    }

    function buildSpeeds() {
      speedsContainer.innerHTML = '';
      speeds.forEach(s => {
        const btn = document.createElement('button');
        btn.textContent = s + 'x';
        btn.onclick = () => { video.playbackRate = s; highlightActiveSpeed(); };
        speedsContainer.appendChild(btn);
      });
      highlightActiveSpeed();
    }

    function highlightActiveSpeed() {
      Array.from(speedsContainer.children).forEach(btn => {
        btn.classList.toggle('active', btn.textContent == video.playbackRate + 'x');
      });
    }

    function addLevelButton(label, url) {
      const btn = document.createElement('button');
      btn.textContent = label;
      btn.onclick = () => switchQuality(url);
      levels.appendChild(btn);
    }

    function switchQuality(url) {
      currentUrl = url;
      loadStream(url);
      highlightActive();
    }

    function setupEvents() {
      // Play / Pause
      playPause.onclick = () => video.paused ? video.play() : video.pause();
      video.onplay = () => playPause.textContent = '❚❚';
      video.onpause = () => playPause.textContent = '►';

      // Time update
      video.ontimeupdate = () => {
        const displayedTime = Math.max(0, video.currentTime);
        const durationTime = Math.max(0, video.duration);
        document.getElementById('currentTime').textContent = fmt(displayedTime);
        document.getElementById('duration').textContent = fmt(durationTime);
        const pct = durationTime > 0 ? (displayedTime / durationTime) * 100 : 0;
        document.getElementById('seek').value = pct;
      };
      document.getElementById('seek').oninput = e => {
        const durationTime = Math.max(0, video.duration);
        video.currentTime = (e.target.value / 100) * durationTime;
      };

      // Mute / Volume
      document.getElementById('mute').onclick = () => {
        video.muted = !video.muted;
        document.getElementById('mute').textContent = video.muted ? '🔇' : '🔊';
      };
      document.getElementById('volume').oninput = e => {
        video.volume = e.target.value;
        video.muted = e.target.value == 0;
      };

      // Fullscreen
      document.getElementById('fullscreen').onclick = () => {
        if (document.fullscreenElement) {
          document.exitFullscreen();
        } else {
          document.getElementById('player').requestFullscreen();
        }
      };
      document.addEventListener('fullscreenchange', () => {
        if (document.fullscreenElement && screen.orientation && screen.orientation.lock) {
          screen.orientation.lock('landscape').catch(() => {});
        }
        if (!document.fullscreenElement && screen.orientation && screen.orientation.unlock) {
          screen.orientation.unlock();
        }
      });

      // Settings panel toggle
      document.getElementById('settingsBtn').onclick = () => {
        document.getElementById('settingsPanel').classList.toggle('active');
      };
      document.addEventListener('click', e => {
        if (document.getElementById('settingsPanel').classList.contains('active') &&
            !document.getElementById('settingsPanel').contains(e.target) &&
            e.target.id !== 'settingsBtn') {
          document.getElementById('settingsPanel').classList.remove('active');
        }
      });
      document.getElementById('themeToggle').onclick = () => {
        const next = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
        document.body.dataset.theme = next;
        document.getElementById('themeToggle').textContent = next === 'dark' ? '🌙' : '☀️';
      };
    }

    function fmt(s) {
      const m = Math.floor(s / 60);
      const sec = Math.floor(s % 60).toString().padStart(2, '0');
      return m + ':' + sec;
    }
  </script>
</body>
</html>
