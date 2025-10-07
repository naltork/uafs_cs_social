<?php
declare(strict_types=1);

namespace App\Middleware;

final class AuthGuard
{
    public static function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /login'); exit;
        }
    }
}