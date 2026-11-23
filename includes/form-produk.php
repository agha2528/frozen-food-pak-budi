<?php
/**
 * Partial formulir produk, dipakai oleh produk-tambah.php dan produk-edit.php.
 *
 * Variabel yang dibutuhkan:
 * - $form          : nilai isian formulir
 * - $errors        : pesan kesalahan per kolom
 * - $formAction    : URL tujuan submit
 * - $daftarKategori, $daftarSupplier
 * - $isEdit        : true saat mengubah data
 */

$nilai = static fn (string $kunci): string => e($form[$kunci] ?? '');
$kelasInvalid = static fn (string $kunci): string => isset($errors[$kunci]) ? ' is-invalid' : '';
$pesanInvalid = static fn (string $kunci): string => isset($errors[$kunci])
    ? '<div class="invalid-feedback d-block">' . e($errors[$kunci]) . '</div>'
    : '';

$daftarSatuan = ['pack', 'pcs', 'kg', 'box', 'bal'];
if (!in_array($form['satuan'], $daftarSatuan, true)) {
    $daftarSatuan[] = $form['satuan'];
}
?>
<form method="post" action="<?= e($formAction) ?>" novalidate>
    <?= csrf_field() ?>

    <?php if (isset($errors['umum'])): ?>
        <div class="alert alert-danger app-alert" role="alert">
            <svg class="icon" aria-hidden="true"><use href="#i-alert"></use></svg>
            <span><?= e($errors['umum']) ?></span>
        </div>
    <?php endif; ?>

    <p class="form-section-title">Informasi Produk</p>

    <div class="row g-3">
        <div class="col-12">
            <label for="nama_produk" class="form-label">
                Nama produk <span class="required-mark">*</span>
            </label>
            <input type="text" class="form-control<?= $kelasInvalid('nama_produk') ?>"
                   id="nama_produk" name="nama_produk" maxlength="120"
                   value="<?= $nilai('nama_produk') ?>"
                   placeholder="Contoh: Nugget Ayam Premium 500g" autofocus>
            <?= $pesanInvalid('nama_produk') ?>
        </div>

        <div class="col-md-6">
            <label for="kategori_id" class="form-label">
                Kategori <span class="required-mark">*</span>
            </label>
            <select class="form-select<?= $kelasInvalid('kategori_id') ?>" id="kategori_id" name="kategori_id">
                <option value="">Pilih kategori</option>
                <?php foreach ($daftarKategori as $kategori): ?>
                    <option value="<?= (int) $kategori['id'] ?>"
                        <?= (string) $kategori['id'] === (string) $form['kategori_id'] ? 'selected' : '' ?>>
                        <?= e($kategori['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $pesanInvalid('kategori_id') ?>
            <?php if ($daftarKategori === []): ?>
                <div class="form-text">Belum ada kategori. <a href="kategori-tambah.php">Tambah kategori dulu</a>.</div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label for="supplier_id" class="form-label">
                Supplier <span class="required-mark">*</span>
            </label>
            <select class="form-select<?= $kelasInvalid('supplier_id') ?>" id="supplier_id" name="supplier_id">
                <option value="">Pilih supplier</option>
                <?php foreach ($daftarSupplier as $supplier): ?>
                    <option value="<?= (int) $supplier['id'] ?>"
                        <?= (string) $supplier['id'] === (string) $form['supplier_id'] ? 'selected' : '' ?>>
                        <?= e($supplier['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $pesanInvalid('supplier_id') ?>
            <?php if ($daftarSupplier === []): ?>
                <div class="form-text">Belum ada supplier. <a href="supplier-tambah.php">Tambah supplier dulu</a>.</div>
            <?php endif; ?>
        </div>
    </div>

    <p class="form-section-title mt-4">Harga dan Stok</p>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <label for="harga" class="form-label">
                Harga jual <span class="required-mark">*</span>
            </label>
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" class="form-control<?= $kelasInvalid('harga') ?>"
                       id="harga" name="harga" min="1" step="100"
                       value="<?= $nilai('harga') ?>" placeholder="35000">
            </div>
            <?= $pesanInvalid('harga') ?>
            <div class="form-text">Isi angka tanpa titik, contoh 35000.</div>
        </div>

        <div class="col-6 col-md-4">
            <label for="stok" class="form-label">
                Stok tersedia <span class="required-mark">*</span>
            </label>
            <input type="number" class="form-control<?= $kelasInvalid('stok') ?>"
                   id="stok" name="stok" min="0" step="1"
                   value="<?= $nilai('stok') ?>" placeholder="0">
            <?= $pesanInvalid('stok') ?>
        </div>

        <div class="col-6 col-md-4">
            <label for="satuan" class="form-label">
                Satuan <span class="required-mark">*</span>
            </label>
            <select class="form-select<?= $kelasInvalid('satuan') ?>" id="satuan" name="satuan">
                <?php foreach ($daftarSatuan as $satuan): ?>
                    <option value="<?= e($satuan) ?>" <?= $form['satuan'] === $satuan ? 'selected' : '' ?>>
                        <?= e(ucfirst($satuan)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $pesanInvalid('satuan') ?>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-brand">
            <svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg>
            <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' ?>
        </button>
        <a href="produk.php" class="btn btn-soft btn-batal">
            <svg class="icon" aria-hidden="true"><use href="#i-x"></use></svg>
            Batal
        </a>
    </div>
</form>
