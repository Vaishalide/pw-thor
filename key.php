<?php
session_start();

$user_id = '';
if (isset($_COOKIE['permanent_user_id'])) {
    $user_id = $_COOKIE['permanent_user_id'];
} else {
    $user_id = uniqid('', true);
    setcookie('permanent_user_id', $user_id, time() + (10 * 365 * 24 * 60 * 60), "/");
}

$message = '';
$showLove = false;
$stored_key = '';

if (isset($_COOKIE['user_key'])) {
    $stored_key = $_COOKIE['user_key'];
    
    $api_url = "https://key-db-eb5d0a77a827.herokuapp.com/api/check?key=" . urlencode($stored_key) . "&id=" . urlencode($user_id);
    $response = @file_get_contents($api_url);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (isset($data['status'])) {
            if ($data['status'] === 'success') {
                $showLove = true;
                
                header("Location: pw.php");
                exit();
            } elseif ($data['status'] === 'unauthorized') {
                setcookie('user_key', '', time() - 3600, "/");
                $stored_key = '';
                $message = "Your key has expired. Please enter a new one.";
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_key'])) {
    $key = isset($_POST['key']) ? trim($_POST['key']) : '';
    
    $api_url = "https://key-db-eb5d0a77a827.herokuapp.com/api/check?key=" . urlencode($key) . "&id=" . urlencode($user_id);
    $response = @file_get_contents($api_url);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (isset($data['status'])) {
            if ($data['status'] === 'success') {
                setcookie('user_key', $key, time() + (30 * 24 * 60 * 60), "/");
                $showLove = true;
                $stored_key = $key;
                
                header("Location: pw.php");
                exit();
            } elseif ($data['status'] === 'unauthorized') {
                $message = "This key has expired. Please enter a valid key.";
            } else {
                $message = "Invalid key. Please try again.";
            }
        } else {
            $message = "Invalid response from server.";
        }
    } else {
        $message = "Error connecting to verification service.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWTHOR Access </title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4ecca3;
            font-size: 28px;
        }
        
        .user-id {
            background: rgba(0, 0, 0, 0.2);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            word-break: break-all;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #4ecca3;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #4ecca3;
            color: #1a1a2e;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
        }
        
        .btn:hover {
            background: #38b592;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f8b500;
            color: #1a1a2e;
        }
        
        .btn-secondary:hover {
            background: #e0a400;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .button-group .btn {
            flex: 1;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        
        .error {
            background: rgba(238, 82, 83, 0.2);
            border: 1px solid #ee5253;
        }
        
        .success {
            background: rgba(46, 213, 115, 0.2);
            border: 1px solid #2ed573;
        }
        
        .love-message {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #4ecca3;
            margin: 30px 0;
            text-shadow: 0 0 10px rgba(78, 204, 163, 0.5);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .hidden {
            display: none;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .clear-key {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: underline;
            cursor: pointer;
        }
        
        .clear-key:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PWTHOR Access Portal</h1>
        
        <div class="user-id">
            Your Permanent ID: <strong><?php echo htmlspecialchars($user_id); ?></strong>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div id="verification-section" <?php echo $showLove ? 'class="hidden"' : ''; ?>>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="key">Enter Your Key:</label>
                    <input type="text" id="key" name="key" placeholder="Paste your key here" value="<?php echo htmlspecialchars($stored_key); ?>" required>
                </div>
                
                <div class="button-group">
                    <button type="submit" name="verify_key" class="btn">Verify Key</button>
                    <a href="generate.php" class="btn btn-secondary">Get Key</a>
                </div>
            </form>
        </div>
        
        <?php if ($showLove): ?>
            <div class="love-message">LOVE YOU THOR</div>
            <div style="text-align: center;">
                <a href="https://t.me/pwthorxiityatra" target="_blank" class="btn">Join Telegram Channel</a>
            </div>
            <div class="clear-key" onclick="clearStoredKey()">Use a different key</div>
        <?php endif; ?>
    </div>
    
    <div class="footer">
        &copy; <?php echo date('Y'); ?> PWTHOR Access System. All rights reserved.
    </div>

    <script>
        function clearStoredKey() {
            document.cookie = "user_key=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            location.reload();
        }
    </script>
</body>
</html>
