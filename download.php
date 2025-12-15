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
$headContext = stream_context_create($options);

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
// --- ADDED: logging, timeouts, shutdown handler ---
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/download_errors.log';

@set_time_limit(0);
@ini_set('default_socket_timeout', 300);

register_shutdown_function(function() use ($logFile, $url) {
    $err = error_get_last();
    if ($err) {
        $msg = sprintf("[%s] SHUTDOWN: %s in %s on line %d. URL=%s\n",
            date('Y-m-d H:i:s'),
            $err['message'],
            $err['file'],
            $err['line'],
            $url
        );
        @error_log($msg, 3, $logFile);
    }
});

// set a read timeout on the remote socket
@stream_set_timeout($stream, 30);

ob_end_clean();
$bytes = 0;
while (!feof($stream)) {
    $data = @fread($stream, 8192);
    if ($data === false) {
        $meta = stream_get_meta_data($stream);
        $msg = sprintf("[%s] fread() failed. meta=%s URL=%s\n",
            date('Y-m-d H:i:s'), trim(print_r($meta, true)), $url
        );
        @error_log($msg, 3, $logFile);
        break;
    }
    if ($data === '') {
        // empty read: check for timeout or broken connection
        $meta = stream_get_meta_data($stream);
        if (!empty($meta['timed_out'])) {
            $msg = sprintf("[%s] stream timed out. meta=%s URL=%s\n",
                date('Y-m-d H:i:s'), trim(print_r($meta, true)), $url
            );
            @error_log($msg, 3, $logFile);
            break;
        }
        usleep(100000);
        continue;
    }

    echo $data;
    $bytes += strlen($data);
    flush();

    if (connection_aborted()) {
        $msg = sprintf("[%s] client aborted after %d bytes. URL=%s\n", date('Y-m-d H:i:s'), $bytes, $url);
        @error_log($msg, 3, $logFile);
        break;
    }

    $meta = stream_get_meta_data($stream);
    if (!empty($meta['timed_out'])) {
        $msg = sprintf("[%s] stream_get_meta_data timed_out after %d bytes. meta=%s URL=%s\n",
            date('Y-m-d H:i:s'), $bytes, trim(print_r($meta, true)), $url
        );
        @error_log($msg, 3, $logFile);
        break;
    }
}

fclose($stream);

$msg = sprintf("[%s] download finished/terminated. total_bytes=%d URL=%s\n", date('Y-m-d H:i:s'), $bytes, $url);
@error_log($msg, 3, $logFile);
