<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $nama = trim($_POST['nama_kategori'] ?? '');

    try {
        if ($action === 'delete') {
            $statement = $pdo->prepare('DELETE FROM kategori WHERE id = ?');
            $statement->execute([$id]);
            flash('success', 'Kategori dihapus.');
        } elseif ($nama !== '') {
            if ($action === 'update') {
                $statement = $pdo->prepare(
                    'UPDATE kategori SET nama_kategori = ? WHERE id = ?'
                );
                $statement->execute([$nama, $id]);
            } else {
                $statement = $pdo->prepare(
                    'INSERT INTO kategori (nama_kategori) VALUES (?)'
                );
                $statement->execute([$nama]);
            }

            flash('success', 'Kategori disimpan.');
        } else {
            $errors[] = 'Nama kategori wajib diisi.';
        }

        if (!$errors) {
            redirect('/admin/kategori.php');
        }
    } catch (PDOException $e) {
        $errors[] = 'Nama kategori sudah ada atau kategori tidak dapat dihapus.';
    }
}

$edit = null;

if (isset($_GET['edit'])) {
    $statement = $pdo->prepare('SELECT * FROM kategori WHERE id = ?');
    $statement->execute([(int) $_GET['edit']]);
    $edit = $statement->fetch();
}

$rows = $pdo->query(
    'SELECT k.*, COUNT(b.id) AS jumlah_barang
     FROM kategori k
     LEFT JOIN barang b ON b.kategori_id = k.id
     GROUP BY k.id
     ORDER BY k.nama_kategori'
)->fetchAll();

page_header('Kategori');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">MASTER DATA</span>
        <h1>Kategori Barang</h1>
        <p>Kelola kelompok atau jenis barang.</p>
    </div>
</div>

<?php foreach ($errors as $error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="form-card">
    <h2><?= $edit ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>

    <form method="post" class="form-inline">
        <?= csrf_field() ?>
        <input
            type="hidden"
            name="action"
            value="<?= $edit ? 'update' : 'create' ?>"
        >

        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
        <?php endif; ?>

        <input
            name="nama_kategori"
            required
            placeholder="Nama kategori"
            value="<?= e($edit['nama_kategori'] ?? '') ?>"
        >
        <button class="btn btn-primary">Simpan</button>

        <?php if ($edit): ?>
            <a class="btn btn-outline" href="/admin/kategori.php">Batal</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nama Kategori</th>
                <th>Jumlah Barang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['nama_kategori']) ?></td>
                    <td><?= (int) $row['jumlah_barang'] ?></td>
                    <td class="actions">
                        <a
                            class="btn btn-outline btn-small"
                            href="?edit=<?= (int) $row['id'] ?>"
                        >Edit</a>
                        <form
                            method="post"
                            class="inline-form"
                            data-confirm="Hapus kategori ini? Barang terkait akan menjadi tanpa kategori."
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
