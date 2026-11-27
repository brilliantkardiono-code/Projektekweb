<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $role = $_POST['role'] ?? 'user';

    if ($id === current_user()['id']) {
        $errors[] = 'Role akun yang sedang digunakan tidak dapat diubah.';
    } elseif (!in_array($role, ['admin', 'user'], true) || $nama === '') {
        $errors[] = 'Data tidak valid.';
    } else {
        $statement = $pdo->prepare(
            'UPDATE users SET nama = ?, role = ? WHERE id = ?'
        );
        $statement->execute([$nama, $role, $id]);
        flash('success', 'Data pengguna diperbarui.');
        redirect('/admin/pengguna.php');
    }
}

$rows = $pdo->query(
    'SELECT id, nama, email, role, created_at
     FROM users
     ORDER BY id DESC'
)->fetchAll();

page_header('Pengguna');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">PENGATURAN</span>
        <h1>Data Pengguna</h1>
        <p>Lihat akun dan ubah peran pengguna.</p>
    </div>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Terdaftar</th>
                <th>Simpan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <?php $isCurrentUser = (int) $row['id'] === (int) current_user()['id']; ?>
                <tr>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <td>
                            <input
                                class="table-input"
                                name="nama"
                                value="<?= e($row['nama']) ?>"
                                required
                            >
                        </td>
                        <td><?= e($row['email']) ?></td>
                        <td>
                            <select name="role" <?= $isCurrentUser ? 'disabled' : '' ?>>
                                <option value="user" <?= $row['role'] === 'user' ? 'selected' : '' ?>>
                                    User
                                </option>
                                <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>
                                    Admin
                                </option>
                            </select>
                        </td>
                        <td><?= e($row['created_at']) ?></td>
                        <td>
                            <button
                                class="btn btn-primary btn-small"
                                <?= $isCurrentUser ? 'disabled' : '' ?>
                            >Simpan</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
