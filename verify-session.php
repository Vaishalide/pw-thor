<?php
// ALWAYS start the session at the very top
session_start();

// 1. Get the token that was previously stored in the session.
$token = $_SESSION['validation_token'] ?? '';

// 2. If no token exists, the user likely accessed this page directly. Send them back.
if (empty($token)) {
    header('Location: generate-key.html');
    exit();
}

// 3. If a token exists, build the final destination URL and perform the redirect.
$finalUrl = 'https://pwthor.site/login-success.php?token=' . $token;
header('Location: ' . $finalUrl);
exit();
?>
