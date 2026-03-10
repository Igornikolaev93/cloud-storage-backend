<?php
/**
 * Проверка статуса проекта Supabase
 */

echo "🔍 ПРОВЕРКА СТАТУСА ПРОЕКТА\n";
echo "===========================\n\n";

$project_ref = 'vvqrogorxkspdudypriy';
$host = 'aws-0-eu-central-1.pooler.supabase.com';
$port = 6543;
$password = 'pppx37CRZxhSUqCt';

echo "Project ref: {$project_ref}\n";
echo "Host: {$host}\n";
echo "Port: {$port}\n\n";

// Проверка доступности API Supabase
echo "Проверка API Supabase...\n";
$api_url = "https://api.supabase.com/v1/projects/{$project_ref}";

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $password, // Пробуем использовать пароль как токен
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "✅ API доступен\n";
} else {
    echo "❌ API недоступен (HTTP {$http_code})\n";
    echo "   Возможно проект не активен или заморожен\n\n";
}

// Проверка через стандартный порт
echo "\nПроверка прямого подключения...\n";
$hosts_to_try = [
    "db.{$project_ref}.supabase.co",
    "aws-0-eu-central-1.pooler.supabase.com",
    $project_ref . ".supabase.co"
];

foreach ($hosts_to_try as $test_host) {
    echo "\nТестируем хост: {$test_host}\n";
    
    $ip = gethostbyname($test_host);
    if ($ip !== $test_host) {
        echo "  ✅ DNS резолвинг: {$ip}\n";
        
        // Проверка порта
        $fp = @fsockopen($test_host, 5432, $errno, $errstr, 3);
        if ($fp) {
            echo "  ✅ Порт 5432 открыт\n";
            fclose($fp);
        } else {
            echo "  ❌ Порт 5432 закрыт: {$errstr}\n";
        }
    } else {
        echo "  ❌ DNS не резолвится\n";
    }
}

echo "\n📋 Инструкция:\n";
echo "1. Зайдите в https://app.supabase.com\n";
echo "2. Проверьте, активен ли проект (не заморожен)\n";
echo "3. Если проект заморожен - разморозьте его\n";
echo "4. Проверьте Connection String в настройках\n";