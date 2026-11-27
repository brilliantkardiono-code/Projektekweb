<?php
require __DIR__ . '/../config/bootstrap.php';
require_user();

$userId = current_user()['id'];

$statement = $pdo->prepare('SELECT COUNT(*) FROM permintaan WHERE user_id = ?');
$statement->execute([$userId]);
$total = (int) $statement->fetchColumn();

$statement = $pdo->prepare(
    "SELECT COUNT(*) FROM permintaan WHERE user_id = ? AND status = 'pending'"
);
$statement->execute([$userId]);
$pending = (int) $statement->fetchColumn();

$statement = $pdo->prepare(
    'SELECT id, tanggal, status
     FROM permintaan
     WHERE user_id = ?
     ORDER BY id DESC
     LIMIT 5'
);
$statement->execute([$userId]);
$recent = $statement->fetchAll();

page_header('Dashboard User');
?>
<div class="page-heading">
    <div>
        <span class="eyebrow">AREA PENGGUNA</span>
        <h1>Halo, <?= e(current_user()['nama']) ?> 👋</h1>
        <p>Ringkasan aktivitas permintaan barang kamu.</p>
    </div>
    <a class="btn btn-primary" href="/user/permintaan.php">+ Buat Permintaan</a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span>Total Permintaan</span>
        <strong><?= $total ?></strong>
        <small>Seluruh permintaan kamu</small>
    </div>
    <div class="stat-card">
        <span>Menunggu</span>
        <strong><?= $pending ?></strong>
        <small>Belum diproses admin</small>
    </div>
</div>

<div class="section-heading">
    <h2>Permintaan Terbaru</h2>
    <a href="/user/riwayat.php">Lihat semua →</a>
</div>

<?php render_request_table($recent); ?>
<?php page_footer(); ?>

<?php
function render_request_table(array $rows): void
{
    echo '<div class="table-wrap"><table><thead><tr><th>ID</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>';

    foreach ($rows as $row) {
        echo '<tr><td>#' . (int) $row['id'] . '</td><td>'
            . e($row['tanggal'])
            . '</td><td><span class="status status-'
            . e($row['status'])
            . '">'
            . e(strtoupper($row['status']))
            . '</span></td></tr>';
    }

    if (!$rows) {
        echo '<tr><td colspan="3" class="empty">Belum ada permintaan.</td></tr>';
    }

    echo '</tbody></table></div>';
}
?>
