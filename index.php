<?php
/**
 * Halaman utama: ringkasan stok, kategori, dan supplier.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$totalProduk   = (int) $pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn();
$totalKategori = (int) $pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
$totalSupplier = (int) $pdo->query('SELECT COUNT(*) FROM supplier')->fetchColumn();
$nilaiStok     = (float) $pdo->query('SELECT COALESCE(SUM(harga * stok), 0) FROM produk')->fetchColumn();
$jumlahMenipis = (int) $pdo->query('SELECT COUNT(*) FROM produk WHERE stok <= 10')->fetchColumn();
$jumlahHabis   = (int) $pdo->query('SELECT COUNT(*) FROM produk WHERE stok = 0')->fetchColumn();

$stmtMenipis = $pdo->query(
    'SELECT p.id, p.nama_produk, p.stok, p.satuan, k.nama AS nama_kategori
       FROM produk p
       JOIN kategori k ON k.id = p.kategori_id
      WHERE p.stok <= 10
      ORDER BY p.stok ASC, p.nama_produk ASC
      LIMIT 6'
);
$produkMenipis = $stmtMenipis->fetchAll();

$perKategori = $pdo->query(
    'SELECT k.id, k.nama, COUNT(p.id) AS jumlah, COALESCE(SUM(p.stok), 0) AS total_stok
       FROM kategori k
       LEFT JOIN produk p ON p.kategori_id = k.id
      GROUP BY k.id, k.nama
      ORDER BY jumlah DESC, k.nama ASC'
)->fetchAll();

$perSupplier = $pdo->query(
    'SELECT s.id, s.nama, COUNT(p.id) AS jumlah, COALESCE(SUM(p.stok), 0) AS total_stok
       FROM supplier s
       LEFT JOIN produk p ON p.supplier_id = s.id
      GROUP BY s.id, s.nama
      ORDER BY jumlah DESC, s.nama ASC'
)->fetchAll();

$produkTerbaru = $pdo->query(
    'SELECT p.id, p.nama_produk, p.harga, p.stok, p.satuan, k.nama AS nama_kategori
       FROM produk p
       JOIN kategori k ON k.id = p.kategori_id
      ORDER BY p.id DESC
      LIMIT 5'
)->fetchAll();

$maksKategori = 0;
foreach ($perKategori as $kategori) {
    $maksKategori = max($maksKategori, (int) $kategori['jumlah']);
}

$maksSupplier = 0;
foreach ($perSupplier as $supplier) {
    $maksSupplier = max($maksSupplier, (int) $supplier['jumlah']);
}

$statistik = [
    [
        'label' => 'Total Produk',
        'nilai' => number_format($totalProduk, 0, ',', '.'),
        'sub'   => 'jenis produk terdaftar',
        'icon'  => 'i-package',
        'warna' => 'blue',
    ],
    [
        'label' => 'Kategori',
        'nilai' => number_format($totalKategori, 0, ',', '.'),
        'sub'   => 'kelompok produk',
        'icon'  => 'i-tag',
        'warna' => 'teal',
    ],
    [
        'label' => 'Supplier',
        'nilai' => number_format($totalSupplier, 0, ',', '.'),
        'sub'   => 'mitra pemasok',
        'icon'  => 'i-truck',
        'warna' => 'violet',
    ],
    [
        'label' => 'Nilai Stok',
        'nilai' => rupiah($nilaiStok),
        'sub'   => 'harga jual x stok saat ini',
        'icon'  => 'i-wallet',
        'warna' => 'amber',
    ],
];

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Ringkasan stok dan produk frozen food hari ini.';
$activePage   = 'dashboard';

require __DIR__ . '/includes/header.php';
?>

<section class="hero-card mb-4">
    <div class="hero-content">
        <span class="hero-badge">
            <svg class="icon" aria-hidden="true"><use href="#i-snow"></use></svg>
            Manajemen Stok Frozen Food
        </span>
        <h2>Selamat datang, Pak Budi!</h2>
        <p>Semua produk, kategori, dan supplier toko bisa dipantau dari satu tempat. Cek stok menipis sebelum barang habis.</p>
        <div class="hero-actions">
            <a href="produk-tambah.php" class="btn btn-light">
                <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
                Tambah Produk
            </a>
            <a href="produk.php" class="btn btn-outline-light">
                Lihat Semua Produk
                <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
            </a>
        </div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <svg class="icon"><use href="#i-snow"></use></svg>
    </div>
</section>

<div class="row g-3 mb-4">
    <?php foreach ($statistik as $item): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card">
                <span class="stat-icon <?= e($item['warna']) ?>">
                    <svg class="icon" aria-hidden="true"><use href="#<?= e($item['icon']) ?>"></use></svg>
                </span>
                <div>
                    <p class="stat-label"><?= e($item['label']) ?></p>
                    <p class="stat-value"><?= e($item['nilai']) ?></p>
                    <p class="stat-sub"><?= e($item['sub']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        <svg class="icon" aria-hidden="true"><use href="#i-alert"></use></svg>
                        Stok Menipis
                    </h2>
                    <p class="panel-subtitle"><?= number_format($jumlahMenipis, 0, ',', '.') ?> produk perlu segera diisi ulang</p>
                </div>
                <a href="produk.php" class="panel-link">
                    Kelola
                    <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
                </a>
            </div>

            <?php if ($produkMenipis === []): ?>
                <div class="empty-state">
                    <span class="empty-icon">
                        <svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg>
                    </span>
                    <h3>Stok aman</h3>
                    <p>Tidak ada produk dengan stok 10 atau kurang. Persediaan toko dalam kondisi baik.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-app align-middle">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th class="col-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produkMenipis as $produk): ?>
                                <?php $status = status_stok((int) $produk['stok']); ?>
                                <tr>
                                    <td>
                                        <p class="produk-nama"><?= e($produk['nama_produk']) ?></p>
                                        <p class="produk-satuan">per <?= e($produk['satuan']) ?></p>
                                    </td>
                                    <td><span class="badge-soft"><?= e($produk['nama_kategori']) ?></span></td>
                                    <td>
                                        <span class="stok-pill <?= e($status['kelas']) ?>">
                                            <?= (int) $produk['stok'] ?> <?= e($produk['satuan']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="produk-edit.php?id=<?= (int) $produk['id'] ?>" class="btn-aksi" title="Edit produk">
                                            <svg class="icon" aria-hidden="true"><use href="#i-pencil"></use></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 pb-4">
                    <?php if ($jumlahHabis > 0): ?>
                        <div class="mb-3">
                            <a href="produk.php?stok=habis" class="panel-link">
                                <?= (int) $jumlahHabis ?> produk sudah habis
                                <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="info-note">
                        <svg class="icon" aria-hidden="true"><use href="#i-info"></use></svg>
                        <span>Ambang batas stok menipis adalah 10 pack. Segera pesan ulang ke supplier sebelum barang habis.</span>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        <svg class="icon" aria-hidden="true"><use href="#i-tag"></use></svg>
                        Produk per Kategori
                    </h2>
                    <p class="panel-subtitle">Sebaran produk berdasarkan kelompoknya</p>
                </div>
                <a href="kategori.php" class="panel-link">
                    Semua
                    <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
                </a>
            </div>
            <div class="panel-body">
                <?php if ($perKategori === []): ?>
                    <p class="text-muted mb-0">Belum ada kategori.</p>
                <?php else: ?>
                    <?php foreach ($perKategori as $kategori): ?>
                        <?php
                        $jumlah = (int) $kategori['jumlah'];
                        $persen = $maksKategori > 0 ? (int) round($jumlah / $maksKategori * 100) : 0;
                        ?>
                        <div class="summary-row">
                            <span class="summary-dot">
                                <svg class="icon" aria-hidden="true"><use href="#i-tag"></use></svg>
                            </span>
                            <div class="summary-info">
                                <p class="summary-name">
                                    <a href="produk.php?kategori_id=<?= (int) $kategori['id'] ?>" class="text-reset">
                                        <?= e($kategori['nama']) ?>
                                    </a>
                                </p>
                                <p class="summary-sub"><?= (int) $kategori['total_stok'] ?> item stok tersedia</p>
                                <div class="progress" role="progressbar" aria-label="Proporsi produk <?= e($kategori['nama']) ?>"
                                     aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: <?= $persen ?>%"></div>
                                </div>
                            </div>
                            <span class="summary-count"><?= $jumlah ?> produk</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        <svg class="icon" aria-hidden="true"><use href="#i-truck"></use></svg>
                        Produk per Supplier
                    </h2>
                    <p class="panel-subtitle">Pemasok dengan produk terbanyak</p>
                </div>
                <a href="supplier.php" class="panel-link">
                    Semua
                    <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
                </a>
            </div>
            <div class="panel-body">
                <?php if ($perSupplier === []): ?>
                    <p class="text-muted mb-0">Belum ada supplier.</p>
                <?php else: ?>
                    <?php foreach ($perSupplier as $supplier): ?>
                        <?php
                        $jumlah = (int) $supplier['jumlah'];
                        $persen = $maksSupplier > 0 ? (int) round($jumlah / $maksSupplier * 100) : 0;
                        ?>
                        <div class="summary-row">
                            <span class="summary-dot">
                                <svg class="icon" aria-hidden="true"><use href="#i-truck"></use></svg>
                            </span>
                            <div class="summary-info">
                                <p class="summary-name">
                                    <a href="produk.php?supplier_id=<?= (int) $supplier['id'] ?>" class="text-reset">
                                        <?= e($supplier['nama']) ?>
                                    </a>
                                </p>
                                <p class="summary-sub"><?= (int) $supplier['total_stok'] ?> item stok tersedia</p>
                                <div class="progress" role="progressbar" aria-label="Proporsi produk <?= e($supplier['nama']) ?>"
                                     aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar green" style="width: <?= $persen ?>%"></div>
                                </div>
                            </div>
                            <span class="summary-count"><?= $jumlah ?> produk</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="col-lg-6">
        <section class="panel h-100">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">
                        <svg class="icon" aria-hidden="true"><use href="#i-box-plus"></use></svg>
                        Produk Terbaru
                    </h2>
                    <p class="panel-subtitle">Lima produk terakhir yang dicatat</p>
                </div>
                <a href="produk-tambah.php" class="panel-link">
                    Tambah
                    <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
                </a>
            </div>

            <?php if ($produkTerbaru === []): ?>
                <div class="empty-state">
                    <span class="empty-icon">
                        <svg class="icon" aria-hidden="true"><use href="#i-package"></use></svg>
                    </span>
                    <h3>Belum ada produk</h3>
                    <p>Mulai catat produk frozen food pertama milik Pak Budi.</p>
                    <a href="produk-tambah.php" class="btn btn-brand btn-sm">
                        <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
                        Tambah Produk
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-app align-middle">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produkTerbaru as $produk): ?>
                                <?php $status = status_stok((int) $produk['stok']); ?>
                                <tr>
                                    <td>
                                        <p class="produk-nama"><?= e($produk['nama_produk']) ?></p>
                                        <p class="produk-satuan"><?= e($produk['nama_kategori']) ?></p>
                                    </td>
                                    <td class="harga-text"><?= e(rupiah($produk['harga'])) ?></td>
                                    <td>
                                        <span class="stok-pill <?= e($status['kelas']) ?>">
                                            <?= (int) $produk['stok'] ?> <?= e($produk['satuan']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
