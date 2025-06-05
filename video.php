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
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .player-wrapper {
      width: 90%;
      max-width: 960px;
    }
  </style>
</head>
<body>

<div class="player-wrapper">
  <video id="player" controls playsinline></video>
</div>

<!-- Plyr JS + HLS -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>

<script>
  const sources = {
    type: 'video',
    title: "<?= addslashes($title) ?>",
    sources: [
      { src: '<?= $video720 ?>', type: 'application/x-mpegURL', size: 720 },
      { src: '<?= $video480 ?>', type: 'application/x-mpegURL', size: 480 },
      { src: '<?= $video360 ?>', type: 'application/x-mpegURL', size: 360 },
      { src: '<?= $video240 ?>', type: 'application/x-mpegURL', size: 240 },
    ],
  };

  const video = document.querySelector('#player');

  if (Hls.isSupported()) {
    const hls = new Hls();
    hls.loadSource(sources.sources[0].src);
    hls.attachMedia(video);
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    video.src = sources.sources[0].src;
  }

  const player = new Plyr(video, {
    captions: { active: true, language: 'en' },
    settings: ['quality', 'speed', 'loop'],
  });
</script>

</body>
</html>
