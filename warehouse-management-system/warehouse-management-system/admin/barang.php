<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        try {
            $st = $pdo->prepare("DELETE FROM barang WHERE id=?");
            $st->execute([$id]);
            flash('success', 'Barang berhasil dihapus.');
            redirect('/admin/barang.php');
        } catch (Throwable $e) {
            $errors[] = 'Barang tidak dapat dihapus karena sudah digunakan dalam transaksi atau permintaan.';
        }
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $kode = trim($_POST['kode_barang'] ?? '');
        $nama = trim($_POST['nama_barang'] ?? '');
        $kategori = (int) ($_POST['kategori_id'] ?? 0);
        $satuan = trim($_POST['satuan'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($kode === '' || $nama === '' || $satuan === '') {
            $errors[] = 'Kode, nama, dan satuan wajib diisi.';
        } else {
            try {
                if ($action === 'create') {
                    $st = $pdo->prepare(
                        "INSERT INTO barang(kode_barang,nama_barang,kategori_id,satuan,deskripsi) VALUES(?,?,?,?,?)"
                    );
                    $st->execute([$kode, $nama, $kategori ?: null, $satuan, $deskripsi ?: null]);
                } elseif ($action === 'update') {
                    $st = $pdo->prepare(
                        "UPDATE barang SET kode_barang=?,nama_barang=?,kategori_id=?,satuan=?,deskripsi=? WHERE id=?"
                    );
                    $st->execute([
                        $kode,
                        $nama,
                        $kategori ?: null,
                        $satuan,
                        $deskripsi ?: null,
                        $id,
                    ]);
                }

                flash('success', 'Data barang berhasil disimpan.');
                redirect('/admin/barang.php');
            } catch (PDOException $e) {
                $errors[] = $e->getCode() === '23000'
                    ? 'Kode barang sudah digunakan.'
                    : 'Gagal menyimpan barang.';
            }
        }
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM barang WHERE id=?");
    $st->execute([(int) $_GET['edit']]);
    $edit = $st->fetch();
}

$cats = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori")->fetchAll();
$items = $pdo->query(
    "SELECT b.*,k.nama_kategori
     FROM barang b
     LEFT JOIN kategori k ON k.id=b.kategori_id
     ORDER BY b.id DESC"
)->fetchAll();

page_header('Kelola Barang');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">MASTER DATA</span>
        <h1>Kelola Barang</h1>
        <p>Tambah, ubah, dan hapus data barang.</p>
    </div>
</div>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="form-card">
    <h2><?= $edit ? 'Edit Barang' : 'Tambah Barang' ?></h2>

    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">

        <?php if ($edit): ?>
            <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
        <?php endif; ?>

        <label>
            Kode Barang
            <input name="kode_barang" required value="<?= e($edit['kode_barang'] ?? '') ?>">
        </label>

        <label>
            Nama Barang
            <input name="nama_barang" required value="<?= e($edit['nama_barang'] ?? '') ?>">
        </label>

        <label>
            Kategori
            <select name="kategori_id">
                <option value="">Tanpa kategori</option>
                <?php foreach ($cats as $c): ?>
                    <option
                        value="<?= (int) $c['id'] ?>"
                        <?= (int) ($edit['kategori_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>
                    >
                        <?= e($c['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Satuan
            <input
                name="satuan"
                required
                placeholder="Unit, Pcs, Dus..."
                value="<?= e($edit['satuan'] ?? '') ?>"
            >
        </label>

        <label class="span-2">
            Deskripsi
            <textarea name="deskripsi" rows="2"><?= e($edit['deskripsi'] ?? '') ?></textarea>
        </label>

        <div class="form-actions">
            <button class="btn btn-primary">
                <?= $edit ? 'Simpan Perubahan' : 'Tambah Barang' ?>
            </button>

            <?php if ($edit): ?>
                <a class="btn btn-outline" href="/admin/barang.php">Batal</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="section-heading">
    <h2>Daftar Barang</h2>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td><?= e($i['kode_barang']) ?></td>
                    <td><?= e($i['nama_barang']) ?></td>
                    <td><?= e($i['nama_kategori'] ?? '-') ?></td>
                    <td><?= e($i['satuan']) ?></td>
                    <td class="actions">
                        <a
                            class="btn btn-outline btn-small"
                            href="?edit=<?= (int) $i['id'] ?>"
                        >
                            Edit
                        </a>
                        <form
                            method="post"
                            class="inline-form"
                            data-confirm="Yakin ingin menghapus barang ini?"
                        >
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
                            <button class="btn btn-danger btn-small">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$items): ?>
                <tr>
                    <td colspan="5" class="empty">Belum ada barang.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
