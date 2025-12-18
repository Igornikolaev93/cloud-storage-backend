<?php
declare(strict_types=1);

namespace App\Controllers;

class BaseController
{
    /**
     * Get JSON data from the request body.
     */
    protected function getRequestData(): array
    {
        $content = file_get_contents('php://input');
        if (empty($content)) {
            return [];
        }
        return json_decode($content, true) ?? [];
    }
}
