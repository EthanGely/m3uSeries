<?php
header('Content-Type: application/json');
session_start();

$action = $_GET['action'] ?? '';

// Simple file-based favorites storage (could be enhanced with database)
$favoritesFile = __DIR__ . '/cache/favorites.json';

function getFavorites() {
    global $favoritesFile;
    if (!file_exists($favoritesFile)) {
        return [];
    }
    $content = file_get_contents($favoritesFile);
    return json_decode($content, true) ?: [];
}

function saveFavorites($favorites) {
    global $favoritesFile;
    $cacheDir = dirname($favoritesFile);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
    file_put_contents($favoritesFile, json_encode($favorites, JSON_PRETTY_PRINT));
}

switch ($action) {
    case 'list':
        echo json_encode(getFavorites());
        break;
        
    case 'add':
        $item = json_decode(file_get_contents('php://input'), true);
        if ($item) {
            $favorites = getFavorites();
            $id = $item['type'] === 'series' ? $item['series_name'] : $item['title'];
            $favorites[$id] = [
                'id' => $id,
                'title' => $item['type'] === 'series' ? $item['series_name'] : $item['title'],
                'type' => $item['type'],
                'image' => $item['image'] ?? null,
                'category' => $item['category'] ?? '',
                'added_at' => date('Y-m-d H:i:s'),
                'data' => $item
            ];
            saveFavorites($favorites);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid item data']);
        }
        break;
        
    case 'remove':
        $id = $_GET['id'] ?? '';
        if ($id) {
            $favorites = getFavorites();
            if (isset($favorites[$id])) {
                unset($favorites[$id]);
                saveFavorites($favorites);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Item not found']);
            }
        } else {
            echo json_encode(['error' => 'Missing item ID']);
        }
        break;
        
    case 'check':
        $id = $_GET['id'] ?? '';
        $favorites = getFavorites();
        echo json_encode(['is_favorite' => isset($favorites[$id])]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>