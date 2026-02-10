<?php
use App\Utils\Auth;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloud Storage</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="/">Cloud Storage</a>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php if (Auth::check()): ?>
                        <li><a href="/files">My Files</a></li>
                        <?php if (Auth::isAdmin()): ?>
                            <li><a href="/admin/users">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="/logout">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/login">Login</a></li>
                        <li><a href="/register">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
