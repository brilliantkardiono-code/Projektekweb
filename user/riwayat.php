<?php
require __DIR__ . '/../config/bootstrap.php';
require_user();

$statement = $pdo->prepare(
    "SELECT p.*,
            GROUP_CONCAT(CONCAT(b.nama_barang, ' (', d.jumlah, ' ', b.satuan, ')') SEPARATOR ', ')
                AS barang
     FROM permintaan p
     LEFT JOIN detail_permintaan d ON d.permintaan_id = p.id
     LEFT JOIN barang b ON b.id = d.barang_id
     WHERE p.user_id = ?
     GROUP BY p.id
     ORDER BY p.id DESC"
);
$statement->execute([current_user()['id']]);
$rows = $statement->fetchAll();

page_header('Riwayat Permintaan');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">DISTRIBUSI</span>
        <h1>Riwayat Permintaan</h1>
        <p>Pantau status permintaan barang kamu.</p>
    </div>
    <a class="btn btn-primary" href="/user/permintaan.php">+ Permintaan Baru</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Catatan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>#<?= (int) $row['id'] ?></td>
                    <td><?= e($row['tanggal']) ?></td>
                    <td><?= e($row['barang'] ?? '-') ?></td>
                    <td><?= e($row['catatan'] ?? '-') ?></td>
                    <td>
                        <span class="status status-<?= e($row['status']) ?>">
                            <?= e(strtoupper($row['status'])) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="5" class="empty">Belum ada riwayat permintaan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
