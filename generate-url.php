<?php
// ALWAYS start the session at the very top
session_start();

// API key is defined
$apiKey = 'be1be8f8f3c02db2e943cc7199c5641971d86283';
// By leaving the alias empty, the API will generate a unique one
$alias = '';

// 1. Generate a secure, random token
$token = bin2hex(random_bytes(16)); // Creates a 32-character hex token

// 2. Store the token in the server-side session
$_SESSION['validation_token'] = $token;

// 3. Add the token to the destination URL as a query parameter
$destinationUrl = 'https://pwthor.site/login-success.php?token=' . $token;

// API URL is prepared
$apiUrl = "https://api.gplinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

// cURL is used for the API call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Response is parsed from JSON
$data = json_decode($response, true);

// Header is set to indicate a JSON response
header('Content-Type: application/json');

if ($data && $data['status'] === 'success') {
    // Return the shortened URL on success
    echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
} else {
    // Return an error message on failure
    http_response_code(500); // Send a server error status
    echo json_encode(['error' => 'URL generation failed', 'details' => $data['message'] ?? 'Unknown API error']);
}
?>
