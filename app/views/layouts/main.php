<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$this->e($title)?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <?php if (App\Utils\Auth::check()): ?>
                    <li><a href="/logout">Logout</a></li>
                    <?php if (App\Utils\Auth::isAdmin()): ?>
                        <li><a href="/admin/users">Admin</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="/login">Login</a></li>
                    <li><a href="/register">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <?=$this->section('content')?>
    </main>

    <footer>
        <p>&copy; 2024 Cloud Storage</p>
    </footer>
    <script src="/js/main.js"></script>
</body>
</html>
