<?php
/**
 * Formulir ubah kategori.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$id = $id !== false && $id !== null ? (int) $id : 0;

if ($id <= 0) {
    set_flash('danger', 'Kategori yang ingin diubah tidak ditemukan.');
    redirect('kategori.php');
}

$kategori = cari_kategori($pdo, $id);
if ($kategori === null) {
    set_flash('danger', 'Kategori yang ingin diubah tidak ditemukan.');
    redirect('kategori.php');
}

$stmtHitung = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE kategori_id = :id');
$stmtHitung->execute([':id' => $id]);
$jumlahProduk = (int) $stmtHitung->fetchColumn();

$errors = [];
$form = [
    'nama'      => (string) $kategori['nama'],
    'deskripsi' => (string) ($kategori['deskripsi'] ?? ''),
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
        $stmtCek = $pdo->prepare('SELECT COUNT(*) FROM kategori WHERE nama = :nama AND id <> :id');
        $stmtCek->execute([
            ':nama' => $form['nama'],
            ':id'   => $id,
        ]);
        if ((int) $stmtCek->fetchColumn() > 0) {
            $errors['nama'] = 'Nama kategori sudah dipakai kategori lain.';
        }
    }

    if (strlen($form['deskripsi']) > 500) {
        $errors['deskripsi'] = 'Deskripsi maksimal 500 karakter.';
    }

    if ($errors === []) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE kategori SET nama = :nama, deskripsi = :deskripsi WHERE id = :id'
            );
            $stmt->execute([
                ':nama'      => $form['nama'],
                ':deskripsi' => $form['deskripsi'] === '' ? null : $form['deskripsi'],
                ':id'        => $id,
            ]);

            set_flash('success', 'Kategori "' . $form['nama'] . '" berhasil diperbarui.');
            redirect('kategori.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? 0) === 1062) {
                $errors['nama'] = 'Nama kategori sudah dipakai kategori lain.';
            } else {
                $errors['umum'] = 'Perubahan kategori gagal disimpan. Silakan coba lagi.';
                error_log('Gagal mengubah kategori: ' . $exception->getMessage());
            }
        }
    }
}

$pageTitle    = 'Edit Kategori';
$pageSubtitle = 'Perbarui nama atau deskripsi kategori.';
$activePage   = 'kategori';
$formAction   = 'kategori-edit.php?id=' . $id;
$isEdit       = true;

require __DIR__ . '/includes/header.php';
?>

<section class="panel form-panel">
    <div class="panel-header">
        <div>
            <h2 class="panel-title">
                <svg class="icon" aria-hidden="true"><use href="#i-pencil"></use></svg>
                Edit Kategori
            </h2>
            <p class="panel-subtitle">
                <?= e($kategori['nama']) ?> &middot; dipakai <?= $jumlahProduk ?> produk
            </p>
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
