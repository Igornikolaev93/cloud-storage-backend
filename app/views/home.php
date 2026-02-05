<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../utils/Auth.php';

use App\Utils\Auth;

if (!Auth::check()) {
    header('Location: /login');
    exit;
}

$user = Auth::user();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Your Cloud Storage</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .container {
            text-align: center;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007aff;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 30px;
        }

        a {
            color: #007aff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>
        <p>You have successfully logged in to your cloud storage account.</p>
        <a href="/logout">Log Out</a>
    </div>
</body>
</html>
