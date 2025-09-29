<?php
// stream.php: Streams a remote MKV file as MP4 using FFmpeg
// Usage: stream.php?url=REMOTE_MKV_URL

if (!isset($_GET['url']) || !filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo 'Invalid or missing URL.';
    exit;
}



$url = $_GET['url'];

// Set headers for MP4 streaming
header('Content-Type: video/mp4');
header('Transfer-Encoding: chunked');
header('Connection: keep-alive');
header('Cache-Control: no-cache');

// Build FFmpeg command
// -i <input> : input file
// -f mp4 : output format
// -movflags frag_keyframe+empty_moov : for streaming
// -analyzeduration and -probesize : for faster start

$userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
$cmd = escapeshellcmd(
    "ffmpeg -hide_banner -loglevel error -analyzeduration 2147483647 -probesize 2147483647 " .
    "-user_agent " . escapeshellarg($userAgent) . " " .
    "-i " . escapeshellarg($url) .
    " -f mp4 -movflags frag_keyframe+empty_moov -vcodec libx264 -preset veryfast -acodec aac -b:v 1200k -b:a 128k -y -"
);

// Open process
$fp = popen($cmd, 'r');
if (!$fp) {
    http_response_code(500);
    echo 'Failed to start FFmpeg.';
    exit;
}

// Stream output
while (!feof($fp)) {
    echo fread($fp, 8192);
    flush();
}
pclose($fp);
