<?php
require __DIR__ . '/config/bootstrap.php';

if (current_user()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (
        $nama === ''
        || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($password) < 8
    ) {
        $error = 'Nama dan email wajib valid. Password minimal 8 karakter.';
    } else {
        try {
            $statement = $pdo->prepare(
                "INSERT INTO users (nama, email, password, role)
                 VALUES (?, ?, ?, 'user')"
            );
            $statement->execute([
                $nama,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
            ]);

            flash('success', 'Registrasi berhasil. Silakan login.');
            redirect('/login.php');
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000'
                ? 'Email sudah terdaftar.'
                : 'Registrasi gagal. Periksa data.';
        }
    }
}

page_header('Register');
?>
<div class="auth-card">
    <span class="eyebrow">AKUN BARU</span>
    <h1>Daftar</h1>
    <p>Buat akun user untuk mengajukan permintaan barang.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-stack">
        <?= csrf_field() ?>
        <label>
            Nama lengkap
            <input name="nama" required maxlength="100">
        </label>
        <label>
            Email
            <input type="email" name="email" required maxlength="150">
        </label>
        <label>
            Password
            <input type="password" name="password" required minlength="8">
            <small>Minimal 8 karakter.</small>
        </label>
        <button class="btn btn-primary btn-full">Buat Akun</button>
    </form>

    <p class="auth-foot">Sudah punya akun? <a href="/login.php">Login</a></p>
</div>

<?php page_footer(); ?>
