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
$alias = 'pwthor' . bin2hex(random_bytes(3));

// 4. Prepare the API call URL
$apiUrl = "https://api.gplinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

// 5. Initialize cURL and execute the API call
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// --- FIX FOR HEROKU RESOURCE LEAK ---
// 💡 Add a header to explicitly close the connection after the API call.
// This prevents idle connections from building up and causing application errors.
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));

$response = curl_exec($ch);
curl_close($ch); // This is also important and correctly closes the cURL handle.

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
