<?php
/**
 * Daftar produk dengan pencarian dan filter kategori/supplier.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$cari = isset($_GET['cari']) && is_string($_GET['cari']) ? trim($_GET['cari']) : '';

$kategoriId = filter_input(INPUT_GET, 'kategori_id', FILTER_VALIDATE_INT);
$kategoriId = $kategoriId !== false && $kategoriId !== null ? (int) $kategoriId : 0;

$supplierId = filter_input(INPUT_GET, 'supplier_id', FILTER_VALIDATE_INT);
$supplierId = $supplierId !== false && $supplierId !== null ? (int) $supplierId : 0;

$filterStok = isset($_GET['stok']) && is_string($_GET['stok']) ? $_GET['stok'] : '';
if (!in_array($filterStok, ['habis', 'menipis'], true)) {
    $filterStok = '';
}

$kondisi = [];
$parameter = [];

if ($cari !== '') {
    $kondisi[] = 'p.nama_produk LIKE :cari';
    $parameter[':cari'] = '%' . $cari . '%';
}

if ($kategoriId > 0) {
    $kondisi[] = 'p.kategori_id = :kategori_id';
    $parameter[':kategori_id'] = $kategoriId;
}

if ($supplierId > 0) {
    $kondisi[] = 'p.supplier_id = :supplier_id';
    $parameter[':supplier_id'] = $supplierId;
}

if ($filterStok === 'habis') {
    $kondisi[] = 'p.stok = 0';
} elseif ($filterStok === 'menipis') {
    $kondisi[] = 'p.stok BETWEEN 1 AND 10';
}

$where = $kondisi === [] ? '' : ' WHERE ' . implode(' AND ', $kondisi);

$sql = 'SELECT p.id, p.nama_produk, p.harga, p.stok, p.satuan,
               k.id AS kategori_id, k.nama AS nama_kategori,
               s.id AS supplier_id, s.nama AS nama_supplier
          FROM produk p
          JOIN kategori k ON k.id = p.kategori_id
          JOIN supplier s ON s.id = p.supplier_id'
    . $where
    . ' ORDER BY p.nama_produk ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($parameter);
$daftarProduk = $stmt->fetchAll();

$totalNilai = 0.0;
foreach ($daftarProduk as $produk) {
    $totalNilai += (float) $produk['harga'] * (int) $produk['stok'];
}

$daftarKategori = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama ASC')->fetchAll();
$daftarSupplier = $pdo->query('SELECT id, nama FROM supplier ORDER BY nama ASC')->fetchAll();

$kategoriTerpilih = $kategoriId > 0 ? cari_kategori($pdo, $kategoriId) : null;
$supplierTerpilih = $supplierId > 0 ? cari_supplier($pdo, $supplierId) : null;

$adaFilter = $cari !== '' || $kategoriId > 0 || $supplierId > 0 || $filterStok !== '';

$pageTitle    = 'Produk';
$pageSubtitle = 'Catat, kelompokkan, dan pantau stok produk frozen food.';
$activePage   = 'produk';

require __DIR__ . '/includes/header.php';
?>

<section class="panel filter-panel">
    <div class="panel-body">
        <form method="get" action="produk.php">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label for="cari" class="form-label">Cari produk</label>
                    <div class="search-field">
                        <svg class="icon" aria-hidden="true"><use href="#i-search"></use></svg>
                        <input type="text" class="form-control" id="cari" name="cari"
                               value="<?= e($cari) ?>" placeholder="Ketik nama produk...">
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id">
                        <option value="">Semua kategori</option>
                        <?php foreach ($daftarKategori as $kategori): ?>
                            <option value="<?= (int) $kategori['id'] ?>"
                                <?= $kategoriId === (int) $kategori['id'] ? 'selected' : '' ?>>
                                <?= e($kategori['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-lg-3">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">Semua supplier</option>
                        <?php foreach ($daftarSupplier as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"
                                <?= $supplierId === (int) $supplier['id'] ? 'selected' : '' ?>>
                                <?= e($supplier['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-lg-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-brand flex-grow-1">
                            <svg class="icon" aria-hidden="true"><use href="#i-search"></use></svg>
                            Filter
                        </button>
                        <a href="produk.php" class="btn btn-soft" title="Reset filter" aria-label="Reset filter">
                            <svg class="icon" aria-hidden="true"><use href="#i-x"></use></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="filter-result">
                <span><strong><?= count($daftarProduk) ?></strong> produk ditemukan</span>
                <span>&middot;</span>
                <span>Total nilai stok: <strong><?= e(rupiah($totalNilai)) ?></strong></span>

                <?php if ($kategoriTerpilih !== null): ?>
                    <span class="filter-chip">
                        <svg class="icon" aria-hidden="true"><use href="#i-tag"></use></svg>
                        <?= e($kategoriTerpilih['nama']) ?>
                    </span>
                <?php endif; ?>

                <?php if ($supplierTerpilih !== null): ?>
                    <span class="filter-chip">
                        <svg class="icon" aria-hidden="true"><use href="#i-truck"></use></svg>
                        <?= e($supplierTerpilih['nama']) ?>
                    </span>
                <?php endif; ?>

                <?php if ($filterStok === 'habis'): ?>
                    <span class="filter-chip">Stok habis</span>
                <?php elseif ($filterStok === 'menipis'): ?>
                    <span class="filter-chip">Stok menipis</span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-package"></use></svg>
                Daftar Produk
            </h2>
            <p class="panel-subtitle">Setiap produk terhubung dengan satu kategori dan satu supplier.</p>
            <p class="panel-subtitle d-md-none mb-0">Geser tabel ke kiri atau kanan untuk melihat semua kolom.</p>
        </div>
        <a href="produk-tambah.php" class="btn btn-brand btn-sm ms-auto">
            <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
            Tambah Produk
        </a>
    </div>

    <?php if ($daftarProduk === []): ?>
        <div class="empty-state">
            <span class="empty-icon">
                <svg class="icon" aria-hidden="true"><use href="#i-package"></use></svg>
            </span>
            <h3><?= $adaFilter ? 'Produk tidak ditemukan' : 'Belum ada produk' ?></h3>
            <p>
                <?= $adaFilter
                    ? 'Coba ubah kata kunci atau filter kategori/supplier yang dipilih.'
                    : 'Mulai catat produk frozen food pertama milik Pak Budi.' ?>
            </p>
            <?php if ($adaFilter): ?>
                <a href="produk.php" class="btn btn-soft btn-sm">
                    <svg class="icon" aria-hidden="true"><use href="#i-x"></use></svg>
                    Reset Filter
                </a>
            <?php else: ?>
                <a href="produk-tambah.php" class="btn btn-brand btn-sm">
                    <svg class="icon" aria-hidden="true"><use href="#i-plus"></use></svg>
                    Tambah Produk
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-app align-middle">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th class="col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarProduk as $produk): ?>
                        <?php $status = status_stok((int) $produk['stok']); ?>
                        <tr>
                            <td>
                                <div class="produk-info">
                                    <span class="produk-thumb">
                                        <svg class="icon" aria-hidden="true"><use href="#i-package"></use></svg>
                                    </span>
                                    <div>
                                        <p class="produk-nama"><?= e($produk['nama_produk']) ?></p>
                                        <p class="produk-satuan">per <?= e($produk['satuan']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="produk.php?kategori_id=<?= (int) $produk['kategori_id'] ?>" class="badge-soft">
                                    <?= e($produk['nama_kategori']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="supplier-text">
                                    <svg class="icon" aria-hidden="true"><use href="#i-truck"></use></svg>
                                    <?= e($produk['nama_supplier']) ?>
                                </span>
                            </td>
                            <td class="harga-text"><?= e(rupiah($produk['harga'])) ?></td>
                            <td>
                                <span class="stok-pill <?= e($status['kelas']) ?>">
                                    <?= (int) $produk['stok'] ?> <?= e($produk['satuan']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="produk-edit.php?id=<?= (int) $produk['id'] ?>"
                                       class="btn-aksi" title="Edit <?= e($produk['nama_produk']) ?>">
                                        <svg class="icon" aria-hidden="true"><use href="#i-pencil"></use></svg>
                                    </a>
                                    <button type="button" class="btn-aksi danger"
                                            title="Hapus <?= e($produk['nama_produk']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#konfirmasiHapus"
                                            data-hapus-url="produk-hapus.php"
                                            data-hapus-id="<?= (int) $produk['id'] ?>"
                                            data-hapus-nama="<?= e($produk['nama_produk']) ?>">
                                        <svg class="icon" aria-hidden="true"><use href="#i-trash"></use></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
