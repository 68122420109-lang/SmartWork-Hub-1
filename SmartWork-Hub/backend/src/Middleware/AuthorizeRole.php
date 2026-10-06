<?php
declare(strict_types=1);

namespace SmartWorkHub\Middleware;

use SmartWorkHub\Core\Response;

final class AuthorizeRole
{
    public static function requireAny(array $allowedRoles): array
    {
        $user = Authenticate::user();
        if (!in_array($user['role'], $allowedRoles, true)) {
            Response::json(['error' => 'Forbidden'], 403);
        }

        return $user;
    }
}
