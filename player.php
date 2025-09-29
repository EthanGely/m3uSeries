<?php
session_start();

if (!isset($_GET['psw']) || $_GET['psw'] !== 'hGxKr297Ab') {
    header('Location: index.php');
    exit;
}

$url = $_GET['url'] ?? '';
$title = $_GET['title'] ?? 'Player';

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('Invalid media URL');
}

// Get file extension to determine video type
$path = parse_url($url, PHP_URL_PATH);
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

// Determine appropriate MIME type
$videoTypes = [
    'mp4' => 'video/mp4',
    'mkv' => 'video/x-matroska',
    'avi' => 'video/x-msvideo',
    'webm' => 'video/webm',
    'ogg' => 'video/ogg',
    'm3u8' => 'application/x-mpegURL',
    'ts' => 'video/MP2T'
];

$videoType = $videoTypes[$ext] ?? 'video/mp4';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>
  <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body { 
      margin: 0; 
      background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); 
      color: white; 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .header {
      background: rgba(0,0,0,0.8);
      backdrop-filter: blur(10px);
      padding: 1rem 2rem;
      border-bottom: 2px solid #e50914;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .header h1 {
      margin: 0;
      font-size: 1.5rem;
      color: #e50914;
    }
    
    .back-btn {
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.3);
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      text-decoration: none;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .back-btn:hover {
      background: rgba(255,255,255,0.2);
      color: white;
      text-decoration: none;
      transform: translateY(-2px);
    }
    
    #player-container { 
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      min-height: 0;
    }
    
    .video-wrapper {
      width: 100%;
      max-width: 1200px;
      background: #000;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 20px 40px rgba(0,0,0,0.5);
    }
    
    .video-js {
      width: 100% !important;
      height: auto !important;
      min-height: 400px;
    }
    
    .video-js .vjs-big-play-button {
      background: linear-gradient(45deg, #e50914, #f40612);
      border: none;
      border-radius: 50%;
      width: 80px;
      height: 80px;
      font-size: 2rem;
      line-height: 80px;
    }
    
    .video-js .vjs-control-bar {
      background: rgba(0,0,0,0.7);
      backdrop-filter: blur(10px);
    }
    
    .video-js .vjs-play-control:hover,
    .video-js .vjs-volume-menu-button:hover,
    .video-js .vjs-fullscreen-control:hover {
      color: #e50914;
    }
    
    .player-info {
      background: rgba(0,0,0,0.5);
      padding: 1rem 2rem;
      border-radius: 0 0 12px 12px;
    }
    
    .player-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #ffffff;
    }
    
    .player-controls {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .control-btn {
      background: linear-gradient(45deg, #007bff, #0056b3);
      border: none;
      color: white;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      text-decoration: none;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.9rem;
    }
    
    .control-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0,123,255,0.3);
      color: white;
      text-decoration: none;
    }
    
    .control-btn.download {
      background: linear-gradient(45deg, #28a745, #20c997);
    }
    
    .control-btn.download:hover {
      box-shadow: 0 8px 20px rgba(40,167,69,0.3);
    }
    
    .error-message {
      text-align: center;
      padding: 2rem;
      color: #ff6b6b;
      background: rgba(255,107,107,0.1);
      border-radius: 12px;
      margin: 2rem;
      border: 1px solid rgba(255,107,107,0.3);
    }
    
    .loading {
      text-align: center;
      padding: 2rem;
      color: #999;
    }
    
    .loading i {
      animation: spin 2s linear infinite;
      font-size: 2rem;
      margin-bottom: 1rem;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    @media (max-width: 768px) {
      .header {
        padding: 1rem;
      }
      
      .header h1 {
        font-size: 1.2rem;
      }
      
      #player-container {
        padding: 1rem;
      }
      
      .video-js {
        min-height: 250px;
      }
      
      .player-controls {
        flex-direction: column;
        gap: 0.5rem;
      }
      
      .control-btn {
        text-align: center;
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <h1><i class="fas fa-play-circle me-2"></i>Video Player</h1>
    <a href="javascript:history.back()" class="back-btn">
      <i class="fas fa-arrow-left"></i>Back
    </a>
  </div>

  <div id="player-container">
    <div class="video-wrapper">
      <div class="loading" id="loading">
        <i class="fas fa-spinner"></i>
        <p>Loading video player...</p>
      </div>
      
      <video
        id="my-player"
        class="video-js vjs-default-skin"
        controls
        preload="auto"
        width="100%"
        height="600"
        data-setup='{"responsive": true, "fluid": true}'
        style="display: none;"
      >
        <source src="<?= htmlspecialchars($url) ?>" type="<?= $videoType ?>">
        <p class="vjs-no-js">
          To view this video please enable JavaScript, and consider upgrading to a web browser that
          <a href="https://videojs.com/html5-video-support/" target="_blank">
            supports HTML5 video
          </a>.
        </p>
      </video>
      
      <div class="player-info">
        <div class="player-title"><?= htmlspecialchars($title) ?></div>
        <div class="player-controls">
          <a href="download.php?url=<?= urlencode($url) ?>&title=<?= urlencode($title) ?>&psw=<?= urlencode($_GET['psw']) ?>" 
             target="_blank" class="control-btn download">
            <i class="fas fa-download"></i>Download
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Video.js -->
  <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>
  <!-- For HLS streaming -->
  <script src="https://cdn.jsdelivr.net/npm/videojs-http-streaming@3.2.0/dist/videojs-http-streaming.min.js"></script>
  <!-- For additional codec support -->
  <script src="https://cdn.jsdelivr.net/npm/videojs-contrib-hls@5.15.0/dist/videojs-contrib-hls.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const loading = document.getElementById('loading');
      const videoElement = document.getElementById('my-player');
      
      try {
        // Initialize Video.js player with enhanced options
        const player = videojs('my-player', {
          responsive: true,
          fluid: true,
          playbackRates: [0.5, 1, 1.25, 1.5, 2],
          html5: {
            vhs: {
              enableLowInitialPlaylist: true,
              smoothQualityChange: true,
              overrideNative: !videojs.browser.IS_SAFARI
            },
            nativeVideoTracks: false,
            nativeAudioTracks: false,
            nativeTextTracks: false
          },
          techOrder: ['html5', 'flash'],
          sources: [{
            src: <?= json_encode($url) ?>,
            type: <?= json_encode($videoType) ?>
          }]
        });

        player.ready(() => {
          loading.style.display = 'none';
          videoElement.style.display = 'block';
          console.log('Player is ready');
          
          // Enable audio track selection if multiple exist
          const audioTracks = player.audioTracks();
          if (audioTracks && audioTracks.length > 1) {
            console.log(`Found ${audioTracks.length} audio tracks`);
          }

          // Handle text tracks for subtitles
          const textTracks = player.textTracks();
          for (let i = 0; i < textTracks.length; i++) {
            console.log(`Text track ${i}: ${textTracks[i].language} - ${textTracks[i].label}`);
          }
        });

        // Error handling
        player.on('error', function(e) {
          console.error('Player error:', e);
          const error = player.error();
          let errorMessage = 'An error occurred while loading the video.';
          
          if (error) {
            switch(error.code) {
              case 1:
                errorMessage = 'Video loading aborted by user.';
                break;
              case 2:
                errorMessage = 'Network error - check your connection.';
                break;
              case 3:
                errorMessage = 'Video format not supported or corrupted.';
                break;
              case 4:
                errorMessage = 'Video source not found or not accessible.';
                break;
              default:
                errorMessage = `Player error (${error.code}): ${error.message || 'Unknown error'}`;
            }
          }
          
          loading.innerHTML = `
            <div class="error-message">
              <i class="fas fa-exclamation-triangle"></i>
              <h3>Playback Error</h3>
              <p>${errorMessage}</p>
              <p>Try downloading the file instead or check if the source is still available.</p>
            </div>
          `;
        });

        // Loading events
        player.on('loadstart', () => {
          console.log('Started loading video');
        });
        
        player.on('canplay', () => {
          console.log('Video can start playing');
          loading.style.display = 'none';
          videoElement.style.display = 'block';
        });
        
        player.on('playing', () => {
          console.log('Video is playing');
        });

        // Debugging info
        player.on('loadedmetadata', () => {
          console.log(`Video dimensions: ${player.videoWidth()}x${player.videoHeight()}`);
          console.log(`Video duration: ${player.duration()} seconds`);
        });

      } catch (error) {
        console.error('Failed to initialize player:', error);
        loading.innerHTML = `
          <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Player Initialization Failed</h3>
            <p>Could not initialize the video player.</p>
            <p>Try downloading the file instead.</p>
          </div>
        `;
      }
    });
  </script>
</body>
</html>