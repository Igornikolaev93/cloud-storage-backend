<?php include __DIR__ . '/partials/header.php'; ?>

<div class="auth-container">
    <h2>Login</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="/login" method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <div class="auth-footer">
        <p>Don't have an account? <a href="/register">Register here</a></p>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
