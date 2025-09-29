<?php
// Replace with your TMDB API key
$apiKey = '61dac66f8f6aecb3498d73f244d18c91';
$seriesName = isset($_GET['name']) ? urlencode($_GET['name']) : '';

if (empty($seriesName)) {
    echo "Please provide a TV series name using ?name=... in the URL.";
    exit;
}

// TMDB API: Search for TV series
$apiUrl = "https://api.themoviedb.org/3/search/tv?api_key=$apiKey&query=$seriesName";

// Fetch data
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

// Check for results
if (!empty($data['results'][0]['poster_path'])) {
    $posterPath = $data['results'][0]['poster_path'];
    $imageUrl = "https://image.tmdb.org/t/p/w500" . $posterPath;
    $title = $data['results'][0]['name'];
    $firstAirDate = $data['results'][0]['first_air_date'];

    echo "<h2>Poster for: " . htmlspecialchars($title) . " (" . htmlspecialchars($firstAirDate) . ")</h2>";
    echo "<img src='$imageUrl' alt='TV Series Poster'>";
} else {
    echo "No image found for " . htmlspecialchars(urldecode($seriesName));
}
?>

