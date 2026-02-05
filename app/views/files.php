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
    <title>Your Dribbbox</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f8f9fb;
            margin: 0;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #1a1a1a;
            margin: 0;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 15px;
            width: 300px;
        }

        .search-bar i {
            color: #888;
        }

        .search-bar input {
            border: none;
            background: none;
            outline: none;
            margin-left: 10px;
            width: 100%;
            font-size: 14px;
        }
        
        .filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filters .recent-filter {
            color: #333;
            background: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            cursor: pointer;
        }

        .view-options button {
            border: 1px solid #e0e0e0;
            background: #fff;
            border-radius: 8px;
            cursor: pointer;
            color: #888;
            font-size: 18px;
            width: 40px;
            height: 40px;
            margin-left: 10px;
        }

        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            position: relative;
        }

        .file-item, .dir-item {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease-in-out;
            position: relative;
        }
        
        .file-item:hover, .dir-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
            border-color: #4a90e2;
        }

        .item-icon {
            font-size: 36px;
            margin-bottom: 20px;
        }
        
        .dir-item .item-icon { color: #5eb5ff; }
        .file-item .item-icon { color: #a9c5e8; }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .item-date {
            font-size: 13px;
            color: #888;
        }

        .item-actions {
            position: absolute;
            top: 15px;
            right: 15px;
        }

        .item-actions button {
            border: none;
            background: none;
            color: #888;
            cursor: pointer;
            font-size: 16px;
        }

        .fab-container {
            position: fixed;
            bottom: 40px;
            right: 40px;
        }
        
        .fab {
            background-color: #4a4ff4;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 28px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.25);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 100; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto; 
            padding: 25px;
            border: none;
            width: 90%; 
            max-width: 450px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
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
        <div class="header">
            <h1>Your Dribbbox</h1>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search Folder">
            </div>
        </div>
        
        <div class="filters">
            <div class="recent-filter">
                Recent <i class="fas fa-chevron-down"></i>
            </div>
            <div class="view-options">
                <button title="List View"><i class="fas fa-list"></i></button>
                <button title="Grid View"><i class="fas fa-th-large"></i></button>
            </div>
        </div>

        <div class="file-grid">
            <?php if ($directoryId && isset($contents['parent_id'])): ?>
                <a href="/files?dir=<?= $contents['parent_id'] ?>" class="dir-item">
                    <div class="item-icon"><i class="fas fa-arrow-left"></i></div>
                    <div class="item-name">..</div>
                </a>
            <?php endif; ?>

            <?php foreach ($contents['directories'] as $directory): ?>
                <a href="/files?dir=<?= $directory['id'] ?>" class="dir-item">
                    <div class="item-icon"><i class="fas fa-folder"></i></div>
                    <div class="item-name"><?= htmlspecialchars($directory['name']) ?></div>
                    <div class="item-date"><?= date('M d, Y', strtotime($directory['created_at'])) ?></div>
                    <div class="item-actions">
                        <button><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php foreach ($contents['files'] as $file): ?>
                <div class="file-item">
                    <div class="item-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="item-name"><?= htmlspecialchars($file['name']) ?></div>
                    <div class="item-date"><?= date('M d, Y', strtotime($file['created_at'])) ?></div>
                    <div class="item-actions">
                        <button><i class="fas fa-ellipsis-h"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="fab-container">
        <button class="fab" id="fab-button">+</button>
    </div>
    
    <!-- Modals -->
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

    <script>
        var uploadModal = document.getElementById("upload-modal");
        var createDirModal = document.getElementById("create-dir-modal");
        var fabButton = document.getElementById("fab-button");
        var closeButtons = document.getElementsByClassName("close-button");

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