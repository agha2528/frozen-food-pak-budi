<?php
/**
 * Proses hapus supplier. Supplier yang masih dipakai produk tidak dihapus.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('supplier.php');
}

validasi_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$id = $id !== false && $id !== null ? (int) $id : 0;

if ($id <= 0) {
    set_flash('danger', 'Data supplier yang ingin dihapus tidak valid.');
    redirect('supplier.php');
}

$supplier = cari_supplier($pdo, $id);
if ($supplier === null) {
    set_flash('danger', 'Supplier tidak ditemukan atau sudah dihapus.');
    redirect('supplier.php');
}

// Cegah penghapusan jika masih ada produk dari supplier ini.
$stmtCek = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE supplier_id = :id');
$stmtCek->execute([':id' => $id]);
$jumlahProduk = (int) $stmtCek->fetchColumn();

if ($jumlahProduk > 0) {
    set_flash(
        'warning',
        'Supplier "' . $supplier['nama'] . '" tidak bisa dihapus karena masih memasok '
        . $jumlahProduk . ' produk. Pindahkan produk tersebut ke supplier lain terlebih dahulu.'
    );
    redirect('supplier.php');
}

try {
    $stmt = $pdo->prepare('DELETE FROM supplier WHERE id = :id');
    $stmt->execute([':id' => $id]);

    set_flash('success', 'Supplier "' . $supplier['nama'] . '" berhasil dihapus.');
} catch (PDOException $exception) {
    set_flash('danger', 'Supplier gagal dihapus karena masih terhubung dengan data produk.');
    error_log('Gagal menghapus supplier: ' . $exception->getMessage());
}

redirect('supplier.php');
