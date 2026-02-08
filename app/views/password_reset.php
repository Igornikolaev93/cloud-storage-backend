<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Your Password</h1>

    <form action="/password-reset/<?php echo htmlspecialchars($token); ?>" method="post">
        <label for="password">New Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="password_confirm">Confirm New Password:</label>
        <input type="password" id="password_confirm" name="password_confirm" required>

        <button type="submit">Reset Password</button>
    </form>

</body>
</html>