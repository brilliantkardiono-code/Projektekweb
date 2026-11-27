<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

$stats = [
    'barang' => (int) $pdo->query('SELECT COUNT(*) FROM barang')->fetchColumn(),
    'stok' => (int) $pdo->query('SELECT COALESCE(SUM(jumlah), 0) FROM stok')->fetchColumn(),
    'user' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'pending' => (int) $pdo->query("SELECT COUNT(*) FROM permintaan WHERE status = 'pending'")->fetchColumn(),
];

$recent = $pdo->query(
    'SELECT p.id, p.tanggal, p.status, u.nama
     FROM permintaan p
     JOIN users u ON u.id = p.user_id
     ORDER BY p.id DESC
     LIMIT 7'
)->fetchAll();

page_header('Dashboard Admin');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">ADMINISTRATOR</span>
        <h1>Dashboard Gudang</h1>
        <p>Ringkasan inventaris dan aktivitas distribusi.</p>
    </div>
</div>

<div class="stats-grid four">
    <div class="stat-card">
        <span>Total Barang</span>
        <strong><?= $stats['barang'] ?></strong>
        <small>Jenis barang terdaftar</small>
    </div>
    <div class="stat-card">
        <span>Total Stok</span>
        <strong><?= $stats['stok'] ?></strong>
        <small>Seluruh unit di gudang</small>
    </div>
    <div class="stat-card">
        <span>Pengguna</span>
        <strong><?= $stats['user'] ?></strong>
        <small>Akun user aktif</small>
    </div>
    <div class="stat-card">
        <span>Menunggu</span>
        <strong><?= $stats['pending'] ?></strong>
        <small>Permintaan perlu diproses</small>
    </div>
</div>

<div class="quick-links">
    <a href="/admin/barang.php">Kelola Barang →</a>
    <a href="/admin/stok.php">Kelola Stok →</a>
    <a href="/admin/permintaan.php">Proses Permintaan →</a>
</div>

<div class="section-heading">
    <h2>Permintaan Terbaru</h2>
    <a href="/admin/permintaan.php">Lihat semua →</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pengguna</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent as $request): ?>
                <tr>
                    <td>#<?= (int) $request['id'] ?></td>
                    <td><?= e($request['nama']) ?></td>
                    <td><?= e($request['tanggal']) ?></td>
                    <td>
                        <span class="status status-<?= e($request['status']) ?>">
                            <?= e(strtoupper($request['status'])) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$recent): ?>
                <tr>
                    <td colspan="4" class="empty">Belum ada permintaan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php page_footer(); ?>
