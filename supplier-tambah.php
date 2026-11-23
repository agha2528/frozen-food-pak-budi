<?php
/**
 * Formulir tambah supplier.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$errors = [];
$form = [
    'nama'   => '',
    'kontak' => '',
    'alamat' => '',
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
        $stmtCek = $pdo->prepare('SELECT COUNT(*) FROM supplier WHERE nama = :nama');
        $stmtCek->execute([':nama' => $form['nama']]);
        if ((int) $stmtCek->fetchColumn() > 0) {
            $errors['nama'] = 'Nama supplier sudah terdaftar.';
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
                'INSERT INTO supplier (nama, kontak, alamat) VALUES (:nama, :kontak, :alamat)'
            );
            $stmt->execute([
                ':nama'   => $form['nama'],
                ':kontak' => $form['kontak'] === '' ? null : $form['kontak'],
                ':alamat' => $form['alamat'] === '' ? null : $form['alamat'],
            ]);

            set_flash('success', 'Supplier "' . $form['nama'] . '" berhasil ditambahkan.');
            redirect('supplier.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? 0) === 1062) {
                $errors['nama'] = 'Nama supplier sudah terdaftar.';
            } else {
                $errors['umum'] = 'Supplier gagal disimpan. Silakan coba lagi.';
                error_log('Gagal menambah supplier: ' . $exception->getMessage());
            }
        }
    }
}

$pageTitle    = 'Tambah Supplier';
$pageSubtitle = 'Catat mitra pemasok beserta kontak dan alamatnya.';
$activePage   = 'supplier';
$formAction   = 'supplier-tambah.php';
$isEdit       = false;

require __DIR__ . '/includes/header.php';
?>

<section class="panel form-panel">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-truck"></use></svg>
                Supplier Baru
            </h2>
            <p class="panel-subtitle">Kolom bertanda <span class="required-mark">*</span> wajib diisi.</p>
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
