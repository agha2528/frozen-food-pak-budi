<?php
/**
 * Riwayat perubahan stok dan harga produk.
 * Mencatat stok masuk, stok keluar, dan penyesuaian harga.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$cari = isset($_GET['cari']) && is_string($_GET['cari']) ? trim($_GET['cari']) : '';

$produkId = filter_input(INPUT_GET, 'produk_id', FILTER_VALIDATE_INT);
$produkId = $produkId !== false && $produkId !== null ? (int) $produkId : 0;

$tipe = isset($_GET['tipe']) && is_string($_GET['tipe']) ? $_GET['tipe'] : '';
if (!in_array($tipe, ['masuk', 'keluar', 'penyesuaian'], true)) {
    $tipe = '';
}

$halaman = filter_input(INPUT_GET, 'halaman', FILTER_VALIDATE_INT);
$halaman = $halaman !== false && $halaman !== null ? max(1, (int) $halaman) : 1;
$perHalaman = 15;

$kondisi = [];
$parameter = [];

if ($cari !== '') {
    $kondisi[] = 'p.nama_produk LIKE :cari';
    $parameter[':cari'] = '%' . $cari . '%';
}

if ($produkId > 0) {
    $kondisi[] = 'r.produk_id = :produk_id';
    $parameter[':produk_id'] = $produkId;
}

if ($tipe !== '') {
    $kondisi[] = 'r.tipe = :tipe';
    $parameter[':tipe'] = $tipe;
}

$where = $kondisi === [] ? '' : ' WHERE ' . implode(' AND ', $kondisi);

$stmtJumlah = $pdo->prepare(
    'SELECT COUNT(*)
       FROM riwayat_stok r
       JOIN produk p ON p.id = r.produk_id'
    . $where
);
$stmtJumlah->execute($parameter);
$totalData = (int) $stmtJumlah->fetchColumn();
$totalHalaman = max(1, (int) ceil($totalData / $perHalaman));

if ($halaman > $totalHalaman) {
    $halaman = $totalHalaman;
}

$offset = ($halaman - 1) * $perHalaman;

$sqlData = 'SELECT r.id, r.produk_id, r.tipe, r.jumlah, r.stok_sebelum, r.stok_sesudah,
                   r.harga_lama, r.harga_baru, r.keterangan, r.created_at,
                   p.nama_produk, p.satuan
              FROM riwayat_stok r
              JOIN produk p ON p.id = r.produk_id'
    . $where
    . ' ORDER BY r.created_at DESC, r.id DESC'
    . ' LIMIT ' . $perHalaman . ' OFFSET ' . $offset;

$stmtData = $pdo->prepare($sqlData);
$stmtData->execute($parameter);
$daftarRiwayat = $stmtData->fetchAll();

$ringkasan = $pdo->query(
    "SELECT
        COALESCE(SUM(CASE WHEN tipe = 'masuk' THEN jumlah ELSE 0 END), 0) AS total_masuk,
        COALESCE(SUM(CASE WHEN tipe = 'keluar' THEN jumlah ELSE 0 END), 0) AS total_keluar,
        COALESCE(SUM(CASE WHEN tipe = 'penyesuaian' THEN 1 ELSE 0 END), 0) AS total_penyesuaian,
        COUNT(*) AS total_riwayat
       FROM riwayat_stok"
)->fetch();

$daftarProduk = $pdo->query('SELECT id, nama_produk FROM produk ORDER BY nama_produk ASC')->fetchAll();

$adaFilter = $cari !== '' || $produkId > 0 || $tipe !== '';

$statistik = [
    [
        'label' => 'Stok Masuk',
        'nilai' => number_format((int) ($ringkasan['total_masuk'] ?? 0), 0, ',', '.'),
        'sub'   => 'total unit bertambah',
        'icon'  => 'i-arrow-down',
        'warna' => 'teal',
    ],
    [
        'label' => 'Stok Keluar',
        'nilai' => number_format((int) ($ringkasan['total_keluar'] ?? 0), 0, ',', '.'),
        'sub'   => 'total unit berkurang',
        'icon'  => 'i-arrow-up',
        'warna' => 'amber',
    ],
    [
        'label' => 'Penyesuaian',
        'nilai' => number_format((int) ($ringkasan['total_penyesuaian'] ?? 0), 0, ',', '.'),
        'sub'   => 'catatan perubahan harga',
        'icon'  => 'i-history',
        'warna' => 'blue',
    ],
    [
        'label' => 'Total Riwayat',
        'nilai' => number_format((int) ($ringkasan['total_riwayat'] ?? 0), 0, ',', '.'),
        'sub'   => 'seluruh aktivitas tercatat',
        'icon'  => 'i-layers',
        'warna' => 'violet',
    ],
];

$tautanHalaman = static function (int $nomor) use ($cari, $produkId, $tipe): string {
    $parameter = [];
    if ($cari !== '') {
        $parameter['cari'] = $cari;
    }
    if ($produkId > 0) {
        $parameter['produk_id'] = $produkId;
    }
    if ($tipe !== '') {
        $parameter['tipe'] = $tipe;
    }
    $parameter['halaman'] = $nomor;

    return 'riwayat.php?' . http_build_query($parameter);
};

$pageTitle    = 'Riwayat Stok';
$pageSubtitle = 'Semua perubahan stok dan harga produk tercatat di sini.';
$activePage   = 'riwayat';

require __DIR__ . '/includes/header.php';
?>

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

<section class="panel filter-panel">
    <div class="panel-body">
        <form method="get" action="riwayat.php">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="cari" class="form-label">Cari produk</label>
                    <div class="search-field">
                        <svg class="icon" aria-hidden="true"><use href="#i-search"></use></svg>
                        <input type="text" class="form-control" id="cari" name="cari"
                               value="<?= e($cari) ?>" placeholder="Ketik nama produk...">
                    </div>
                </div>

                <div class="col-12 col-md-5 col-lg-3">
                    <label for="produk_id" class="form-label">Produk</label>
                    <select class="form-select" id="produk_id" name="produk_id">
                        <option value="">Semua produk</option>
                        <?php foreach ($daftarProduk as $produk): ?>
                            <option value="<?= (int) $produk['id'] ?>"
                                <?= $produkId === (int) $produk['id'] ? 'selected' : '' ?>>
                                <?= e($produk['nama_produk']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label for="tipe" class="form-label">Jenis</label>
                    <select class="form-select" id="tipe" name="tipe">
                        <option value="">Semua jenis</option>
                        <option value="masuk" <?= $tipe === 'masuk' ? 'selected' : '' ?>>Stok masuk</option>
                        <option value="keluar" <?= $tipe === 'keluar' ? 'selected' : '' ?>>Stok keluar</option>
                        <option value="penyesuaian" <?= $tipe === 'penyesuaian' ? 'selected' : '' ?>>Penyesuaian</option>
                    </select>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-brand flex-grow-1">
                            <svg class="icon" aria-hidden="true"><use href="#i-search"></use></svg>
                            Filter
                        </button>
                        <a href="riwayat.php" class="btn btn-soft" title="Reset filter" aria-label="Reset filter">
                            <svg class="icon" aria-hidden="true"><use href="#i-x"></use></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="filter-result">
                <span><strong><?= $totalData ?></strong> catatan ditemukan</span>
                <?php if ($adaFilter): ?>
                    <span class="filter-chip">Filter aktif</span>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-history"></use></svg>
                Aktivitas Stok
            </h2>
            <p class="panel-subtitle">Diurutkan dari aktivitas paling baru.</p>
        </div>
        <a href="produk.php" class="btn btn-soft btn-sm ms-auto">
            <svg class="icon" aria-hidden="true"><use href="#i-package"></use></svg>
            Daftar Produk
        </a>
    </div>

    <?php if ($daftarRiwayat === []): ?>
        <div class="empty-state">
            <span class="empty-icon">
                <svg class="icon" aria-hidden="true"><use href="#i-history"></use></svg>
            </span>
            <h3><?= $adaFilter ? 'Riwayat tidak ditemukan' : 'Belum ada riwayat' ?></h3>
            <p>
                <?= $adaFilter
                    ? 'Coba ubah kata kunci, produk, atau jenis aktivitas yang dipilih.'
                    : 'Riwayat akan muncul otomatis saat stok ditambah, dikurangi, atau harga diubah.' ?>
            </p>
            <?php if ($adaFilter): ?>
                <a href="riwayat.php" class="btn btn-soft btn-sm">
                    <svg class="icon" aria-hidden="true"><use href="#i-x"></use></svg>
                    Reset Filter
                </a>
            <?php else: ?>
                <a href="produk.php" class="btn btn-brand btn-sm">
                    <svg class="icon" aria-hidden="true"><use href="#i-box-plus"></use></svg>
                    Kelola Stok Produk
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-app align-middle">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Produk</th>
                        <th>Jenis</th>
                        <th>Perubahan</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarRiwayat as $riwayat): ?>
                        <?php
                        $selisih = (int) $riwayat['stok_sesudah'] - (int) $riwayat['stok_sebelum'];
                        $kelasSelisih = $selisih > 0 ? 'plus' : ($selisih < 0 ? 'minus' : 'netral');
                        $hargaLama = $riwayat['harga_lama'] !== null ? (float) $riwayat['harga_lama'] : null;
                        $hargaBaru = $riwayat['harga_baru'] !== null ? (float) $riwayat['harga_baru'] : null;
                        $hargaBerubah = $hargaLama !== null && $hargaBaru !== null
                            && abs($hargaLama - $hargaBaru) > 0.001;
                        ?>
                        <tr>
                            <td class="riwayat-waktu">
                                <?= e(tanggal_waktu_indonesia($riwayat['created_at'])) ?>
                            </td>
                            <td>
                                <p class="produk-nama"><?= e($riwayat['nama_produk']) ?></p>
                                <p class="produk-satuan">per <?= e($riwayat['satuan']) ?></p>
                            </td>
                            <td>
                                <span class="riwayat-tipe <?= e($riwayat['tipe']) ?>">
                                    <?= e(label_tipe_stok($riwayat['tipe'])) ?>
                                </span>
                            </td>
                            <td>
                                <span class="selisih <?= e($kelasSelisih) ?>">
                                    <?= e(format_selisih_stok((int) $riwayat['stok_sebelum'], (int) $riwayat['stok_sesudah'])) ?>
                                    <?= e($riwayat['satuan']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="stok-arrow">
                                    <?= (int) $riwayat['stok_sebelum'] ?>
                                    <svg class="icon icon-mini" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
                                    <b><?= (int) $riwayat['stok_sesudah'] ?></b>
                                </span>
                            </td>
                            <td>
                                <?php if ($hargaBerubah): ?>
                                    <span class="harga-change">
                                        <s><?= e(rupiah($hargaLama)) ?></s>
                                        <b><?= e(rupiah($hargaBaru)) ?></b>
                                    </span>
                                <?php elseif ($hargaBaru !== null): ?>
                                    <span class="harga-text"><?= e(rupiah($hargaBaru)) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="riwayat-keterangan">
                                <?= $riwayat['keterangan'] !== null && $riwayat['keterangan'] !== ''
                                    ? e($riwayat['keterangan'])
                                    : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalHalaman > 1): ?>
            <div class="pagination-app">
                <span class="info">
                    Halaman <?= $halaman ?> dari <?= $totalHalaman ?>
                    &middot; <?= number_format($totalData, 0, ',', '.') ?> catatan
                </span>
                <div class="nav-buttons">
                    <?php if ($halaman > 1): ?>
                        <a href="<?= e($tautanHalaman($halaman - 1)) ?>" class="btn btn-soft btn-sm">
                            <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
                            Sebelumnya
                        </a>
                    <?php else: ?>
                        <span class="btn btn-light btn-sm disabled">Sebelumnya</span>
                    <?php endif; ?>

                    <?php if ($halaman < $totalHalaman): ?>
                        <a href="<?= e($tautanHalaman($halaman + 1)) ?>" class="btn btn-brand btn-sm">
                            Berikutnya
                            <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-light btn-sm disabled">Berikutnya</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
