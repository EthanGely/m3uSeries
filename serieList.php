<?php
// Include TMDB helper functions
require_once 'includes/tmdb-helper.php';

$series = scandir('/var/www/html/series/downloads/');

$exclude = ['.', '..', 'pending'];
$series = array_filter($series, function ($serie) use ($exclude) {
    return !in_array($serie, $exclude);
});

$pageTitle = 'Series - m3u Series';
include 'includes/head.php';
?>
<style>
    body {
        background: #f8f9fa !important;
    }
    .page-title {
        background: #343a40;
        color: #fff;
        padding: 2rem 0 1.5rem 0;
        margin-bottom: 2rem;
        border-radius: 0 0 1rem 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        text-align: center;
    }
    .page-title i {
        margin-right: 0.5rem;
        color: #ffc107;
    }
    .card {
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .card:hover {
        transform: translateY(-5px) scale(1.03);
        box-shadow: 0 6px 24px rgba(0,0,0,0.15);
    }
    .card-title {
        font-weight: 700;
        font-size: 1.15rem;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
        text-align: center;
    }
    .card-img-top {
        height: 350px;
        object-fit: cover;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    .btn-primary {
        width: 100%;
    }
</style>
<body>
    <div class="container">
        <div class="page-title">
            <i class="fas fa-tv fa-2x"></i>
            <h1 class="d-inline align-middle">Liste des Séries</h1>
        </div>
        <div class="row mt-4">
            <?php foreach ($series as $serie): ?>
                <?php
                // Use TMDB helper function for consistency
                $seriesData = getTmdbSeriesData($serie);
                $imageUrl = $seriesData['poster_path'] ?? '';
                $title = $seriesData['name'] ?? $serie;
                ?>
                <?php if ($serie !== '.' && $serie !== '..'): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <?php if (!empty($imageUrl)): ?>
                                <img id="img_<?= md5($serie) ?>" src="<?= htmlspecialchars($imageUrl) ?>" class="card-img-top" alt="Poster for <?= htmlspecialchars($title) ?>" onerror="handleImageError('img_<?= md5($serie) ?>', 'series', '<?= htmlspecialchars($serie) ?>')">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 350px; background: rgba(255,255,255,0.1);">
                                    <i class="fas fa-tv fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($title); ?></h5>
                                <a href="serieSaison.php?serie=<?= urlencode($serie) ?>" class="btn btn-primary">
                                    <i class="fas fa-list"></i> Voir les saisons
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        <?= getTmdbJavaScript() ?>
    </script>
</body>

</html>