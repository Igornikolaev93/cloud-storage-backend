<?php
declare(strict_types=1);
use App\Utils\Auth;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

// Get user data
$user = Auth::user();

// --- FIX: The current directory ID is now correctly retrieved from the URL query parameter ---
// This ensures that all file and directory operations happen in the context of the correct folder.
$currentDirectoryId = isset($_GET['dir']) ? (int)$_GET['dir'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Files</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-4">
        <header class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">My Files</h1>
            <div class="flex items-center">
                <span class="mr-4">Welcome, <?= htmlspecialchars($user['username']) ?>!</span>
                <a href="/logout" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Logout</a>
            </div>
        </header>

        <div class="mb-4 flex space-x-2">
            <!-- File Upload Form -->
            <form action="/files/upload" method="post" enctype="multipart/form-data" class="flex-1">
                 <!-- FIX: The current directory ID is now correctly included as a hidden field. -->
                <input type="hidden" name="directory_id" value="<?= $currentDirectoryId ?>">
                <input type="file" name="file" class="p-2 border rounded w-full">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Upload File</button>
            </form>

            <!-- Directory Creation Form -->
            <form action="/directories/add" method="post" class="flex-1">
                <!-- FIX: The current directory ID is now correctly included as a hidden field. -->
                <input type="hidden" name="directory_id" value="<?= $currentDirectoryId ?>">
                <input type="text" name="name" placeholder="New directory name" class="p-2 border rounded w-full">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Create Directory</button>
            </form>
        </div>

        <!-- File and Directory Listing -->
        <div class="bg-white shadow-md rounded p-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 bg-gray-50"></th>
                    </tr>
                </thead>
                <tbody id="file-list">
                    <!-- Dynamic content will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- FIX: The fetch URL now correctly includes the directory ID query parameter ---
            const directoryId = <?= json_encode($currentDirectoryId) ?>;
            const apiUrl = directoryId ? `/directories/get/${directoryId}` : '/files/list';

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const fileList = document.getElementById('file-list');
                    fileList.innerHTML = ''; // Clear existing content

                    // "Up" button for navigating to the parent directory
                    if (data.data.parent_id !== undefined) {
                        const upRow = `<tr>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <a href="/files${data.data.parent_id ? '?dir=' + data.data.parent_id : ''}" class="text-blue-600 hover:text-blue-800">.. (Up)</a>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>`;
                        fileList.innerHTML += upRow;
                    }

                    // Render directories
                    data.data.directories.forEach(dir => {
                        const dirRow = `<tr>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <a href="/files?dir=${dir.id}" class="text-blue-600 hover:text-blue-800">${dir.name}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap">${new Date(dir.created_at).toLocaleString()}</td>
                            <td class="px-6 py-4 whitespace-no-wrap text-right">
                                <form action="/directories/remove" method="post" onsubmit="return confirm('Are you sure you want to delete this directory?');">
                                    <input type="hidden" name="id" value="${dir.id}">
                                    <input type="hidden" name="directory_id" value="${directoryId}">
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>`;
                        fileList.innerHTML += dirRow;
                    });

                    // Render files
                    data.data.files.forEach(file => {
                        const fileRow = `<tr>
                            <td class="px-6 py-4 whitespace-no-wrap">${file.name}</td>
                            <td class="px-6 py-4 whitespace-no-wrap">${new Date(file.created_at).toLocaleString()}</td>
                            <td class="px-6 py-4 whitespace-no-wrap text-right">
                                <form action="/files/remove" method="post" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                    <input type="hidden" name="id" value="${file.id}">
                                    <input type="hidden" name="directory_id" value="${directoryId}">
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </td>
                        </tr>`;
                        fileList.innerHTML += fileRow;
                    });
                })
                .catch(error => console.error('Error loading file list:', error));
        });
    </script>

</body>
</html>
