<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$sql = "SELECT p.*, u.nama,
               GROUP_CONCAT(
                   CONCAT(b.nama_barang, ' (', d.jumlah, ' ', b.satuan, ') @ ', g.nama_gudang)
                   SEPARATOR '; '
               ) AS daftar_barang
        FROM permintaan p
        JOIN users u ON u.id = p.user_id
        LEFT JOIN detail_permintaan d ON d.permintaan_id = p.id
        LEFT JOIN barang b ON b.id = d.barang_id
        LEFT JOIN gudang g ON g.id = d.gudang_id
        GROUP BY p.id
        ORDER BY p.id DESC";
$rows = $pdo->query($sql)->fetchAll();

page_header('Permintaan Barang');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">DISTRIBUSI</span>
        <h1>Permintaan Barang</h1>
        <p>Periksa dan proses permintaan dari pengguna.</p>
    </div>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID / Tanggal</th>
                <th>Pengguna</th>
                <th>Barang diminta</th>
                <th>Catatan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        #<?= (int) $row['id'] ?>
                        <small class="table-sub"><?= e($row['tanggal']) ?></small>
                    </td>
                    <td><?= e($row['nama']) ?></td>
                    <td><?= e($row['daftar_barang'] ?? '-') ?></td>
                    <td><?= e($row['catatan'] ?? '-') ?></td>
                    <td>
                        <span class="status status-<?= e($row['status']) ?>">
                            <?= e(strtoupper($row['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <form
                                method="post"
                                action="/admin/proses_permintaan.php"
                                class="action-stack"
                            >
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <button name="aksi" value="approve" class="btn btn-primary btn-small">
                                    Setujui
                                </button>
                                <button name="aksi" value="reject" class="btn btn-danger btn-small">
                                    Tolak
                                </button>
                            </form>
                        <?php else: ?>
                            <small>Sudah diproses</small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
                <tr>
                    <td colspan="6" class="empty">Belum ada permintaan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
