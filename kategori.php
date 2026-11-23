<?php
/**
 * Daftar kategori produk.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$daftarKategori = $pdo->query(
    'SELECT k.id, k.nama, k.deskripsi, COUNT(p.id) AS jumlah_produk
       FROM kategori k
       LEFT JOIN produk p ON p.kategori_id = k.id
      GROUP BY k.id, k.nama, k.deskripsi
      ORDER BY k.nama ASC'
)->fetchAll();

$pageTitle    = 'Kategori';
$pageSubtitle = 'Kelompokkan produk berdasarkan jenisnya.';
$activePage   = 'kategori';

require __DIR__ . '/includes/header.php';
?>

<section class="panel mb-4">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-tag"></use></svg>
                Daftar Kategori
            </h2>
            <p class="panel-subtitle">
                <?= count($daftarKategori) ?> kategori terdaftar. Kategori yang masih dipakai produk tidak dapat dihapus.
            </p>
        </div>
        <a href="kategori-tambah.php" class="btn btn-brand btn-sm ms-auto">
            <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
            Tambah Kategori
        </a>
    </div>
</section>

<?php if ($daftarKategori === []): ?>
    <section class="panel">
        <div class="empty-state">
            <span class="empty-icon">
                <svg class="icon" aria-hidden="true"><use href="#i-tag"></use></svg>
            </span>
            <h3>Belum ada kategori</h3>
            <p>Buat kategori pertama agar produk frozen food bisa dikelompokkan.</p>
            <a href="kategori-tambah.php" class="btn btn-brand btn-sm">
                <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
                Tambah Kategori
            </a>
        </div>
    </section>
<?php else: ?>
    <div class="row g-3">
        <?php foreach ($daftarKategori as $kategori): ?>
            <?php $jumlahProduk = (int) $kategori['jumlah_produk']; ?>
            <div class="col-12 col-md-6 col-xl-4">
                <article class="data-card">
                    <div class="data-card-body">
                        <div class="data-card-head">
                            <span class="data-icon">
                                <svg class="icon" aria-hidden="true"><use href="#i-tag"></use></svg>
                            </span>
                            <div>
                                <h3>
                                    <a href="produk.php?kategori_id=<?= (int) $kategori['id'] ?>" class="text-reset">
                                        <?= e($kategori['nama']) ?>
                                    </a>
                                </h3>
                                <p class="produk-satuan">Kategori produk</p>
                            </div>
                        </div>

                        <p class="data-desc">
                            <?= $kategori['deskripsi'] !== null && $kategori['deskripsi'] !== ''
                                ? e($kategori['deskripsi'])
                                : 'Belum ada deskripsi untuk kategori ini.' ?>
                        </p>

                        <span class="count-badge">
                            <svg class="icon" aria-hidden="true"><use href="#i-package"></use></svg>
                            <?= $jumlahProduk ?> produk
                        </span>
                    </div>

                    <div class="data-card-foot">
                        <a href="produk.php?kategori_id=<?= (int) $kategori['id'] ?>" class="panel-link">
                            Lihat produk
                            <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
                        </a>
                        <div class="ms-auto d-flex gap-1">
                            <a href="kategori-edit.php?id=<?= (int) $kategori['id'] ?>"
                               class="btn-aksi" title="Edit kategori <?= e($kategori['nama']) ?>">
                                <svg class="icon" aria-hidden="true"><use href="#i-pencil"></use></svg>
                            </a>
                            <?php if ($jumlahProduk === 0): ?>
                                <button type="button" class="btn-aksi danger"
                                        title="Hapus kategori <?= e($kategori['nama']) ?>"
                                        data-bs-toggle="modal" data-bs-target="#konfirmasiHapus"
                                        data-hapus-url="kategori-hapus.php"
                                        data-hapus-id="<?= (int) $kategori['id'] ?>"
                                        data-hapus-nama="<?= e($kategori['nama']) ?>">
                                    <svg class="icon" aria-hidden="true"><use href="#i-trash"></use></svg>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn-aksi" disabled
                                        title="Tidak bisa dihapus: masih dipakai <?= $jumlahProduk ?> produk">
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
