<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Storage</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <?php
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
    ?>
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/styles.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $basePath; ?>/">Cloud Storage</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if (App\Utils\Auth::check()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>/">My Files</a>
                    </li>
                    <?php if (App\Utils\Auth::hasRole('admin')): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $basePath; ?>/admin/users">Admin</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>/logout">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $basePath; ?>/register">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
