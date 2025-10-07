<?php
session_start();

$user_id = isset($_COOKIE['permanent_user_id']) ? $_COOKIE['permanent_user_id'] : '';

if (empty($user_id)) {
    header('Location: pw.php');
    exit();
}
$shortenedUrl = '';
$videoUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_key'])) {

    $api_url = "https://key-db-eb5d0a77a827.herokuapp.com/api/login?id=" . urlencode($user_id);
    $response = @file_get_contents($api_url);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        
        if (isset($data['status']) && $data['status'] === 'success') {
            $shortenedUrl = $data['shortenedUrl'];
            $videoUrl = $data['video_url'];
            
            header("Location: " . $shortenedUrl);
            exit();
        }
    }
    
    $error = "Error generating key. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Key - PWTHOR Access Portal</title>
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
        
        .btn-back {
            background: #6c757d;
        }
        
        .btn-back:hover {
            background: #5a6268;
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
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generate Key</h1>
        
        <div class="user-id">
            Your Permanent ID: <strong><?php echo htmlspecialchars($user_id); ?></strong>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="text-center">
            <form method="POST" action="">
                <button type="submit" name="generate_key" class="btn">Generate Key</button>
            </form>
            
            <div style="margin-top: 20px;">
                <a href="pw.php" class="btn btn-back">Back to Main</a>
            </div>
        </div>
    </div>
    
    <div class="footer">
        &copy; <?php echo date('Y'); ?> PW THOR Access System. All rights reserved.
    </div>
</body>
</html>
