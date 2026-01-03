<?php include 'partials/header.php'; ?>

<div class="container">
    <h2>My Files</h2>
    <p>Welcome, <?php echo $username; ?>! <a href="/logout">Logout</a></p>
    
    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <h3>Upload File</h3>
    <form action="/files/add" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <input type="file" name="file" class="form-control-file">
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <hr>

    <h3>My Files</h3>
    <ul class="list-group">
        <?php foreach ($files as $file): ?>
            <li class="list-group-item">
                <?php echo htmlspecialchars($file['filename']); ?>
                <a href="/files/remove/<?php echo $file['id']; ?>" class="btn btn-danger btn-sm float-right">Delete</a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php include 'partials/footer.php'; ?>
