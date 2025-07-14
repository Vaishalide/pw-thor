const params = new URLSearchParams(window.location.search);
    const videoURL = params.get("playurl");

    const video = document.getElementById("video");
    const playPause = document.getElementById("playPause");
    const playIcon = document.getElementById("playIcon");
    const forward = document.getElementById("forward");
    const rewind = document.getElementById("rewind");
    const fullscreen = document.getElementById("fullscreen");
    const time = document.getElementById("time");
    const progressContainer = document.getElementById("progressContainer");
    const progressBar = document.getElementById("progressBar");
    const videoContainer = document.getElementById("videoContainer");
    const controls = document.getElementById("controls");

    function getVideoType(url) {
      if (url.endsWith(".mp4")) return "video/mp4";
      if (url.endsWith(".webm")) return "video/webm";
      if (url.endsWith(".m3u8")) return "application/x-mpegURL";
      return "video/mp4";
    }

    const source = document.createElement("source");
    source.src = videoURL;
    source.type = getVideoType(videoURL);
    video.appendChild(source);
    video.load();

    playPause.addEventListener("click", () => {
      if (video.paused) {
        video.play();
        playIcon.textContent = "❚❚";
      } else {
        video.pause();
        playIcon.textContent = "▶";
      }
    });

    forward.addEventListener("click", () => {
      video.currentTime += 10;
    });

    rewind.addEventListener("click", () => {
      video.currentTime -= 10;
    });

    fullscreen.addEventListener("click", () => {
      if (!document.fullscreenElement) {
        videoContainer.requestFullscreen();
      } else {
        document.exitFullscreen();
      }
    });

    function format(sec) {
      const h = Math.floor(sec / 3600);
      const m = Math.floor((sec % 3600) / 60);
      const s = Math.floor(sec % 60);
      return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }

    video.addEventListener("timeupdate", () => {
      time.textContent = `${format(video.currentTime)} / ${format(video.duration || 0)}`;
      const progress = (video.currentTime / video.duration) * 100;
      progressBar.style.width = `${progress}%`;
    });

    progressContainer.addEventListener("click", (e) => {
      const rect = progressContainer.getBoundingClientRect();
      const pos = (e.clientX - rect.left) / rect.width;
      video.currentTime = pos * video.duration;
    });

    // Auto-hide controls in fullscreen
    let hideTimer;
    let isFullscreen = false;

    function showControlsTemporarily() {
      controls.classList.remove("hide-controls");
      progressContainer.classList.remove("hide-controls");
      clearTimeout(hideTimer);
      if (isFullscreen) {
        hideTimer = setTimeout(() => {
          controls.classList.add("hide-controls");
          progressContainer.classList.add("hide-controls");
        }, 3000);
      }
    }

    document.addEventListener("fullscreenchange", () => {
      isFullscreen = !!document.fullscreenElement;
      showControlsTemporarily();
    });

    // Tap screen to show controls again
    document.addEventListener("click", () => {
      if (isFullscreen) showControlsTemporarily();
    });
