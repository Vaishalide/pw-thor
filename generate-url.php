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

// --- CHANGE IS HERE ---
// 3. Generate a random alias with the prefix 'pwthor'.
// random_bytes(3) creates 3 random bytes, and bin2hex() converts them to a 6-character hex string.
$alias = 'pwthor' . bin2hex(random_bytes(3));

// 4. Prepare and execute the API call to gplinks.com with the new destination URL and custom alias
$apiUrl = "https://api.gplinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// 5. Process the API response
if ($data && $data['status'] === 'success') {
    // Return the newly generated URL
    echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
} else {
    // Return an error message on failure
    http_response_code(500);
    echo json_encode(['error' => 'URL generation failed', 'details' => $data['message'] ?? 'Unknown API error']);
}
?>
