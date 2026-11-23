<?php
/**
 * Konfigurasi koneksi database.
 * Sesuaikan DB_USER dan DB_PASS dengan pengaturan MySQL di komputer masing-masing.
 */

declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

define('DB_HOST', 'localhost');
define('DB_NAME', 'db_frozen_food');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$opsi = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opsi);
} catch (PDOException $exception) {
    error_log('Koneksi database gagal: ' . $exception->getMessage());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Koneksi Database Gagal</title>
        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                background: #eef8fc;
                color: #132b3a;
                font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            }
            .kotak {
                max-width: 560px;
                margin: 24px;
                padding: 32px;
                background: #ffffff;
                border: 1px solid #dbeaf1;
                border-radius: 16px;
                box-shadow: 0 18px 45px rgba(13, 56, 79, 0.12);
            }
            h1 { margin: 0 0 12px; font-size: 22px; }
            p { margin: 0 0 10px; line-height: 1.6; }
            code {
                padding: 2px 6px;
                background: #eef8fc;
                border-radius: 6px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="kotak">
            <h1>Koneksi Database Gagal</h1>
            <p>Aplikasi tidak bisa terhubung ke database <code><?= htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') ?></code>.</p>
            <p>Pastikan MySQL/MariaDB sudah berjalan dan database sudah diimpor dari file <code>database/database.sql</code>.</p>
            <p>Periksa juga pengaturan <code>DB_USER</code> dan <code>DB_PASS</code> di <code>config/koneksi.php</code>.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
