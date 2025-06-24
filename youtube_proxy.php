<?php
// --- THE SECURITY CHECK ---
// Set your allowed domain.
$allowed_origin = 'https://yourzone.ct.ws';

// Check if the Origin header is present and if it matches our allowed domain.
// Browsers send the Origin header for cross-site requests (like from your HTML to your PHP).
if (!isset($_SERVER['HTTP_ORIGIN']) || $_SERVER['HTTP_ORIGIN'] !== $allowed_origin) {
    // If the origin is missing or doesn't match, block the request.
    header('Content-Type: application/json');
    http_response_code(403); // 403 Forbidden
    echo json_encode(['error' => ['message' => 'Access denied.']]);
    exit;
}

// --- IMPORTANT FOR CORS ---
// We must also tell the browser that we allow our own domain to access this script.
header("Access-Control-Allow-Origin: " . $allowed_origin);
header("Access-Control-Allow-Methods: GET");


// --- If the origin is valid, proceed with the original logic ---

require_once 'config.php';
// We already sent some headers, but we can set the Content-Type again if needed,
// though it's better to keep it consistent.
header('Content-Type: application/json');

$apiKey = defined('YOUTUBE_API_KEY') ? YOUTUBE_API_KEY : '';
if (empty($apiKey) || $apiKey === 'YOUR_API_KEY_HERE') {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'API Key is not configured on the server.']]);
    exit;
}

$apiUrl = 'https://www.googleapis.com/youtube/v3/';
$params = [];
$endpoint = '';

if (isset($_GET['q'])) {
    $endpoint = 'search';
    $params = ['part' => 'snippet', 'maxResults' => 50, 'q' => $_GET['q'], 'type' => 'video'];
} elseif (isset($_GET['id'])) {
    $endpoint = 'videos';
    $params = ['part' => 'snippet', 'id' => $_GET['id']];
} else {
    // This is the fix for the PWABuilder packager
    http_response_code(200);
    echo json_encode(['items' => []]);
    exit;
}

$params['key'] = $apiKey;
$finalUrl = $apiUrl . $endpoint . '?' . http_build_query($params);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $finalUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'YouTubeMusicPlayerProxy/1.0');

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'Server cURL Error: ' . curl_error($ch)]]);
} else {
    // We already set the main status code, but let's re-apply it based on YouTube's response
    http_response_code($httpcode);
    echo $response;
}

curl_close($ch);
?>