<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Share;
use App\Models\User;
use App\Models\File;
use App\Utils\Auth;
use App\Utils\Response;

class ShareController extends BaseController
{
    /**
     * Share a file with a user.
     */
    public function share(int $fileId)
    {
        $currentUser = Auth::getUser();
        if (!$currentUser) {
            Response::redirect('/login');
        }

        $file = File::findById($fileId);

        // Check if the file exists and belongs to the current user
        if (!$file || $file['user_id'] !== $currentUser['id']) {
            Response::json(['error' => 'File not found or access denied.'], 404);
            return;
        }

        $email = $_POST['email'] ?? '';
        $userToShareWith = User::findByEmail($email);

        if (!$userToShareWith) {
            Response::json(['error' => 'User not found.'], 404);
            return;
        }

        Share::add($fileId, $userToShareWith['id']);

        Response::json(['success' => true]);
    }

    /**
     * Unshare a file from a user.
     */
    public function unshare(int $fileId, int $userId)
    {
        $currentUser = Auth::getUser();
        if (!$currentUser) {
            Response::redirect('/login');
        }

        $file = File::findById($fileId);

        // Check if the file exists and belongs to the current user
        if (!$file || $file['user_id'] !== $currentUser['id']) {
            Response::json(['error' => 'File not found or access denied.'], 404);
            return;
        }

        Share::remove($fileId, $userId);

        Response::json(['success' => true]);
    }
}
