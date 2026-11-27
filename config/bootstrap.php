<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

$configFile = __DIR__ . '/database.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    exit('Konfigurasi database belum dibuat. Salin config/database.example.php menjadi config/database.php.');
}
$db = require $configFile;

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4",
        $db['username'],
        $db['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa konfigurasi MySQL di config/database.php.');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Permintaan tidak valid (CSRF). Silakan kembali dan coba lagi.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('/login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (current_user()['role'] !== 'admin') {
        http_response_code(403);
        exit('Akses ditolak. Halaman ini hanya untuk admin.');
    }
}

function require_user(): void
{
    require_login();
    if (current_user()['role'] !== 'user') {
        http_response_code(403);
        exit('Akses ditolak. Halaman ini hanya untuk user.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function page_header(string $title): void
{
    $user = current_user();
    $flash = take_flash();
    ?>
    <!doctype html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> - Warehouse</title>
        <link rel="stylesheet" href="/assets/css/style.css">
        <script defer src="/assets/js/app.js"></script>
    </head>
    <body>
        <header class="topbar">
            <a class="brand" href="/index.php">
                <span class="brand-mark">W</span> Warehouse
            </a>
            <nav class="topnav">
                <a href="/index.php">Beranda</a>
                <a href="/catalog.php">Katalog</a>
                <?php if ($user): ?>
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="/admin/dashboard.php">Dashboard Admin</a>
                    <?php else: ?>
                        <a href="/user/dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <span class="nav-user"><?= e($user['nama']) ?></span>
                    <form method="post" action="/logout.php" class="inline-form">
                        <?= csrf_field() ?>
                        <button class="btn btn-outline btn-small">Keluar</button>
                    </form>
                <?php else: ?>
                    <a href="/login.php">Login</a>
                    <a class="btn btn-primary btn-small" href="/register.php">Daftar</a>
                <?php endif; ?>
            </nav>
        </header>
        <main class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>" data-alert>
                <?= e($flash['message']) ?>
                <button type="button" class="alert-close" aria-label="Tutup">×</button>
            </div>
        <?php endif; ?>
    <?php
}

function page_footer(): void
{
    ?>
    </main>
    <footer class="footer">
        © <?= date('Y') ?> Warehouse Management System · Project akademik
    </footer>
    </body>
    </html>
    <?php
}
