<?php
declare(strict_types=1);

namespace SmartWorkHub\Middleware;

use SmartWorkHub\Config\Database;
use SmartWorkHub\Core\Response;

final class Authenticate
{
    public static function user(): array
    {
        $tokenHash = self::tokenHash();
        $statement = Database::connection()->prepare(
            'SELECT u.id, u.email, u.role
             FROM access_tokens t
             INNER JOIN users u ON u.id = t.user_id
             WHERE t.token_hash = :token_hash
               AND t.expires_at > CURRENT_TIMESTAMP
               AND u.status = :status
             LIMIT 1'
        );
        $statement->execute([
            'token_hash' => $tokenHash,
            'status' => 'active',
        ]);
        $user = $statement->fetch();

        if (!$user) {
            Response::json(['error' => 'Unauthenticated'], 401);
        }

        return [
            'id' => (int) $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
    }

    public static function tokenHash(): string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+([a-f0-9]{64})$/i', $header, $matches)) {
            Response::json(['error' => 'Unauthenticated'], 401);
        }

        return hash('sha256', $matches[1]);
    }
}
