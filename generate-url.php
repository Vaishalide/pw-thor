<?php
$apiKey = '2f15491d0c2b98eddcd7d9b32957df6088f00f90';
$destinationUrl = 'https://pwthor.site/login-success.html';
$alias = 'pw_thor1';

$apiUrl = "https://easysky.in/api?api=$apiKey&url=" . urlencode($destinationUrl) . "&alias=$alias&format=json";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // only for debugging

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(['error' => curl_error($ch)]);
} else {
    $data = json_decode($response, true);
    if (isset($data['status']) && $data['status'] === 'success') {
        echo json_encode(['shortenedUrl' => $data['shortenedUrl']]);
    } else {
        echo json_encode(['error' => 'API responded: ' . $response]);
    }
}

curl_close($ch);
?>
