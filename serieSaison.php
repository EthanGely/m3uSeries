<?php
$serie = isset($_GET['serie']) ? $_GET['serie'] : '';
if (empty($serie)) {
    die('Série non spécifiée.');
}

$serieExists = file_exists('/var/www/html/series/downloads/' . $serie);
if (!$serieExists) {
    die('Série introuvable.');
}

$saisons = scandir('/var/www/html/series/downloads/' . $serie);

// Placeholder for serie image
$serieImage = "https://via.placeholder.com/600x350?text=" . urlencode($serie);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Series (saisons)</title>
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
        .serie-image-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .serie-image {
            width: 100%;
            max-width: 600px;
            height: 350px;
            object-fit: cover;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            border: 4px solid #fff;
        }
        .card {
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: transform 0.15s, box-shadow 0.15s;
            border-radius: 1rem;
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
        .btn-primary {
            width: 100%;
        }
        .back-btn {
            margin-top: 2rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="page-title">
            <i class="fas fa-tv fa-2x"></i>
            <h1 class="d-inline align-middle">Liste des Saisons</h1>
        </div>
        <div class="serie-image-container">
            <img src="<?= htmlspecialchars($serieImage) ?>" alt="Image de la série" class="serie-image">
        </div>
        <div class="row mt-4">
            <?php foreach ($saisons as $saison): ?>
                <?php if ($saison !== '.' && $saison !== '..'): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($saison); ?></h5>
                                <a target="_blank" href="series.php?serie=<?= urlencode($serie) ?>&saison=<?= urlencode($saison) ?>" class="btn btn-primary">Télécharger</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <div class="row back-btn">
            <div class="col-md-12 text-center">
                <a href="serieList.php" class="btn btn-secondary">Retour à la liste des séries</a>
            </div>
        </div>
    </div>
</body>

</html>