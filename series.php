<?php
$serie = $_GET['serie'] ?? null;
$saison = $_GET['saison'] ?? null;

if (empty($serie) || empty($saison)) {
    http_response_code(400);
    echo "Invalid request.";
    exit;
}

$baseDir = '/var/www/html/series/downloads/';
$saison = urldecode($saison); // Decode the URL-encoded string
$filename = urldecode($serie); // Get the filename without path

$file = $baseDir . $filename . '/' . $saison;

if (file_exists($file)) {
    // Set headers to force download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $saison . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    http_response_code(404);
    echo "File not found : " . $file;
    exit;
}
