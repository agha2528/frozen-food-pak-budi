<?php
/**
 * Proses hapus produk. Hanya menerima permintaan POST.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('produk.php');
}

validasi_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$id = $id !== false && $id !== null ? (int) $id : 0;

if ($id <= 0) {
    set_flash('danger', 'Data produk yang ingin dihapus tidak valid.');
    redirect('produk.php');
}

$produk = cari_produk($pdo, $id);
if ($produk === null) {
    set_flash('danger', 'Produk tidak ditemukan atau sudah dihapus.');
    redirect('produk.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM produk WHERE id = :id');
    $stmt->execute([':id' => $id]);

    set_flash('success', 'Produk "' . $produk['nama_produk'] . '" berhasil dihapus.');
} catch (PDOException $exception) {
    set_flash('danger', 'Produk gagal dihapus karena masih terhubung dengan data lain.');
    error_log('Gagal menghapus produk: ' . $exception->getMessage());
}

redirect('produk.php');
