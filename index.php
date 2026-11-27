<?php
require __DIR__ . '/config/bootstrap.php';
page_header('Beranda');
?>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">WAREHOUSE MANAGEMENT SYSTEM</span>
        <h1>Kelola inventaris gudang dengan lebih mudah.</h1>
        <p>
            Platform sederhana untuk melihat ketersediaan barang, mengajukan permintaan,
            dan mengelola distribusi gudang.
        </p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="/catalog.php">Lihat Katalog</a>
            <?php if (!current_user()): ?>
                <a class="btn btn-outline" href="/register.php">Buat Akun</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-art">
        <div class="art-card">
            <span class="art-icon">▦</span>
            <strong>Inventaris Teratur</strong>
            <small>Stok dan distribusi tercatat</small>
        </div>
        <div class="art-stat">
            <b>Real-time</b>
            <small>Pemantauan stok</small>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-heading">
        <div>
            <span class="eyebrow">FITUR UTAMA</span>
            <h2>Satu tempat untuk kebutuhan gudang</h2>
        </div>
    </div>

    <div class="feature-grid">
        <article class="feature-card">
            <div class="feature-icon">▤</div>
            <h3>Data Barang</h3>
            <p>Lihat katalog barang beserta kategori dan satuannya.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon">⇄</div>
            <h3>Distribusi</h3>
            <p>Ajukan kebutuhan barang dan pantau status permintaan.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon">▥</div>
            <h3>Kontrol Stok</h3>
            <p>Admin mengelola stok masuk dan keluar gudang.</p>
        </article>
    </div>
</section>

<?php page_footer(); ?>
