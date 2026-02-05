<?php
// This file is now a simple template. All logic has been moved to the HomeController.
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

        .actions a {
            color: #007aff;
            text-decoration: none;
            margin: 0 10px;
            font-size: 1.2em;
        }

        .actions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>
        <p>You have successfully logged in to your cloud storage account.</p>
        <div class="actions">
            <a href="/files">My Files</a>
            <a href="/logout">Log Out</a>
        </div>
    </div>
</body>
</html>
