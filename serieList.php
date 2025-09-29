<?php
$apiKey = '61dac66f8f6aecb3498d73f244d18c91';
$series = scandir('/var/www/html/series/downloads/');

$exclude = ['.', '..', 'pending'];
$series = array_filter($series, function ($serie) use ($exclude) {
    return !in_array($serie, $exclude);
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Series</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/v4-shims.min.css">
    <style>
        body {
            background: #f8f9fa;
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
</head>

<body>
    <div class="container">
        <div class="page-title">
            <i class="fas fa-tv fa-2x"></i>
            <h1 class="d-inline align-middle">Liste des Séries</h1>
        </div>
        <div class="row mt-4">
            <?php foreach ($series as $serie): ?>
                <?php
                $apiUrl = "https://api.themoviedb.org/3/search/tv?api_key=$apiKey&query=" . urlencode($serie);
                $response = file_get_contents($apiUrl);
                $data = json_decode($response, true);

                $imageUrl = '';
                $title = '';
                if (!empty($data['results'][0]['poster_path'])) {
                    $posterPath = $data['results'][0]['poster_path'];
                    $imageUrl = "https://image.tmdb.org/t/p/w500" . $posterPath;
                    $title = $data['results'][0]['name'];
                }
                ?>
                <?php if ($serie !== '.' && $serie !== '..'): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <?php if (!empty($imageUrl)) {
                                echo "<img src='$imageUrl' class='card-img-top' alt='Poster for $title'>";
                            } ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= !empty($title) ? $title : $serie; ?></h5>
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
</body>

</html>