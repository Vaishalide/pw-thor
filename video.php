<?php
$video720 = $_GET['videoUrl'] ?? '';
$video480 = $_GET['videoUrl1'] ?? '';
$video360 = $_GET['videoUrl2'] ?? '';
$video240 = $_GET['videoUrl3'] ?? '';
$title    = $_GET['title'] ?? 'Lecture Video';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>

  <!-- Plyr CSS -->
  <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #111;
      color: #fff;
      font-family: Arial, sans-serif;
    }

    .top-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #1e1e1e;
      padding: 10px 20px;
    }

    .top-bar a {
      color: #00aced;
      text-decoration: none;
      font-weight: bold;
      font-size: 16px;
    }

    .top-bar h1 {
      color: #fff;
      font-size: 18px;
      margin: 0;
      flex-grow: 1;
      text-align: center;
    }

    .player-wrapper {
      width: 100%;
      max-width: 960px;
      margin: 0 auto;
      padding: 20px;
    }

    video {
      width: 100%;
      border-radius: 10px;
    }
  </style>
</head>
<body>

  <!-- 🔝 Top Bar -->
  <div class="top-bar">
    <a href="javascript:history.back()">← Back</a>
    <h1><?= htmlspecialchars($title) ?></h1>
    <div style="width: 60px;"></div> <!-- empty div to balance spacing -->
  </div>

  <!-- 🎥 Video Player -->
  <div class="player-wrapper">
    <video id="player" controls playsinline></video>
  </div>

  <!-- Plyr + HLS -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>

  <script>
    const sources = [
      { label: '720p', src: '<?= $video720 ?>', size: 720 },
      { label: '480p', src: '<?= $video480 ?>', size: 480 },
      { label: '360p', src: '<?= $video360 ?>', size: 360 },
      { label: '240p', src: '<?= $video240 ?>', size: 240 },
    ].filter(video => video.src); // only include sources that exist

    const video = document.getElementById('player');

    if (Hls.isSupported()) {
      const hls = new Hls();
      hls.loadSource(sources[0].src);
      hls.attachMedia(video);

      hls.on(Hls.Events.MANIFEST_PARSED, function () {
        // Auto fullscreen on desktop
        if (window.innerWidth > 1024) {
          const requestFullScreen = video.requestFullscreen || video.webkitRequestFullscreen || video.mozRequestFullScreen || video.msRequestFullscreen;
          if (requestFullScreen) requestFullScreen.call(video);
        }
      });
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
      video.src = sources[0].src;
    }

    const player = new Plyr(video, {
      captions: { active: true, language: 'en' },
      settings: ['quality', 'speed', 'loop'],
    });
  </script>

</body>
</html>
