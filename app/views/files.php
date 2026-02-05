<?php
//- Files View
//- This view is the main interface for users to manage their files and directories.
//- It displays a list of directories and files, allows for navigation,
//- and provides forms for creating directories and uploading files.
//- The view receives data from the FileController, including a list of
//- directories and files for the current path.

//- Additionally, we have added:
//- 1. A "Logout" button in the header.
//- 2. "Delete" buttons for each file and directory.
//- 3. JavaScript confirmation dialogs for delete actions to prevent accidental deletion.

declare(strict_types=1);

//- Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$currentDir = isset($_GET['dir']) ? (int)$_GET['dir'] : null;

//- Redirect to login if user is not authenticated
if (!$user) {
    header('Location: /login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Files</title>
    <style>
        body { font-family: sans-serif; margin: 0; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .header h1 { margin: 0; }
        .header .user-info { font-size: 1.1em; }
        .breadcrumb a { text-decoration: none; color: #007bff; }
        .file-list { list-style: none; padding: 0; }
        .file-item { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .file-item:last-child { border-bottom: none; }
        .file-item .icon { margin-right: 15px; }
        .file-item .name { flex-grow: 1; }
        .file-item .actions { display: flex; }
        .file-item .actions form { margin-left: 10px; }
        .fab { position: fixed; right: 30px; bottom: 30px; background: #007bff; color: white; width: 60px; height: 60px; border-radius: 50%; text-align: center; line-height: 60px; font-size: 24px; cursor: pointer; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 8px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .logout-btn {
            background-color: #f44336;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>My Files</h1>
            <div class="user-info">
                Welcome, <?= htmlspecialchars($user['username']) ?>!
                <a href="/logout" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="breadcrumb">
            <a href="/files">Home</a>
            <?php if ($parent_id): ?>
                / <a href="/files?dir=<?= $parent_id ?>">Up</a>
            <?php endif; ?>
        </div>

        <ul class="file-list">
            <?php if ($parent_id !== null): ?>
                <li class="file-item">
                    <div class="icon">&#128193;</div>
                    <a href="/files?dir=<?= $parent_id ?>" class="name">..</a>
                </li>
            <?php endif; ?>
            <?php foreach ($directories as $dir): ?>
                <li class="file-item">
                    <div class="icon">&#128193;</div>
                    <a href="/files?dir=<?= $dir['id'] ?>" class="name"><?= htmlspecialchars($dir['name']) ?></a>
                    <div class="actions">
                        <form action="/directories/remove" method="post" onsubmit="return confirm('Are you sure you want to delete this directory and all its contents?');">
                            <input type="hidden" name="id" value="<?= $dir['id'] ?>">
                            <input type="hidden" name="directory_id" value="<?= $currentDir ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
            <?php foreach ($files as $file): ?>
                <li class="file-item">
                    <div class="icon">&#128196;</div>
                    <div class="name"><?= htmlspecialchars($file['name']) ?></div>
                    <div class="actions">
                        <form action="/files/remove" method="post" onsubmit="return confirm('Are you sure you want to delete this file?');">
                            <input type="hidden" name="id" value="<?= $file['id'] ?>">
                            <input type="hidden" name="directory_id" value="<?= $currentDir ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="fab">+</div>

    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Upload a File</h2>
            <form action="/files/upload" method="post" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <input type="hidden" name="directory_id" value="<?= $currentDir ?>">
                <button type="submit">Upload</button>
            </form>
        </div>
    </div>

    <div id="createDirModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Create a Directory</h2>
            <form action="/directories/add" method="post">
                <input type="text" name="name" placeholder="Directory Name" required>
                <input type="hidden" name="directory_id" value="<?= $currentDir ?>">
                <button type="submit">Create</button>
            </form>
        </div>
    </div>
    <script>
        const fabButton = document.querySelector('.fab');
        const uploadModal = document.getElementById('uploadModal');
        const createDirModal = document.getElementById('createDirModal');
        const closeButtons = document.querySelectorAll('.close');

        fabButton.onclick = function() {
            if (confirm("Create a new Directory? (Cancel to upload a file)")) {
                createDirModal.style.display = "block";
            } else {
                uploadModal.style.display = "block";
            }
        }

        for (let i = 0; i < closeButtons.length; i++) {
            closeButtons[i].onclick = function() {
                this.parentElement.parentElement.style.display = "none";
            }
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>
