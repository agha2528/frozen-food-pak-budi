<?php
/**
 * Kumpulan fungsi bantu untuk seluruh halaman aplikasi.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Mengubah teks menjadi aman saat ditampilkan di HTML.
 */
function e(mixed $nilai): string
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Mengambil nilai teks dari input POST lalu menghapus spasi di ujung.
 */
function input_teks(string $kunci): string
{
    $nilai = $_POST[$kunci] ?? '';

    return is_string($nilai) ? trim($nilai) : '';
}

/**
 * Mengarahkan pengguna ke halaman lain, lalu menghentikan eksekusi.
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Menyimpan pesan singkat untuk ditampilkan pada halaman berikutnya.
 */
function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = [
        'tipe'  => $tipe,
        'pesan' => $pesan,
    ];
}

/**
 * Mengambil sekaligus menghapus pesan flash dari session.
 */
function ambil_flash(): ?array
{
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Membuat token CSRF untuk formulir.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Menampilkan input hidden berisi token CSRF.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Memastikan permintaan POST berasal dari formulir aplikasi sendiri.
 */
function validasi_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        set_flash('danger', 'Sesi formulir tidak valid. Silakan coba lagi.');
        redirect('index.php');
    }
}

/**
 * Memformat angka menjadi rupiah, contoh: 35000 -> Rp 35.000.
 */
function rupiah(mixed $angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/**
 * Nama bulan dalam bahasa Indonesia.
 */
function nama_bulan(int $bulan): string
{
    $daftar = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    return $daftar[$bulan] ?? '';
}

/**
 * Tanggal hari ini dalam format Indonesia, contoh: 23 November 2026.
 */
function tanggal_hari_ini(): string
{
    return date('j') . ' ' . nama_bulan((int) date('n')) . ' ' . date('Y');
}

/**
 * Format tanggal dan jam dari database, contoh: 24 November 2026, 08:15.
 */
function tanggal_waktu_indonesia(?string $waktu): string
{
    if ($waktu === null || $waktu === '') {
        return '-';
    }

    $timestamp = strtotime($waktu);
    if ($timestamp === false) {
        return '-';
    }

    return date('j', $timestamp) . ' ' . nama_bulan((int) date('n', $timestamp))
        . ' ' . date('Y', $timestamp) . ', ' . date('H:i', $timestamp);
}

/**
 * Menampilkan selisih stok dengan tanda + atau -, contoh: +8 atau -5.
 */
function format_selisih_stok(int $stokSebelum, int $stokSesudah): string
{
    $selisih = $stokSesudah - $stokSebelum;

    return $selisih > 0 ? '+' . $selisih : (string) $selisih;
}

/**
 * Label ramah pengguna untuk tipe riwayat stok.
 */
function label_tipe_stok(string $tipe): string
{
    $label = [
        'masuk'       => 'Stok Masuk',
        'keluar'      => 'Stok Keluar',
        'penyesuaian' => 'Penyesuaian',
    ];

    return $label[$tipe] ?? 'Perubahan';
}

/**
 * Menentukan kelas warna dan label berdasarkan jumlah stok.
 *
 * @return array{kelas: string, label: string}
 */
function status_stok(int $stok): array
{
    if ($stok <= 0) {
        return ['kelas' => 'stok-habis', 'label' => 'Habis'];
    }

    if ($stok <= 10) {
        return ['kelas' => 'stok-menipis', 'label' => 'Menipis'];
    }

    return ['kelas' => 'stok-aman', 'label' => 'Tersedia'];
}

/**
 * Mengambil data kategori berdasarkan id.
 */
function cari_kategori(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, nama, deskripsi FROM kategori WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();

    return $data === false ? null : $data;
}

/**
 * Mengambil data supplier berdasarkan id.
 */
function cari_supplier(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, nama, kontak, alamat FROM supplier WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();

    return $data === false ? null : $data;
}

/**
 * Mengambil data produk berdasarkan id.
 */
function cari_produk(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT p.*, k.nama AS nama_kategori, s.nama AS nama_supplier
           FROM produk p
           JOIN kategori k ON k.id = p.kategori_id
           JOIN supplier s ON s.id = p.supplier_id
          WHERE p.id = :id
          LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch();

    return $data === false ? null : $data;
}
