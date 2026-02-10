<?php $this->layout('layouts/main', ['title' => 'Admin Panel']) ?>

<h1>User Management</h1>

<?php if (isset($error)): ?>
    <p style="color: red;"><?= $this->e($error) ?></p>
<?php endif; ?>

<table>
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
                <td><?= $this->e($user['id']) ?></td>
                <td><?= $this->e($user['username']) ?></td>
                <td><?= $this->e($user['email']) ?></td>
                <td><?= $this->e($user['role']) ?></td>
                <td>
                    <a href="/admin/users/edit?id=<?= $this->e($user['id']) ?>">Edit</a>
                    <form action="/admin/users/delete" method="post" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $this->e($user['id']) ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
