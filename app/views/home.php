<?php include __DIR__ . '/partials/header.php'; ?>

<div class="welcome-container">
    <h1>Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>
    <p>You have successfully logged in to your cloud storage account.</p>
    <div class="actions">
        <a href="/files">My Files</a>
        <a href="/logout">Log Out</a>
    </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
