<?php
/**
 * TMDB Helper Functions
 * Provides consistent TMDB image fetching across the application
 */

// TMDB API Configuration
define('TMDB_API_KEY', '61dac66f8f6aecb3498d73f244d18c91');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('TMDB_IMAGE_BASE', 'https://image.tmdb.org/t/p/w500');

/**
 * Fetch TMDB image for a TV series
 * @param string $seriesName Name of the TV series
 * @return string|null Image URL or null if not found
 */
function getTmdbSeriesImage($seriesName) {
    $apiUrl = TMDB_BASE_URL . '/search/tv?api_key=' . TMDB_API_KEY . '&query=' . urlencode($seriesName);
    
    $response = @file_get_contents($apiUrl);
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    if (!empty($data['results'][0]['poster_path'])) {
        return TMDB_IMAGE_BASE . $data['results'][0]['poster_path'];
    }
    
    return null;
}

/**
 * Fetch TMDB image for a movie
 * @param string $movieTitle Title of the movie
 * @return string|null Image URL or null if not found
 */
function getTmdbMovieImage($movieTitle) {
    $apiUrl = TMDB_BASE_URL . '/search/movie?api_key=' . TMDB_API_KEY . '&query=' . urlencode($movieTitle);
    
    $response = @file_get_contents($apiUrl);
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    if (!empty($data['results'][0]['poster_path'])) {
        return TMDB_IMAGE_BASE . $data['results'][0]['poster_path'];
    }
    
    return null;
}

/**
 * Get TMDB series information including image and metadata
 * @param string $seriesName Name of the TV series
 * @return array|null Series data or null if not found
 */
function getTmdbSeriesData($seriesName) {
    $apiUrl = TMDB_BASE_URL . '/search/tv?api_key=' . TMDB_API_KEY . '&query=' . urlencode($seriesName);
    
    $response = @file_get_contents($apiUrl);
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    if (!empty($data['results'][0])) {
        $result = $data['results'][0];
        return [
            'name' => $result['name'],
            'first_air_date' => $result['first_air_date'] ?? '',
            'poster_path' => !empty($result['poster_path']) ? TMDB_IMAGE_BASE . $result['poster_path'] : null,
            'overview' => $result['overview'] ?? ''
        ];
    }
    
    return null;
}

/**
 * Generate JavaScript for TMDB image fallback on frontend
 * This function outputs JavaScript code that can be used to fetch TMDB images
 * when the original image fails to load
 */
function getTmdbJavaScript() {
    $apiKey = TMDB_API_KEY;
    return "
    // TMDB Image Fallback JavaScript
    async function fetchTmdbImage(type, title, season = null, episode = null) {
        const apiKey = '$apiKey';
        const baseUrl = 'https://api.themoviedb.org/3';
        const imgBase = 'https://image.tmdb.org/t/p/w500';
        
        try {
            let url = '';
            if (type === 'series') {
                url = `\${baseUrl}/search/tv?api_key=\${apiKey}&query=\${encodeURIComponent(title)}`;
                const resp = await fetch(url);
                const data = await resp.json();
                if (data.results && data.results.length > 0) {
                    const show = data.results[0];
                    if (season && episode) {
                        const tvId = show.id;
                        const epUrl = `\${baseUrl}/tv/\${tvId}/season/\${season}/episode/\${episode}/images?api_key=\${apiKey}`;
                        const epResp = await fetch(epUrl);
                        const epData = await epResp.json();
                        if (epData.stills && epData.stills.length > 0) {
                            return imgBase + epData.stills[0].file_path;
                        }
                    }
                    if (show.poster_path) {
                        return imgBase + show.poster_path;
                    }
                }
            } else {
                url = `\${baseUrl}/search/movie?api_key=\${apiKey}&query=\${encodeURIComponent(title)}`;
                const resp = await fetch(url);
                const data = await resp.json();
                if (data.results && data.results.length > 0 && data.results[0].poster_path) {
                    return imgBase + data.results[0].poster_path;
                }
            }
        } catch (error) {
            console.error('TMDB API error:', error);
        }
        return null;
    }
    
    // Handle image error with TMDB fallback
    async function handleImageError(imgId, type, title, season = null, episode = null) {
        const imgElem = document.getElementById(imgId);
        if (!imgElem) return;
        
        // Try TMDB
        const tmdbImg = await fetchTmdbImage(type, title, season, episode);
        if (tmdbImg) {
            imgElem.src = tmdbImg;
            imgElem.onerror = function() {
                imgElem.parentNode.innerHTML = `<div class='placeholder d-flex align-items-center justify-content-center h-100'><i class='fas fa-\${type === 'movie' ? 'film' : 'tv'} fa-3x text-muted'></i></div>`;
            };
        } else {
            imgElem.parentNode.innerHTML = `<div class='placeholder d-flex align-items-center justify-content-center h-100'><i class='fas fa-\${type === 'movie' ? 'film' : 'tv'} fa-3x text-muted'></i></div>`;
        }
    }
    ";
}
?>