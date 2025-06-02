<?php
// video.php

// Start session if you plan to use session-based methods in the future.
// session_start();

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
    header('Location: index.html');
    exit;
}

// Safely escape for embedding in HTML/JS
$escapedVideoUrl = addslashes($videoUrl);
$escapedTitle    = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $escapedTitle; ?></title>
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <style>
    body {
      background: #0e0f1b;
      color: #fff;
      margin: 0;
      font-family: 'Poppins', sans-serif;
    }
    #videoPlayer {
      width: 100%;
      max-width: 800px;
      margin: 2rem auto;
      display: block;
    }
    h1 {
      text-align: center;
      margin-top: 1rem;
      font-size: 1.5rem;
    }
    .container {
      padding: 1rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1><?php echo $escapedTitle; ?></h1>
    <video id="videoPlayer" controls></video>
  </div>

  <script>
    (function() {
      const videoUrl = '<?php echo $escapedVideoUrl; ?>';
      const video = document.getElementById('videoPlayer');

      if (Hls.isSupported()) {
        const hls = new Hls();
        hls.loadSource(videoUrl);
        hls.attachMedia(video);
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = videoUrl;
      } else {
        document.body.innerHTML = '<p style="color: gold; text-align: center; margin-top: 2rem;">Your browser cannot play this video.</p>';
      }
    })();
  </script>
</body>
</html>
