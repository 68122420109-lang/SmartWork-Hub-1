<?php
declare(strict_types=1);

namespace SmartWorkHub\Controllers;

use SmartWorkHub\Core\Response;

final class HealthController
{
    public static function show(): void
    {
        Response::json([
            'status' => 'ok',
            'service' => 'SmartWork Hub API',
        ]);
    }
}
