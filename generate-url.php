<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// === CONFIGURATION ===
$shortenerApiKey = '2f15491d0c2b98eddcd7d9b32957df6088f00f90';
$shortenerAlias = 'pw_thor'; // Optional
$storageDir = '/tmp/';

if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// === STEP 1: Accept content or file upload ===
$localFilePath = '';
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $filename = uniqid('upload_', true) . '_' . basename($_FILES['file']['name']);
    $localFilePath = $storageDir . $filename;
    move_uploaded_file($_FILES['file']['tmp_name'], $localFilePath);
} elseif (isset($_POST['content'])) {
    $filename = uniqid('content_', true) . '.txt';
    $localFilePath = $storageDir . $filename;
    file_put_contents($localFilePath, $_POST['content']);
} else {
    echo json_encode(["error" => "No file or content provided"]);
    exit;
}

// === STEP 2: Upload file to transfer.sh ===
$uploadName = basename($localFilePath);
$uploadUrl = 'https://transfer.sh/' . $uploadName;
$file = fopen($localFilePath, 'r');

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $uploadUrl,
    CURLOPT_PUT => true,
    CURLOPT_INFILE => $file,
    CURLOPT_INFILESIZE => filesize($localFilePath),
    CURLOPT_RETURNTRANSFER => true,
]);
$uploadResponse = curl_exec($ch);
curl_close($ch);
fclose($file);

if (!$uploadResponse || !filter_var($uploadResponse, FILTER_VALIDATE_URL)) {
    echo json_encode(["error" => "File upload failed", "raw" => $uploadResponse]);
    exit;
}

// === STEP 3: Shorten the uploaded URL using EasySky ===
$encodedUrl = urlencode($uploadResponse);
$shortenApi = "https://easysky.in/api?api=$shortenerApiKey&url=$encodedUrl&alias=$shortenerAlias&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $shortenApi);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['status']) && $data['status'] === 'success') {
    echo json_encode([
        "success" => true,
        "short_url" => $data['shortenedUrl'],
        "original_url" => $uploadResponse
    ]);
} else {
    echo json_encode([
        "error" => "Shortening failed",
        "api_response" => $data
    ]);
}
