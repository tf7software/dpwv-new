<?php
include 'api-key.php';
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);

// Validate and sanitize the page parameter
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;

$swlink = 'https://vidsrc.xyz/movies/latest/page-' . $page . '.json';
$json_string = @file_get_contents($swlink);

// Check if file_get_contents was successful
if ($json_string === FALSE) {
    echo json_encode(['error' => 'Unable to fetch data from the source.']);
    exit;
}

$parsed_json = json_decode($json_string, true);

// Check if json_decode was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Error decoding JSON from the source.']);
    exit;
}

$parsed = isset($parsed_json['result']) ? $parsed_json['result'] : [];

$rows = [];

foreach ($parsed as $value) {
    $imdb = isset($value['imdb_id']) ? $value['imdb_id'] : '';
    $tmdb = isset($value['tmdb_id']) ? $value['tmdb_id'] : '';
    $title = isset($value['title']) ? $value['title'] : '';
    $imdbembed = isset($value['embed_url']) ? $value['embed_url'] : '';
    $tmdbembed = isset($value['embed_url_tmdb']) ? $value['embed_url_tmdb'] : '';

    // Fetch poster information from TMDB API
    $tmdb_api_url = 'https://api.themoviedb.org/3/find/' . $imdb . '?external_source=imdb_id&api_key=' . $apikey;
    $json = @file_get_contents($tmdb_api_url);

    // Check if file_get_contents was successful
    if ($json === FALSE) {
        $poster = 'n/a';
    } else {
        $obj = json_decode($json, true);
        
        // Check if json_decode was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            $poster = 'n/a';
        } else {
            $poster_path = isset($obj['movie_results'][0]['poster_path']) ? $obj['movie_results'][0]['poster_path'] : '';
            $poster = $poster_path ? 'https://image.tmdb.org/t/p/w300' . $poster_path : 'n/a';
        }
    }

    $rows[] = [
        'imdb_id' => $imdb,
        'tmdb_id' => $tmdb,
        'title' => $title,
        'imdb_embed' => $imdbembed,
        'tmdb_embed' => $tmdbembed,
        'poster' => $poster,
    ];
}

// Encode the final output as JSON
$finaljson = json_encode($rows);

// Check if json_encode was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Error encoding JSON for the output.']);
    exit;
}

// Replace unwanted substrings in the final JSON
$finaljson = str_replace(['vidsrc.me', 'movie?imdb=', 'movie?tmdb='], ['www.2embed.cc', '', ''], $finaljson);

echo $finaljson;
?>
