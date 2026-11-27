<?php
require __DIR__ . '/config/bootstrap.php';

if (current_user()) {
    redirect(
        current_user()['role'] === 'admin'
            ? '/admin/dashboard.php'
            : '/user/dashboard.php'
    );
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $statement = $pdo->prepare(
        'SELECT id, nama, email, password, role FROM users WHERE email = ?'
    );
    $statement->execute([$email]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        redirect(
            $user['role'] === 'admin'
                ? '/admin/dashboard.php'
                : '/user/dashboard.php'
        );
    }

    $error = 'Email atau password salah.';
}

page_header('Login');
?>
<div class="auth-card">
    <span class="eyebrow">SELAMAT DATANG KEMBALI</span>
    <h1>Login</h1>
    <p>Masuk untuk mengakses sistem gudang.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-stack">
        <?= csrf_field() ?>
        <label>
            Email
            <input type="email" name="email" required autocomplete="email">
        </label>
        <label>
            Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button class="btn btn-primary btn-full">Masuk</button>
    </form>

    <p class="auth-foot">Belum punya akun? <a href="/register.php">Daftar sekarang</a></p>
</div>

<?php page_footer(); ?>
