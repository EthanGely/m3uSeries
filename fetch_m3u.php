<?php
header('Content-Type: application/json');

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
if (strlen($search) < 3) {
    echo json_encode([]);
    exit;
}

// Load most recent cached file
$files = glob(__DIR__ . '/cache/playlist-*.m3u');
rsort($files);
$cachedFile = $files[0] ?? null;

if (!$cachedFile || !file_exists($cachedFile)) {
    echo json_encode(['error' => 'Playlist not available']);
    exit;
}

// Stream/parse M3U and filter
$results = [];
$title = '';
$maxResults = 50;

$handle = fopen($cachedFile, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $line = trim($line);
        if (stripos($line, '#EXTINF:') === 0) {
            // Extract tvg-logo if present
            $tvgLogo = null;
            if (preg_match('/tvg-logo="([^"]+)"/i', $line, $matches)) {
                $tvgLogo = $matches[1];
            }
            $parts = explode(',', $line, 2);
            $title = isset($parts[1]) ? trim($parts[1]) : '';
        } elseif ($title && filter_var($line, FILTER_VALIDATE_URL)) {
            if (stripos($title, $search) !== false) {
                $results[] = [
                    'title' => $title,
                    'url' => $line,
                    'image' => $tvgLogo ?? null
                ];
                if (count($results) >= $maxResults) break;
            }
            $title = ''; // reset
            $tvgLogo = null; // reset
        }
    }
    fclose($handle);
}

echo json_encode($results);
