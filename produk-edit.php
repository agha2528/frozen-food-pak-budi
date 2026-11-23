<?php
/**
 * Formulir ubah data produk.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = $id !== false && $id !== null ? (int) $id : 0;

if ($id <= 0) {
    set_flash('danger', 'Produk yang ingin diubah tidak ditemukan.');
    redirect('produk.php');
}

$produk = cari_produk($pdo, $id);
if ($produk === null) {
    set_flash('danger', 'Produk yang ingin diubah tidak ditemukan.');
    redirect('produk.php');
}

$daftarKategori = $pdo->query('SELECT id, nama FROM kategori ORDER BY nama ASC')->fetchAll();
$daftarSupplier = $pdo->query('SELECT id, nama FROM supplier ORDER BY nama ASC')->fetchAll();

$errors = [];
$form = [
    'nama_produk' => (string) $produk['nama_produk'],
    'kategori_id' => (string) $produk['kategori_id'],
    'supplier_id' => (string) $produk['supplier_id'],
    'harga'       => (string) (int) $produk['harga'],
    'stok'        => (string) $produk['stok'],
    'satuan'      => (string) $produk['satuan'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validasi_csrf();

    $form['nama_produk'] = input_teks('nama_produk');
    $form['kategori_id'] = input_teks('kategori_id');
    $form['supplier_id'] = input_teks('supplier_id');
    $form['harga']       = input_teks('harga');
    $form['stok']        = input_teks('stok');
    $form['satuan']      = input_teks('satuan');

    // Validasi nama produk (nama milik produk lain tidak boleh sama)
    if ($form['nama_produk'] === '') {
        $errors['nama_produk'] = 'Nama produk wajib diisi.';
    } elseif (strlen($form['nama_produk']) > 120) {
        $errors['nama_produk'] = 'Nama produk maksimal 120 karakter.';
    } else {
        $stmtCek = $pdo->prepare(
            'SELECT COUNT(*) FROM produk WHERE nama_produk = :nama_produk AND id <> :id'
        );
        $stmtCek->execute([
            ':nama_produk' => $form['nama_produk'],
            ':id'          => $id,
        ]);
        if ((int) $stmtCek->fetchColumn() > 0) {
            $errors['nama_produk'] = 'Nama produk sudah dipakai produk lain.';
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
                'UPDATE produk
                    SET nama_produk = :nama_produk,
                        kategori_id = :kategori_id,
                        supplier_id = :supplier_id,
                        harga       = :harga,
                        stok        = :stok,
                        satuan      = :satuan
                  WHERE id = :id'
            );
            $stmt->execute([
                ':nama_produk' => $form['nama_produk'],
                ':kategori_id' => (int) $kategoriId,
                ':supplier_id' => (int) $supplierId,
                ':harga'       => (float) $form['harga'],
                ':stok'        => (int) $form['stok'],
                ':satuan'      => $form['satuan'],
                ':id'          => $id,
            ]);

            set_flash('success', 'Produk "' . $form['nama_produk'] . '" berhasil diperbarui.');
            redirect('produk.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? 0) === 1062) {
                $errors['nama_produk'] = 'Nama produk sudah dipakai produk lain.';
            } else {
                $errors['umum'] = 'Perubahan produk gagal disimpan. Silakan coba lagi.';
                error_log('Gagal mengubah produk: ' . $exception->getMessage());
            }
        }
    }
}

$pageTitle    = 'Edit Produk';
$pageSubtitle = 'Perbarui informasi produk, harga, stok, kategori, atau supplier.';
$activePage   = 'produk';
$formAction   = 'produk-edit.php?id=' . $id;
$isEdit       = true;

require __DIR__ . '/includes/header.php';
?>

<section class="panel form-panel">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-pencil"></use></svg>
                Edit Produk
            </h2>
            <p class="panel-subtitle"><?= e($produk['nama_produk']) ?></p>
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
