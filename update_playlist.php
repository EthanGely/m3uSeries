<?php
function update_playlist() {
    $date = date('Ymd');
    $cacheDir = __DIR__ . '/cache';
    $cachedFile = "$cacheDir/playlist-$date.m3u";

    // Check if today's file exists
    if (file_exists($cachedFile)) {
        // File for today exists, stop
        return;
    }

    // Download new playlist
    $m3uUrl = 'http://365hub.cc:2103/get.php?username=ga9BWt36&password=s2hcKGq&type=m3u_plus&output=ts'; // replace with actual URL
    $opts = [
        "http" => [
            "header" => "User-Agent: VLC/3.0.16\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $content = file_get_contents($m3uUrl, false, $context);

    if ($content !== false) {
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        file_put_contents($cachedFile, $content);

        // Delete older playlist files
        foreach (glob("$cacheDir/playlist-*.m3u") as $file) {
            if ($file !== $cachedFile) {
                unlink($file);
            }
        }
    }
}
