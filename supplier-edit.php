<?php
/**
 * Formulir ubah supplier.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = $id !== false && $id !== null ? (int) $id : 0;

if ($id <= 0) {
    set_flash('danger', 'Supplier yang ingin diubah tidak ditemukan.');
    redirect('supplier.php');
}

$supplier = cari_supplier($pdo, $id);
if ($supplier === null) {
    set_flash('danger', 'Supplier yang ingin diubah tidak ditemukan.');
    redirect('supplier.php');
}

$stmtHitung = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE supplier_id = :id');
$stmtHitung->execute([':id' => $id]);
$jumlahProduk = (int) $stmtHitung->fetchColumn();

$errors = [];
$form = [
    'nama'   => (string) $supplier['nama'],
    'kontak' => (string) ($supplier['kontak'] ?? ''),
    'alamat' => (string) ($supplier['alamat'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validasi_csrf();

    $form['nama']   = input_teks('nama');
    $form['kontak'] = input_teks('kontak');
    $form['alamat'] = input_teks('alamat');

    if ($form['nama'] === '') {
        $errors['nama'] = 'Nama supplier wajib diisi.';
    } elseif (strlen($form['nama']) > 100) {
        $errors['nama'] = 'Nama supplier maksimal 100 karakter.';
    } else {
        $stmtCek = $pdo->prepare('SELECT COUNT(*) FROM supplier WHERE nama = :nama AND id <> :id');
        $stmtCek->execute([
            ':nama' => $form['nama'],
            ':id'   => $id,
        ]);
        if ((int) $stmtCek->fetchColumn() > 0) {
            $errors['nama'] = 'Nama supplier sudah dipakai supplier lain.';
        }
    }

    if ($form['kontak'] !== '' && !preg_match('/^[0-9+\-\s().]{6,30}$/', $form['kontak'])) {
        $errors['kontak'] = 'Nomor kontak hanya boleh berisi angka, spasi, +, -, titik, dan kurung (6-30 karakter).';
    }

    if (strlen($form['alamat']) > 255) {
        $errors['alamat'] = 'Alamat maksimal 255 karakter.';
    }

    if ($errors === []) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE supplier
                    SET nama = :nama, kontak = :kontak, alamat = :alamat
                  WHERE id = :id'
            );
            $stmt->execute([
                ':nama'   => $form['nama'],
                ':kontak' => $form['kontak'] === '' ? null : $form['kontak'],
                ':alamat' => $form['alamat'] === '' ? null : $form['alamat'],
                ':id'     => $id,
            ]);

            set_flash('success', 'Supplier "' . $form['nama'] . '" berhasil diperbarui.');
            redirect('supplier.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? 0) === 1062) {
                $errors['nama'] = 'Nama supplier sudah dipakai supplier lain.';
            } else {
                $errors['umum'] = 'Perubahan supplier gagal disimpan. Silakan coba lagi.';
                error_log('Gagal mengubah supplier: ' . $exception->getMessage());
            }
        }
    }
}

$pageTitle    = 'Edit Supplier';
$pageSubtitle = 'Perbarui nama, kontak, atau alamat supplier.';
$activePage   = 'supplier';
$formAction   = 'supplier-edit.php?id=' . $id;
$isEdit       = true;

require __DIR__ . '/includes/header.php';
?>

<section class="panel form-panel">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-pencil"></use></svg>
                Edit Supplier
            </h2>
            <p class="panel-subtitle">
                <?= e($supplier['nama']) ?> &middot; memasok <?= $jumlahProduk ?> produk
            </p>
        </div>
        <a href="supplier.php" class="btn btn-soft btn-sm ms-auto">
            <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
            Daftar Supplier
        </a>
    </div>
    <div class="panel-body">
        <?php require __DIR__ . '/includes/form-supplier.php'; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
