CREATE DATABASE IF NOT EXISTS `ujikom_faktur`;
USE `ujikom_faktur`;

-- Disable foreign key checks to allow dropping and recreating tables cleanly
SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they already exist to avoid duplicate key or table exists errors
DROP TABLE IF EXISTS `detail_faktur`;
DROP TABLE IF EXISTS `faktur`;
DROP TABLE IF EXISTS `produk`;
DROP TABLE IF EXISTS `customer`;
DROP TABLE IF EXISTS `perusahaan`;

-- Table: perusahaan
-- id_perusahaan, nama_perusahaan, alamat, no_telp, fax
CREATE TABLE IF NOT EXISTS `perusahaan` (
  `id_perusahaan` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_perusahaan` VARCHAR(150) NOT NULL,
  `alamat` TEXT NOT NULL,
  `no_telp` VARCHAR(20) NOT NULL,
  `fax` VARCHAR(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: customer
-- id_customer, nama_customer, perusahaan_cust, alamat
CREATE TABLE IF NOT EXISTS `customer` (
  `id_customer` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_customer` VARCHAR(150) NOT NULL,
  `perusahaan_cust` VARCHAR(150) NOT NULL,
  `alamat` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: produk
-- id_produk, nama_produk, price, jenis, stock
CREATE TABLE IF NOT EXISTS `produk` (
  `id_produk` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_produk` VARCHAR(150) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `jenis` VARCHAR(50) NOT NULL,
  `stock` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: faktur
-- no_faktur, tgl_faktur, due_date, metode_bayar, ppn, dp, grand_total, user, id_customer (fk), id_perusahaan (fk)
CREATE TABLE IF NOT EXISTS `faktur` (
  `no_faktur` VARCHAR(50) PRIMARY KEY,
  `tgl_faktur` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `metode_bayar` VARCHAR(50) NOT NULL,
  `ppn` DECIMAL(5,2) DEFAULT 0.00,
  `dp` DECIMAL(12,2) DEFAULT 0.00,
  `grand_total` DECIMAL(12,2) NOT NULL,
  `user` VARCHAR(100) NOT NULL,
  `id_customer` INT NOT NULL,
  `id_perusahaan` INT NOT NULL,
  FOREIGN KEY (`id_customer`) REFERENCES `customer`(`id_customer`) ON DELETE CASCADE,
  FOREIGN KEY (`id_perusahaan`) REFERENCES `perusahaan`(`id_perusahaan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: detail_faktur
-- id_produk (fk), no_faktur (fk), qty, price
CREATE TABLE IF NOT EXISTS `detail_faktur` (
  `id_detail` INT AUTO_INCREMENT PRIMARY KEY,
  `id_produk` INT NOT NULL,
  `no_faktur` VARCHAR(50) NOT NULL,
  `qty` INT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`id_produk`) REFERENCES `produk`(`id_produk`) ON DELETE CASCADE,
  FOREIGN KEY (`no_faktur`) REFERENCES `faktur`(`no_faktur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Enable foreign key checks back
SET FOREIGN_KEY_CHECKS = 1;

-- Insert 5 Master Data Rows for Perusahaan
INSERT INTO `perusahaan` (`nama_perusahaan`, `alamat`, `no_telp`, `fax`) VALUES
('PT. Tech Inovasi Utama', 'Jl. Sudirman No. 12, Jakarta', '021-5551234', '021-5551235'),
('CV. Sumber Makmur', 'Jl. Merdeka No. 45, Bandung', '022-7778888', '022-7778889'),
('PT. Global Distribusi', 'Jl. Gatot Subroto No. 88, Surabaya', '031-4445555', '031-4445556'),
('UD. Berkah Abadi', 'Jl. Ahmad Yani No. 101, Semarang', '024-3332222', NULL),
('PT. Solusi Mandiri', 'Jl. Diponegoro No. 15, Yogyakarta', '0274-999888', '0274-999889');

-- Insert 5 Master Data Rows for Customer
INSERT INTO `customer` (`nama_customer`, `perusahaan_cust`, `alamat`) VALUES
('Budi Santoso', 'PT. Maju Bersama', 'Jl. Kebon Jeruk No. 5, Jakarta'),
('Ani Wijaya', 'CV. Prima Sentosa', 'Jl. Dago No. 12, Bandung'),
('Candra Kusuma', 'PT. Sinar Jaya', 'Jl. Pemuda No. 78, Surabaya'),
('Dewi Lestari', 'Toko Rejeki', 'Jl. Pahlawan No. 34, Semarang'),
('Eko Prasetyo', 'PT. Harapan Bangsa', 'Jl. Gejayan No. 22, Yogyakarta');

-- Insert 5 Master Data Rows for Produk
INSERT INTO `produk` (`nama_produk`, `price`, `jenis`, `stock`) VALUES
('Laptop Asus Zenbook', 15000000.00, 'Elektronik', 25),
('Mouse Wireless Logitech', 250000.00, 'Aksesoris', 100),
('Printer Epson L3210', 2800000.00, 'Elektronik', 15),
('Monitor Dell 24 Inch', 2100000.00, 'Elektronik', 30),
('Keyboard Mechanical Keychron', 1200000.00, 'Aksesoris', 40);

-- Insert 2 Transaction Data Rows (Faktur & Detail Faktur)
-- Transaction 1
INSERT INTO `faktur` (`no_faktur`, `tgl_faktur`, `due_date`, `metode_bayar`, `ppn`, `dp`, `grand_total`, `user`, `id_customer`, `id_perusahaan`) VALUES
('INV-2026-0001', '2026-08-10', '2026-08-25', 'Transfer Bank', 11.00, 5000000.00, 28194000.00, 'Admin Budi', 1, 1);

INSERT INTO `detail_faktur` (`id_produk`, `no_faktur`, `qty`, `price`) VALUES
(1, 'INV-2026-0001', 1, 15000000.00), -- 15,000,000
(3, 'INV-2026-0001', 2, 2800000.00),  -- 5,600,000
(5, 'INV-2026-0001', 4, 1200000.00);  -- 4,800,000

-- Transaction 2
INSERT INTO `faktur` (`no_faktur`, `tgl_faktur`, `due_date`, `metode_bayar`, `ppn`, `dp`, `grand_total`, `user`, `id_customer`, `id_perusahaan`) VALUES
('INV-2026-0002', '2026-08-12', '2026-09-12', 'Cash', 0.00, 0.00, 2600000.00, 'Admin Ani', 2, 2);

INSERT INTO `detail_faktur` (`id_produk`, `no_faktur`, `qty`, `price`) VALUES
(2, 'INV-2026-0002', 2, 250000.00), -- 500,000
(4, 'INV-2026-0002', 1, 2100000.00); -- 2,100,000
