<?php include __DIR__ . '/../partials/header.php'; ?>

<div class="container">
    <h2>User Management</h2>

    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <form action="/admin/users/<?php echo $user['id']; ?>/role" method="post" style="display: inline;">
                            <select name="role" class="form-control-sm">
                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                        <form action="/admin/users/<?php echo $user['id']; ?>/delete" method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="/admin/users?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
