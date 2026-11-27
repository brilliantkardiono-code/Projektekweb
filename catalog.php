<?php
require __DIR__ . '/config/bootstrap.php';

$query = trim($_GET['q'] ?? '');
$sql = 'SELECT b.*, k.nama_kategori, COALESCE(SUM(s.jumlah), 0) AS total_stok
        FROM barang b
        LEFT JOIN kategori k ON k.id = b.kategori_id
        LEFT JOIN stok s ON s.barang_id = b.id';
$params = [];

if ($query !== '') {
    $sql .= ' WHERE b.nama_barang LIKE ? OR b.kode_barang LIKE ? OR k.nama_kategori LIKE ?';
    $like = '%' . $query . '%';
    $params = [$like, $like, $like];
}

$sql .= ' GROUP BY b.id ORDER BY b.nama_barang';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$items = $statement->fetchAll();

page_header('Katalog Barang');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">INVENTARIS</span>
        <h1>Katalog Barang</h1>
        <p>Daftar barang yang tersedia di gudang.</p>
    </div>
</div>

<form class="searchbar" method="get">
    <input
        name="q"
        value="<?= e($query) ?>"
        placeholder="Cari nama, kode, atau kategori barang..."
    >
    <button class="btn btn-primary">Cari</button>
</form>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Total Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['kode_barang']) ?></td>
                    <td>
                        <strong><?= e($item['nama_barang']) ?></strong>
                        <small class="table-sub"><?= e($item['deskripsi']) ?></small>
                    </td>
                    <td><?= e($item['nama_kategori'] ?? '-') ?></td>
                    <td><?= e($item['satuan']) ?></td>
                    <td><span class="stock-pill"><?= (int) $item['total_stok'] ?></span></td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$items): ?>
                <tr>
                    <td colspan="5" class="empty">Barang tidak ditemukan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
