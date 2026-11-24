<?php
/**
 * Bagian atas (head, sidebar, topbar, dan notifikasi) untuk semua halaman.
 * Variabel opsional: $pageTitle, $pageSubtitle, $activePage.
 */

$pageTitle    = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';
$activePage   = $activePage ?? '';
$flash        = ambil_flash();

$menuUtama = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'url' => 'index.php', 'icon' => 'i-grid'],
    ['key' => 'produk', 'label' => 'Produk', 'url' => 'produk.php', 'icon' => 'i-package'],
    ['key' => 'riwayat', 'label' => 'Riwayat Stok', 'url' => 'riwayat.php', 'icon' => 'i-history'],
    ['key' => 'kategori', 'label' => 'Kategori', 'url' => 'kategori.php', 'icon' => 'i-tag'],
    ['key' => 'supplier', 'label' => 'Supplier', 'url' => 'supplier.php', 'icon' => 'i-truck'],
];

$jumlahStokMenipis = 0;
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $jumlahStokMenipis = (int) $pdo->query('SELECT COUNT(*) FROM produk WHERE stok <= 10')->fetchColumn();
    } catch (PDOException $exception) {
        error_log('Gagal menghitung stok menipis: ' . $exception->getMessage());
    }
}

$tipeAlert = ['success', 'danger', 'warning', 'info'];
$flashTipe = $flash !== null && in_array($flash['tipe'] ?? '', $tipeAlert, true)
    ? $flash['tipe']
    : 'info';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi pencatatan produk frozen food Pak Budi">
    <title><?= e($pageTitle) ?> &middot; FrozenStock</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2">
</head>
<body>
<!-- Kumpulan ikon SVG agar tidak perlu library ikon tambahan -->
<svg xmlns="http://www.w3.org/2000/svg" class="ikon-sprite" aria-hidden="true">
    <symbol id="i-grid" viewBox="0 0 24 24">
        <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
        <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
        <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
        <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
    </symbol>
    <symbol id="i-package" viewBox="0 0 24 24">
        <path d="M21 8l-9-5-9 5v8l9 5 9-5V8z"></path>
        <path d="M3.3 7.9L12 12.5l8.7-4.6"></path>
        <path d="M12 22V12.5"></path>
    </symbol>
    <symbol id="i-tag" viewBox="0 0 24 24">
        <path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0l-7.2-7.2a2 2 0 0 1-.6-1.4V4a1 1 0 0 1 1-1h8a2 2 0 0 1 1.4.6l7.2 7.2a2 2 0 0 1 .2 1.6z"></path>
        <circle cx="7.5" cy="7.5" r="1.2"></circle>
    </symbol>
    <symbol id="i-truck" viewBox="0 0 24 24">
        <path d="M1 4h15v12H1z"></path>
        <path d="M16 8h4l3 3v5h-7V8z"></path>
        <circle cx="5.5" cy="18.5" r="2.5"></circle>
        <circle cx="18.5" cy="18.5" r="2.5"></circle>
        <path d="M8 18.5h8"></path>
    </symbol>
    <symbol id="i-menu" viewBox="0 0 24 24">
        <path d="M3 6h18M3 12h18M3 18h18"></path>
    </symbol>
    <symbol id="i-calendar" viewBox="0 0 24 24">
        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
        <path d="M16 3v4M8 3v4M3 10h18"></path>
    </symbol>
    <symbol id="i-plus" viewBox="0 0 24 24">
        <path d="M12 5v14M5 12h14"></path>
    </symbol>
    <symbol id="i-pencil" viewBox="0 0 24 24">
        <path d="M12 20h9"></path>
        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
    </symbol>
    <symbol id="i-trash" viewBox="0 0 24 24">
        <path d="M3 6h18"></path>
        <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"></path>
        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
        <path d="M10 11v6M14 11v6"></path>
    </symbol>
    <symbol id="i-search" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="7"></circle>
        <path d="M20 20l-3.5-3.5"></path>
    </symbol>
    <symbol id="i-x" viewBox="0 0 24 24">
        <path d="M18 6L6 18M6 6l12 12"></path>
    </symbol>
    <symbol id="i-snow" viewBox="0 0 24 24">
        <path d="M12 2v20M3.3 7l17.4 10M20.7 7L3.3 17"></path>
        <path d="M12 5l2.2-2.2M12 5L9.8 2.8M12 19l2.2 2.2M12 19l-2.2 2.2"></path>
        <path d="M5.4 8.5l-3-.4M5.4 8.5l.4-3M18.6 15.5l3 .4M18.6 15.5l-.4 3M5.4 15.5l-3 .4M5.4 15.5l.4 3M18.6 8.5l3-.4M18.6 8.5l-.4-3"></path>
    </symbol>
    <symbol id="i-arrow-right" viewBox="0 0 24 24">
        <path d="M5 12h14M13 6l6 6-6 6"></path>
    </symbol>
    <symbol id="i-alert" viewBox="0 0 24 24">
        <path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"></path>
        <path d="M12 9v4M12 17h.01"></path>
    </symbol>
    <symbol id="i-check" viewBox="0 0 24 24">
        <path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"></path>
        <path d="M22 4L12 14l-3-3"></path>
    </symbol>
    <symbol id="i-info" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"></circle>
        <path d="M12 16v-4M12 8h.01"></path>
    </symbol>
    <symbol id="i-wallet" viewBox="0 0 24 24">
        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path>
        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5"></path>
        <path d="M18 12a2 2 0 0 0 0 4h4v-4z"></path>
    </symbol>
    <symbol id="i-layers" viewBox="0 0 24 24">
        <path d="M12 2l9 5-9 5-9-5 9-5z"></path>
        <path d="M3 12l9 5 9-5"></path>
        <path d="M3 17l9 5 9-5"></path>
    </symbol>
    <symbol id="i-phone" viewBox="0 0 24 24">
        <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2.1z"></path>
    </symbol>
    <symbol id="i-pin" viewBox="0 0 24 24">
        <path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"></path>
        <circle cx="12" cy="10" r="3"></circle>
    </symbol>
    <symbol id="i-box-plus" viewBox="0 0 24 24">
        <path d="M21 8l-9-5-9 5v8l9 5 9-5V8z"></path>
        <path d="M3.3 7.9L12 12.5l8.7-4.6"></path>
        <path d="M12 22V12.5"></path>
        <path d="M17 13v6M14 16h6"></path>
    </symbol>
    <symbol id="i-history" viewBox="0 0 24 24">
        <path d="M3 12a9 9 0 1 0 2.6-6.4L3 8"></path>
        <path d="M3 3v5h5"></path>
        <path d="M12 7v5l3 2"></path>
    </symbol>
    <symbol id="i-arrow-down" viewBox="0 0 24 24">
        <path d="M12 5v14M6 13l6 6 6-6"></path>
    </symbol>
    <symbol id="i-arrow-up" viewBox="0 0 24 24">
        <path d="M12 19V5M6 11l6-6 6 6"></path>
    </symbol>
</svg>

<div class="app-shell">
    <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebarMenu" aria-label="Menu navigasi utama">
        <div class="offcanvas-body">
            <div class="sidebar-brand">
                <span class="brand-logo">
                    <svg class="icon" aria-hidden="true"><use href="#i-snow"></use></svg>
                </span>
                <span class="brand-text">
                    <span class="brand-name">FrozenStock</span>
                    <span class="brand-sub">Toko Pak Budi</span>
                </span>
                <button type="button" class="btn-close btn-close-white sidebar-close d-lg-none"
                        data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Tutup menu"></button>
            </div>

            <p class="sidebar-label">Menu Utama</p>
            <nav class="sidebar-nav">
                <?php foreach ($menuUtama as $menu): ?>
                    <a href="<?= e($menu['url']) ?>"
                       class="sidebar-link<?= $activePage === $menu['key'] ? ' active' : '' ?>"
                       <?= $activePage === $menu['key'] ? 'aria-current="page"' : '' ?>>
                        <svg class="icon" aria-hidden="true"><use href="#<?= e($menu['icon']) ?>"></use></svg>
                        <span><?= e($menu['label']) ?></span>
                        <?php if ($menu['key'] === 'produk' && $jumlahStokMenipis > 0): ?>
                            <span class="nav-badge" title="<?= $jumlahStokMenipis ?> produk stok menipis">
                                <?= $jumlahStokMenipis ?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-tip">
                <svg class="icon" aria-hidden="true"><use href="#i-info"></use></svg>
                <p><strong>Tips singkat</strong>Gunakan tombol stok di daftar produk untuk menambah stok atau mengubah harga dengan cepat.</p>
            </div>

            <div class="sidebar-user">
                <span class="avatar">PB</span>
                <span class="sidebar-user-text">
                    <strong>Pak Budi</strong>
                    <small>Pemilik Toko</small>
                </span>
            </div>
        </div>
    </aside>

    <div class="main-area">
        <header class="topbar">
            <button class="btn btn-menu d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"
                    aria-controls="sidebarMenu" aria-label="Buka menu">
                <svg class="icon" aria-hidden="true"><use href="#i-menu"></use></svg>
            </button>
            <div class="topbar-heading">
                <p class="topbar-eyebrow">Toko Frozen Food Pak Budi</p>
                <h1><?= e($pageTitle) ?></h1>
                <?php if ($pageSubtitle !== ''): ?>
                    <p class="topbar-subtitle"><?= e($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <div class="topbar-date d-none d-md-inline-flex">
                <svg class="icon" aria-hidden="true"><use href="#i-calendar"></use></svg>
                <span><?= e(tanggal_hari_ini()) ?></span>
            </div>
        </header>

        <main class="content">
            <?php if ($flash !== null): ?>
                <div class="alert alert-<?= e($flashTipe) ?> alert-dismissible fade show app-alert" role="alert">
                    <svg class="icon" aria-hidden="true">
                        <use href="#<?= $flashTipe === 'success' ? 'i-check' : ($flashTipe === 'danger' ? 'i-alert' : 'i-info') ?>"></use>
                    </svg>
                    <span><?= e($flash['pesan'] ?? '') ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>
