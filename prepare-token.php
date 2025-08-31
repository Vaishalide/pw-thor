<?php
// ALWAYS start the session at the very top
session_start();

// Set the header to indicate JSON response right away
header('Content-Type: application/json');

// 1. Generate a new, secure token and save it to the session.
$token = bin2hex(random_bytes(16));
$_SESSION['validation_token'] = $token;

// 2. Return a success message to the frontend.
echo json_encode(['status' => 'success', 'message' => 'Token created.']);
?>
