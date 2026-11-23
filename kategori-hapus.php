<?php
/**
 * Proses hapus kategori. Kategori yang masih dipakai produk tidak dihapus.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('kategori.php');
}

validasi_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$id = $id !== false && $id !== null ? (int) $id : 0;

if ($id <= 0) {
    set_flash('danger', 'Data kategori yang ingin dihapus tidak valid.');
    redirect('kategori.php');
}

$kategori = cari_kategori($pdo, $id);
if ($kategori === null) {
    set_flash('danger', 'Kategori tidak ditemukan atau sudah dihapus.');
    redirect('kategori.php');
}

// Cegah penghapusan jika masih ada produk yang memakai kategori ini.
$stmtCek = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE kategori_id = :id');
$stmtCek->execute([':id' => $id]);
$jumlahProduk = (int) $stmtCek->fetchColumn();

if ($jumlahProduk > 0) {
    set_flash(
        'warning',
        'Kategori "' . $kategori['nama'] . '" tidak bisa dihapus karena masih digunakan oleh '
        . $jumlahProduk . ' produk. Pindahkan produk tersebut ke kategori lain terlebih dahulu.'
    );
    redirect('kategori.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM kategori WHERE id = :id');
    $stmt->execute([':id' => $id]);

    set_flash('success', 'Kategori "' . $kategori['nama'] . '" berhasil dihapus.');
} catch (PDOException $exception) {
    set_flash('danger', 'Kategori gagal dihapus karena masih terhubung dengan data produk.');
    error_log('Gagal menghapus kategori: ' . $exception->getMessage());
}

redirect('kategori.php');
