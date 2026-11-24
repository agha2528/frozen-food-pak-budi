-- ============================================================
--  Database Aplikasi Pencatatan Produk Frozen Food Pak Budi
--  DBMS   : MySQL / MariaDB
--  File   : database/database.sql
--  Catatan: Import lewat phpMyAdmin atau terminal:
--           mysql -u root -p < database/database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `db_frozen_food`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_frozen_food`;

-- Hapus tabel lama agar import ulang tidak bentrok (urutan: anak -> induk)
DROP TABLE IF EXISTS `riwayat_stok`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `supplier`;

-- ------------------------------------------------------------
-- Tabel: kategori
-- ------------------------------------------------------------
CREATE TABLE `kategori` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(100) NOT NULL,
  `deskripsi`  TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kategori_nama` (`nama`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: supplier
-- ------------------------------------------------------------
CREATE TABLE `supplier` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama`       VARCHAR(100) NOT NULL,
  `kontak`     VARCHAR(30) NULL,
  `alamat`     TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_supplier_nama` (`nama`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: produk
-- Setiap produk wajib terhubung ke satu kategori dan satu supplier.
-- ON DELETE RESTRICT menjaga data produk tetap aman ketika induknya
-- mencoba dihapus.
-- ------------------------------------------------------------
CREATE TABLE `produk` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_produk`  VARCHAR(120) NOT NULL,
  `kategori_id`  INT UNSIGNED NOT NULL,
  `supplier_id`  INT UNSIGNED NOT NULL,
  `harga`        DECIMAL(12, 2) NOT NULL,
  `stok`         INT UNSIGNED NOT NULL DEFAULT 0,
  `satuan`       VARCHAR(20) NOT NULL DEFAULT 'pack',
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_produk_nama` (`nama_produk`),
  KEY `idx_produk_kategori` (`kategori_id`),
  KEY `idx_produk_supplier` (`supplier_id`),
  CONSTRAINT `fk_produk_kategori`
    FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
  CONSTRAINT `fk_produk_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: riwayat_stok
-- Mencatat setiap penambahan, pengurangan, penyesuaian stok,
-- dan perubahan harga produk.
-- ------------------------------------------------------------
CREATE TABLE `riwayat_stok` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `produk_id`    INT UNSIGNED NOT NULL,
  `tipe`         ENUM('masuk', 'keluar', 'penyesuaian') NOT NULL DEFAULT 'masuk',
  `jumlah`       INT UNSIGNED NOT NULL DEFAULT 0,
  `stok_sebelum` INT UNSIGNED NOT NULL DEFAULT 0,
  `stok_sesudah` INT UNSIGNED NOT NULL DEFAULT 0,
  `harga_lama`   DECIMAL(12, 2) NULL,
  `harga_baru`   DECIMAL(12, 2) NULL,
  `keterangan`   VARCHAR(150) NULL,
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_riwayat_produk` (`produk_id`),
  KEY `idx_riwayat_tanggal` (`created_at`),
  CONSTRAINT `fk_riwayat_produk`
    FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- ============================================================
--  DATA CONTOH
-- ============================================================

INSERT INTO `kategori` (`id`, `nama`, `deskripsi`) VALUES
(1, 'Nugget & Sosis', 'Produk olahan ayam dan ikan berbentuk nugget, sosis, serta otak-otak.'),
(2, 'Seafood Beku', 'Udang, cumi, dan ikan fillet yang dibekukan dalam kondisi segar.'),
(3, 'Sayur & Buah Beku', 'Sayuran dan buah siap masak yang dibekukan untuk menjaga kesegaran.'),
(4, 'Makanan Siap Saji', 'Dimsum, katsu, bakso, dan lauk praktis yang tinggal dipanaskan.');

INSERT INTO `supplier` (`id`, `nama`, `kontak`, `alamat`) VALUES
(1, 'PT Frozen Nusantara', '021-55667788', 'Jl. Industri Raya No. 12, Jakarta Utara'),
(2, 'CV Laut Biru Seafood', '0812-3456-7890', 'Jl. Pelabuhan Muara Baru No. 8, Jakarta Utara'),
(3, 'PT Sayur Segar Abadi', '022-77889900', 'Jl. Soekarno-Hatta No. 45, Bandung'),
(4, 'UD Dapur Praktis', '0857-1122-3344', 'Jl. Raya Bogor KM 27, Depok');

INSERT INTO `produk`
  (`id`, `nama_produk`, `kategori_id`, `supplier_id`, `harga`, `stok`, `satuan`)
VALUES
(1,  'Nugget Ayam Premium 500g',      1, 1, 35000.00, 48, 'pack'),
(2,  'Sosis Ayam Cocktail 500g',      1, 1, 28000.00, 60, 'pack'),
(3,  'Nugget Ikan Dori 500g',         1, 2, 32000.00, 35, 'pack'),
(4,  'Otak-Otak Ikan Tenggiri 250g',  1, 2, 25000.00, 40, 'pack'),
(5,  'Udang Vaname Beku 500g',        2, 2, 55000.00, 25, 'pack'),
(6,  'Fillet Dori Beku 1kg',          2, 2, 48000.00, 30, 'pack'),
(7,  'Cumi Ring Beku 500g',           2, 2, 45000.00, 20, 'pack'),
(8,  'Mixed Vegetable 500g',          3, 3, 18000.00, 50, 'pack'),
(9,  'Edamame Beku 500g',             3, 3, 22000.00, 45, 'pack'),
(10, 'Kentang Goreng Shoestring 1kg', 3, 3, 30000.00, 55, 'pack'),
(11, 'Dimsum Ayam 500g',              4, 4, 40000.00, 38, 'pack'),
(12, 'Chicken Katsu 500g',            4, 4, 42000.00, 22, 'pack'),
(13, 'Bakso Sapi Frozen 500g',        4, 4, 38000.00,  9, 'pack'),
(14, 'Jagung Manis Beku 500g',        3, 3, 20000.00,  0, 'pack'),
(15, 'Sosis Sapi Smoked 500g',        1, 1, 33000.00, 18, 'pack');

-- Riwayat stok contoh: masuk, keluar, dan penyesuaian harga.
INSERT INTO `riwayat_stok`
  (`produk_id`, `tipe`, `jumlah`, `stok_sebelum`, `stok_sesudah`,
   `harga_lama`, `harga_baru`, `keterangan`, `created_at`)
VALUES
(1,  'masuk',       40,  0, 40, NULL,     35000.00, 'Stok awal produk', NOW() - INTERVAL 9 DAY),
(2,  'masuk',       60,  0, 60, NULL,     28000.00, 'Stok awal produk', NOW() - INTERVAL 8 DAY),
(12, 'masuk',       18,  0, 18, NULL,     40000.00, 'Stok awal produk', NOW() - INTERVAL 7 DAY),
(1,  'masuk',        8, 40, 48, 35000.00, 35000.00, 'Restock dari PT Frozen Nusantara', NOW() - INTERVAL 2 DAY),
(5,  'masuk',       30,  0, 30, NULL,     55000.00, 'Stok awal produk', NOW() - INTERVAL 5 DAY),
(5,  'keluar',       5, 30, 25, 55000.00, 55000.00, 'Penjualan harian', NOW() - INTERVAL 1 DAY),
(8,  'masuk',       50,  0, 50, NULL,     18000.00, 'Stok awal produk', NOW() - INTERVAL 4 DAY),
(13, 'masuk',       20,  0, 20, NULL,     38000.00, 'Stok awal produk', NOW() - INTERVAL 8 DAY),
(13, 'keluar',      11, 20,  9, 38000.00, 38000.00, 'Penjualan akhir pekan', NOW() - INTERVAL 6 HOUR),
(14, 'masuk',       12,  0, 12, NULL,     20000.00, 'Stok awal produk', NOW() - INTERVAL 3 DAY),
(14, 'keluar',      12, 12,  0, 20000.00, 20000.00, 'Stok habis terjual', NOW() - INTERVAL 8 HOUR),
(12, 'penyesuaian',  4, 18, 22, 40000.00, 42000.00, 'Restock dan penyesuaian harga', NOW() - INTERVAL 12 HOUR);

