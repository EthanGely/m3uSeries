<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== 'hGxKr297Ab') {
    header('Location: index.php');
    exit;
}

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
                    if (strcasecmp($currentSeriesName, $seriesName) === 0) {
                        $tvgLogo = null;
                        if (preg_match('/tvg-logo="([^"]+)"/i', $extinf, $logoMatches)) {
                            $tvgLogo = $logoMatches[1];
                        }
                        
                        $episodes[] = [
                            'title' => $title,
                            'url' => $line,
                            'season' => (int)$matches[2],
                            'episode' => (int)$matches[3],
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($seriesName) ?> - Episodes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .header {
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e50914;
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
        
        .btn-favorite {
            background: linear-gradient(45deg, #e50914, #f40612);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-favorite:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 9, 20, 0.4);
            color: white;
        }
        
        .btn-favorite.is-favorite {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #333;
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
        
        .back-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
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
            <?php if (!empty($episodes[0]['image'])): ?>
                <img src="<?= htmlspecialchars($episodes[0]['image']) ?>" alt="<?= htmlspecialchars($seriesName) ?>" class="series-poster">
            <?php else: ?>
                <div class="series-poster d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.1);">
                    <i class="fas fa-tv fa-3x text-muted"></i>
                </div>
            <?php endif; ?>
            
            <div class="series-info flex-grow-1">
                <h1><?= htmlspecialchars($seriesName) ?></h1>
                <p class="text-muted mb-3">
                    <?= count($seasons) ?> season<?= count($seasons) > 1 ? 's' : '' ?> • 
                    <?= count($episodes) ?> episode<?= count($episodes) > 1 ? 's' : '' ?>
                </p>
                <button class="btn btn-favorite" onclick="toggleFavorite()">
                    <i class="fas fa-heart me-2"></i>
                    <span id="favorite-text">Add to Favorites</span>
                </button>
            </div>
        </div>

        <?php if (empty($seasons)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No episodes found for this series.
            </div>
        <?php else: ?>
            <?php foreach ($seasons as $seasonNum => $seasonEpisodes): ?>
                <div class="season-section">
                    <h2 class="season-title">
                        <i class="fas fa-play-circle"></i>
                        Season <?= $seasonNum ?>
                    </h2>
                    
                    <div class="row">
                        <?php foreach ($seasonEpisodes as $episode): ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="episode-card">
                                    <div class="episode-number">Episode <?= $episode['episode'] ?></div>
                                    <div class="episode-title"><?= htmlspecialchars($episode['title']) ?></div>
                                    <div class="episode-actions">
                                        <a href="player.php?url=<?= urlencode($episode['url']) ?>&title=<?= urlencode($episode['title']) ?>&psw=<?= urlencode($_SESSION['loggedin']) ?>" 
                                           target="_blank" class="btn btn-play btn-sm">
                                            <i class="fas fa-play me-1"></i>Play
                                        </a>
                                        <a href="download.php?url=<?= urlencode($episode['url']) ?>&title=<?= urlencode($episode['title']) ?>&psw=<?= urlencode($_SESSION['loggedin']) ?>" 
                                           target="_blank" class="btn btn-download btn-sm">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        let isFavorite = false;
        const seriesName = <?= json_encode($seriesName) ?>;
        const seriesData = {
            type: 'series',
            series_name: seriesName,
            image: <?= json_encode($episodes[0]['image'] ?? null) ?>,
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
    </script>
</body>
</html>