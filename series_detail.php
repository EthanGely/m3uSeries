<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== 'hGxKr297Ab') {
    header('Location: index.php');
    exit;
}

// Include TMDB helper functions
require_once 'includes/tmdb-helper.php';

$seriesName = $_GET['series'] ?? '';
if (empty($seriesName)) {
    header('Location: index.php');
    exit;
}

// Get episodes for this series
$episodes = [];
$files = glob(__DIR__ . '/cache/playlist-*.m3u');
rsort($files);
$cachedFile = $files[0] ?? null;

if ($cachedFile && file_exists($cachedFile)) {
    $handle = fopen($cachedFile, 'r');
    if ($handle) {
        $title = '';
        $extinf = '';
        
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (stripos($line, '#EXTINF:') === 0) {
                $extinf = $line;
                $parts = explode(',', $line, 2);
                $title = isset($parts[1]) ? trim($parts[1]) : '';
            } elseif ($title && filter_var($line, FILTER_VALIDATE_URL)) {
                // Parse series info
                if (preg_match('/^(.+?)\s*\([^)]*\)\s*(?:FHD|HD|SD)?\s*S(\d+)\s*E(\d+)$/i', $title, $matches)) {
                        $currentSeriesName = trim($matches[1]);
                        $seasonNum = (int)$matches[2];
                        $episodeNum = (int)$matches[3];
                        if (strcasecmp($currentSeriesName, $seriesName) === 0) {
                            $tvgLogo = null;
                            if (preg_match('/tvg-logo="([^"]+)"/i', $extinf, $logoMatches)) {
                                $tvgLogo = $logoMatches[1];
                            }
                            // Ensure correct season and episode assignment
                            $episodes[] = [
                                'title' => $title,
                                'url' => $line,
                                'season' => $seasonNum,
                                'episode' => $episodeNum,
                                'image' => $tvgLogo
                            ];
                        }
                }
                $title = '';
                $extinf = '';
            }
        }
        fclose($handle);
    }
}

// Group episodes by season
$seasons = [];
foreach ($episodes as $episode) {
    $seasonNum = $episode['season'];
    if (!isset($seasons[$seasonNum])) {
        $seasons[$seasonNum] = [];
    }
    $seasons[$seasonNum][] = $episode;
}

// Sort seasons and episodes
ksort($seasons);
foreach ($seasons as &$seasonEpisodes) {
    usort($seasonEpisodes, function($a, $b) {
        return $a['episode'] - $b['episode'];
    });
}

// Try to get TMDB image if no image available
$seriesImage = null;
if (!empty($episodes) && empty($episodes[0]['image'])) {
    $seriesImage = getTmdbSeriesImage($seriesName);
}

$pageTitle = htmlspecialchars($seriesName) . ' - Episodes';
include 'includes/head.php';
include 'includes/common-styles.php';
?>
<style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%) !important;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .series-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .series-poster {
            width: 150px;
            height: 225px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }
        
        .series-info h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }
        
        .season-section {
            margin-bottom: 3rem;
        }
        
        .season-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            color: #e50914;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .episode-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .episode-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            background: rgba(255,255,255,0.15);
        }
        
        .episode-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }
        
        .episode-number {
            color: #e50914;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .btn-episode {
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            margin-top: 0.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-play {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
        }
        
        .btn-play:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .btn-download {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            color: white;
        }
        
        .btn-download:hover {
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-tv me-2"></i>
                    m3u Series
                </h1>
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back to Search
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="series-header">
            <?php 
            $displayImage = $episodes[0]['image'] ?? $seriesImage;
            if (!empty($displayImage)): ?>
                <img src="<?= htmlspecialchars($displayImage) ?>" alt="<?= htmlspecialchars($seriesName) ?>" class="series-poster" id="serie-image">
            <?php else: ?>
                <div class="series-poster d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.1);">
                    <i class="fas fa-tv fa-3x text-muted"></i>
                </div>
            <?php endif; ?>
            
            <div class="series-info flex-grow-1">
                <h1><?= htmlspecialchars($seriesName) ?></h1>
                <p class="mb-3">
                    <?= count($seasons) ?> season<?= count($seasons) > 1 ? 's' : '' ?> • 
                    <?= count($episodes) ?> episode<?= count($episodes) > 1 ? 's' : '' ?>
                </p>
                <button class="btn btn-favorite" onclick="toggleFavorite()">
                    <i class="fas fa-heart me-2"></i>
                    <span id="favorite-text">Add to Favorites</span>
                </button>
            </div>
        </div>

        <?php if (empty($seasons)) { ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No episodes found for this series.
            </div>
        <?php } else { ?>
            <?php foreach ($seasons as $seasonNum => $sEpes): ?>
                <div class="season-section">
                    <h2 class="season-title">
                        <i class="fas fa-play-circle"></i>
                        Season <?= $seasonNum ?>
                    </h2>
                    
                    <div class="row">
                        <?php foreach ($sEpes as $episode): ?>
                                <?php if ($episode['season'] == $seasonNum ||true): ?>
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="episode-card">
                                            <div class="episode-number">Episode <?= $episode['episode'] ?></div>
                                            <div class="episode-title"><?= htmlspecialchars($episode['title']) ?></div>
                                            <div class="episode-actions">
                                                <?php
                                                    $supportedExts = ['mp4', 'webm', 'ogg', 'm3u8', 'ts'];
                                                    $epExt = strtolower(pathinfo(parse_url($episode['url'], PHP_URL_PATH), PATHINFO_EXTENSION));
                                                    if (in_array($epExt, $supportedExts)) {
                                                ?>
                                                    <a href="player.php?url=<?= urlencode($episode['url']) ?>&title=<?= urlencode($episode['title']) ?>&psw=<?= urlencode($_SESSION['loggedin']) ?>" 
                                                       target="_blank" class="btn btn-play btn-sm">
                                                        <i class="fas fa-play me-1"></i>Play
                                                    </a>
                                                <?php } ?>
                                                <a href="download.php?url=<?= urlencode($episode['url']) ?>&title=<?= urlencode($episode['title']) ?>&psw=<?= urlencode($_SESSION['loggedin']) ?>" 
                                                   target="_blank" class="btn btn-download btn-sm">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php } ?>
    </div>

    <script>
        let isFavorite = false;
        const seriesName = <?= json_encode($seriesName) ?>;
        const seriesData = {
            type: 'series',
            series_name: seriesName,
            image: <?= json_encode($displayImage ?? null) ?>,
            episodes: <?= json_encode($episodes) ?>
        };

        // Check if already a favorite
        function checkFavorite() {
            fetch(`favorites.php?action=check&id=${encodeURIComponent(seriesName)}`)
                .then(res => res.json())
                .then(data => {
                    isFavorite = data.is_favorite;
                    updateFavoriteButton();
                });
        }

        function updateFavoriteButton() {
            const btn = document.querySelector('.btn-favorite');
            const text = document.getElementById('favorite-text');
            const icon = btn.querySelector('i');
            
            if (isFavorite) {
                btn.classList.add('is-favorite');
                text.textContent = 'Remove from Favorites';
                icon.className = 'fas fa-heart-broken me-2';
            } else {
                btn.classList.remove('is-favorite');
                text.textContent = 'Add to Favorites';
                icon.className = 'fas fa-heart me-2';
            }
        }

        function toggleFavorite() {
            if (isFavorite) {
                // Remove from favorites
                fetch(`favorites.php?action=remove&id=${encodeURIComponent(seriesName)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            isFavorite = false;
                            updateFavoriteButton();
                        }
                    });
            } else {
                // Add to favorites
                fetch('favorites.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(seriesData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        isFavorite = true;
                        updateFavoriteButton();
                    }
                });
            }
        }

        // Initialize
        checkFavorite();
        
        // Add TMDB JavaScript
        <?= getTmdbJavaScript() ?>

        document.addEventListener('DOMContentLoaded', () => {
            const img = document.getElementById('serie-image');
            if (img) {
                img.onerror = function() {
                    handleImageError('serie-image', 'series', seriesName);
                };
                // If image is already broken before handler is set
                if (!img.complete || img.naturalWidth === 0) {
                    handleImageError('serie-image', 'series', seriesName);
                }
            }
        });
    </script>
</body>
</html>