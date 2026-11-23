<?php
/**
 * Daftar supplier produk.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$daftarSupplier = $pdo->query(
    'SELECT s.id, s.nama, s.kontak, s.alamat, COUNT(p.id) AS jumlah_produk
       FROM supplier s
       LEFT JOIN produk p ON p.supplier_id = s.id
      GROUP BY s.id, s.nama, s.kontak, s.alamat
      ORDER BY s.nama ASC'
)->fetchAll();

$pageTitle    = 'Supplier';
$pageSubtitle = 'Kelola mitra pemasok produk frozen food.';
$activePage   = 'supplier';

require __DIR__ . '/includes/header.php';
?>

<section class="panel mb-4">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-truck"></use></svg>
                Daftar Supplier
            </h2>
            <p class="panel-subtitle">
                <?= count($daftarSupplier) ?> supplier terdaftar. Supplier yang masih memasok produk tidak dapat dihapus.
            </p>
        </div>
        <a href="supplier-tambah.php" class="btn btn-brand btn-sm ms-auto">
            <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
            Tambah Supplier
        </a>
    </div>
</section>

<?php if ($daftarSupplier === []): ?>
    <section class="panel">
        <div class="empty-state">
            <span class="empty-icon">
                <svg class="icon" aria-hidden="true"><use href="#i-truck"></use></svg>
            </span>
            <h3>Belum ada supplier</h3>
            <p>Tambahkan supplier pertama agar setiap produk tercatat asal pemasoknya.</p>
            <a href="supplier-tambah.php" class="btn btn-brand btn-sm">
                <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
                Tambah Supplier
            </a>
        </div>
    </section>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($daftarSupplier as $supplier): ?>
            <?php $jumlahProduk = (int) $supplier['jumlah_produk']; ?>
            <div class="col-12 col-md-6 col-xl-4">
                <article class="data-card">
                    <div class="data-card-body">
                        <div class="data-card-head">
                            <span class="data-icon green">
                                <svg class="icon" aria-hidden="true"><use href="#i-truck"></use></svg>
                            </span>
                            <div>
                                <h3>
                                    <a href="produk.php?supplier_id=<?= (int) $supplier['id'] ?>" class="text-reset">
                                        <?= e($supplier['nama']) ?>
                                    </a>
                                </h3>
                                <p class="produk-satuan">Mitra pemasok</p>
                            </div>
                        </div>

                        <ul class="data-meta">
                            <li>
                                <svg class="icon" aria-hidden="true"><use href="#i-phone"></use></svg>
                                <span>
                                    <?= $supplier['kontak'] !== null && $supplier['kontak'] !== ''
                                        ? e($supplier['kontak'])
                                        : 'Kontak belum diisi' ?>
                                </span>
                            </li>
                            <li>
                                <svg class="icon" aria-hidden="true"><use href="#i-pin"></use></svg>
                                <span>
                                    <?= $supplier['alamat'] !== null && $supplier['alamat'] !== ''
                                        ? e($supplier['alamat'])
                                        : 'Alamat belum diisi' ?>
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div class="data-card-foot">
                        <a href="produk.php?supplier_id=<?= (int) $supplier['id'] ?>" class="count-badge">
                            <svg class="icon" aria-hidden="true"><use href="#i-package"></use></svg>
                            <?= $jumlahProduk ?> produk
                        </a>
                        <div class="ms-auto d-flex gap-1">
                            <a href="supplier-edit.php?id=<?= (int) $supplier['id'] ?>"
                               class="btn-aksi" title="Edit supplier <?= e($supplier['nama']) ?>">
                                <svg class="icon" aria-hidden="true"><use href="#i-pencil"></use></svg>
                            </a>
                            <?php if ($jumlahProduk === 0): ?>
                                <button type="button" class="btn-aksi danger"
                                        title="Hapus supplier <?= e($supplier['nama']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#konfirmasiHapus"
                                        data-hapus-url="supplier-hapus.php"
                                        data-hapus-id="<?= (int) $supplier['id'] ?>"
                                        data-hapus-nama="<?= e($supplier['nama']) ?>">
                                    <svg class="icon" aria-hidden="true"><use href="#i-trash"></use></svg>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-aksi" disabled
                                        title="Tidak bisa dihapus: masih memasok <?= $jumlahProduk ?> produk">
                                    <svg class="icon" aria-hidden="true"><use href="#i-trash"></use></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
