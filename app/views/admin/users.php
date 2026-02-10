<?php
$this->layout('layouts/main', ['title' => 'Admin - Users']);
?>

<div class="container">
    <h2>User Management</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="/admin/users/edit?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <form action="/admin/users/delete?id=<?= $user['id'] ?>" method="POST" style="display: inline-block;">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
