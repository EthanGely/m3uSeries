<?php
header('Content-Type: application/json');

$action = $_GET['action'] ?? 'search';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Load most recent cached file
$files = glob(__DIR__ . '/cache/playlist-*.m3u');
rsort($files);
$cachedFile = $files[0] ?? null;

if (!$cachedFile || !file_exists($cachedFile)) {
    echo json_encode(['error' => 'Playlist not available']);
    exit;
}

// Function to check if item is a TV channel
function isTVChannel($extinf, $title) {
    // Check for tvg-name with channel-like content
    if (preg_match('/tvg-name="([^"]+)"/i', $extinf, $matches)) {
        $tvgName = trim($matches[1]);
        if (!empty($tvgName) && (stripos($tvgName, '|') !== false || stripos($tvgName, 'TV') !== false)) {
            return false;
        }
    }
    
    // Check for TV in group-title
    if (preg_match('/group-title="([^"]+)"/i', $extinf, $matches)) {
        $groupTitle = trim($matches[1]);
        if (stripos($groupTitle, 'TV') !== false) {
            return false;
        }
    }
    
    return false;
}

// Function to parse series info
function parseSeriesInfo($title) {
    // Pattern for series: "Serie name (Other infos) S01 E01"
    if (preg_match('/^(.+?)\s*\([^)]*\)\s*(?:FHD|HD|SD)?\s*S(\d+)\s*E(\d+)$/i', $title, $matches)) {
        return [
            'type' => 'series',
            'series_name' => trim($matches[1]),
            'season' => (int)$matches[2],
            'episode' => (int)$matches[3],
            'original_title' => $title
        ];
    }
    
    return [
        'type' => 'movie',
        'title' => $title,
        'original_title' => $title
    ];
}

// Handle different actions
if ($action === 'categories') {
    // Get all categories
    $categories = [];
    $handle = fopen($cachedFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (stripos($line, '#EXTINF:') === 0) {
                if (preg_match('/group-title="([^"]+)"/i', $line, $matches)) {
                    $category = trim($matches[1]);
                    if (!empty($category) && stripos($category, 'TV') === false) {
                        $categories[$category] = ($categories[$category] ?? 0) + 1;
                    }
                }
            }
        }
        fclose($handle);
    }
    echo json_encode($categories);
    exit;
}

if ($action === 'search') {
    if (strlen($search) < 2) {  // Reduced minimum from 3 to 2
        echo json_encode([]);
        exit;
    }

    $results = [];
    $series = [];
    $movies = [];
    $title = '';
    $extinf = '';
    $maxResults = 100;

    $handle = fopen($cachedFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (stripos($line, '#EXTINF:') === 0) {
                $extinf = $line;
                // Extract tvg-logo if present
                $tvgLogo = null;
                if (preg_match('/tvg-logo="([^"]+)"/i', $line, $matches)) {
                    $tvgLogo = $matches[1];
                }
                // Extract group-title if present
                $groupTitle = '';
                if (preg_match('/group-title="([^"]+)"/i', $line, $matches)) {
                    $groupTitle = trim($matches[1]);
                }
                
                $parts = explode(',', $line, 2);
                $title = isset($parts[1]) ? trim($parts[1]) : '';
            } elseif ($title && filter_var($line, FILTER_VALIDATE_URL)) {
                // Skip TV channels
                if (isTVChannel($extinf, $title)) {
                    $title = '';
                    $extinf = '';
                    continue;
                }
                
                $parsedInfo = parseSeriesInfo($title);
                
                // Check if title matches search (case insensitive, partial match)
                $searchMatch = false;
                if ($parsedInfo['type'] === 'series') {
                    $searchMatch = stripos($parsedInfo['series_name'], $search) !== false;
                } else {
                    $searchMatch = stripos($title, $search) !== false;
                }
                
                if ($searchMatch) {
                    $item = [
                        'title' => $title,
                        'url' => $line,
                        'image' => $tvgLogo ?? null,
                        'category' => $groupTitle,
                        'type' => $parsedInfo['type']
                    ];
                    
                    if ($parsedInfo['type'] === 'series') {
                        $seriesName = $parsedInfo['series_name'];
                        if (!isset($series[$seriesName])) {
                            $series[$seriesName] = [
                                'series_name' => $seriesName,
                                'type' => 'series',
                                'episodes' => [],
                                'image' => $tvgLogo,
                                'category' => $groupTitle
                            ];
                        }
                        $series[$seriesName]['episodes'][] = [
                            'title' => $title,
                            'url' => $line,
                            'season' => $parsedInfo['season'],
                            'episode' => $parsedInfo['episode']
                        ];
                    } else {
                        $movies[] = $item;
                    }
                    
                    if (count($series) + count($movies) >= $maxResults) break;
                }
                $title = '';
                $extinf = '';
            }
        }
        fclose($handle);
    }
    
    // Combine series and movies
    foreach ($series as $serie) {
        // Sort episodes by season and episode
        usort($serie['episodes'], function($a, $b) {
            if ($a['season'] === $b['season']) {
                return $a['episode'] - $b['episode'];
            }
            return $a['season'] - $b['season'];
        });
        $results[] = $serie;
    }
    
    $results = array_merge($results, $movies);
    echo json_encode($results);
}

if ($action === 'category') {
    $category = $_GET['category'] ?? '';
    if (empty($category)) {
        echo json_encode([]);
        exit;
    }

    $results = [];
    $series = [];
    $movies = [];
    $title = '';
    $extinf = '';
    $maxResults = 100;

    $handle = fopen($cachedFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if (stripos($line, '#EXTINF:') === 0) {
                $extinf = $line;
                $tvgLogo = null;
                if (preg_match('/tvg-logo="([^"]+)"/i', $line, $matches)) {
                    $tvgLogo = $matches[1];
                }
                $groupTitle = '';
                if (preg_match('/group-title="([^"]+)"/i', $line, $matches)) {
                    $groupTitle = trim($matches[1]);
                }
                
                $parts = explode(',', $line, 2);
                $title = isset($parts[1]) ? trim($parts[1]) : '';
            } elseif ($title && filter_var($line, FILTER_VALIDATE_URL)) {
                if (isTVChannel($extinf, $title)) {
                    $title = '';
                    $extinf = '';
                    continue;
                }
                
                // Check if item belongs to the requested category
                if (preg_match('/group-title="([^"]+)"/i', $extinf, $matches)) {
                    $itemCategory = trim($matches[1]);
                    if (strcasecmp($itemCategory, $category) === 0) {
                        $parsedInfo = parseSeriesInfo($title);
                        
                        $item = [
                            'title' => $title,
                            'url' => $line,
                            'image' => $tvgLogo ?? null,
                            'category' => $itemCategory,
                            'type' => $parsedInfo['type']
                        ];
                        
                        if ($parsedInfo['type'] === 'series') {
                            $seriesName = $parsedInfo['series_name'];
                            if (!isset($series[$seriesName])) {
                                $series[$seriesName] = [
                                    'series_name' => $seriesName,
                                    'type' => 'series',
                                    'episodes' => [],
                                    'image' => $tvgLogo,
                                    'category' => $itemCategory
                                ];
                            }
                            $series[$seriesName]['episodes'][] = [
                                'title' => $title,
                                'url' => $line,
                                'season' => $parsedInfo['season'],
                                'episode' => $parsedInfo['episode']
                            ];
                        } else {
                            $movies[] = $item;
                        }
                        
                        if (count($series) + count($movies) >= $maxResults) break;
                    }
                }
                $title = '';
                $extinf = '';
            }
        }
        fclose($handle);
    }
    
    // Combine and sort results
    foreach ($series as $serie) {
        usort($serie['episodes'], function($a, $b) {
            if ($a['season'] === $b['season']) {
                return $a['episode'] - $b['episode'];
            }
            return $a['season'] - $b['season'];
        });
        $results[] = $serie;
    }
    
    $results = array_merge($results, $movies);
    echo json_encode($results);
}
