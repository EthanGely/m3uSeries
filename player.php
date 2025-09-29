<?php
$url = $_GET['url'] ?? '';
$title = $_GET['title'] ?? 'Player';

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('Invalid media URL');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($title) ?></title>
  <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
  <style>
    body { margin: 0; background: #000; color: white; font-family: sans-serif; }
    #player-container { padding: 1rem; }
    .vjs-control-bar { font-size: 1rem; }
  </style>
</head>
<body>

<div id="player-container">
  <video
    id="my-player"
    class="video-js vjs-default-skin"
    controls
    preload="auto"
    width="100%"
    height="auto"
    data-setup='{}'
  >
    <source src="<?= htmlspecialchars($url) ?>" type="video/mp4">
    <!-- You can add VTT subtitle tracks manually here if you want -->
  </video>
</div>

<!-- Video.js -->
<script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
<!-- For HLS streaming in browsers that don’t support it natively -->
<script src="https://cdn.jsdelivr.net/npm/videojs-http-streaming@3.2.0/dist/videojs-http-streaming.min.js"></script>

<script>
  const player = videojs('my-player');

  player.ready(() => {
    // Enable audio track selection (if multiple exist)
    const audioTracks = player.audioTracks();
    if (audioTracks && audioTracks.length > 1) {
      console.log("Audio tracks found:", audioTracks.length);
    }

    // Show text tracks
    const textTracks = player.textTracks();
    for (let i = 0; i < textTracks.length; i++) {
      textTracks[i].mode = "showing";
    }
  });
</script>

</body>
</html>
