<?php
declare(strict_types=1);

namespace SmartWorkHub\Core;

final class Response
{
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function requestBody(): array
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body === false ? '' : $body, true);

        if (!is_array($data)) {
            self::json(['error' => 'Request body must be a valid JSON object'], 400);
        }

        return $data;
    }
}
