<?php
/**
 * Partial formulir supplier.
 *
 * Variabel yang dibutuhkan: $form, $errors, $formAction, $isEdit.
 */

$kelasInvalid = static fn (string $kunci): string => isset($errors[$kunci]) ? ' is-invalid' : '';
$pesanInvalid = static fn (string $kunci): string => isset($errors[$kunci])
    ? '<div class="invalid-feedback d-block">' . e($errors[$kunci]) . '</div>'
    : '';
?>
<form method="post" action="<?= e($formAction) ?>" novalidate>
    <?= csrf_field() ?>

    <?php if (isset($errors['umum'])): ?>
        <div class="alert alert-danger app-alert" role="alert">
            <svg class="icon" aria-hidden="true"><use href="#i-alert"></use></svg>
            <span><?= e($errors['umum']) ?></span>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="nama" class="form-label">
            Nama supplier <span class="required-mark">*</span>
        </label>
        <input type="text" class="form-control<?= $kelasInvalid('nama') ?>"
               id="nama" name="nama" maxlength="100"
               value="<?= e($form['nama']) ?>"
               placeholder="Contoh: PT Frozen Nusantara" autofocus>
        <?= $pesanInvalid('nama') ?>
    </div>

    <div class="mb-3">
        <label for="kontak" class="form-label">Nomor kontak</label>
        <input type="text" class="form-control<?= $kelasInvalid('kontak') ?>"
               id="kontak" name="kontak" maxlength="30"
               value="<?= e($form['kontak']) ?>"
               placeholder="Contoh: 021-55667788">
        <?= $pesanInvalid('kontak') ?>
        <div class="form-text">Opsional. Boleh berisi angka, spasi, tanda +, -, titik, dan kurung.</div>
    </div>

    <div class="mb-4">
        <label for="alamat" class="form-label">Alamat</label>
        <textarea class="form-control<?= $kelasInvalid('alamat') ?>"
                  id="alamat" name="alamat" rows="3" maxlength="255"
                  placeholder="Alamat lengkap supplier..."><?= e($form['alamat']) ?></textarea>
        <?= $pesanInvalid('alamat') ?>
        <div class="form-text">Opsional, maksimal 255 karakter.</div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-brand">
            <svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg>
            <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Supplier' ?>
        </button>
        <a href="supplier.php" class="btn btn-soft btn-batal">
            <svg class="icon" aria-hidden="true"><use href="#i-x"></use></svg>
            Batal
        </a>
    </div>
</form>
