<?php
$video720 = $_GET['videoUrl'] ?? '';
$video480 = $_GET['videoUrl1'] ?? '';
$video360 = $_GET['videoUrl2'] ?? '';
$video240 = $_GET['videoUrl3'] ?? '';
$title = $_GET['title'] ?? 'Video';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($title); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <style>
    body {
      background: #000;
      color: #fff;
      font-family: Arial, sans-serif;
      text-align: center;
      margin: 0;
      padding: 2rem;
    }
    video {
      width: 100%;
      max-width: 800px;
      border: 2px solid #ffc107;
      border-radius: 10px;
    }
    select {
      padding: 0.5rem 1rem;
      font-size: 1rem;
      border-radius: 6px;
      margin-top: 1rem;
    }
  </style>
</head>
<body>

  <h2><?php echo htmlspecialchars($title); ?></h2>

  <video id="videoPlayer" controls autoplay></video>

  <div>
    <label for="quality">Choose Quality:</label>
    <select id="quality">
      <option value="<?php echo htmlspecialchars($video720); ?>">720p</option>
      <option value="<?php echo htmlspecialchars($video480); ?>">480p</option>
      <option value="<?php echo htmlspecialchars($video360); ?>">360p</option>
      <option value="<?php echo htmlspecialchars($video240); ?>">240p</option>
    </select>
  </div>

  <script>
    const video = document.getElementById('videoPlayer');
    const qualitySelector = document.getElementById('quality');

    function loadStream(url) {
      if (Hls.isSupported()) {
        if (window.hls) window.hls.destroy();
        const hls = new Hls();
        hls.loadSource(url);
        hls.attachMedia(video);
        window.hls = hls;
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = url;
      }
    }

    // Load initial quality (720p by default)
    loadStream(qualitySelector.value);

    // Handle quality change
    qualitySelector.addEventListener('change', function () {
      loadStream(this.value);
    });
  </script>

</body>
</html>
