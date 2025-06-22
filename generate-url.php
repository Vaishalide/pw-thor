<?php
// API key aur destination URL define kar lete hain
$apiKey = 'c5c905fcda3a5b7420b87d578de49d4e5fe05892';
$destinationUrl = 'https://pwthor.site/login-success.html';
$alias = 'PW_thor'; // Optional alias

// API URL ko prepare karte hain
$apiUrl = "https://pocolinks.com/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

// cURL se API call karte hain
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Response ko JSON format mein parse karte hain
$data = json_decode($response, true);

if ($data['status'] === 'success') {
    // Agar success ho to short URL return karo
    echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
} else {
    // Error message return karo
    echo json_encode(['error' => 'URL generation failed']);
}
?>
