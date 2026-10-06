<?php
declare(strict_types=1);

namespace SmartWorkHub\Controllers;

use DateTimeImmutable;
use SmartWorkHub\Config\Database;
use SmartWorkHub\Core\Response;
use SmartWorkHub\Middleware\Authenticate;

final class AuthController
{
    public static function login(): void
    {
        $data = Response::requestBody();
        $email = filter_var($data['email'] ?? null, FILTER_VALIDATE_EMAIL);
        $password = $data['password'] ?? null;

        if ($email === false || !is_string($password) || $password === '') {
            Response::json(['error' => 'Valid email and password are required'], 422);
        }

        $pdo = Database::connection();
        $statement = $pdo->prepare(
            'SELECT id, email, password_hash, role, status
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            Response::json(['error' => 'Invalid email or password'], 401);
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $plainToken);
        $ttlHours = filter_var(
            $_ENV['ACCESS_TOKEN_TTL_HOURS'] ?? '12',
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 720]]
        );
        $expiresAt = (new DateTimeImmutable())
            ->modify('+' . ($ttlHours ?: 12) . ' hours')
            ->format('Y-m-d H:i:s');

        $insert = $pdo->prepare(
            'INSERT INTO access_tokens (user_id, token_hash, expires_at)
             VALUES (:user_id, :token_hash, :expires_at)'
        );
        $insert->execute([
            'user_id' => $user['id'],
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        Response::json([
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'user' => [
                'id' => (int) $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
            ],
        ]);
    }

    public static function me(): void
    {
        Response::json(['user' => Authenticate::user()]);
    }

    public static function logout(): void
    {
        $tokenHash = Authenticate::tokenHash();
        $statement = Database::connection()->prepare(
            'DELETE FROM access_tokens WHERE token_hash = :token_hash'
        );
        $statement->execute(['token_hash' => $tokenHash]);

        Response::json(['message' => 'Logged out']);
    }
}
