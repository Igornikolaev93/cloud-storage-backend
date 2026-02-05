
<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\File;
use App\Models\Database;

class FileAndDirectoryOperationsTest extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        // Set up a test user
        $this->user = [
            'id' => 1,
            'email' => 'test@example.com',
            'password' => 'password'
        ];

        // Set up a test database
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTables($pdo);
        Database::setConnection($pdo);
    }

    private function createTables(PDO $pdo)
    {
        $pdo->exec('
            CREATE TABLE users (
                id INTEGER PRIMARY KEY,
                email TEXT NOT NULL,
                password TEXT NOT NULL
            );
        ');
        $pdo->exec('
            CREATE TABLE directories (
                id INTEGER PRIMARY KEY,
                user_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                parent_id INTEGER
            );
        ');
        $pdo->exec('
            CREATE TABLE files (
                id INTEGER PRIMARY KEY,
                user_id INTEGER NOT NULL,
                directory_id INTEGER,
                name TEXT NOT NULL,
                stored_name TEXT NOT NULL,
                mime_type TEXT NOT NULL,
                size INTEGER NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ');
    }

    public function testDirectoryCreation()
    {
        $dirId = File::createDirectory($this->user['id'], 'test-dir');
        $this->assertNotNull($dirId);

        $dir = File::findDirectoryById($dirId, $this->user['id']);
        $this->assertEquals('test-dir', $dir['name']);
    }

    public function testFileCreation()
    {
        $fileId = File::createFile($this->user['id'], null, 'test-file.txt', 'stored-file.txt', 'text/plain', 123);
        $this->assertNotNull($fileId);

        $file = File::findFileById($fileId, $this->user['id']);
        $this->assertEquals('test-file.txt', $file['name']);
    }

    public function testDirectoryRename()
    {
        $dirId = File::createDirectory($this->user['id'], 'test-dir');
        File::renameDirectory($dirId, $this->user['id'], 'new-test-dir');

        $dir = File::findDirectoryById($dirId, $this->user['id']);
        $this->assertEquals('new-test-dir', $dir['name']);
    }

    public function testFileRename()
    {
        $fileId = File::createFile($this->user['id'], null, 'test-file.txt', 'stored-file.txt', 'text/plain', 123);
        File::renameFile($fileId, $this->user['id'], 'new-test-file.txt');

        $file = File::findFileById($fileId, $this->user['id']);
        $this->assertEquals('new-test-file.txt', $file['name']);
    }

    public function testDirectoryDeletion()
    {
        $dirId = File::createDirectory($this->user['id'], 'test-dir');
        File::deleteDirectory($dirId, $this->user['id']);

        $dir = File::findDirectoryById($dirId, $this->user['id']);
        $this->assertNull($dir);
    }

    public function testFileDeletion()
    {
        $fileId = File::createFile($this->user['id'], null, 'test-file.txt', 'stored-file.txt', 'text/plain', 123);
        File::deleteFile($fileId, $this->user['id']);

        $file = File::findFileById($fileId, $this->user['id']);
        $this->assertNull($file);
    }
}
