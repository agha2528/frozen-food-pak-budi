<?php
/**
 * Formulir tambah produk.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$daftarKategori = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama ASC')->fetchAll();
$daftarSupplier = $pdo->query('SELECT id, nama FROM supplier ORDER BY nama ASC')->fetchAll();

$errors = [];
$form = [
    'nama_produk' => '',
    'kategori_id' => '',
    'supplier_id' => '',
    'harga'       => '',
    'stok'        => '',
    'satuan'      => 'pack',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validasi_csrf();

    $form['nama_produk'] = input_teks('nama_produk');
    $form['kategori_id'] = input_teks('kategori_id');
    $form['supplier_id'] = input_teks('supplier_id');
    $form['harga']       = input_teks('harga');
    $form['stok']        = input_teks('stok');
    $form['satuan']      = input_teks('satuan');

    // Validasi nama produk
    if ($form['nama_produk'] === '') {
        $errors['nama_produk'] = 'Nama produk wajib diisi.';
    } elseif (strlen($form['nama_produk']) > 120) {
        $errors['nama_produk'] = 'Nama produk maksimal 120 karakter.';
    } else {
        $stmtCek = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE nama_produk = :nama_produk');
        $stmtCek->execute([':nama_produk' => $form['nama_produk']]);
        if ((int) $stmtCek->fetchColumn() > 0) {
            $errors['nama_produk'] = 'Nama produk sudah terdaftar. Gunakan nama lain.';
        }
    }

    // Validasi kategori
    $kategoriId = filter_var($form['kategori_id'], FILTER_VALIDATE_INT);
    if ($kategoriId === false || $kategoriId <= 0) {
        $errors['kategori_id'] = 'Pilih kategori produk.';
    } elseif (cari_kategori($pdo, (int) $kategoriId) === null) {
        $errors['kategori_id'] = 'Kategori yang dipilih tidak ditemukan.';
    }

    // Validasi supplier
    $supplierId = filter_var($form['supplier_id'], FILTER_VALIDATE_INT);
    if ($supplierId === false || $supplierId <= 0) {
        $errors['supplier_id'] = 'Pilih supplier produk.';
    } elseif (cari_supplier($pdo, (int) $supplierId) === null) {
        $errors['supplier_id'] = 'Supplier yang dipilih tidak ditemukan.';
    }

    // Validasi harga
    if ($form['harga'] === '') {
        $errors['harga'] = 'Harga wajib diisi.';
    } elseif (!is_numeric($form['harga'])) {
        $errors['harga'] = 'Harga harus berupa angka.';
    } elseif ((float) $form['harga'] <= 0) {
        $errors['harga'] = 'Harga harus lebih dari 0.';
    } elseif ((float) $form['harga'] > 999999999.99) {
        $errors['harga'] = 'Harga terlalu besar.';
    }

    // Validasi stok
    if ($form['stok'] === '') {
        $errors['stok'] = 'Stok wajib diisi.';
    } else {
        $stokValid = filter_var($form['stok'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0],
        ]);
        if ($stokValid === false) {
            $errors['stok'] = 'Stok harus berupa angka bulat 0 atau lebih.';
        }
    }

    // Validasi satuan
    if (!in_array($form['satuan'], ['pack', 'pcs', 'kg', 'box', 'bal'], true)) {
        $errors['satuan'] = 'Pilih satuan yang tersedia.';
    }

    if ($errors === []) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO produk (nama_produk, kategori_id, supplier_id, harga, stok, satuan)
                 VALUES (:nama_produk, :kategori_id, :supplier_id, :harga, :stok, :satuan)'
            );
            $stmt->execute([
                ':nama_produk' => $form['nama_produk'],
                ':kategori_id' => (int) $kategoriId,
                ':supplier_id' => (int) $supplierId,
                ':harga'       => (float) $form['harga'],
                ':stok'        => (int) $form['stok'],
                ':satuan'      => $form['satuan'],
            ]);

            set_flash('success', 'Produk "' . $form['nama_produk'] . '" berhasil ditambahkan.');
            redirect('produk.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? 0) === 1062) {
                $errors['nama_produk'] = 'Nama produk sudah terdaftar. Gunakan nama lain.';
            } else {
                $errors['umum'] = 'Produk gagal disimpan. Silakan coba lagi.';
                error_log('Gagal menambah produk: ' . $exception->getMessage());
            }
        }
    }
}

$pageTitle    = 'Tambah Produk';
$pageSubtitle = 'Catat produk frozen food baru beserta kategori dan supplier-nya.';
$activePage   = 'produk';
$formAction   = 'produk-tambah.php';
$isEdit       = false;

require __DIR__ . '/includes/header.php';
?>

<section class="panel form-panel">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-box-plus"></use></svg>
                Produk Baru
            </h2>
            <p class="panel-subtitle">Kolom bertanda <span class="required-mark">*</span> wajib diisi.</p>
        </div>
        <a href="produk.php" class="btn btn-soft btn-sm ms-auto">
            <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
            Daftar Produk
        </a>
    </div>
    <div class="panel-body">
        <?php require __DIR__ . '/includes/form-produk.php'; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
