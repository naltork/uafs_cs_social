<?php
declare(strict_types=1);

namespace App\Support;

use PDO;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Container
{
    public PDO $db;
    public Environment $twig;
    public string $appKey;

    public function __construct()
    {
        // Start session early
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Basic cookie hardening
            session_set_cookie_params([
                'secure'   => false, // set true when using HTTPS
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        $this->appKey = (string)($_ENV['APP_KEY'] ?? '');
        if ($this->appKey === '') {
            throw new \RuntimeException('APP_KEY missing in .env');
        }

        // PDO (MariaDB)
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'] ?? '127.0.0.1',
            $_ENV['DB_PORT'] ?? '3306',
            $_ENV['DB_NAME'] ?? 'uafs_social'
        );

        $this->db = new PDO(
            $dsn,
            $_ENV['DB_USER'] ?? 'uafs_app',
            $_ENV['DB_PASS'] ?? '',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );

        // Twig
        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => (($_ENV['APP_DEBUG'] ?? 'false') === 'true')
        ]);
    }
}