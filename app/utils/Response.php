<?php
declare(strict_types=1);

namespace App\Utils;

class Response
{
    /**
     * Send a JSON response.
     *
     * @param mixed $data Data to be encoded in JSON.
     * @param int $statusCode HTTP status code.
     */
    public static function json($data, int $statusCode = 200): void
    {
        // Set the HTTP status
        http_response_code($statusCode);

        // Set the Content-Type header
        header('Content-Type: application/json');

        // Output the data in JSON format and terminate the script
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect the user to a different URL.
     *
     * @param string $url The URL to redirect to.
     */
    public static function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}
