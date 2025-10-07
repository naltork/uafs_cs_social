<?php
declare(strict_types=1);

use App\Support\Container;
use App\Controllers\AuthController;
use App\Middleware\AuthGuard;
use App\Support\Csrf;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$isDebug = (($_ENV['APP_DEBUG'] ?? 'false') === 'true');
ini_set('display_errors', $isDebug ? '1' : '0');
ini_set('log_errors', '1');

function send(string $html, int $code=200): void {
    http_response_code($code);
    header('Content-Type: text/html; charset=utf-8');
    echo $html; exit;
}
function redirect(string $path, int $code=302): void {
    if (!str_starts_with($path, '/')) $path = '/';
    header('Location: ' . $path, true, $code); exit;
}
function notFound(): void { send('Not Found', 404); }
function methodNotAllowed(): void { send('Method Not Allowed', 405); }

try {
    $c = new Container();
    $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    $auth = new AuthController($c);

    // root -> login
    if ($uri === '/' && $method === 'GET') { redirect('/login'); }

    if ($uri === '/login') {
        if ($method === 'GET') { $auth->showLogin(); exit; }
        if ($method === 'POST') { $auth->login();    exit; }
        methodNotAllowed();
    }

    if ($uri === '/signup') {
        if ($method === 'GET') { $auth->showSignup(); exit; }
        if ($method === 'POST') { $auth->signup();    exit; }
        methodNotAllowed();
    }

    if ($uri === '/logout') {
        if ($method === 'POST') { $auth->logout(); exit; }
        methodNotAllowed();
    }

    // Protected example: /home
    if ($uri === '/home' && $method === 'GET') {
        AuthGuard::requireLogin();
        echo $c->twig->render('home.twig', [
            'user'       => $_SESSION['user'],
            'csrf_token' => Csrf::token($c),
        ]);
        exit;
    }

    notFound();
} catch (Throwable $e) {
    $errorMsg = sprintf("[%s] %s in %s:%d\n%s",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString()
    );
    error_log($errorMsg);

    $userMsg = $isDebug
        ? '<pre>' . htmlspecialchars($errorMsg, ENT_QUOTES) . '</pre>'
        : 'Something went wrong.';

    send($userMsg, 500);
}