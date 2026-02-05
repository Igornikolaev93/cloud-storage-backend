<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../models/File.php';

use App\Utils\Auth;
use App\Models\File;

if (!Auth::check()) {
    header('Location: /login');
    exit;
}

$user = Auth::user();
$directoryId = isset($_GET['dir']) ? (int)$_GET['dir'] : null;
$contents = File::getDirectoryContents($user['id'], $directoryId);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Files</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 960px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007aff;
            margin-bottom: 20px;
        }

        .file-manager {
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .file-toolbar {
            padding: 10px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
        }

        .file-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .file-list-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .file-list-item:last-child {
            border-bottom: none;
        }

        .file-icon {
            margin-right: 10px;
            color: #007aff;
        }

        .file-name {
            flex-grow: 1;
        }

        .file-actions {
            display: flex;
        }

        .file-actions button {
            margin-left: 10px;
        }
        
        .nav-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .nav-buttons a {
            color: #007aff;
            text-decoration: none;
            margin-left: 20px;
        } 
        
        .nav-buttons a:hover {
            text-decoration: underline;
        }

        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            max-width: 500px;
            border-radius: 8px;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="nav-buttons">
            <a href="/">Home</a>
            <a href="/logout">Log Out</a>
        </div>
        <h1>My Files</h1>

        <div class="file-manager">
            <div class="file-toolbar">
                <button id="upload-button">Upload File</button>
                <button id="create-dir-button">Create Directory</button>
            </div>
            <ul class="file-list">
                <?php if ($directoryId): ?>
                    <li class="file-list-item">
                        <i class="fas fa-folder-open file-icon"></i>
                        <a href="/files?dir=<?= $contents['parent_id'] ?>" class="file-name">..</a>
                    </li>
                <?php endif; ?>

                <?php foreach ($contents['directories'] as $directory): ?>
                    <li class="file-list-item">
                        <i class="fas fa-folder file-icon"></i>
                        <a href="/files?dir=<?= $directory['id'] ?>" class="file-name"><?= htmlspecialchars($directory['name']) ?></a>
                        <div class="file-actions">
                            <button class="rename-button" data-id="<?= $directory['id'] ?>" data-name="<?= htmlspecialchars($directory['name']) ?>" data-type="directory">Rename</button>
                            <button class="delete-button" data-id="<?= $directory['id'] ?>" data-name="<?= htmlspecialchars($directory['name']) ?>" data-type="directory">Delete</button>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php foreach ($contents['files'] as $file): ?>
                    <li class="file-list-item">
                        <i class="fas fa-file file-icon"></i>
                        <span class="file-name"><?= htmlspecialchars($file['name']) ?></span>
                        <div class="file-actions">
                            <button class="rename-button" data-id="<?= $file['id'] ?>" data-name="<?= htmlspecialchars($file['name']) ?>" data-type="file">Rename</button>
                            <button class="delete-button" data-id="<?= $file['id'] ?>" data-name="<?= htmlspecialchars($file['name']) ?>" data-type="file">Delete</button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Upload File</h2>
            <form action="/files/add" method="post" enctype="multipart/form-data">
                <input type="hidden" name="directory_id" value="<?= $directoryId ?>">
                <input type="file" name="file" required>
                <button type="submit">Upload</button>
            </form>
        </div>
    </div>

    <!-- Create Directory Modal -->
    <div id="create-dir-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Create Directory</h2>
            <form action="/directories/add" method="post">
                <input type="hidden" name="directory_id" value="<?= $directoryId ?>">
                <input type="text" name="name" placeholder="Directory Name" required>
                <button type="submit">Create</button>
            </form>
        </div>
    </div>

    <!-- Rename Modal -->
    <div id="rename-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Rename</h2>
            <form id="rename-form" method="post">
                <input type="hidden" name="id">
                <input type="hidden" name="type">
                <input type="text" name="name" required>
                <button type="submit">Rename</button>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="delete-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Delete</h2>
            <p>Are you sure you want to delete <strong id="delete-name"></strong>?</p>
            <form id="delete-form" method="post">
                <input type="hidden" name="id">
                <input type="hidden" name="type">
                <button type="submit">Delete</button>
            </form>
        </div>
    </div>

    <script>
        // Get the modals
        var uploadModal = document.getElementById("upload-modal");
        var createDirModal = document.getElementById("create-dir-modal");
        var renameModal = document.getElementById("rename-modal");
        var deleteModal = document.getElementById("delete-modal");

        // Get the buttons that open the modals
        var uploadBtn = document.getElementById("upload-button");
        var createDirBtn = document.getElementById("create-dir-button");
        var renameButtons = document.getElementsByClassName("rename-button");
        var deleteButtons = document.getElementsByClassName("delete-button");

        // Get the <span> elements that close the modals
        var closeButtons = document.getElementsByClassName("close-button");

        // When the user clicks the button, open the modal 
        uploadBtn.onclick = function() {
            uploadModal.style.display = "block";
        }
        createDirBtn.onclick = function() {
            createDirModal.style.display = "block";
        }

        for (let i = 0; i < renameButtons.length; i++) {
            renameButtons[i].onclick = function() {
                let id = this.dataset.id;
                let name = this.dataset.name;
                let type = this.dataset.type;
                let form = document.getElementById('rename-form');
                form.action = '/' + type + 's/rename';
                form.elements['id'].value = id;
                form.elements['name'].value = name;
                form.elements['type'].value = type;
                renameModal.style.display = "block";
            }
        }

        for (let i = 0; i < deleteButtons.length; i++) {
            deleteButtons[i].onclick = function() {
                let id = this.dataset.id;
                let name = this.dataset.name;
                let type = this.dataset.type;
                let form = document.getElementById('delete-form');
                form.action = '/' + type + 's/remove/' + id;
                form.elements['id'].value = id;
                form.elements['type'].value = type;
                document.getElementById('delete-name').textContent = name;
                deleteModal.style.display = "block";
            }
        }

        // When the user clicks on <span> (x), close the modal
        for (let i = 0; i < closeButtons.length; i++) {
            closeButtons[i].onclick = function() {
                uploadModal.style.display = "none";
                createDirModal.style.display = "none";
                renameModal.style.display = "none";
                deleteModal.style.display = "none";
            }
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == uploadModal) {
                uploadModal.style.display = "none";
            } else if (event.target == createDirModal) {
                createDirModal.style.display = "none";
            } else if (event.target == renameModal) {
                renameModal.style.display = "none";
            } else if (event.target == deleteModal) {
                deleteModal.style.display = "none";
            }
        }
    </script>

</body>
</html>
