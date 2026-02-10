<?php
declare(strict_types=1);
use App\Utils\Auth;

// --- Session, Authentication, and Error Handling ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

$user = Auth::getUser();
$currentParentId = isset($_GET['dir']) ? (int)$_GET['dir'] : null;
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Files</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-4">
        <!-- Header -->
        <header class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">My Files</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, <?= htmlspecialchars($user['username']) ?>!</span>
                <a href="/logout" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg">Logout</a>
            </div>
        </header>

        <!-- Error Message Banner -->
        <?php if ($error_message): ?>
            <div id="error-banner" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline"><?= $error_message ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="document.getElementById('error-banner').style.display='none';">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.818l-2.651 3.031a1.2 1.2 0 1 1-1.697-1.697l2.651-3.03-2.651-3.031a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.651 3.03 2.651 3.031a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
        <?php endif; ?>

        <!-- Action Forms -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- File Upload -->
            <form action="/files/upload" method="post" enctype="multipart/form-data" class="bg-white p-4 rounded-lg shadow-sm">
                <input type="hidden" name="parent_id" value="<?= $currentParentId ?>">
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload a New File</label>
                <div class="flex space-x-2">
                    <input type="file" name="file" class="flex-grow p-2 border rounded-lg">
                    <button type="submit" name="upload" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg"><i data-feather="upload"></i></button>
                </div>
            </form>

            <!-- Directory Creation -->
            <form action="/directories/add" method="post" class="bg-white p-4 rounded-lg shadow-sm">
                <input type="hidden" name="parent_id" value="<?= $currentParentId ?>">
                <label class="block text-sm font-medium text-gray-700 mb-2">Create a New Directory</label>
                <div class="flex space-x-2">
                    <input type="text" name="name" placeholder="Directory name" class="flex-grow p-2 border rounded-lg">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg"><i data-feather="folder-plus"></i></button>
                </div>
            </form>
        </div>

        <!-- File Listing -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody id="file-list" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function renameDirectory(directoryId, currentName) {
            const newName = prompt("Enter the new directory name:", currentName);
            if (newName && newName.trim() !== '' && newName !== currentName) {
                const formData = new FormData();
                formData.append('id', directoryId);
                formData.append('name', newName.trim());
                
                fetch('/directories/rename', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                }).then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Failed to rename directory.');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while renaming the directory.');
                });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const parentId = <?= json_encode($currentParentId) ?>;
            const apiUrl = parentId ? `/directories/get/${parentId}` : '/files/list';

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const fileList = document.getElementById('file-list');
                    fileList.innerHTML = '';

                    if (data.data.parent_id !== undefined && data.data.parent_id !== null) {
                        const upUrl = data.data.parent_id ? `/files?dir=${data.data.parent_id}` : '/files';
                        fileList.innerHTML += `<tr class="hover:bg-gray-50"><td class="px-6 py-4"><a href="${upUrl}" class="flex items-center text-blue-600"><i data-feather="arrow-up" class="mr-2"></i> .. (Up)</a></td><td></td><td></td></tr>`;
                    }

                    data.data.directories.forEach(dir => {
                        fileList.innerHTML += `<tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><a href="/files?dir=${dir.id}" class="flex items-center text-blue-600"><i data-feather="folder" class="mr-2"></i> ${dir.name}</a></td>
                            <td class="px-6 py-4 text-sm text-gray-500">${new Date(dir.created_at).toLocaleString()}</td>
                            <td class="px-6 py-4 text-right"><button onclick="renameDirectory(${dir.id}, '${dir.name}')" class="text-blue-500 mr-2"><i data-feather="edit"></i></button><form action="/directories/remove" method="post" onsubmit="return confirm('Delete?');" style="display: inline-block;"><input type="hidden" name="id" value="${dir.id}"><input type="hidden" name="parent_id" value="${parentId}"><button type="submit" class="text-red-500"><i data-feather="trash-2"></i></button></form></td></tr>`;
                    });

                    data.data.files.forEach(file => {
                        fileList.innerHTML += `<tr class="hover:bg-gray-50">
                            <td class="px-6 py-4"><span class="flex items-center"><i data-feather="file" class="mr-2"></i> ${file.name}</span></td>
                            <td class="px-6 py-4 text-sm text-gray-500">${new Date(file.created_at).toLocaleString()}</td>
                            <td class="px-6 py-4 text-right"><a href="/files/download/${file.id}" class="text-blue-500 mr-2"><i data-feather="download"></i></a><form action="/files/remove" method="post" onsubmit="return confirm('Delete?');" style="display: inline-block;"><input type="hidden" name="id" value="${file.id}"><input type="hidden" name="parent_id" value="${parentId}"><button type="submit" class="text-red-500"><i data-feather="trash-2"></i></button></form></td></tr>`;
                    });

                    feather.replace();
                })
                .catch(error => {
                    console.error('Error:', error);
                    fileList.innerHTML = '<tr><td colspan="3" class="text-center py-10">Error loading files.</td></tr>';
                });
        });
    </script>

</body>
</html>
