<?php
// ALWAYS start the session at the very top
session_start();

// Set the header to indicate JSON response right away
header('Content-Type: application/json');

// 1. ALWAYS generate a new, secure token for EVERY request.
$token = bin2hex(random_bytes(16));
$_SESSION['validation_token'] = $token;

// 2. Prepare the destination URL with the NEW token
$destinationUrl = 'https://pwthor.site/login-success.php?token=' . $token;

// API key is defined
$apiKey = 'be1be8f8f3c02db2e943cc7199c5641971d86283';

// 3. Generate a random alias with the prefix 'pwthor'.
$alias = '' . bin2hex(random_bytes(3));

// 4. Prepare the API call URL
$apiUrl = "https://api.gplinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

// 5. Initialize cURL and execute the API call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));

// --- FIX FOR H12 TIMEOUT ERRORS ---
// 💡 Set a connection timeout of 10 seconds.
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
// 💡 Set a total timeout of 15 seconds for the API call.
// This is less than Heroku's 30-second limit, preventing H12 errors.
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);

// --- IMPROVED ERROR HANDLING ---
// Check if the cURL request failed (e.g., due to a timeout).
if ($response === false) {
    $error_message = 'URL generation failed: ' . curl_error($ch);
    curl_close($ch);
    http_response_code(504); // Gateway Timeout
    echo json_encode(['error' => 'The link generation service is not responding.', 'details' => $error_message]);
    exit(); // Stop the script
}

curl_close($ch);

$data = json_decode($response, true);

// 6. Process the API response
if ($data && $data['status'] === 'success') {
    // Return the newly generated URL
    echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
} else {
    // Return an error message on failure
    http_response_code(500);
    echo json_encode(['error' => 'URL generation failed', 'details' => $data['message'] ?? 'Unknown API error']);
}
?>
