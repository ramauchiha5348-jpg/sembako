-- Database: db_prediksi_sembako
-- Dibuat untuk Sistem Prediksi Penjualan Toko Sembako

-- Database disesuaikan dengan environment (bisa 'railway' atau 'db_prediksi_sembako')
-- CREATE DATABASE dan USE dihapus agar kompatibel dengan Railway

-- 1. Tabel user
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed user admin (password: admin123)
INSERT INTO `user` (`id_user`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$e2iGkFgw2CIG.0xpTuObm.7YLtmJTKsDSqRjcuC1gRnIc28uQfbSW');

-- 2. Tabel produk
DROP TABLE IF EXISTS `produk`;
CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(100) NOT NULL,
  `satuan` varchar(30) NOT NULL,
  `harga` int(11) NOT NULL,
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed data produk
INSERT INTO `produk` (`id_produk`, `nama_produk`, `satuan`, `harga`) VALUES
(1, 'Beras Premium', 'Karung 10kg', 145000),
(2, 'Minyak Goreng Bimoli 2L', 'Pouch', 38000),
(3, 'Gula Pasir Gulaku 1kg', 'Pouch', 17500),
(4, 'Telur Ayam Ras', 'Peti 10kg', 270000),
(5, 'Mie Instan Indomie Goreng', 'Dus', 118000);

-- 3. Tabel penjualan
DROP TABLE IF EXISTS `penjualan`;
CREATE TABLE `penjualan` (
  `id_penjualan` int(11) NOT NULL AUTO_INCREMENT,
  `id_produk` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah_terjual` int(11) NOT NULL,
  PRIMARY KEY (`id_penjualan`),
  KEY `fk_penjualan_produk` (`id_produk`),
  CONSTRAINT `fk_penjualan_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed data penjualan untuk 5 bulan (Januari - Mei 2026)
-- Untuk kebutuhan MA-3 minimal dibutuhkan data 3 periode.
INSERT INTO `penjualan` (`id_produk`, `tanggal`, `jumlah_terjual`) VALUES
-- Beras Premium (Produk 1)
(1, '2026-01-15', 45),
(1, '2026-02-14', 52),
(1, '2026-03-18', 48),
(1, '2026-04-12', 60),
(1, '2026-05-16', 58),

-- Minyak Goreng Bimoli 2L (Produk 2)
(2, '2026-01-10', 110),
(2, '2026-02-12', 125),
(2, '2026-03-15', 115),
(2, '2026-04-18', 140),
(2, '2026-05-20', 135),

-- Gula Pasir Gulaku 1kg (Produk 3)
(3, '2026-01-05', 85),
(3, '2026-02-06', 90),
(3, '2026-03-08', 88),
(3, '2026-04-10', 95),
(3, '2026-05-12', 102),

-- Telur Ayam Ras (Produk 4)
(4, '2026-01-20', 25),
(4, '2026-02-22', 28),
(4, '2026-03-21', 30),
(4, '2026-04-25', 35),
(4, '2026-05-27', 32),

-- Mie Instan Indomie Goreng (Produk 5)
(5, '2026-01-25', 70),
(5, '2026-02-24', 75),
(5, '2026-03-27', 80),
(5, '2026-04-26', 85),
(5, '2026-05-28', 90);

-- 4. Tabel prediksi
DROP TABLE IF EXISTS `prediksi`;
CREATE TABLE `prediksi` (
  `id_prediksi` int(11) NOT NULL AUTO_INCREMENT,
  `id_produk` int(11) NOT NULL,
  `hasil_prediksi` double(8,2) NOT NULL,
  `periode` varchar(20) NOT NULL,
  PRIMARY KEY (`id_prediksi`),
  KEY `fk_prediksi_produk` (`id_produk`),
  CONSTRAINT `fk_prediksi_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed data prediksi awal untuk Juni 2026 (Prediksi bulan ke-6 berdasarkan MA-3 data Maret, April, Mei)
-- Beras: (48 + 60 + 58) / 3 = 55.33
-- Minyak: (115 + 140 + 135) / 3 = 130.00
-- Gula: (88 + 95 + 102) / 3 = 95.00
-- Telur: (30 + 35 + 32) / 3 = 32.33
-- Mie Instan: (80 + 85 + 90) / 3 = 85.00
INSERT INTO `prediksi` (`id_produk`, `hasil_prediksi`, `periode`) VALUES
(1, 55.33, '2026-06'),
(2, 130.00, '2026-06'),
(3, 95.00, '2026-06'),
(4, 32.33, '2026-06'),
(5, 85.00, '2026-06');
