<?php
// ALWAYS start the session at the very top
session_start();

// Set the header to indicate JSON response right away
header('Content-Type: application/json');

// --- IMPROVED LOGIC ---
// 1. Check if a valid shortened URL already exists in the session.
if (isset($_SESSION['shortened_url'])) {
    // If it exists, return it immediately without calling the API.
    echo json_encode(['shortenedUrl' => $_SESSION['shortened_url']]);
    exit(); // Stop the script here.
}

// If no URL exists in the session, then proceed to generate a new one.

// API key is defined
$apiKey = 'be1be8f8f3c02db2e943cc7199c5641971d86283';
// By leaving the alias empty, the API will generate a unique one
$alias = '';

// 2. Generate a secure, random token for validation
$token = bin2hex(random_bytes(16));
$_SESSION['validation_token'] = $token;

// 3. Prepare the destination URL
$destinationUrl = 'https://pwthor.site/login-success.php?token=' . $token;

// 4. Prepare and execute the API call to gplinks.com
$apiUrl = "https://api.gplinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// 5. Process the API response
if ($data && $data['status'] === 'success') {
    // --- IMPROVED LOGIC ---
    // On success, SAVE the new URL in the session for future requests.
    $_SESSION['shortened_url'] = $data['shortenedUrl'];

    // Return the newly generated URL
    echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
} else {
    // Return an error message on failure
    http_response_code(500);
    echo json_encode(['error' => 'URL generation failed', 'details' => $data['message'] ?? 'Unknown API error']);
}
?>
