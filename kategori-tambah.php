<?php
/**
 * Formulir tambah kategori.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$errors = [];
$form = [
    'nama'      => '',
    'deskripsi' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validasi_csrf();

    $form['nama']      = input_teks('nama');
    $form['deskripsi'] = input_teks('deskripsi');

    if ($form['nama'] === '') {
        $errors['nama'] = 'Nama kategori wajib diisi.';
    } elseif (strlen($form['nama']) > 100) {
        $errors['nama'] = 'Nama kategori maksimal 100 karakter.';
    } else {
        $stmtCek = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE nama = :nama');
        $stmtCek->execute([':nama' => $form['nama']]);
        if ((int) $stmtCek->fetchColumn() > 0) {
            $errors['nama'] = 'Nama kategori sudah terdaftar.';
        }
    }

    if (strlen($form['deskripsi']) > 500) {
        $errors['deskripsi'] = 'Deskripsi maksimal 500 karakter.';
    }

    if ($errors === []) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO kategori (nama, deskripsi) VALUES (:nama, :deskripsi)'
            );
            $stmt->execute([
                ':nama'      => $form['nama'],
                ':deskripsi' => $form['deskripsi'] === '' ? null : $form['deskripsi'],
            ]);

            set_flash('success', 'Kategori "' . $form['nama'] . '" berhasil ditambahkan.');
            redirect('kategori.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? 0) === 1062) {
                $errors['nama'] = 'Nama kategori sudah terdaftar.';
            } else {
                $errors['umum'] = 'Kategori gagal disimpan. Silakan coba lagi.';
                error_log('Gagal menambah kategori: ' . $exception->getMessage());
            }
        }
    }
}

$pageTitle    = 'Tambah Kategori';
$pageSubtitle = 'Buat kelompok produk baru agar pencatatan lebih rapi.';
$activePage   = 'kategori';
$formAction   = 'kategori-tambah.php';
$isEdit       = false;

require __DIR__ . '/includes/header.php';
?>

<section class="panel form-panel">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-tag"></use></svg>
                Kategori Baru
            </h2>
            <p class="panel-subtitle">Kolom bertanda <span class="required-mark">*</span> wajib diisi.</p>
        </div>
        <a href="kategori.php" class="btn btn-soft btn-sm ms-auto">
            <svg class="icon" aria-hidden="true"><use href="#i-arrow-right"></use></svg>
            Daftar Kategori
        </a>
    </div>
    <div class="panel-body">
        <?php require __DIR__ . '/includes/form-kategori.php'; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
