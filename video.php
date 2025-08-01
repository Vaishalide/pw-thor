<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRM Video Player</title>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #111;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 20px;
        }


        .player-container {
            max-width: 900px;
            width: 100%;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }

        .video-wrapper {
            position: relative;
            width: 100%;
            background: #000;
        }

        video {
            width: 100%;
            display: block;
        }

        .controls-overlay {
            position: absolute;
            pointer-events: none;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 2;
        }

        .video-wrapper:hover .controls-overlay,
        .controls-overlay.visible {
            opacity: 1;
        }

        .top-bar,
        .bottom-bar {
            padding: 15px;
            display: flex;
            align-items: center;
        }

        .top-bar {
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0));
            pointer-events: auto;
            /* ✅ clickable */
            z-index: 10;
        }

        .bottom-bar {
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0));
            flex-direction: column;
            padding-bottom: 10px;
        }

        .control-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            transition: transform 0.2s ease;
            pointer-events: auto;
            z-index: 9999;



        }

        .control-btn:hover {
            transform: scale(1.1);
        }

        .top-bar .spacer {
            flex-grow: 1;
        }

        .progress-bar-wrapper {
            width: 100%;
            padding: 0 5px;
            margin-bottom: 10px;
        }

        #progress-bar {
            width: 100%;
            -webkit-appearance: none;
            appearance: none;
            height: 5px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 5px;
            cursor: pointer;
        }

        #progress-bar::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 15px;
            height: 15px;
            background: #ff0000;
            border-radius: 50%;
            cursor: pointer;
        }

        #progress-bar::-moz-range-thumb {
            width: 15px;
            height: 15px;
            background: #ff0000;
            border-radius: 50%;
            cursor: pointer;
            border: none;
        }

        .controls-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            align-items: center;
        }

        .left-controls,
        .right-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .volume-container {
            display: flex;
            align-items: center;
        }

        #volume-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 4px;
            transition: width 0.3s ease;
            cursor: pointer;
        }


        .volume-container:hover #volume-slider {
            width: 80px;
        }

        #volume-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 12px;
            height: 12px;
            background: #fff;
            border-radius: 50%;
            cursor: pointer;
        }

        #volume-slider::-moz-range-thumb {
            width: 12px;
            height: 12px;
            background: #fff;
            border-radius: 50%;
            cursor: pointer;
            border: none;
        }

        .time-display {
            font-size: 14px;
        }

        /* Status and Error styling */
        .status-container {
            padding: 10px 20px;
            background: #222;
        }

        .status {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            padding: 10px;
            color: white;
            font-size: 12px;
            line-height: 1.4;
            max-height: 50px;
            overflow-y: auto;
        }

        .error {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid rgba(255, 0, 0, 0.5);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 20px;
            display: none;
        }

        .error.show {
            display: block;
        }

        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 3;
            display: none;
            text-align: center;
            color: white;
            font-size: 16px;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid #fff;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Settings Menu Styles */
        .settings-menu {
            position: absolute;
            bottom: 80px;
            right: 15px;
            background: rgba(0, 0, 0, 0.9);
            border-radius: 8px;
            padding: 15px;
            min-width: 200px;
            display: none;
            z-index: 10;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .settings-menu.show {
            display: block;
        }

        .settings-item {
            margin-bottom: 15px;
        }

        .settings-item:last-child {
            margin-bottom: 0;
        }

        .settings-label {
            display: block;
            color: #fff;
            font-size: 12px;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .settings-select {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            color: #fff;
            padding: 8px 10px;
            font-size: 12px;
            cursor: pointer;
        }

        .settings-select:focus {
            outline: none;
            border-color: #ff0000;
        }

        .settings-select option {
            background: #333;
            color: #fff;
        }

        .telegram-button-wrapper {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .telegram-btn {
            display: inline-block;
            background-color: #0088cc;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .telegram-btn i {
            margin-right: 8px;
        }

        .telegram-btn:hover {
            background-color: #006fa7;
        }
    </style>
</head>

<body>
    <div class="player-container">
        <div class="video-wrapper">
            <video id="video" preload="metadata" poster="">
                Your browser does not support the video tag.
            </video>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                Loading DRM content...
            </div>


            <div class="controls-overlay">
                <div class="top-bar">
                    <button id="back-btn" class="control-btn"><i class="fas fa-arrow-left"></i></button>
                    <div class="spacer"></div>
                    <button id="menu-btn" class="control-btn"><i class="fas fa-ellipsis-v"></i></button>
                </div>

                <div class="bottom-bar">
                    <div class="progress-bar-wrapper">
                        <input type="range" id="progress-bar" min="0" max="100" step="0.1" value="0">
                    </div>

                    <div class="controls-row">
                        <div class="left-controls">
                            <button id="play-pause-btn" class="control-btn"><i class="fas fa-play"></i></button>
                            <button id="rewind-btn" class="control-btn"><i class="fas fa-rotate-left"></i></button>
                            <button id="forward-btn" class="control-btn"><i class="fas fa-rotate-right"></i></button>
                            <div class="volume-container">
                                <button id="volume-btn" class="control-btn"><i class="fas fa-volume-up"></i></button>
                                <input type="range" id="volume-slider" min="0" max="1" step="0.01" value="1">
                            </div>
                        </div>
                        <div class="right-controls">
                            <span id="time-display" class="time-display">00:00 / 00:00</span>
                            <button id="settings-btn" class="control-btn"><i class="fas fa-cog"></i></button>
                            <button id="pip-btn" class="control-btn"><i class="fas fa-clone"></i></button>
                            <button id="fullscreen-btn" class="control-btn"><i class="fas fa-expand"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Menu -->
            <div class="settings-menu" id="settings-menu">
                <div class="settings-item">
                    <label class="settings-label">Playback Speed</label>
                    <select class="settings-select" id="speed-select">
                        <option value="0.25">0.25x</option>
                        <option value="0.5">0.5x</option>
                        <option value="0.75">0.75x</option>
                        <option value="1" selected>1x (Normal)</option>
                        <option value="1.25">1.25x</option>
                        <option value="1.5">1.5x</option>
                        <option value="1.75">1.75x</option>
                        <option value="2">2x</option>
                    </select>
                </div>
                <div class="settings-item">
                    <label class="settings-label">Quality</label>
                    <select class="settings-select" id="quality-select">
                        <option value="auto" selected>Auto</option>
                    </select>
                </div>
            </div>
        </div>


        <div class="status-container">
            <div class="error" id="error"></div>
            <div class="status">
                <div id="statusText">Ready to load DRM content with signed segment requests</div>
            </div>
        </div>
    </div>

    <div class="telegram-button-wrapper">
        <a href="https://t.me/YourChannelUsername" target="_blank" class="telegram-btn">
            <i class="fab fa-telegram-plane"></i> Join our Telegram
        </a>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/shaka-player/4.3.6/shaka-player.compiled.min.js"></script>
    <script>
        // Elements
        const playerContainer = document.querySelector('.player-container');
        const videoWrapper = document.querySelector('.video-wrapper');
        const video = document.getElementById('video');
        const loading = document.getElementById('loading');
        const error = document.getElementById('error');
        const statusText = document.getElementById('statusText');


        // New Controls
        const controlsOverlay = document.querySelector('.controls-overlay');
        const playPauseBtn = document.getElementById('play-pause-btn');
        const rewindBtn = document.getElementById('rewind-btn');
        const forwardBtn = document.getElementById('forward-btn');
        const volumeBtn = document.getElementById('volume-btn');
        const volumeSlider = document.getElementById('volume-slider');
        const progressBar = document.getElementById('progress-bar');
        const timeDisplay = document.getElementById('time-display');
        const pipBtn = document.getElementById('pip-btn');
        const fullscreenBtn = document.getElementById('fullscreen-btn');
        const settingsBtn = document.getElementById('settings-btn');
        const settingsMenu = document.getElementById('settings-menu');
        const speedSelect = document.getElementById('speed-select');
        const qualitySelect = document.getElementById('quality-select');

        let player = null;
        let videoData = null;
        let signedParams = null;
        let streamType = 'drm'; // Default stream type
        let controlsTimeout;

        // Function to get URL parameters
        function getUrlParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Function to get configuration from URL parameters
        function getConfigFromUrl() {
            const type = getUrlParameter('type');
            const url = getUrlParameter('url');
            const keyid = getUrlParameter('keyid');
            const key = getUrlParameter('key');
            const pssh = getUrlParameter('pssh');

            console.log('URL Parameters:', { type, url, keyid, key, pssh });

            if (!url) {
                throw new Error('Missing required parameter: url is required!');
            }

            // List of potential signing parameters for CloudFront, Google Media CDN, etc.
            const signingParams = ['URLPrefix', 'Expires', 'KeyName', 'Signature', 'Key-Pair-Id', 'Policy'];

            // Handle HLS live streams
            if (type === 'live') {
                const liveUrl = new URL(url);
                const extractedParams = {};
                signingParams.forEach(param => {
                    if (liveUrl.searchParams.has(param)) {
                        extractedParams[param] = liveUrl.searchParams.get(param);
                    }
                });
                const cleanLiveUrl = new URL(liveUrl.origin + liveUrl.pathname);

                return {
                    streamType: 'live',
                    videoData: {
                        url: cleanLiveUrl.toString()
                    },
                    signedParams: extractedParams
                };
            }

            // Handle DRM streams
            if (!keyid || !key) {
                throw new Error('Missing parameters. For DRM, `keyid` and `key` are required. For live streams, add `?type=live` to the URL.');
            }

            const mpdUrl = new URL(url);
            const extractedParams = {};
            signingParams.forEach(param => {
                if (mpdUrl.searchParams.has(param)) {
                    extractedParams[param] = mpdUrl.searchParams.get(param);
                }
            });

            const cleanMpdUrl = new URL(mpdUrl.origin + mpdUrl.pathname);

            return {
                streamType: 'drm',
                videoData: {
                    drmType: "ClearKey",
                    key_strings: `--key ${keyid}:${key}`,
                    keys: [`${keyid}:${key}`],
                    pssh: pssh || "",
                    url: cleanMpdUrl.toString()
                },
                signedParams: extractedParams
            };
        }


        function initializeConfig() {
            try {
                const config = getConfigFromUrl();
                videoData = config.videoData;
                signedParams = config.signedParams;
                streamType = config.streamType;

                if (streamType === 'live') {
                    updateStatus(`Live HLS configuration loaded. URL: ${videoData.url.substring(0, 50)}...`);
                } else {
                    updateStatus(`DRM configuration loaded. MPD: ${videoData.url.substring(0, 50)}...`);
                }
                setTimeout(() => loadContent(), 1000);
            } catch (err) {
                showError(err.message);
                updateStatus('Waiting for URL parameters...');
            }
        }

        class SignedSegmentFilter {
            constructor(signedParams) {
                this.signedParams = signedParams;
            }
            apply(type, request) {
                if (type === shaka.net.NetworkingEngine.RequestType.MANIFEST ||
                    type === shaka.net.NetworkingEngine.RequestType.SEGMENT) {
                    const uri = new URL(request.uris[0]);
                    Object.keys(this.signedParams).forEach(key => {
                        uri.searchParams.set(key, this.signedParams[key]);
                    });
                    request.uris[0] = uri.toString();
                    updateStatus(`${type} request with signed params: ${uri.pathname.split('/').pop()}`);
                }
                return request;
            }
        }

        function initializePlayer() {
            shaka.polyfill.installAll();
            if (!shaka.Player.isBrowserSupported()) {
                showError('Browser not supported for playback!');
                return false;
            }
            player = new shaka.Player(video);
            player.addEventListener('error', onPlayerError);

            // Only configure DRM if it's a DRM stream
            if (streamType === 'drm' && videoData.keys) {
                player.configure({
                    drm: {
                        clearKeys: parseClearKeys(videoData.keys)
                    }
                });
            }

            // The network filter is still needed for signed URLs in both cases
            const networkingEngine = player.getNetworkingEngine();
            if (signedParams && Object.keys(signedParams).length > 0) {
                const signedFilter = new SignedSegmentFilter(signedParams);
                networkingEngine.registerRequestFilter((type, request) => signedFilter.apply(type, request));
            }
            return true;
        }

        function parseClearKeys(keys) {
            const clearKeys = {};
            keys.forEach(keyPair => {
                const [keyId, key] = keyPair.split(':');
                clearKeys[keyId] = key;
            });
            return clearKeys;
        }

        async function loadContent() {
            if (!videoData) {
                showError('No configuration found. Please provide required URL parameters.');
                updateStatus('Configuration required');
                return;
            }
            if (!player) {
                if (!initializePlayer()) return;
            }
            try {
                showLoading(true);
                if (streamType === 'live') {
                    updateStatus('Loading HLS live stream...');
                } else {
                    updateStatus('Loading DRM content with signed segment requests...');
                }
                await player.load(videoData.url);
                showLoading(false);
                updateStatus('Content loaded successfully!');
                populateQualityOptions();
            } catch (err) {
                showLoading(false);
                showError(`Failed to load content: ${err.message}`);
                updateStatus('Failed to load content');
            }
        }


        function onPlayerError(event) {
            showError('Player error: ' + event.detail.message);
            updateStatus('Player error occurred');
        }

        function showLoading(show) {
            loading.classList.toggle('show', show);
        }

        function showError(message) {
            error.textContent = message;
            error.classList.add('show');
            clearTimeout(error.timeout);
            error.timeout = setTimeout(() => {
                error.classList.remove('show');
            }, 5000);
        }

        function updateStatus(message) {
            statusText.textContent = message;
        }

        // --- NEW CONTROL LOGIC ---

        function togglePlayPause() {
            if (video.paused || video.ended) {
                video.play();
            } else {
                video.pause();
            }
        }

        function updatePlayPauseIcon() {
            const icon = playPauseBtn.querySelector('i');
            if (video.paused || video.ended) {
                icon.classList.remove('fa-pause');
                icon.classList.add('fa-play');
            } else {
                icon.classList.remove('fa-play');
                icon.classList.add('fa-pause');
            }
        }

        function seek(seconds) {
            video.currentTime += seconds;
        }

        function handleVolumeChange() {
            video.volume = volumeSlider.value;
            if (video.volume === 0) {
                video.muted = true;
            } else {
                video.muted = false;
            }
        }

        function updateVolumeIcon() {
            const icon = volumeBtn.querySelector('i');
            icon.classList.remove('fa-volume-up', 'fa-volume-down', 'fa-volume-mute');
            if (video.muted || video.volume === 0) {
                icon.classList.add('fa-volume-mute');
            } else if (video.volume < 0.5) {
                icon.classList.add('fa-volume-down');
            } else {
                icon.classList.add('fa-volume-up');
            }
        }

        function toggleMute() {
            video.muted = !video.muted;
            if (video.muted) {
                volumeSlider.value = 0;
            } else {
                volumeSlider.value = video.volume > 0 ? video.volume : 1;
            }
            updateVolumeIcon();
        }

        function updateProgress() {
            const value = (video.currentTime / video.duration) * 100;
            progressBar.value = value || 0;

            const thumbPosition = value / 100;
            progressBar.style.background = `linear-gradient(to right, #ff0000 ${thumbPosition * 100}%, rgba(255, 255, 255, 0.4) ${thumbPosition * 100}%)`;


            // Update time display
            const currentTime = formatTime(video.currentTime);
            const duration = formatTime(video.duration);
            timeDisplay.textContent = `${currentTime} / ${duration}`;
        }

        function setProgress(e) {
            const newTime = (e.target.value / 100) * video.duration;
            video.currentTime = newTime;
        }

        function formatTime(time) {
            if (isNaN(time)) return "00:00";
            const date = new Date(null);
            date.setSeconds(time);
            const hours = date.getUTCHours();
            const minutes = date.getUTCMinutes();
            const seconds = date.getUTCSeconds();


            if (hours > 0) {
                return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        function togglePictureInPicture() {
            if (document.pictureInPictureElement) {
                document.exitPictureInPicture();
            } else if (document.pictureInPictureEnabled) {
                video.requestPictureInPicture();
            }
        }

        // --- Fullscreen toggle ---
        function toggleFullscreen() {
            const targetElement = videoWrapper;

            if (!document.fullscreenElement) {
                targetElement.requestFullscreen()
                    .then(() => {
                        console.log("Entered fullscreen");

                        // ✅ Try locking orientation to landscape
                        if (screen.orientation && screen.orientation.lock) {
                            screen.orientation.lock('landscape').catch(err => {
                                console.warn("Orientation lock failed:", err.message);
                            });
                        }
                    })
                    .catch(err => {
                        console.error("Error entering fullscreen:", err);
                        showError(`Fullscreen error: ${err.message}`);
                    });
            } else {
                document.exitFullscreen()
                    .then(() => {
                        console.log("Exited fullscreen");

                        // ✅ Try unlocking orientation on exit
                        if (screen.orientation && screen.orientation.unlock) {
                            screen.orientation.unlock();
                        }
                    })
                    .catch(err => {
                        console.error("Error exiting fullscreen:", err);
                        showError(`Fullscreen exit error: ${err.message}`);
                    });
            }
        }


        function updateFullscreenIcon() {
            const icon = fullscreenBtn.querySelector('i');
            if (document.fullscreenElement) {
                icon.classList.remove('fa-expand');
                icon.classList.add('fa-compress');
            } else {
                icon.classList.remove('fa-compress');
                icon.classList.add('fa-expand');
            }
        }

        fullscreenBtn.addEventListener('click', toggleFullscreen);
        document.addEventListener('fullscreenchange', updateFullscreenIcon);


        function hideControls() {
            if (video.paused) return;
            controlsOverlay.classList.remove('visible');
        }

        function showControls() {
            controlsOverlay.classList.add('visible');
            clearTimeout(controlsTimeout);
            controlsTimeout = setTimeout(hideControls, 3000);
        }

        // Settings functionality
        function toggleSettingsMenu() {
            settingsMenu.classList.toggle('show');
        }

        function handleSpeedChange() {
            const speed = parseFloat(speedSelect.value);
            video.playbackRate = speed;
            updateStatus(`Playback speed changed to ${speed}x`);
        }

        function handleQualityChange() {
            const quality = qualitySelect.value;
            if (player && quality !== 'auto') {
                const variants = player.getVariantTracks();
                const selectedVariant = variants.find(variant =>
                    variant.height === parseInt(quality)
                );
                if (selectedVariant) {
                    player.selectVariantTrack(selectedVariant);
                    updateStatus(`Quality changed to ${quality}p`);
                }
            } else if (player && quality === 'auto') {
                player.selectVariantTrack(null);
                updateStatus('Quality set to Auto');
            }
        }

        function populateQualityOptions() {
            if (!player) return;

            const variants = player.getVariantTracks();
            const currentQuality = qualitySelect.value;

            // Clear existing options except Auto
            qualitySelect.innerHTML = '<option value="auto" selected>Auto</option>';

            // Add quality options
            const uniqueQualities = [...new Set(variants.map(v => v.height))].sort((a, b) => b - a);
            uniqueQualities.forEach(height => {
                const option = document.createElement('option');
                option.value = height;
                option.textContent = `${height}p`;
                qualitySelect.appendChild(option);
            });

            // Restore current selection if it still exists
            if (currentQuality && uniqueQualities.includes(parseInt(currentQuality))) {
                qualitySelect.value = currentQuality;
            }
        }


        // Event Listeners for new controls
        playPauseBtn.addEventListener('click', togglePlayPause);
        video.addEventListener('click', togglePlayPause);
        video.addEventListener('play', updatePlayPauseIcon);
        video.addEventListener('pause', updatePlayPauseIcon);
        video.addEventListener('ended', updatePlayPauseIcon);

        rewindBtn.addEventListener('click', () => seek(-10));
        forwardBtn.addEventListener('click', () => seek(10));

        volumeBtn.addEventListener('click', toggleMute);
        volumeSlider.addEventListener('input', handleVolumeChange);
        video.addEventListener('volumechange', updateVolumeIcon);

        video.addEventListener('timeupdate', updateProgress);
        video.addEventListener('loadedmetadata', updateProgress);
        progressBar.addEventListener('input', setProgress);

        pipBtn.addEventListener('click', togglePictureInPicture);
        fullscreenBtn.addEventListener('click', toggleFullscreen);
        document.addEventListener('fullscreenchange', updateFullscreenIcon);

        // Settings event listeners
        settingsBtn.addEventListener('click', toggleSettingsMenu);
        speedSelect.addEventListener('change', handleSpeedChange);
        qualitySelect.addEventListener('change', handleQualityChange);

        // Close settings menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!settingsMenu.contains(e.target) && !settingsBtn.contains(e.target)) {
                settingsMenu.classList.remove('show');
            }
        });

        videoWrapper.addEventListener('mousemove', () => {
            if (!video.paused) {
                showControls();
            }
        });

        video.addEventListener('play', () => {
            showControls(); // Start hiding logic when video starts
        });

        video.addEventListener('pause', () => {
            showControls(); // Keep controls visible when paused
            clearTimeout(controlsTimeout); // Don’t auto-hide
        });

        function showControls() {
            controlsOverlay.classList.add('visible');
            clearTimeout(controlsTimeout);
            if (!video.paused) {
                controlsTimeout = setTimeout(() => {
                    controlsOverlay.classList.remove('visible');
                }, 3000); // 3 seconds
            }
        }


        const backBtn = document.getElementById('back-btn');

        backBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (document.fullscreenElement) {
                // First exit fullscreen
                try {
                    await document.exitFullscreen();
                    console.log("Exited fullscreen via back button");
                } catch (err) {
                    console.error("Error exiting fullscreen:", err);
                }
            } else {
                // Not in fullscreen, go back
                history.back();
            }
        });





    </script>
</body>

</html>
