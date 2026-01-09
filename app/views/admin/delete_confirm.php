<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Deletion</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Confirm Deletion</h1>
        <p>Are you sure you want to delete this user? This action cannot be undone.</p>
        <form action="/admin/users/delete/<?= $userId ?>" method="post">
            <input type="hidden" name="confirm" value="true">
            <button type="submit" class="btn btn-danger">Delete</button>
            <a href="/admin/users" class="btn">Cancel</a>
        </form>
    </div>
</body>
</html>
