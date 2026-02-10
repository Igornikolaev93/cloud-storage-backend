<?php $this->layout('layouts/main', ['title' => 'Edit User']) ?>

<h1>Edit User</h1>

<?php if (isset($error)): ?>
    <p style="color: red;"><?= $this->e($error) ?></p>
<?php endif; ?>

<form action="/admin/users/update" method="post">
    <input type="hidden" name="id" value="<?= $this->e($user['id']) ?>">
    
    <div>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= $this->e($user['username']) ?>" required>
    </div>
    
    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= $this->e($user['email']) ?>" required>
    </div>
    
    <div>
        <label for="role">Role</label>
        <select id="role" name="role">
            <option value="user" <?= ($user['role'] === 'user') ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
        </select>
    </div>
    
    <button type="submit">Update User</button>
</form>
