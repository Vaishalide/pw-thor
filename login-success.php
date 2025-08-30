<?php
// ALWAYS start the session at the very top
session_start();

// 1. Get the token from the URL and the session
$received_token = $_GET['token'] ?? '';
$session_token = $_SESSION['validation_token'] ?? '';

// 2. Validate the tokens
// - Check if both tokens exist.
// - Use hash_equals for a secure, timing-attack-safe comparison.
if (empty($received_token) || empty($session_token) || !hash_equals($session_token, $received_token)) {
    // If tokens don't match, it's a bypass attempt.
    // Redirect to the key generation page and stop execution.
    header('Location: generate-key.html');
    exit();
}

// 3. IMPORTANT: Unset the token after successful validation
// This ensures the token can only be used ONCE.
unset($_SESSION['validation_token']);

// If we reach here, validation was successful.
// Now we can proceed to set the permanent session cookie.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Success</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
  </head>
<body>
  <h2>Login Successful!</h2>
  <p>Aap ab 24 ghante ke liye logged in hain.</p>

  <script>
// This part remains the same as it's for setting the final session cookie
    const secretKey = '$YgSaj3OmNEt/PL';
    const now = new Date();
    const expirationTimestamp = now.getTime() + 24 * 60 * 60 * 1000;
    const encryptedTimestamp = CryptoJS.AES.encrypt(expirationTimestamp.toString(), secretKey).toString();
    const expirationDate = new Date(expirationTimestamp);
    document.cookie = `session_token=${encryptedTimestamp}; expires=${expirationDate.toUTCString()}; path=/`;

    // Redirect to the main page
    window.location.href = 'pw.html';  </script>
</body>
</html>
