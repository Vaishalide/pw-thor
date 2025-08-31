<?php
// ALWAYS start the session at the very top
session_start();

// Set the header to indicate JSON response right away
header('Content-Type: application/json');

// --- FIX: REMOVED THE FLAWED CACHING LOGIC ---
// We will now generate a fresh link every time.

// API key is defined
$apiKey = 'be1be8f8f3c02db2e943cc7199c5641971d86283';
// By leaving the alias empty, the API will generate a unique one
$alias = '';

// 1. Generate a secure, random token for validation
$token = bin2hex(random_bytes(16));
$_SESSION['validation_token'] = $token;

// 2. Prepare the destination URL
$destinationUrl = 'https://pwthor.site/login-success.php?token=' . $token;

// 3. Prepare and execute the API call to gplinks.com
$apiUrl = "https://api.gplinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// --- FIX: ADDED ROBUST TIMEOUTS ---
// Set a 10-second connection timeout
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
// Set a 15-second total timeout for the entire request
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);

// Always good to check for cURL errors (like timeouts)
if ($response === false) {
    http_response_code(504); // Gateway Timeout
    echo json_encode(['error' => 'The link generation service is not responding.', 'details' => curl_error($ch)]);
    curl_close($ch);
    exit();
}

curl_close($ch);

$data = json_decode($response, true);

// 4. Process the API response
if ($data && $data['status'] === 'success') {
    // Return the newly generated URL
    echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
} else {
    // Return an error message on failure
    http_response_code(500);
    echo json_encode(['error' => 'URL generation failed', 'details' => $data['message'] ?? 'Unknown API error']);
}
?>
