<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="container">
    <h1>Reset Password</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="/password/reset/<?php echo $token; ?>" method="post">
        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
