<?php include __DIR__ . '/partials/header.php'; ?>

<div class="hero">
    <div class="hero-content">
        <h1>Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>
        <p>You have successfully logged in to your cloud storage account.</p>
        <div class="actions">
            <a href="/files" class="btn btn-primary">My Files</a>
            <a href="/logout" class="btn">Log Out</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
