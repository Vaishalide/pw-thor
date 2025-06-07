<?php
$video720 = $_GET['videoUrl']  ?? '';
$video480 = $_GET['videoUrl1'] ?? '';
$video360 = $_GET['videoUrl2'] ?? '';
$video240 = $_GET['videoUrl3'] ?? '';
$title    = $_GET['title']     ?? 'Video Player';
?>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <link rel="stylesheet" href="https://cdn.plyr.io/3.6.12/plyr.css" />
        <!-- add Hls.js -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<!-- then Plyr.js -->
<script src="https://cdn.plyr.io/3.6.12/plyr.js"></script>

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

            .label-container{
                position: relative;
                top: 5px;
                left:70px;
                display:table;
                visibility: hidden;
            }

            .label-text{
                color:#FFF;
                background:rgba(51,51,51,0.5);
                display:table-cell;
                vertical-align:middle;
                padding:10px;
                border-radius:3px;
            }

            .label-arrow{
                display:table-cell;
                vertical-align:middle;
                color:#333;
                opacity:0.5;
                transform: scaleX(-1);
            }

            a.float + div.label-container {
              visibility: hidden;
              opacity: 0;
              transition: visibility 0s, opacity 0.5s ease;
            }

            a.float:hover + div.label-container{
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
            }

            .loading {
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

            select {
                margin-top: 20px;
                padding: 10px;
                color: #fff;
                background-color: #333;
                border: none;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div id="loading" class="loading">
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
        </div>
        <video controls crossorigin playsinline id="videoContainer" style="background-color: #0A0909;">
            <source src="{{ video_url }}">
        </video>
        
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const videoUrl = urlParams.get('videoUrl');  // 720p
            const videoUrl1 = urlParams.get('videoUrl1');  // 480p
            const videoUrl2 = urlParams.get('videoUrl2');  // 360p
            const videoUrl3 = urlParams.get('videoUrl3');  // 240p

            const video = document.getElementById("videoContainer");
            
            // Default video URL to 720p
            video.src = videoUrl;

            // Quality selection UI
            const qualitySelect = document.createElement('select');
            const qualities = [
                { label: "720p", value: videoUrl },
                { label: "480p", value: videoUrl1 },
                { label: "360p", value: videoUrl2 },
                { label: "240p", value: videoUrl3 }
            ];

            qualities.forEach(quality => {
                const option = document.createElement('option');
                option.value = quality.value;
                option.text = quality.label;
                qualitySelect.appendChild(option);
            });

            // Add to the page
            document.body.appendChild(qualitySelect);

            // Event listener to switch quality
            qualitySelect.addEventListener('change', function(e) {
                video.src = e.target.value;
                video.play();
            });

            const defaultOptions = {
                controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'rewind', 'fast-forward', 'settings', 'fullscreen', 'keyboard'],
                autoplay: true,
                captions: { active: true, update: true },
                speed: { options: [0.5, 1, 1.25, 1.5, 1.75, 2, 2.5, 3] }
            };
             function loadStream(url) {
      if (hlsInstance) {
        hlsInstance.destroy();
        hlsInstance = null;
      }
      if (!url) return;
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

    // Format seconds → M:SS
    function formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

function setupHLS(url) {
                if (Hls.isSupported()) {
                    const hls = new Hls();
                    hls.loadSource(url);
                    hls.attachMedia(video);
                    hls.on(Hls.Events.MANIFEST_PARSED, function () {
                        loader.style.display = "none";
                        video.play();
                    });
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = url;
                    video.addEventListener('loadedmetadata', function () {
                        loader.style.display = "none";
                        video.play();
                    });
                } else {
                    loader.innerText = "This browser does not support HLS.";
                }
            }

            setupHLS(defaultSource);

            // Initialize Plyr
            const player = new Plyr(video);
            const initializePlayer = () => {
                const bodyElement = document.querySelector("body");
                const loadingElement = document.getElementById("loading");
                loadingElement.style.display = "none";
                bodyElement.style.visibility = "visible";
                
                defaultOptions.previewThumbnails = {
                    enabled: true,
                    src: videoUrl
                };

                defaultOptions.tooltips = { controls: true, seek: true };
                
                const player = new Plyr(video, defaultOptions);
                window.player = player;
            };

            if (videoUrl.includes('/video/')) {
                if (Hls.isSupported()) {
                    const hls = new Hls();
                    hls.loadSource(videoUrl);
                    hls.attachMedia(video);
                    
                    hls.on(Hls.Events.MANIFEST_PARSED, function (event, data) {
                        const availableQualities = hls.levels.map((l) => l.height);
                        availableQualities.unshift(0);
                        
                        defaultOptions.quality = {
                            default: 0,
                            options: availableQualities,
                            forced: true,
                            onChange: function(newQuality) {
                                if (newQuality === 0) {
                                    hls.currentLevel = -1;
                                } else {
                                    hls.levels.forEach((level, levelIndex) => {
                                        if (level.height === newQuality) {
                                            hls.currentLevel = levelIndex;
                                        }
                                    });
                                }
                            },
                        };
                        
                        initializePlayer();
                    });
                    
                    window.hls = hls;
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = videoUrl;
                    initializePlayer();
                }
            } else if (videoUrl.includes('.mpd')) {
                const dash = dashjs.MediaPlayer().create();
                dash.initialize(video, videoUrl, false);
                dash.updateSettings({
                    streaming: {
                        abr: {
                            autoSwitchBitrate: {
                                audio: false,
                                video: false
                            }
                        },
                        fastSwitchEnabled: true,
                        lowLatencyEnabled: true
                    }
                });
                dash.on("streamInitialized", function() {
                    const availableQualities = dash.getBitrateInfoListFor("video").map((l) => l.height);
                    defaultOptions.quality = {
                        default: availableQualities[0].height,
                        options: availableQualities,
                        forced: true,
                        onChange: function(newQuality) {
                            dash.getBitrateInfoListFor("video").forEach((level, levelIndex) => {
                                if (level.height === newQuality) {
                                    dash.setQualityFor("video", level.qualityIndex);
                                }
                            });
                        },
                    };
                    initializePlayer();
                });
                dash.attachView(video);
                window.dash = dash;
            } else {
                video.src = videoUrl;
                initializePlayer();
            }
        });
        </script>
    </body>
</html>
