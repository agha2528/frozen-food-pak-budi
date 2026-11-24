<?php
/**
 * Proses cepat untuk menambah/mengurangi stok dan mengubah harga produk.
 * Setiap perubahan dicatat ke tabel riwayat_stok.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('produk.php');
}

validasi_csrf();

$tujuan = input_teks('tujuan');
if (!in_array($tujuan, ['index.php', 'produk.php', 'riwayat.php'], true)) {
    $tujuan = 'produk.php';
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$id = $id !== false && $id !== null ? (int) $id : 0;

if ($id <= 0) {
    set_flash('danger', 'Produk yang ingin diperbarui tidak valid.');
    redirect($tujuan);
}

$produk = cari_produk($pdo, $id);
if ($produk === null) {
    set_flash('danger', 'Produk tidak ditemukan atau sudah dihapus.');
    redirect($tujuan);
}

$aksi = input_teks('aksi');
if (!in_array($aksi, ['tambah', 'kurang'], true)) {
    $aksi = 'tambah';
}

$jumlahInput = filter_input(INPUT_POST, 'jumlah', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 0],
]);
$jumlah = $jumlahInput !== false && $jumlahInput !== null ? (int) $jumlahInput : null;

$hargaInput = input_teks('harga');
$keterangan = input_teks('keterangan');

if ($jumlah === null) {
    set_flash('danger', 'Jumlah stok harus berupa angka bulat 0 atau lebih.');
    redirect($tujuan);
}

if ($hargaInput === '' || !is_numeric($hargaInput)) {
    set_flash('danger', 'Harga jual harus berupa angka.');
    redirect($tujuan);
}

$hargaBaru = (float) $hargaInput;
if ($hargaBaru <= 0 || $hargaBaru > 999999999.99) {
    set_flash('danger', 'Harga jual harus lebih dari 0 dan tidak terlalu besar.');
    redirect($tujuan);
}

if (strlen($keterangan) > 150) {
    set_flash('danger', 'Keterangan maksimal 150 karakter.');
    redirect($tujuan);
}

$stokLama = (int) $produk['stok'];
$hargaLama = (float) $produk['harga'];

if ($aksi === 'kurang') {
    if ($jumlah > $stokLama) {
        set_flash(
            'danger',
            'Stok tidak cukup. Stok "' . $produk['nama_produk'] . '" saat ini hanya '
            . $stokLama . ' ' . $produk['satuan'] . '.'
        );
        redirect($tujuan);
    }
    $stokBaru = $stokLama - $jumlah;
} else {
    $stokBaru = $stokLama + $jumlah;
}

$hargaBerubah = abs($hargaBaru - $hargaLama) > 0.001;

if ($jumlah === 0 && !$hargaBerubah) {
    set_flash('warning', 'Tidak ada perubahan stok atau harga yang disimpan.');
    redirect($tujuan);
}

// Tentukan tipe riwayat dan keterangan bawaan.
if ($jumlah === 0) {
    $tipe = 'penyesuaian';
    $keteranganBawaan = 'Perubahan harga';
} elseif ($aksi === 'tambah') {
    $tipe = 'masuk';
    $keteranganBawaan = 'Penambahan stok';
} else {
    $tipe = 'keluar';
    $keteranganBawaan = 'Pengurangan stok';
}

if ($keterangan === '') {
    $keterangan = $keteranganBawaan;
}

try {
    $pdo->beginTransaction();

    $stmtUpdate = $pdo->prepare('UPDATE produk SET stok = :stok, harga = :harga WHERE id = :id');
    $stmtUpdate->execute([
        ':stok'  => $stokBaru,
        ':harga' => $hargaBaru,
        ':id'    => $id,
    ]);

    $stmtRiwayat = $pdo->prepare(
        'INSERT INTO riwayat_stok
            (produk_id, tipe, jumlah, stok_sebelum, stok_sesudah, harga_lama, harga_baru, keterangan)
         VALUES
            (:produk_id, :tipe, :jumlah, :stok_sebelum, :stok_sesudah, :harga_lama, :harga_baru, :keterangan)'
    );
    $stmtRiwayat->execute([
        ':produk_id'    => $id,
        ':tipe'         => $tipe,
        ':jumlah'       => $jumlah,
        ':stok_sebelum' => $stokLama,
        ':stok_sesudah' => $stokBaru,
        ':harga_lama'   => $hargaLama,
        ':harga_baru'   => $hargaBaru,
        ':keterangan'   => $keterangan,
    ]);

    $pdo->commit();
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Gagal memperbarui stok produk: ' . $exception->getMessage());
    set_flash('danger', 'Stok dan harga gagal diperbarui. Silakan coba lagi.');
    redirect($tujuan);
}

$pesan = 'Produk "' . $produk['nama_produk'] . '" berhasil diperbarui.';
if ($jumlah > 0) {
    $pesan .= ' Stok ' . ($aksi === 'tambah' ? 'bertambah ' : 'berkurang ') . $jumlah
        . ' ' . $produk['satuan'] . '.';
}
if ($hargaBerubah) {
    $pesan .= ' Harga jual sekarang ' . rupiah($hargaBaru) . '.';
}
$pesan .= ' Stok saat ini ' . $stokBaru . ' ' . $produk['satuan'] . '.';

set_flash('success', $pesan);
redirect($tujuan);
