<?php
// API key aur destination URL define kar lete hain
$apiKey = '2f15491d0c2b98eddcd7d9b32957df6088f00f90';
$destinationUrl = 'https://pwthor.site/login-success.html';
$alias = 'SDV_bots'; // Optional alias

// API URL ko prepare karte hain
$apiUrl = "https://easysky.in/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

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
