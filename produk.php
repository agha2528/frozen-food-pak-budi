<?php
/**
 * Daftar produk dengan pencarian, filter, pengurutan,
 * serta tombol cepat untuk menambah stok dan mengubah harga.
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
if (!in_array($filterStok, ['habis', 'menipis', 'aman'], true)) {
    $filterStok = '';
}

$pilihanUrutan = [
    'nama'         => 'Nama A-Z',
    'stok_rendah'  => 'Stok tersedikit',
    'stok_tinggi'  => 'Stok terbanyak',
    'harga_rendah' => 'Harga termurah',
    'harga_tinggi' => 'Harga termahal',
    'terbaru'      => 'Produk terbaru',
];

$urutan = isset($_GET['urutan']) && is_string($_GET['urutan']) ? $_GET['urutan'] : 'nama';
if (!array_key_exists($urutan, $pilihanUrutan)) {
    $urutan = 'nama';
}

$orderBy = [
    'nama'         => 'p.nama_produk ASC',
    'stok_rendah'  => 'p.stok ASC, p.nama_produk ASC',
    'stok_tinggi'  => 'p.stok DESC, p.nama_produk ASC',
    'harga_rendah' => 'p.harga ASC, p.nama_produk ASC',
    'harga_tinggi' => 'p.harga DESC, p.nama_produk ASC',
    'terbaru'      => 'p.id DESC',
][$urutan];

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
} elseif ($filterStok === 'aman') {
    $kondisi[] = 'p.stok > 10';
}

$where = $kondisi === [] ? '' : ' WHERE ' . implode(' AND ', $kondisi);

$sql = 'SELECT p.id, p.nama_produk, p.harga, p.stok, p.satuan,
               k.id AS kategori_id, k.nama AS nama_kategori,
               s.id AS supplier_id, s.nama AS nama_supplier
          FROM produk p
          JOIN kategori k ON k.id = p.kategori_id
          JOIN supplier s ON s.id = p.supplier_id'
    . $where
    . ' ORDER BY ' . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($parameter);
$daftarProduk = $stmt->fetchAll();

$totalNilai = 0.0;
foreach ($daftarProduk as $produk) {
    $totalNilai += (float) $produk['harga'] * (int) $produk['stok'];
}

$ringkasanStok = $pdo->query(
    "SELECT
        COALESCE(SUM(CASE WHEN stok = 0 THEN 1 ELSE 0 END), 0) AS habis,
        COALESCE(SUM(CASE WHEN stok BETWEEN 1 AND 10 THEN 1 ELSE 0 END), 0) AS menipis,
        COALESCE(SUM(CASE WHEN stok > 10 THEN 1 ELSE 0 END), 0) AS aman
       FROM produk"
)->fetch();

$jumlahHabis   = (int) ($ringkasanStok['habis'] ?? 0);
$jumlahMenipis = (int) ($ringkasanStok['menipis'] ?? 0);
$jumlahAman    = (int) ($ringkasanStok['aman'] ?? 0);

$daftarKategori = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama ASC')->fetchAll();
$daftarSupplier = $pdo->query('SELECT id, nama FROM supplier ORDER BY nama ASC')->fetchAll();

$kategoriTerpilih = $kategoriId > 0 ? cari_kategori($pdo, $kategoriId) : null;
$supplierTerpilih = $supplierId > 0 ? cari_supplier($pdo, $supplierId) : null;

$adaFilter = $cari !== '' || $kategoriId > 0 || $supplierId > 0 || $filterStok !== '';

$labelFilterStok = [
    'habis'   => 'Stok habis',
    'menipis' => 'Stok menipis',
    'aman'    => 'Stok aman',
];

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

                <div class="col-6 col-lg-2">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id">
                        <option value="">Semua</option>
                        <?php foreach ($daftarKategori as $kategori): ?>
                            <option value="<?= (int) $kategori['id'] ?>"
                                <?= $kategoriId === (int) $kategori['id'] ? 'selected' : '' ?>>
                                <?= e($kategori['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">Semua</option>
                        <?php foreach ($daftarSupplier as $supplier): ?>
                            <option value="<?= (int) $supplier['id'] ?>"
                                <?= $supplierId === (int) $supplier['id'] ? 'selected' : '' ?>>
                                <?= e($supplier['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="stok" class="form-label">Status stok</label>
                    <select class="form-select" id="stok" name="stok">
                        <option value="">Semua status</option>
                        <option value="aman" <?= $filterStok === 'aman' ? 'selected' : '' ?>>Aman (&gt; 10)</option>
                        <option value="menipis" <?= $filterStok === 'menipis' ? 'selected' : '' ?>>Menipis (1-10)</option>
                        <option value="habis" <?= $filterStok === 'habis' ? 'selected' : '' ?>>Habis (0)</option>
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="urutan" class="form-label">Urutkan</label>
                    <select class="form-select" id="urutan" name="urutan">
                        <?php foreach ($pilihanUrutan as $nilai => $label): ?>
                            <option value="<?= e($nilai) ?>" <?= $urutan === $nilai ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="submit" class="btn btn-brand">
                    <svg class="icon" aria-hidden="true"><use href="#i-search"></use></svg>
                    Terapkan Filter
                </button>
                <a href="produk.php" class="btn btn-soft">
                    <svg class="icon" aria-hidden="true"><use href="#i-x"></use></svg>
                    Reset
                </a>

                <div class="filter-quick ms-lg-auto">
                    <a href="produk.php?stok=aman" class="quick-pill aman<?= $filterStok === 'aman' ? ' active' : '' ?>">
                        <span class="dot"></span>Aman <b><?= $jumlahAman ?></b>
                    </a>
                    <a href="produk.php?stok=menipis" class="quick-pill menipis<?= $filterStok === 'menipis' ? ' active' : '' ?>">
                        <span class="dot"></span>Menipis <b><?= $jumlahMenipis ?></b>
                    </a>
                    <a href="produk.php?stok=habis" class="quick-pill habis<?= $filterStok === 'habis' ? ' active' : '' ?>">
                        <span class="dot"></span>Habis <b><?= $jumlahHabis ?></b>
                    </a>
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

                <?php if ($filterStok !== ''): ?>
                    <span class="filter-chip"><?= e($labelFilterStok[$filterStok]) ?></span>
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
            <p class="panel-subtitle">Klik tombol kotak untuk menambah stok atau mengubah harga dengan cepat.</p>
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
                    ? 'Coba ubah kata kunci, status stok, atau filter kategori/supplier yang dipilih.'
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
                        <th class="col-aksi col-aksi-lebar">Aksi</th>
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
                                    <button type="button" class="btn-aksi stok"
                                            title="Kelola stok &amp; harga <?= e($produk['nama_produk']) ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalStok"
                                            data-stok-id="<?= (int) $produk['id'] ?>"
                                            data-stok-nama="<?= e($produk['nama_produk']) ?>"
                                            data-stok-kategori="<?= e($produk['nama_kategori']) ?>"
                                            data-stok-jumlah="<?= (int) $produk['stok'] ?>"
                                            data-stok-satuan="<?= e($produk['satuan']) ?>"
                                            data-stok-harga="<?= (int) $produk['harga'] ?>"
                                            data-stok-tujuan="produk.php">
                                        <svg class="icon" aria-hidden="true"><use href="#i-box-plus"></use></svg>
                                    </button>
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
