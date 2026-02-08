<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
</head>
<body>
    <h1>Request a Password Reset</h1>

    <?php if (isset($success) && $success): ?>
        <p>If an account with that email exists, a password reset link has been sent.</p>
    <?php else: ?>
        <form action="/password-reset" method="post">
            <label for="email">Your Email:</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Send Reset Link</button>
        </form>
    <?php endif; ?>

</body>
</html>