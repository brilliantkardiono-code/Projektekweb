<?php
require __DIR__ . '/config/bootstrap.php';

// Jalankan sekali setelah import database. Hapus file ini setelah admin berhasil dibuat.
$email = 'admin@warehouse.local';
$statement = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$statement->execute([$email]);

if ($statement->fetch()) {
    exit('Akun admin default sudah ada. Hapus file setup_admin.php sekarang.');
}

$hash = password_hash('Admin123!', PASSWORD_DEFAULT);
$statement = $pdo->prepare(
    "INSERT INTO users (nama, email, password, role)
     VALUES (?, ?, ?, 'admin')"
);
$statement->execute(['Administrator', $email, $hash]);

echo 'Admin berhasil dibuat. Email: admin@warehouse.local | Password: Admin123!'
    . '<br>Segera login, ganti password, lalu hapus file setup_admin.php.';
