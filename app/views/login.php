<?php include __DIR__ . '/partials/header.php'; ?>

<div class="login-container">
    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form action="/login" method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <div class="register-link">
        <p>Don't have an account? <a href="/register">Register here</a></p>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
