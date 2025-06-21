<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// === CONFIGURATION ===
$shortenerApiKey = '2f15491d0c2b98eddcd7d9b32957df6088f00f90';
$storageDir = '/tmp/';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// ✅ Accept JSON input manually
if (isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $jsonInput = file_get_contents('php://input');
    $jsonData = json_decode($jsonInput, true);
    $_POST = $jsonData ?? [];
}

$destinationUrl = $_POST['destinationUrl'] ?? null;
$alias = $_POST['alias'] ?? 'pwthor';

if (!$destinationUrl) {
    echo json_encode(["error" => "No file or content provided"]);
    exit;
}

// === STEP: Call EasySky URL shortener API ===
$encodedUrl = urlencode($destinationUrl);
$apiUrl = "https://easysky.in/api?api=$shortenerApiKey&url=$encodedUrl&alias=$alias&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['status']) && $data['status'] === 'success') {
    echo json_encode([
        "success" => true,
        "shortenedUrl" => $data['shortenedUrl'],
        "originalUrl" => $destinationUrl
    ]);
} else {
    echo json_encode([
        "error" => "Shortening failed",
        "api_response" => $data
    ]);
}
