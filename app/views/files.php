<?php
declare(strict_types=1);
use App\Utils\Auth;

// --- Session and Authentication ---
// Ensures a session is active and the user is authenticated before rendering the page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

// --- User and Directory Context ---
// Retrieves the logged-in user and the current directory ID from the URL.
$user = Auth::user();
$currentDirectoryId = isset($_GET['dir']) ? (int)$_GET['dir'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Files</title>
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Feather Icons for a cleaner UI -->
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto p-4">
        <!-- Header Section -->
        <header class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">My Files</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Welcome, <?= htmlspecialchars($user['username']) ?>!</span>
                <a href="/logout" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg transition-transform transform hover:scale-105">Logout</a>
            </div>
        </header>

        <!-- Action Forms: File Upload and Directory Creation -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- File Upload -->
            <form action="/files/upload" method="post" enctype="multipart/form-data" class="bg-white p-4 rounded-lg shadow-sm">
                <input type="hidden" name="directory_id" value="<?= $currentDirectoryId ?>">
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload a New File</label>
                <div class="flex space-x-2">
                    <input type="file" name="file" class="flex-grow p-2 border border-gray-300 rounded-lg">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-transform transform hover:scale-105">
                        <i data-feather="upload" class="h-5 w-5"></i>
                    </button>
                </div>
            </form>

            <!-- Directory Creation -->
            <form action="/directories/add" method="post" class="bg-white p-4 rounded-lg shadow-sm">
                <input type="hidden" name="directory_id" value="<?= $currentDirectoryId ?>">
                <label class="block text-sm font-medium text-gray-700 mb-2">Create a New Directory</label>
                <div class="flex space-x-2">
                    <input type="text" name="name" placeholder="Directory name" class="flex-grow p-2 border border-gray-300 rounded-lg">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition-transform transform hover:scale-105">
                        <i data-feather="folder-plus" class="h-5 w-5"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- File and Directory Listing -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="file-list" class="divide-y divide-gray-200">
                    <!-- Dynamic content is loaded here via JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- JavaScript to fetch and display file/directory data -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const directoryId = <?= json_encode($currentDirectoryId) ?>;
            const apiUrl = directoryId ? `/directories/get/${directoryId}` : '/files/list';

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const fileList = document.getElementById('file-list');
                    fileList.innerHTML = ''; // Clear previous content

                    // "Up" navigation link
                    if (data.data.parent_id !== undefined && data.data.parent_id !== null) {
                        const upUrl = data.data.parent_id ? `/files?dir=${data.data.parent_id}` : '/files';
                        fileList.innerHTML += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="${upUrl}" class="flex items-center text-blue-600 hover:text-blue-800">
                                        <i data-feather="arrow-up" class="h-5 w-5 mr-2"></i> .. (Up)
                                    </a>
                                </td>
                                <td></td>
                                <td></td>
                            </tr>`;
                    }

                    // Render directories with folder icons
                    data.data.directories.forEach(dir => {
                        fileList.innerHTML += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="/files?dir=${dir.id}" class="flex items-center text-blue-600 hover:text-blue-800">
                                        <i data-feather="folder" class="h-5 w-5 mr-2"></i> ${dir.name}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(dir.created_at).toLocaleString()}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <form action="/directories/remove" method="post" onsubmit="return confirm('Delete this directory?');">
                                        <input type="hidden" name="id" value="${dir.id}">
                                        <input type="hidden" name="directory_id" value="${directoryId}">
                                        <button type="submit" class="text-red-500 hover:text-red-700"><i data-feather="trash-2" class="h-5 w-5"></i></button>
                                    </form>
                                </td>
                            </tr>`;
                    });

                    // Render files with file icons
                    data.data.files.forEach(file => {
                        fileList.innerHTML += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="flex items-center">
                                        <i data-feather="file" class="h-5 w-5 mr-2"></i> ${file.name}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${new Date(file.created_at).toLocaleString()}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <form action="/files/remove" method="post" onsubmit="return confirm('Delete this file?');">
                                        <input type="hidden" name="id" value="${file.id}">
                                        <input type="hidden" name="directory_id" value="${directoryId}">
                                        <button type="submit" class="text-red-500 hover:text-red-700"><i data-feather="trash-2" class="h-5 w-5"></i></button>
                                    </form>
                                </td>
                            </tr>`;
                    });

                    // Activate Feather Icons
                    feather.replace();
                })
                .catch(error => {
                    console.error('Error loading file list:', error);
                    fileList.innerHTML = '<tr><td colspan="3" class="text-center py-10">Error loading files. Please try again.</td></tr>';
                });
        });
    </script>

</body>
</html>
