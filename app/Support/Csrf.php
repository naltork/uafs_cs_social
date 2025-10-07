<?php
declare(strict_types=1);

namespace App\Support;

final class Csrf
{
    public static function token(Container $c): string
    {
        // HMAC(session_id) using APP_KEY; rotate per session
        $t = hash_hmac('sha256', session_id(), $c->appKey);
        $_SESSION['_csrf'] = $t;
        return $t;
    }

    public static function check(Container $c, ?string $posted): void
    {
        $expected = $_SESSION['_csrf'] ?? null;
        if (!$expected || !$posted || !hash_equals($expected, $posted)) {
            http_response_code(403);
            exit('CSRF token mismatch');
        }
    }
}