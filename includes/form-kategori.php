<?php
/**
 * Partial formulir kategori.
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
            Nama kategori <span class="required-mark">*</span>
        </label>
        <input type="text" class="form-control<?= $kelasInvalid('nama') ?>"
               id="nama" name="nama" maxlength="100"
               value="<?= e($form['nama']) ?>"
               placeholder="Contoh: Nugget &amp; Sosis" autofocus>
        <?= $pesanInvalid('nama') ?>
    </div>

    <div class="mb-4">
        <label for="deskripsi" class="form-label">Deskripsi</label>
        <textarea class="form-control<?= $kelasInvalid('deskripsi') ?>"
                  id="deskripsi" name="deskripsi" rows="4" maxlength="500"
                  placeholder="Jelaskan singkat jenis produk dalam kategori ini..."><?= e($form['deskripsi']) ?></textarea>
        <?= $pesanInvalid('deskripsi') ?>
        <div class="form-text">Opsional, maksimal 500 karakter.</div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-brand">
            <svg class="icon" aria-hidden="true"><use href="#i-check"></use></svg>
            <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Kategori' ?>
        </button>
        <a href="kategori.php" class="btn btn-soft btn-batal">
            <svg class="icon" aria-hidden="true"><use href="#i-x"></use></svg>
            Batal
        </a>
    </div>
</form>
