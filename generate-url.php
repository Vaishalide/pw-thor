<?php
// API key aur destination URL define kar lete hain
$apiKey = 'd65b72e5f21fc77f6a1d4218f4c1c60c7c3dba07';
$destinationUrl = 'https://pwthor.site/login-success.html';
$alias = 'PW_thor'; // Optional alias

// API URL ko prepare karte hain
$apiUrl = "https://getlinks.in/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

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
