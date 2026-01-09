<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <h2>Confirm Deletion</h2>
    <p>Are you sure you want to delete this user?</p>
    
    <form action="/admin/users/delete/<?php echo $userId; ?>" method="post">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
        <a href="/admin/users" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
