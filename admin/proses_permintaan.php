<?php
require __DIR__ . '/../config/bootstrap.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/permintaan.php');
}

verify_csrf();

$id = (int) ($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';

if ($id < 1 || !in_array($aksi, ['approve', 'reject'], true)) {
    flash('error', 'Aksi tidak valid.');
    redirect('/admin/permintaan.php');
}

$pdo->beginTransaction();

try {
    $statement = $pdo->prepare('SELECT * FROM permintaan WHERE id = ? FOR UPDATE');
    $statement->execute([$id]);
    $request = $statement->fetch();

    if (!$request || $request['status'] !== 'pending') {
        throw new RuntimeException('Permintaan tidak ditemukan atau sudah diproses.');
    }

    if ($aksi === 'approve') {
        $statement = $pdo->prepare(
            'SELECT * FROM detail_permintaan WHERE permintaan_id = ?'
        );
        $statement->execute([$id]);
        $details = $statement->fetchAll();

        foreach ($details as $item) {
            $statement = $pdo->prepare(
                'SELECT id, jumlah
                 FROM stok
                 WHERE barang_id = ? AND gudang_id = ?
                 FOR UPDATE'
            );
            $statement->execute([$item['barang_id'], $item['gudang_id']]);
            $stock = $statement->fetch();

            if (!$stock || (int) $stock['jumlah'] < (int) $item['jumlah']) {
                throw new RuntimeException(
                    'Stok tidak mencukupi untuk salah satu barang. Permintaan belum disetujui.'
                );
            }

            $statement = $pdo->prepare('UPDATE stok SET jumlah = jumlah - ? WHERE id = ?');
            $statement->execute([$item['jumlah'], $stock['id']]);

            $statement = $pdo->prepare(
                "INSERT INTO transaksi_stok
                    (barang_id, gudang_id, user_id, jenis, jumlah, keterangan)
                 VALUES (?, ?, ?, 'keluar', ?, ?)"
            );
            $statement->execute([
                $item['barang_id'],
                $item['gudang_id'],
                current_user()['id'],
                $item['jumlah'],
                'Permintaan #' . $id . ' disetujui',
            ]);
        }

        $status = 'approved';
    } else {
        $status = 'rejected';
    }

    $statement = $pdo->prepare(
        'UPDATE permintaan
         SET status = ?, diproses_oleh = ?, diproses_pada = NOW()
         WHERE id = ?'
    );
    $statement->execute([$status, current_user()['id'], $id]);
    $pdo->commit();

    flash(
        'success',
        $status === 'approved'
            ? 'Permintaan disetujui dan stok telah dikurangi.'
            : 'Permintaan ditolak.'
    );
} catch (Throwable $e) {
    $pdo->rollBack();
    flash(
        'error',
        $e instanceof RuntimeException
            ? $e->getMessage()
            : 'Gagal memproses permintaan.'
    );
}

redirect('/admin/permintaan.php');
