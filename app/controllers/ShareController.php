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
    public function share($fileId)
    {
        $fileId = (int) $fileId;
        $currentUser = Auth::getUser();
        if (!$currentUser) {
            Response::redirect('/login');
        }

        $file = File::findById($fileId);

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

    public function unshare($fileId, $userId)
    {
        $fileId = (int) $fileId;
        $userId = (int) $userId;
        $currentUser = Auth::getUser();
        if (!$currentUser) {
            Response::redirect('/login');
        }

        $file = File::findById($fileId);

        if (!$file || $file['user_id'] !== $currentUser['id']) {
            Response::json(['error' => 'File not found or access denied.'], 404);
            return;
        }

        Share::remove($fileId, $userId);

        Response::json(['success' => true]);
    }

    public function getSharedUsers($fileId)
    {
        $fileId = (int) $fileId;
        $currentUser = Auth::getUser();
        if (!$currentUser) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $file = File::findById($fileId);

        if (!$file || $file['user_id'] !== $currentUser['id']) {
            Response::json(['error' => 'File not found or access denied.'], 404);
            return;
        }

        $sharedUsers = Share::findByFileId($fileId);

        Response::json($sharedUsers);
    }

    public function shareWithUser($fileId, $userId)
    {
        $fileId = (int) $fileId;
        $userId = (int) $userId;
        $currentUser = Auth::getUser();
        if (!$currentUser) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }

        $file = File::findById($fileId);

        if (!$file || $file['user_id'] !== $currentUser['id']) {
            Response::json(['error' => 'File not found or access denied.'], 404);
            return;
        }

        Share::add($fileId, $userId);

        Response::json(['success' => true]);
    }
}
