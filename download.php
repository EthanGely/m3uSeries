<?php

session_start();

if (!isset($_GET['psw']) || $_GET['psw'] !== 'hGxKr297Ab') {
    http_response_code(403);
    exit('Access denied. Please provide the correct password.');
}

if (!isset($_GET['url'])) {
    http_response_code(400);
    exit('Missing URL');
}

$url = $_GET['url'];
$title = $_GET['title'] ?? 'stream';

// Validate and sanitize
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('Invalid URL');
}

// Extract extension
$path = parse_url($url, PHP_URL_PATH);
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

// Block extensions commonly used by live channels
$blockedExtensions = ['ts', 'm3u8', 'mpd', 'flv'];
if (in_array($ext, $blockedExtensions)) {
    http_response_code(403);
    exit('Le type de fichier n\'est pas autorisé (surement car c\'est une chaine de TV).');
}

// Allow only whitelisted extensions
$allowedExt = ['m3u', 'mp4', 'mkv', 'avi'];
if (!in_array($ext, $allowedExt)) {
    http_response_code(403);
    exit('Le type de fichier n\'est pas autorisé (surement car c\'est une chaine de TV).');
}

// MIME type map
$mimeTypes = [
    'm3u' => 'audio/x-mpegurl',
    'mp4' => 'video/mp4',
    'mkv' => 'video/x-matroska',
    'avi' => 'video/x-msvideo',
];

$mime = $mimeTypes[$ext] ?? 'application/octet-stream';
$filename = basename($title) . '.' . $ext;

// Custom User-Agent
$options = [
    "http" => [
        "header" => "User-Agent: VLC/3.0.16 LibVLC/3.0.16\r\n"
    ]
];
$context = stream_context_create($options);

// Try to get file size first using HEAD request
$fileSize = 0;
$headOptions = [
    "http" => [
        "method" => "HEAD",
        "header" => "User-Agent: VLC/3.0.16 LibVLC/3.0.16\r\n"
    ]
];
$headContext = stream_context_create($headOptions);

// Suppress warnings for the HEAD request
$headers = @get_headers($url, 1, $headContext);
if ($headers && isset($headers['Content-Length'])) {
    $fileSize = is_array($headers['Content-Length']) 
        ? end($headers['Content-Length']) 
        : $headers['Content-Length'];
}

// Set headers
header("Content-Type: $mime");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Transfer-Encoding: binary");

// Set content length if we got it
if ($fileSize > 0) {
    header("Content-Length: $fileSize");
}

// Stream to browser
$stream = @fopen($url, 'rb', false, $context);
if ($stream === false) {
    http_response_code(404);
    exit('Could not load stream.');
}

// Stream file in chunks
while (!feof($stream)) {
    echo fread($stream, 8192);
    flush();
    
    // Check if client disconnected
    if (connection_aborted()) {
        break;
    }
}
fclose($stream);
