<?php
// ALWAYS start the session at the very top
session_start();

// Set the header to indicate JSON response right away
header('Content-Type: application/json');

// --- FIX IS HERE ---
// 1. ALWAYS generate a new, secure token for EVERY request.
// This ensures that even if we use a cached URL, the session has a fresh token
// ready for the validation on the success page.
$token = bin2hex(random_bytes(16));
$_SESSION['validation_token'] = $token;

// 2. Prepare the destination URL with the NEW token
$destinationUrl = 'https://pwthor.site/login-success.php?token=' . $token;

// --- IMPROVED LOGIC ---
// 3. Check if a valid shortened URL already exists in the session.
// Note: This block is now removed because the destination URL changes with each new token.
// We must generate a new shortlink for each new token to ensure validation works.

// API key is defined
$apiKey = 'be1be8f8f3c02db2e943cc7199c5641971d86283';
// By leaving the alias empty, the API will generate a unique one
$alias = '';

// 4. Prepare and execute the API call to gplinks.com with the new destination URL
$apiUrl = "https://api.gplinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// 5. Process the API response
if ($data && $data['status'] === 'success') {
    // We no longer need to save the URL in the session, as we generate a new one each time.
    // $_SESSION['shortened_url'] = $data['shortenedUrl'];

    // Return the newly generated URL
    echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
} else {
    // Return an error message on failure
    http_response_code(500);
    echo json_encode(['error' => 'URL generation failed', 'details' => $data['message'] ?? 'Unknown API error']);
}
?>
