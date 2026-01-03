<?php
if ($argc < 2) {
    echo "Usage: php create_hash.php <password>\n";
    exit(1);
}

$password = $argv[1];
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
