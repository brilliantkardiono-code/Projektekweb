<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_gudang'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');

    try {
        if ($action === 'delete') {
            $statement = $pdo->prepare('DELETE FROM gudang WHERE id = ?');
            $statement->execute([$id]);
            flash('success', 'Gudang dihapus.');
        } elseif ($nama && $lokasi) {
            if ($action === 'update') {
                $statement = $pdo->prepare(
                    'UPDATE gudang
                     SET nama_gudang = ?, lokasi = ?
                     WHERE id = ?'
                );
                $statement->execute([$nama, $lokasi, $id]);
            } else {
                $statement = $pdo->prepare(
                    'INSERT INTO gudang (nama_gudang, lokasi)
                     VALUES (?, ?)'
                );
                $statement->execute([$nama, $lokasi]);
            }

            flash('success', 'Data gudang disimpan.');
        } else {
            $errors[] = 'Nama dan lokasi wajib diisi.';
        }

        if (!$errors) {
            redirect('/admin/gudang.php');
        }
    } catch (Throwable $e) {
        $errors[] = 'Gudang tidak dapat dihapus karena sudah digunakan atau data tidak valid.';
    }
}

$edit = null;

if (isset($_GET['edit'])) {
    $statement = $pdo->prepare('SELECT * FROM gudang WHERE id = ?');
    $statement->execute([(int) $_GET['edit']]);
    $edit = $statement->fetch();
}

$rows = $pdo->query(
    'SELECT g.*, COALESCE(SUM(s.jumlah), 0) AS total_stok
     FROM gudang g
     LEFT JOIN stok s ON s.gudang_id = g.id
     GROUP BY g.id
     ORDER BY g.id DESC'
)->fetchAll();

page_header('Gudang');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">MASTER DATA</span>
        <h1>Data Gudang</h1>
        <p>Kelola lokasi penyimpanan barang.</p>
    </div>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="form-card">
    <h2><?= $edit ? 'Edit Gudang' : 'Tambah Gudang' ?></h2>

    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input
            type="hidden"
            name="action"
            value="<?= $edit ? 'update' : 'create' ?>"
        >

        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
        <?php endif; ?>

        <label>
            Nama Gudang
            <input
                name="nama_gudang"
                required
                value="<?= e($edit['nama_gudang'] ?? '') ?>"
            >
        </label>

        <label>
            Lokasi
            <input
                name="lokasi"
                required
                value="<?= e($edit['lokasi'] ?? '') ?>"
            >
        </label>

        <div class="form-actions">
            <button class="btn btn-primary">Simpan</button>
            <?php if ($edit): ?>
                <a class="btn btn-outline" href="/admin/gudang.php">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama Gudang</th>
                <th>Lokasi</th>
                <th>Total Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['nama_gudang']) ?></td>
                    <td><?= e($row['lokasi']) ?></td>
                    <td><?= (int) $row['total_stok'] ?></td>
                    <td class="actions">
                        <a
                            class="btn btn-outline btn-small"
                            href="?edit=<?= (int) $row['id'] ?>"
                        >Edit</a>

                        <form
                            method="post"
                            class="inline-form"
                            data-confirm="Hapus gudang ini?"
                        >
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                            <button class="btn btn-danger btn-small">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
