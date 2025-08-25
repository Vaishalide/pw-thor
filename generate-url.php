<?php
// ALWAYS start the session at the very top
session_start();

// API key aur destination URL define kar lete hain
$apiKey = 'be1be8f8f3c02db2e943cc7199c5641971d86283';
$alias = ''; // Optional alias

// 1. Generate a secure, random token
// This token will be used to verify the user came from the correct link
$token = bin2hex(random_bytes(16)); // Creates a 32-character hex token

// 2. Store the token in the server-side session
$_SESSION['validation_token'] = $token;

// 3. Add the token to the destination URL as a query parameter
$destinationUrl = 'https://pwthor.site/login-success.php?token=' . $token; // Note: page is now .php

// API URL ko prepare karte hain
$apiUrl = "https://api.gplinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

// cURL se API call karte hain
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Response ko JSON format mein parse karte hain
$data = json_decode($response, true);

// Set the header to indicate JSON response
header('Content-Type: application/json');

if ($data && $data['status'] === 'success') {
    // Agar success ho to short URL return karo
    echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
} else {
    // Error message return karo
    http_response_code(500); // Send a server error status
    echo json_encode(['error' => 'URL generation failed', 'details' => $data['message'] ?? 'Unknown API error']);
}
?>
